<?php

namespace App\Services;

use Illuminate\Support\Collection;

class GraduationData
{
    protected function path(string $file): string
    {
        return storage_path('app/graduation/'.$file);
    }

    protected function read(string $file): mixed
    {
        $path = $this->path($file);

        if (! is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return $decoded ?? [];
    }

    public function graduands(): Collection
    {
        return collect($this->read('graduands.json'))
            ->map(fn (array $row) => (object) $row)
            ->sortBy('full_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    public function studyLevels(): Collection
    {
        return $this->graduands()->pluck('study_level')->unique()->sort()->values();
    }

    public function programmes(?string $studyLevel = null): Collection
    {
        return $this->graduands()
            ->when($studyLevel, fn (Collection $items) => $items->where('study_level', $studyLevel))
            ->pluck('programme')
            ->unique()
            ->sort()
            ->values();
    }

    public function bestStudents(): Collection
    {
        return $this->graduands()
            ->where('is_best_student', true)
            ->sortByDesc('gpa')
            ->values();
    }

    public function timetable(): Collection
    {
        return collect($this->read('timetable.json'))
            ->map(fn (array $row) => (object) $row)
            ->sortBy('starts_at')
            ->values();
    }

    public function eventTypes(): Collection
    {
        return $this->timetable()->pluck('event_type')->unique()->sort()->values();
    }

    public function locations(): Collection
    {
        return collect($this->read('locations.json'))
            ->map(fn (array $row) => (object) $row)
            ->values();
    }

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
