<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin account
        User::updateOrCreate(
            ['email' => 'admin@freshbasket.com'],
            [
                'name'     => 'Store Admin',
                'email'    => 'admin@freshbasket.com',
                'password' => Hash::make('Admin@123'),
                'is_admin' => true,
                'is_guest' => false,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Admin created: admin@freshbasket.com / Admin@123');
    }
}
