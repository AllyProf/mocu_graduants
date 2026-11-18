<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds admin user, academic lookups, programmes, students, and staff.
 *
 * Password is taken from ADMIN_PASSWORD in .env (default: "password").
 * Change this before deploying to production.
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
