<?php

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

echo "Testing login process...\n";

try {
    // Simulate the login request
    $requestData = [
        'last_name' => 'Admin',
        'given_name' => 'Super',
        'middle_name' => '', // empty
        'dob' => '2000-01-01',
        'password' => 'admin123'
    ];

    echo "Looking for user with: {$requestData['last_name']}, {$requestData['given_name']}, {$requestData['dob']}\n";

    // Use the same query as AuthController
    $query = User::withTrashed()
        ->where('last_name', $requestData['last_name'])
        ->where('first_name', $requestData['given_name'])
        ->whereDate('birthday', $requestData['dob']);

    // Handle middle name
    if (!empty($requestData['middle_name'])) {
        $query->where('middle_name', $requestData['middle_name']);
    } else {
        $query->where(function($q) {
            $q->whereNull('middle_name')->orWhere('middle_name', '');
        });
    }

    $user = $query->first();

    if (!$user) {
        echo "ERROR: User not found!\n";

        // Try without whereDate
        echo "Trying without whereDate...\n";
        $query2 = User::withTrashed()
            ->where('last_name', $requestData['last_name'])
            ->where('first_name', $requestData['given_name'])
            ->where('birthday', $requestData['dob']);

        if (!empty($requestData['middle_name'])) {
            $query2->where('middle_name', $requestData['middle_name']);
        } else {
            $query2->where(function($q) {
                $q->whereNull('middle_name')->orWhere('middle_name', '');
            });
        }

        $user2 = $query2->first();
        if ($user2) {
            echo "Found with exact birthday match!\n";
            $user = $user2;
        } else {
            echo "Still not found\n";
        }
    }

    if ($user) {
        echo "User found: {$user->first_name} {$user->last_name} (ID: {$user->id})\n";

        // Check password
        if (Hash::check($requestData['password'], $user->password)) {
            echo "Password correct!\n";
        } else {
            echo "ERROR: Password incorrect!\n";
        }

        // Check if soft deleted
        if ($user->trashed()) {
            echo "ERROR: User is soft deleted!\n";
        } else {
            echo "User is active\n";

            // Try to login
            Auth::login($user);
            echo "Auth::login() executed\n";

            $role = strtolower($user->role);
            echo "User role: {$role}\n";

            if (in_array($role, ['admin', 'super_admin', 'facilitator'])) {
                echo "Should redirect to /dashboard\n";
            } else {
                echo "Should redirect to student flow\n";
            }
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}