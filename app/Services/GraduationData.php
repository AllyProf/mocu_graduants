<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Graduation data access layer for MoCU Graduation Companion.
 *
 * All ceremony content is read from JSON under storage/app/graduation/.
 */
class GraduationData
{
    /** @var list<string> */
    protected array $preferredStudyLevels = [
        'PhD',
        'Masters',
        'Postgraduate Diploma',
        'Bachelor Degree',
        'Diploma',
        'Ordinary Diploma',
        'Certificate',
    ];

    /** @var Collection<int, object>|null */
    protected ?Collection $graduandsCache = null;

    /**
     * Absolute path to a graduation JSON file.
     */
    protected function path(string $file): string
    {
        return storage_path('app/graduation/'.$file);
    }

    /**
     * Read and decode a JSON file. Missing/invalid files return [].
     */
    protected function read(string $file): mixed
    {
        $path = $this->path($file);

        if (! is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return $decoded ?? [];
    }

    /**
     * Write a JSON file atomically.
     */
    protected function write(string $file, mixed $data): void
    {
        $path = $this->path($file);
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Full graduands list from graduands.json, ordered by level then name.
     */
    public function graduands(): Collection
    {
        if ($this->graduandsCache !== null) {
            return $this->graduandsCache;
        }

        $this->graduandsCache = collect($this->read('graduands.json'))
            ->map(fn (array $row) => (object) $row)
            ->sort(function ($a, $b) {
                $levelCmp = $this->studyLevelRank($a->study_level ?? null)
                    <=> $this->studyLevelRank($b->study_level ?? null);

                if ($levelCmp !== 0) {
                    return $levelCmp;
                }

                return strnatcasecmp((string) ($a->full_name ?? ''), (string) ($b->full_name ?? ''));
            })
            ->values();

        return $this->graduandsCache;
    }

    /**
     * Canonical study-level order used by filters and sorting (PhD → Certificate).
     * Includes levels from the MoCU catalogue even when no graduands are loaded yet.
     */
    public function studyLevels(): Collection
    {
        $found = $this->graduands()
            ->pluck('study_level')
            ->filter()
            ->unique()
            ->values();

        $fromCatalogue = $this->programmeCatalogue()
            ->pluck('level')
            ->filter()
            ->unique()
            ->values();

        $available = $found->merge($fromCatalogue)->unique()->values();

        return collect($this->preferredStudyLevels)
            ->filter(fn ($level) => $available->contains($level))
            ->values()
            ->merge($available->diff($this->preferredStudyLevels)->values())
            ->values();
    }

    /**
     * Programme short codes grouped by study level (for cascading filters).
     *
     * @return array<string, list<string>>
     */
    public function programmesByLevel(): array
    {
        $grouped = [];

        foreach ($this->programmeCatalogue() as $row) {
            $level = trim((string) ($row->level ?? ''));
            $short = trim((string) ($row->short ?? ''));
            if ($level === '' || $short === '') {
                continue;
            }
            $grouped[$level] ??= [];
            if (! in_array($short, $grouped[$level], true)) {
                $grouped[$level][] = $short;
            }
        }

        foreach ($grouped as $level => $codes) {
            natcasesort($codes);
            $grouped[$level] = array_values($codes);
        }

        return $grouped;
    }

    /**
     * Numeric rank for study level sorting (unknown levels go last).
     */
    public function studyLevelRank(?string $level): int
    {
        $rank = array_search($level, $this->preferredStudyLevels, true);

        return $rank === false ? 999 : (int) $rank;
    }

    /**
     * Year-of-study options (used on Best Students filters).
     */
    public function yearsOfStudy(): Collection
    {
        $preferred = ['First Year', 'Second Year', 'Third Year', 'Fourth Year'];

        $found = $this->graduands()
            ->pluck('year_of_study')
            ->filter()
            ->unique()
            ->values();

        return collect($preferred)
            ->filter(fn ($year) => $found->contains($year))
            ->values()
            ->merge($found->diff($preferred)->sort()->values())
            ->values();
    }

    /**
     * Official MoCU programme catalogue (programmes.json), merged with any
     * codes found only in graduands.json so filters/gallery stay complete.
     */
    public function programmeCatalogue(?string $studyLevel = null): Collection
    {
        $fromFile = collect($this->read('programmes.json'))
            ->filter(fn ($row) => is_array($row))
            ->map(fn (array $row) => (object) [
                'short' => trim((string) ($row['short'] ?? '')),
                'name' => trim((string) ($row['name'] ?? '')),
                'level' => trim((string) ($row['level'] ?? '')),
                'duration' => trim((string) ($row['duration'] ?? '')),
                'delivery' => trim((string) ($row['delivery'] ?? '')),
                'session' => trim((string) ($row['session'] ?? '')),
            ])
            ->filter(fn ($row) => $row->short !== '');

        $fromGraduands = $this->graduands()
            ->map(fn ($row) => (object) [
                'short' => trim((string) ($row->programme_short ?? '')),
                'name' => trim((string) ($row->programme ?? '')),
                'level' => trim((string) ($row->study_level ?? '')),
                'duration' => '',
                'delivery' => '',
                'session' => '',
            ])
            ->filter(fn ($row) => $row->short !== '');

        $merged = $fromFile
            ->concat($fromGraduands)
            ->unique(fn ($row) => strtolower($row->short))
            ->values();

        if ($studyLevel) {
            $merged = $merged->filter(
                fn ($row) => strcasecmp((string) $row->level, $studyLevel) === 0
            )->values();
        }

        $levelRank = fn ($row) => $this->studyLevelRank($row->level ?: null);

        return $merged
            ->sort(function ($a, $b) use ($levelRank) {
                $cmp = $levelRank($a) <=> $levelRank($b);
                if ($cmp !== 0) {
                    return $cmp;
                }

                return strnatcasecmp((string) $a->short, (string) $b->short);
            })
            ->values();
    }

    /**
     * Programme short-code list for filter dropdowns / gallery picker.
     */
    public function programmes(?string $studyLevel = null): Collection
    {
        return $this->programmeCatalogue($studyLevel)
            ->pluck('short')
            ->filter()
            ->values();
    }

    /**
     * Programme options with short code + full name (for gallery picker labels).
     */
    public function programmeOptions(): Collection
    {
        return $this->programmeCatalogue()
            ->map(fn ($row) => (object) [
                'short' => (string) $row->short,
                'name' => (string) ($row->name !== '' ? $row->name : $row->short),
            ])
            ->values();
    }

    /**
     * Best-student subset: overall award first, then by GPA descending.
     */
    public function bestStudents(): Collection
    {
        return $this->graduands()
            ->filter(fn ($row) => ! empty($row->is_best_student))
            ->sort(function ($a, $b) {
                $aOverall = ($a->best_award ?? '') === 'overall' ? 0 : 1;
                $bOverall = ($b->best_award ?? '') === 'overall' ? 0 : 1;

                if ($aOverall !== $bOverall) {
                    return $aOverall <=> $bOverall;
                }

                return ((float) ($b->gpa ?? 0)) <=> ((float) ($a->gpa ?? 0));
            })
            ->values();
    }

    /**
     * Ceremony timetable events sorted by start time.
     */
    public function timetable(): Collection
    {
        return collect($this->read('timetable.json'))
            ->map(fn (array $row) => (object) $row)
            ->sortBy('starts_at')
            ->values();
    }

    /**
     * Distinct timetable event types for filters.
     */
    public function eventTypes(): Collection
    {
        return $this->timetable()->pluck('event_type')->unique()->sort()->values();
    }

    /**
     * Campus / venue locations for the venue guide.
     */
    public function locations(): Collection
    {
        return collect($this->read('locations.json'))
            ->map(fn (array $row) => (object) $row)
            ->values();
    }

    /**
     * University leaders for the Leaders page.
     */
    public function leaders(): Collection
    {
        return collect($this->read('leaders.json'))
            ->map(fn (array $row) => (object) $row)
            ->values();
    }

    /**
     * Gallery memories (newest first).
     */
    public function gallery(): Collection
    {
        return collect($this->read('gallery.json'))
            ->map(fn (array $row) => (object) $row)
            ->sortByDesc(fn ($row) => $row->created_at ?? '')
            ->values();
    }

    /**
     * Gallery memories grouped by programme (uploads for the same programme merge).
     */
    public function galleryByProgramme(): Collection
    {
        return $this->gallery()
            ->groupBy(fn ($row) => (string) ($row->programme ?? ''))
            ->filter(fn ($items, $programme) => $programme !== '')
            ->map(function (Collection $items, string $programme) {
                $images = $items
                    ->flatMap(fn ($row) => collect($row->images ?? []))
                    ->filter()
                    ->values()
                    ->all();

                return (object) [
                    'programme' => $programme,
                    'programme_name' => (string) ($items->first()->programme_name ?? ''),
                    'images' => $images,
                    'count' => count($images),
                    'updated_at' => (string) ($items->first()->created_at ?? ''),
                ];
            })
            ->sortBy('programme', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * Persist a new gallery memory entry.
     *
     * @param  array{programme: string, programme_name?: string, caption?: string, images: array<int, string>}  $memory
     */
    public function saveGalleryMemory(array $memory): object
    {
        $entry = [
            'id' => (string) Str::uuid(),
            'programme' => $memory['programme'],
            'programme_name' => $memory['programme_name'] ?? '',
            'caption' => $memory['caption'] ?? '',
            'images' => array_values($memory['images']),
            'created_at' => now()->toIso8601String(),
        ];

        $items = $this->read('gallery.json');
        if (! is_array($items)) {
            $items = [];
        }

        array_unshift($items, $entry);
        $this->write('gallery.json', $items);

        return (object) $entry;
    }

    /**
     * Live ceremony stream configuration.
     */
    public function live(): object
    {
        $data = $this->read('live.json');

        if (! is_array($data) || $data === []) {
            $data = [
                'title' => 'Live Ceremony Streams',
                'message' => 'Select an available stream below.',
                'streams' => [],
            ];
        }

        return (object) [
            'title' => $data['title'] ?? 'Live Ceremony Streams',
            'message' => $data['message'] ?? '',
            'streams' => collect($data['streams'] ?? [])->map(fn ($row) => (object) $row)->values(),
        ];
    }

    /**
     * Help desk contacts and FAQs.
     */
    public function help(): object
    {
        $data = $this->read('help.json');

        if (! is_array($data) || $data === []) {
            $data = [
                'title' => 'Help',
                'intro' => '',
                'contacts' => [],
                'faqs' => [],
            ];
        }

        return (object) [
            'title' => $data['title'] ?? 'Help',
            'intro' => $data['intro'] ?? '',
            'contacts' => collect($data['contacts'] ?? [])->map(fn ($row) => (object) $row),
            'faqs' => collect($data['faqs'] ?? [])->map(fn ($row) => (object) $row),
        ];
    }
}
