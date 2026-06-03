<?php

namespace Database\Seeders;

use App\Enum\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = env('VEARA_ADMIN_PASSWORD');

        if (blank($password) || strlen($password) < 12) {
            throw new \RuntimeException('VEARA_ADMIN_PASSWORD must be set to at least 12 characters before seeding the admin user.');
        }

        User::updateOrCreate([
            'email' => env('VEARA_ADMIN_EMAIL', 'admin@admin.com'),
        ], [
            'first_name' => 'admin',
            'last_name'  => 'admin',
            'phone'      => '0123456789',
            'password'   => Hash::make($password),
            'role'       => Role::ADMIN->value,
        ]);
    }
}
