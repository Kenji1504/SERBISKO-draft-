<?php

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Testing login credentials...\n";

try {
    // Test the exact query from AuthController
    $query = User::withTrashed()
        ->where('last_name', 'Admin')
        ->where('first_name', 'Super')
        ->where('birthday', '2000-01-01');

    // Since middle_name is not provided, check for null or empty
    $query->where(function($q) {
        $q->whereNull('middle_name')->orWhere('middle_name', '');
    });

    $user = $query->first();

    if (!$user) {
        echo "ERROR: User not found with these criteria!\n";
        echo "Checking all users in database:\n";

        $allUsers = User::all();
        foreach ($allUsers as $u) {
            echo "- ID: {$u->id}, Name: {$u->first_name} {$u->last_name}, Birthday: {$u->birthday}, Role: {$u->role}\n";
        }
    } else {
        echo "User found: {$user->first_name} {$user->last_name} (ID: {$user->id})\n";

        // Test password
        if (Hash::check('admin123', $user->password)) {
            echo "Password is correct!\n";
        } else {
            echo "ERROR: Password is incorrect!\n";
            echo "Stored hash: " . substr($user->password, 0, 20) . "...\n";
        }

        // Check if soft deleted
        if ($user->trashed()) {
            echo "ERROR: User account is revoked!\n";
        } else {
            echo "User account is active.\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nExpected login data:\n";
echo "- last_name: Admin\n";
echo "- given_name: Super\n";
echo "- middle_name: (empty)\n";
echo "- dob: 2000-01-01\n";
echo "- password: admin123\n";