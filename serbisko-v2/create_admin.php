<?php

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // Check if Super Admin exists
    $user = User::where('first_name', 'Super')
               ->where('last_name', 'Admin')
               ->where('birthday', '2000-01-01')
               ->first();

    if ($user) {
        echo "Super Admin user already exists!\n";
        echo "ID: " . $user->id . "\n";
        echo "Name: " . $user->first_name . " " . $user->last_name . "\n";
        echo "Role: " . $user->role . "\n";
        echo "Birthday: " . $user->birthday . "\n";
    } else {
        echo "Creating Super Admin user...\n";
        $user = User::create([
            'first_name'     => 'Super',
            'last_name'      => 'Admin',
            'middle_name'    => null,
            'extension_name' => null,
            'birthday'       => '2000-01-01',
            'role'           => 'super_admin',
            'password'       => Hash::make('admin123'),
        ]);
        echo "Super Admin user created with ID: " . $user->id . "\n";
    }

    echo "\n=== LOGIN CREDENTIALS ===\n";
    echo "Last Name: Admin\n";
    echo "Given Name: Super\n";
    echo "Middle Name: (leave completely blank)\n";
    echo "Date of Birth: 2000-01-01\n";
    echo "Password: admin123\n";
    echo "========================\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}