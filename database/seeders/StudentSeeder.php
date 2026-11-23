<?php

namespace Database\Seeders;

use App\Models\AcademicLevel;
use App\Models\AcademicYear;
use App\Models\Programme;
use App\Models\Student;
use App\Models\YearOfStudy;
use Illuminate\Database\Seeder;

/**
 * Imports students/graduands from the legacy JSON file into the students table.
 */
class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/graduands.json');
        if (! is_file($path)) {
            $this->command?->warn('seeders/data/graduands.json not found — skipping student import.');

            return;
        }

        $rows = json_decode((string) file_get_contents($path), true);
        if (! is_array($rows) || $rows === []) {
            return;
        }

        $levels = AcademicLevel::query()->pluck('id', 'name');
        $years = YearOfStudy::query()->pluck('id', 'name');
        $academicYear = AcademicYear::query()->where('is_current', true)->first()
            ?? AcademicYear::query()->first();

        if (! $academicYear) {
            $this->command?->error('No academic year found. Run AcademicLookupSeeder first.');

            return;
        }

        Student::query()->delete();

        foreach ($rows as $index => $row) {
            $levelName = trim((string) ($row['study_level'] ?? ''));
            $short = trim((string) ($row['programme_short'] ?? ''));
            $yearName = trim((string) ($row['year_of_study'] ?? ''));

            if ($levelName === '' || $short === '' || ! isset($levels[$levelName])) {
                continue;
            }

            $programme = Programme::query()
                ->where('short_code', $short)
                ->where('academic_level_id', $levels[$levelName])
                ->first();

            if (! $programme) {
                $programme = Programme::query()->create([
                    'academic_level_id' => $levels[$levelName],
                    'short_code' => $short,
                    'name' => trim((string) ($row['programme'] ?? $short)),
                    'is_active' => true,
                ]);
            }

            Student::query()->create([
                'registration_number' => sprintf('MOCU/%s/%04d', date('Y'), $index + 1),
                'full_name' => trim((string) ($row['full_name'] ?? 'Unknown')),
                'gender' => $row['gender'] ?? null,
                'programme_id' => $programme->id,
                'academic_level_id' => $levels[$levelName],
                'year_of_study_id' => $yearName !== '' ? ($years[$yearName] ?? null) : null,
                'academic_year_id' => $academicYear->id,
                'gpa' => isset($row['gpa']) ? (float) $row['gpa'] : null,
                'is_best_student' => (bool) ($row['is_best_student'] ?? false),
                'best_award' => $row['best_award'] ?? null,
                'is_active' => true,
            ]);
        }
    }
}
