<?php

namespace Database\Seeders;

use App\Models\AcademicLevel;
use App\Models\AcademicYear;
use App\Models\YearOfStudy;
use Illuminate\Database\Seeder;

/**
 * Seeds academic levels, years of study, and the current academic year.
 */
class AcademicLookupSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['name' => 'PhD', 'code' => 'PHD', 'sort_order' => 1],
            ['name' => 'Masters', 'code' => 'MSC', 'sort_order' => 2],
            ['name' => 'Postgraduate Diploma', 'code' => 'PGD', 'sort_order' => 3],
            ['name' => 'Bachelor Degree', 'code' => 'BACH', 'sort_order' => 4],
            ['name' => 'Diploma', 'code' => 'DIP', 'sort_order' => 5],
            ['name' => 'Certificate', 'code' => 'CERT', 'sort_order' => 6],
        ];

        foreach ($levels as $level) {
            AcademicLevel::query()->updateOrCreate(
                ['name' => $level['name']],
                [
                    'code' => $level['code'],
                    'sort_order' => $level['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        $years = [
            ['name' => 'First Year', 'year_number' => 1, 'sort_order' => 1],
            ['name' => 'Second Year', 'year_number' => 2, 'sort_order' => 2],
            ['name' => 'Third Year', 'year_number' => 3, 'sort_order' => 3],
        ];

        foreach ($years as $year) {
            YearOfStudy::query()->updateOrCreate(
                ['name' => $year['name']],
                [
                    'year_number' => $year['year_number'],
                    'sort_order' => $year['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        // Mark only one academic year as current.
        AcademicYear::query()->update(['is_current' => false]);

        AcademicYear::query()->updateOrCreate(
            ['name' => '2025/2026'],
            [
                'starts_on' => '2025-10-01',
                'ends_on' => '2026-09-30',
                'is_current' => true,
                'is_active' => true,
            ]
        );
    }
}
