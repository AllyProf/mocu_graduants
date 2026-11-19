<?php

namespace App\Services;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Builds and caches Graduands / Best Students PDF reports on disk.
 *
 * Full (unfiltered) reports are pre-warmed for near-instant downloads.
 * Filtered reports are generated once per filter set + data version, then reused.
 */
class ListPdfCacheService
{
    public const TYPE_GRADUANDS = 'graduands';

    public const TYPE_BEST_STUDENTS = 'best-students';

    /**
     * Return a cached PDF download, generating the file on cache miss.
     *
     * Cache hits skip DomPDF and student-list work — only a file serve.
     */
    public function download(string $type, Request $request, GraduationData $data): BinaryFileResponse
    {
        $filters = $this->normalizeFilters($request);
        $meta = $this->metaFor($type);
        $path = $this->cachePath($type, $filters);

        if (! is_file($path)) {
            $rows = $this->rowsFor($type, $data, $filters);
            $this->writePdf($path, $meta, $rows, $filters);
        }

        return response()->download($path, $meta['filename'], [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    /**
     * Pre-generate the full (unfiltered) PDFs used by most taps.
     *
     * @return array<string, string> map of type => absolute file path
     */
    public function warmFullReports(GraduationData $data): array
    {
        $built = [];

        foreach ([self::TYPE_GRADUANDS, self::TYPE_BEST_STUDENTS] as $type) {
            $filters = $this->normalizeFilters(new Request());
            $rows = $this->rowsFor($type, $data, $filters);
            $meta = $this->metaFor($type);
            $path = $this->cachePath($type, $filters);
            $stable = $this->stableFullPath($type);

            $this->writePdf($path, $meta, $rows, $filters);
            File::ensureDirectoryExists(dirname($stable));
            File::copy($path, $stable);

            $built[$type] = $stable;
        }

        return $built;
    }

    /**
     * Delete all cached PDFs (call after bulk student imports).
     */
    public function clear(): void
    {
        $root = $this->cacheRoot();
        if (is_dir($root)) {
            File::deleteDirectory($root);
        }
        File::ensureDirectoryExists($root);
    }

    /**
     * @return array{q: string, level: string, programme: string, year: string}
     */
    public function normalizeFilters(Request $request): array
    {
        return [
            'q' => Str::lower(trim((string) $request->query('q', ''))),
            'level' => trim((string) $request->query('level', '')),
            'programme' => trim((string) $request->query('programme', '')),
            'year' => trim((string) $request->query('year', '')),
        ];
    }

    /**
     * Data version changes when student rows change, invalidating old cache keys.
     */
    public function dataVersion(): string
    {
        $stamp = Student::query()->max('updated_at');

        return $stamp ? (string) strtotime((string) $stamp) : '0';
    }

    protected function cacheRoot(): string
    {
        return storage_path('app/graduation/pdf-cache');
    }

    /**
     * Stable public-ish path for the full unfiltered report (easy CDN/sync target).
     */
    protected function stableFullPath(string $type): string
    {
        $name = $type === self::TYPE_BEST_STUDENTS
            ? 'mocu-best-students.pdf'
            : 'mocu-graduands.pdf';

        return $this->cacheRoot().DIRECTORY_SEPARATOR.'full'.DIRECTORY_SEPARATOR.$name;
    }

    /**
     * @param  array{q: string, level: string, programme: string, year: string}  $filters
     */
    protected function cachePath(string $type, array $filters): string
    {
        $key = hash('sha256', json_encode([
            'type' => $type,
            'filters' => $filters,
            'version' => $this->dataVersion(),
        ], JSON_THROW_ON_ERROR));

        $dir = $this->cacheRoot().DIRECTORY_SEPARATOR.$type;

        return $dir.DIRECTORY_SEPARATOR.$key.'.pdf';
    }

    /**
     * @param  array{q: string, level: string, programme: string, year: string}  $filters
     */
    protected function rowsFor(string $type, GraduationData $data, array $filters): Collection
    {
        $source = $type === self::TYPE_BEST_STUDENTS
            ? $data->bestStudents()
            : $data->graduands();

        return $this->filterPeople($source, $filters);
    }

    /**
     * @return array{title: string, eventTitle: string, sectionTitle: string, filename: string, showYear: bool, showAward: bool}
     */
    protected function metaFor(string $type): array
    {
        if ($type === self::TYPE_BEST_STUDENTS) {
            return [
                'title' => 'Best Students Report',
                'eventTitle' => 'Graduation Ceremony · 11 December 2026',
                'sectionTitle' => 'Best Students List',
                'filename' => 'mocu-best-students.pdf',
                'showYear' => true,
                'showAward' => true,
            ];
        }

        return [
            'title' => 'Graduands Ceremony Report',
            'eventTitle' => 'Graduation Ceremony · 11 December 2026',
            'sectionTitle' => 'Graduands List',
            'filename' => 'mocu-graduands.pdf',
            'showYear' => false,
            'showAward' => true,
        ];
    }

    /**
     * @param  array{title: string, eventTitle: string, sectionTitle: string, filename: string, showYear: bool, showAward: bool}  $meta
     * @param  array{q: string, level: string, programme: string, year: string}  $filters
     */
    protected function writePdf(string $path, array $meta, Collection $rows, array $filters): void
    {
        File::ensureDirectoryExists(dirname($path));

        $pdf = Pdf::loadView('SmartGradu_App.pdf.people-list', [
            'title' => $meta['title'],
            'eventTitle' => $meta['eventTitle'],
            'sectionTitle' => $meta['sectionTitle'],
            'rows' => $rows,
            'stats' => $this->buildStats($rows),
            'filterSummary' => $this->filterSummary($filters),
            'generatedAt' => now()->format('d M Y H:i'),
            'showYear' => $meta['showYear'],
            'showAward' => $meta['showAward'],
            'logoSrc' => $this->logoDataUri(),
        ])->setPaper('a4', 'portrait');

        file_put_contents($path, $pdf->output());
    }

    /**
     * @param  array{q: string, level: string, programme: string, year: string}  $filters
     */
    protected function filterPeople(Collection $rows, array $filters): Collection
    {
        $q = $filters['q'];
        $level = $filters['level'];
        $programme = $filters['programme'];
        $year = $filters['year'];

        return $rows
            ->filter(function ($row) use ($q, $level, $programme, $year) {
                $hay = Str::lower(implode(' ', array_filter([
                    $row->full_name ?? '',
                    $row->programme_short ?? '',
                    $row->programme ?? '',
                    $row->gender ?? '',
                    $row->year_of_study ?? '',
                ])));

                if ($q !== '' && ! str_contains($hay, $q)) {
                    return false;
                }

                if ($level !== '' && strcasecmp((string) ($row->study_level ?? ''), $level) !== 0) {
                    return false;
                }

                if ($year !== '' && strcasecmp((string) ($row->year_of_study ?? ''), $year) !== 0) {
                    return false;
                }

                if ($programme !== '') {
                    $rowProgramme = (string) ($row->programme_short ?? '');
                    $isOverall = ($row->best_award ?? '') === 'overall';
                    $rowYear = Str::lower((string) ($row->year_of_study ?? ''));

                    if (strcasecmp($rowProgramme, $programme) !== 0
                        && ! ($isOverall && ($year === '' || $rowYear === 'third year'))) {
                        return false;
                    }
                }

                return true;
            })
            ->values();
    }

    protected function buildStats(Collection $rows): array
    {
        return [
            'total' => $rows->count(),
            'male' => $rows->filter(fn ($row) => strcasecmp((string) ($row->gender ?? ''), 'Male') === 0)->count(),
            'female' => $rows->filter(fn ($row) => strcasecmp((string) ($row->gender ?? ''), 'Female') === 0)->count(),
            'best' => $rows->filter(fn ($row) => ! empty($row->is_best_student))->count(),
            'programmes' => $rows->pluck('programme_short')->filter()->unique()->count(),
            'levels' => $rows->pluck('study_level')->filter()->unique()->count(),
        ];
    }

    /**
     * @param  array{q: string, level: string, programme: string, year: string}  $filters
     */
    protected function filterSummary(array $filters): string
    {
        $parts = [];
        $labels = ['level' => 'Level', 'programme' => 'Programme', 'year' => 'Year', 'q' => 'Search'];

        foreach ($labels as $key => $label) {
            if (($filters[$key] ?? '') !== '') {
                $parts[] = $label.': '.$filters[$key];
            }
        }

        return implode(' · ', $parts);
    }

    protected function logoDataUri(): string
    {
        $path = public_path('SmartGradu_App/assets/img/mocu_logo_clear.png');

        if (! is_file($path)) {
            $path = public_path('SmartGradu_App/assets/img/mocu_logo_nav.png');
        }

        if (! is_file($path)) {
            return '';
        }

        $mime = str_ends_with(strtolower($path), '.png') ? 'image/png' : 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }
}
