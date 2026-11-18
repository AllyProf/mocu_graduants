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
            ->sort(function ($a, $b) {
                $levelCmp = $this->studyLevelRank($a->study_level ?? '')
                    <=> $this->studyLevelRank($b->study_level ?? '');

                if ($levelCmp !== 0) {
                    return $levelCmp;
                }

                return strnatcasecmp((string) ($a->full_name ?? ''), (string) ($b->full_name ?? ''));
            })
            ->values();
    }

    public function studyLevels(): Collection
    {
        return collect([
            'PhD',
            'Masters',
            'Postgraduate Diploma',
            'Bachelor Degree',
            'Diploma',
            'Certificate',
        ]);
    }

    public function studyLevelRank(?string $level): int
    {
        $rank = $this->studyLevels()->search($level);

        return $rank === false ? 999 : (int) $rank;
    }

    public function complaintEmail(): string
    {
        $help = $this->help();
        $email = $help->contacts
            ->first(fn ($contact) => ($contact->type ?? '') === 'email');

        return is_object($email) ? trim((string) ($email->value ?? '')) : '';
    }

    public function saveComplaint(array $complaint): void
    {
        $path = $this->path('complaints.json');
        $existing = $this->read('complaints.json');

        if (! is_array($existing)) {
            $existing = [];
        }

        $existing[] = $complaint;

        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $path,
            json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public function yearsOfStudy(): Collection
    {
        return collect([
            'First Year',
            'Second Year',
            'Third Year',
        ]);
    }

    public function programmes(?string $studyLevel = null): Collection
    {
        return collect([
            'BBICT',
            'BHRM',
            'BME',
            'BMFED',
            'BPSCM',
            'LL.B',
            'BCMA',
            'BA-CMA',
            'BEC',
            'BCED',
            'BAF',
            'BAT',
            'DBEM',
            'DBRM',
            'DBICT',
            'DMFM',
            'DCMA',
            'CMA',
            'CAF',
            'CHRM',
            'CIT',
            'CL',
            'CMF',
        ]);
    }

    public function bestStudents(): Collection
    {
        return $this->graduands()
            ->where('is_best_student', true)
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
