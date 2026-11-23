<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Optional DB seeders. The live companion serves ceremony data from
 * storage/app/graduation/*.json and does not require these tables.
 *
 * Run only when you intentionally use MySQL/SQLite for admin tooling:
 *   php artisan migrate --seed
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $password = (string) env('ADMIN_PASSWORD', 'password');

        User::query()->updateOrCreate(
            ['email' => 'admin@mocu.ac.tz'],
            [
                'name' => 'Graduation Admin',
                'password' => Hash::make($password),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        $this->call([
            AcademicLookupSeeder::class,
            ProgrammeSeeder::class,
            StudentSeeder::class,
            StaffSeeder::class,
        ]);
    }
}
