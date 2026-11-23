<?php

namespace Database\Seeders;

use App\Models\AcademicLevel;
use App\Models\Programme;
use Illuminate\Database\Seeder;

/**
 * Seeds programmes from the MoCU catalogue (programmes.json) and graduands JSON.
 */
class ProgrammeSeeder extends Seeder
{
    public function run(): void
    {
        $levels = AcademicLevel::query()->pluck('id', 'name');

        $cataloguePath = storage_path('app/graduation/programmes.json');
        if (is_file($cataloguePath)) {
            $catalogue = json_decode((string) file_get_contents($cataloguePath), true);
            if (is_array($catalogue)) {
                foreach ($catalogue as $row) {
                    if (! is_array($row)) {
                        continue;
                    }

                    $levelName = trim((string) ($row['level'] ?? ''));
                    $short = trim((string) ($row['short'] ?? ''));
                    $name = trim((string) ($row['name'] ?? $short));

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
        }

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

                Programme::query()->firstOrCreate(
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
    }
}
