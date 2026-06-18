<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    protected $signature   = 'admin:create {--email=} {--name=} {--password=}';
    protected $description = 'Create or promote a user to admin';

    public function handle()
    {
        $email    = $this->option('email')    ?? $this->ask('Admin email');
        $name     = $this->option('name')     ?? $this->ask('Admin name', 'Store Admin');
        $password = $this->option('password') ?? $this->secret('Password (min 8 chars)');

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.'); return 1;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password), 'is_admin' => true, 'is_guest' => false, 'email_verified_at' => now()]
        );

        $this->info("✅ Admin '{$user->name}' ({$user->email}) created/updated.");
        return 0;
    }
}
