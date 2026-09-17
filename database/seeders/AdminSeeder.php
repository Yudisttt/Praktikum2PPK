<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@jara.com'],
            [
                'name' => 'Administrator Jara',
                'password' => Hash::make('password'),
                'role' => UserRole::ADMIN->value,
                'status' => true,
            ]
        );
    }
}
