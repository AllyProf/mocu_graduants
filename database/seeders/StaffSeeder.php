<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Links the seeded admin user to a staff profile (password stays on users).
 */
class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@mocu.ac.tz')->first();

        if (! $admin) {
            return;
        }

        $admin->forceFill([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ])->save();

        Staff::query()->updateOrCreate(
            ['user_id' => $admin->id],
            [
                'staff_number' => 'STF-0001',
                'full_name' => $admin->name,
                'email' => $admin->email,
                'phone' => '+255272751833',
                'department' => 'Graduation Office',
                'designation' => 'Graduation Administrator',
                'is_active' => true,
            ]
        );
    }
}
