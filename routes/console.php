<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('dev:test-user {email=dev@example.com} {--password=password}', function (string $email) {
    $password = (string) $this->option('password');
    $name = str($email)->before('@')->replace(['.', '_', '-'], ' ')->title()->toString();

    $user = User::query()->firstOrCreate(
        ['email' => $email],
        [
            'name' => $name !== '' ? $name : 'Dev Tester',
            'password' => Hash::make($password),
        ]
    );

    $this->info("User ready: {$user->email}");
    $this->line("Password: {$password}");
})->purpose('Create or reuse a local test user for notification testing');
