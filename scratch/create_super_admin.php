<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Ensure the ENUM is updated (doing it via DB statement just in case)
try {
    DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('standard', 'department_head', 'admin', 'executive', 'super_admin') NOT NULL DEFAULT 'standard'");
} catch (\Exception $e) {
    // If it fails, maybe the user already did it or we don't have permissions, but we proceed
}

$user = User::where('email', 'super_admin@mcdonalds.mu')->first();

if (!$user) {
    $user = new User();
    $user->name = 'Super Admin';
    $user->username = 'superadmin';
    $user->email = 'super_admin@mcdonalds.mu';
    $user->password = Hash::make('password');
    $user->role = 'super_admin';
    $user->location_id = 6;
    $user->department_id = 15;
    $user->is_active = 1;
    $user->save();
    echo "Super Admin created successfully.\n";
} else {
    echo "Super Admin already exists.\n";
}
print_r($user->toArray());
