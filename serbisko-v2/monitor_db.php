<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== SERBISKO DATABASE MONITOR ===\n\n";

// 1. Table Counts
echo "📊 TABLE COUNTS:\n";
$tables = ['users', 'students', 'pre_enrollments', 'sessions', 'sync_histories'];
foreach ($tables as $table) {
    try {
        $count = DB::table($table)->count();
        echo "  {$table}: {$count} records\n";
    } catch (Exception $e) {
        echo "  {$table}: Table not found\n";
    }
}

echo "\n";

// 2. Recent Users
echo "👥 RECENT USERS:\n";
$users = DB::table('users')->orderBy('created_at', 'desc')->limit(3)->get();
foreach ($users as $user) {
    echo "  {$user->first_name} {$user->last_name} ({$user->role}) - Created: {$user->created_at}\n";
}

echo "\n";

// 3. Pre-enrollment Status
echo "📝 PRE-ENROLLMENT STATUS:\n";
$statuses = DB::table('pre_enrollments')
    ->select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();

foreach ($statuses as $status) {
    echo "  {$status->status}: {$status->count} applications\n";
}

echo "\n";

// 4. Database File Info
$dbPath = database_path('database.sqlite');
if (file_exists($dbPath)) {
    $size = filesize($dbPath);
    $sizeMB = round($size / 1024 / 1024, 2);
    echo "💾 DATABASE FILE:\n";
    echo "  Location: {$dbPath}\n";
    echo "  Size: {$sizeMB} MB\n";
    echo "  Last Modified: " . date('Y-m-d H:i:s', filemtime($dbPath)) . "\n";
}

echo "\n=== END MONITOR ===\n";
