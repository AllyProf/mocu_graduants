<?php

namespace App\Services;

use App\Models\AcademicLevel;
use App\Models\Complaint;
use App\Models\Programme;
use App\Models\Student;
use App\Models\YearOfStudy;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Graduation data access layer for MoCU Graduation Companion.
 *
 * Student / programme / academic lookups come from the database.
 * Supporting content (timetable, venues, live, help) still reads JSON
 * until those modules are migrated in a later step.
 */
class GraduationData
{
    /**
     * Absolute path to a legacy JSON file (supporting modules only).
     */
    protected function path(string $file): string
    {
        return storage_path('app/graduation/'.$file);
    }

    /**
     * Read and decode a legacy JSON file. Missing/invalid files return [].
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
     * Full graduands list from students table, ordered by level then name.
     */
    public function graduands(): Collection
    {
        return Student::query()
            ->with(['programme', 'academicLevel', 'yearOfStudy', 'academicYear'])
            ->where('is_active', true)
            ->get()
            ->sort(function ($a, $b) {
                $levelCmp = $this->studyLevelRank($a->academicLevel?->name)
                    <=> $this->studyLevelRank($b->academicLevel?->name);

                if ($levelCmp !== 0) {
                    return $levelCmp;
                }

                return strnatcasecmp((string) $a->full_name, (string) $b->full_name);
            })
            ->values()
            ->map(fn (Student $student) => $student->toListObject());
    }

    /**
     * Canonical study-level order used by filters and sorting (PhD → Certificate).
     */
    public function studyLevels(): Collection
    {
        return AcademicLevel::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name')
            ->values();
    }

    /**
     * Numeric rank for study level sorting (unknown levels go last).
     */
    public function studyLevelRank(?string $level): int
    {
        $rank = $this->studyLevels()->search($level);

        return $rank === false ? 999 : (int) $rank;
    }

    /**
     * Support email from help.json (used for complaint notifications).
     */
    public function complaintEmail(): string
    {
        $help = $this->help();
        $email = $help->contacts
            ->first(fn ($contact) => ($contact->type ?? '') === 'email');

        return is_object($email) ? trim((string) ($email->value ?? '')) : '';
    }

    /**
     * Persist a missing-name complaint in the complaints table.
     */
    public function saveComplaint(array $complaint): void
    {
        $levelId = null;
        if (! empty($complaint['study_level'])) {
            $levelId = AcademicLevel::query()
                ->where('name', $complaint['study_level'])
                ->value('id');
        }

        $programmeId = null;
        if (! empty($complaint['programme'])) {
            $programmeId = Programme::query()
                ->where('short_code', $complaint['programme'])
                ->when($levelId, fn ($q) => $q->where('academic_level_id', $levelId))
                ->value('id');
        }

        Complaint::query()->create([
            'public_id' => $complaint['id'] ?? (string) Str::uuid(),
            'full_name' => $complaint['full_name'],
            'registration_number' => $complaint['registration_number'] ?: null,
            'academic_level_id' => $levelId,
            'programme_id' => $programmeId,
            'study_level' => $complaint['study_level'] ?: null,
            'programme' => $complaint['programme'] ?: null,
            'country_code' => $complaint['country_code'],
            'phone' => $complaint['phone'],
            'contact' => $complaint['contact'],
            'message' => $complaint['message'],
            'list_type' => $complaint['list_type'] ?? 'graduands',
            'searched_name' => $complaint['searched_name'] ?: null,
            'status' => 'pending',
            'ip_address' => $complaint['ip'] ?? null,
        ]);
    }

    /**
     * Year-of-study options (used on Best Students filters).
     */
    public function yearsOfStudy(): Collection
    {
        return YearOfStudy::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name')
            ->values();
    }

    /**
     * Programme short-code list for filter dropdowns.
     */
    public function programmes(?string $studyLevel = null): Collection
    {
        $query = Programme::query()->where('is_active', true);

        if ($studyLevel) {
            $query->whereHas('academicLevel', fn ($q) => $q->where('name', $studyLevel));
        }

        return $query
            ->orderBy('short_code')
            ->pluck('short_code')
            ->unique()
            ->values();
    }

    /**
     * Best-student subset: overall award first, then by GPA descending.
     */
    public function bestStudents(): Collection
    {
        return Student::query()
            ->with(['programme', 'academicLevel', 'yearOfStudy', 'academicYear'])
            ->where('is_active', true)
            ->where('is_best_student', true)
            ->get()
            ->sort(function ($a, $b) {
                $aOverall = ($a->best_award ?? '') === 'overall' ? 0 : 1;
                $bOverall = ($b->best_award ?? '') === 'overall' ? 0 : 1;

                if ($aOverall !== $bOverall) {
                    return $aOverall <=> $bOverall;
                }

                return ((float) ($b->gpa ?? 0)) <=> ((float) ($a->gpa ?? 0));
            })
            ->values()
            ->map(fn (Student $student) => $student->toListObject());
    }

    /**
     * Ceremony timetable events sorted by start time (legacy JSON).
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
     * Campus / venue locations for the venue guide (legacy JSON).
     */
    public function locations(): Collection
    {
        return collect($this->read('locations.json'))
            ->map(fn (array $row) => (object) $row)
            ->values();
    }

    /**
     * Live ceremony stream configuration (legacy JSON).
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
     * Help desk contacts and FAQs (legacy JSON).
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
