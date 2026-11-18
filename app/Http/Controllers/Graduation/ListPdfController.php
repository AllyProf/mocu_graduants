<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Services\GraduationData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Generates downloadable PDF reports for graduands and best students.
 *
 * Query filters (q, level, programme, year) mirror the on-page list filters
 * so the exported PDF matches what the user is viewing.
 */
class ListPdfController extends Controller
{
    /**
     * Export the graduands list as PDF.
     */
    public function graduands(Request $request, GraduationData $data)
    {
        $rows = $this->filterPeople($data->graduands(), $request);

        return $this->downloadPdf(
            title: 'Graduands Ceremony Report',
            eventTitle: 'Graduation Ceremony · 11 December 2026',
            sectionTitle: 'Graduands List',
            rows: $rows,
            filename: 'mocu-graduands.pdf',
            showYear: false,
            showAward: true,
            request: $request,
        );
    }

    /**
     * Export the best-students list as PDF (includes year of study).
     */
    public function bestStudents(Request $request, GraduationData $data)
    {
        $rows = $this->filterPeople($data->bestStudents(), $request);

        return $this->downloadPdf(
            title: 'Best Students Report',
            eventTitle: 'Graduation Ceremony · 11 December 2026',
            sectionTitle: 'Best Students List',
            rows: $rows,
            filename: 'mocu-best-students.pdf',
            showYear: true,
            showAward: true,
            request: $request,
        );
    }

    /**
     * Apply the same client-side filter rules on the server for PDF export.
     */
    protected function filterPeople(Collection $rows, Request $request): Collection
    {
        $q = Str::lower(trim((string) $request->query('q', '')));
        $level = trim((string) $request->query('level', ''));
        $programme = trim((string) $request->query('programme', ''));
        $year = trim((string) $request->query('year', ''));

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

                // Overall best remains visible for Third Year when a programme is selected.
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

    /**
     * Summary counters shown at the top of the PDF report.
     */
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
     * Human-readable filter line embedded in the PDF metadata.
     */
    protected function filterSummary(Request $request): string
    {
        $parts = [];

        foreach (['level' => 'Level', 'programme' => 'Programme', 'year' => 'Year', 'q' => 'Search'] as $key => $label) {
            $value = trim((string) $request->query($key, ''));
            if ($value !== '') {
                $parts[] = $label.': '.$value;
            }
        }

        return implode(' · ', $parts);
    }

    /**
     * Embed MoCU logo as a data-URI so DomPDF can render it offline.
     */
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

    /**
     * Render the Blade PDF template and return a file download response.
     */
    protected function downloadPdf(
        string $title,
        string $eventTitle,
        string $sectionTitle,
        Collection $rows,
        string $filename,
        bool $showYear,
        bool $showAward,
        Request $request,
    ) {
        $pdf = Pdf::loadView('SmartGradu_App.pdf.people-list', [
            'title' => $title,
            'eventTitle' => $eventTitle,
            'sectionTitle' => $sectionTitle,
            'rows' => $rows,
            'stats' => $this->buildStats($rows),
            'filterSummary' => $this->filterSummary($request),
            'generatedAt' => now()->format('d M Y H:i'),
            'showYear' => $showYear,
            'showAward' => $showAward,
            'logoSrc' => $this->logoDataUri(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
