<?php

namespace Database\Seeders;

use App\Models\AcademicLevel;
use App\Models\Programme;
use Illuminate\Database\Seeder;

/**
 * Seeds programmes from the legacy graduands JSON plus a static short-code catalogue.
 */
class ProgrammeSeeder extends Seeder
{
    public function run(): void
    {
        $levels = AcademicLevel::query()->pluck('id', 'name');
        $path = database_path('seeders/data/graduands.json');
        $rows = is_file($path) ? json_decode((string) file_get_contents($path), true) : [];

        if (is_array($rows)) {
            foreach ($rows as $row) {
                $levelName = trim((string) ($row['study_level'] ?? ''));
                $short = trim((string) ($row['programme_short'] ?? ''));
                $name = trim((string) ($row['programme'] ?? $short));

                if ($levelName === '' || $short === '' || ! isset($levels[$levelName])) {
                    continue;
                }

                Programme::query()->updateOrCreate(
                    [
                        'short_code' => $short,
                        'academic_level_id' => $levels[$levelName],
                    ],
                    [
                        'name' => $name !== '' ? $name : $short,
                        'is_active' => true,
                    ]
                );
            }
        }

        // Ensure common filter options exist even if missing from sample rows.
        $defaults = [
            'Bachelor Degree' => [
                'BBICT' => 'Bachelor of Business Information and Communication Technology',
                'BHRM' => 'Bachelor of Human Resource Management',
                'BME' => 'Bachelor of Microfinance and Enterprise Development',
                'BMFED' => 'Bachelor of Marketing and Entrepreneurship Development',
                'BPSCM' => 'Bachelor of Procurement and Supply Chain Management',
                'LL.B' => 'Bachelor of Laws',
                'BCMA' => 'Bachelor of Cooperative Management and Accounting',
                'BA-CMA' => 'Bachelor of Arts in Cooperative Management and Accounting',
                'BEC' => 'Bachelor of Economics',
                'BCED' => 'Bachelor of Community Economic Development',
                'BAF' => 'Bachelor of Accounting and Finance',
                'BAT' => 'Bachelor of Accounting with Taxation',
            ],
            'Diploma' => [
                'DBEM' => 'Diploma in Business and Enterprise Management',
                'DBRM' => 'Diploma in Business Risk Management',
                'DBICT' => 'Diploma in Business Information and Communication Technology',
                'DMFM' => 'Diploma in Microfinance Management',
                'DCMA' => 'Diploma in Cooperative Management and Accounting',
            ],
            'Certificate' => [
                'CMA' => 'Certificate in Cooperative Management and Accounting',
                'CAF' => 'Certificate in Accounting and Finance',
                'CHRM' => 'Certificate in Human Resource Management',
                'CIT' => 'Certificate in Information Technology',
                'CL' => 'Certificate in Law',
                'CMF' => 'Certificate in Microfinance',
            ],
        ];

        foreach ($defaults as $levelName => $programmes) {
            if (! isset($levels[$levelName])) {
                continue;
            }

            foreach ($programmes as $short => $name) {
                Programme::query()->firstOrCreate(
                    [
                        'short_code' => $short,
                        'academic_level_id' => $levels[$levelName],
                    ],
                    [
                        'name' => $name,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
