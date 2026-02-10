<?php

use Illuminate\Support\Facades\Hash;

use App\User;


require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create or retrieve admin user
$user = User::firstOrCreate(
    ['email' => 'admin@test.com'],
    [
        'name' => 'Admin User',
        'password' => Hash::make('password'),
        'role' => 'admin'
    ]
);

// Delete existing tokens for clean slate (optional)
$user->tokens()->delete();

// Create new token
$token = $user->createToken('test-token')->plainTextToken;

echo "Token: " . $token . "\n";
