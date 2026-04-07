<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Users in Database ===\n";
$users = User::all();
foreach($users as $user) {
    echo "First: {$user->first_name} | Last: {$user->last_name} | Middle: {$user->middle_name} | Birthday: {$user->birthday} | Password: " . substr($user->password, 0, 10) . "... | Role: {$user->role}\n";
}
