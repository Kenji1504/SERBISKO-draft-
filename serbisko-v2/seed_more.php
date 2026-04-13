<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$students = [
    [
        'first' => 'Jane',
        'last' => 'Doe',
        'middle' => 'Smith',
        'lrn' => '123456789012',
        'status' => 'Regular',
        'grade' => 'Grade 11'
    ],
    [
        'first' => 'John',
        'last' => 'Smith',
        'middle' => 'Johnson',
        'lrn' => '987654321098',
        'status' => 'ALS',
        'grade' => 'Grade 12'
    ],
    [
        'first' => 'Alice',
        'last' => 'Guo',
        'middle' => 'Xiao',
        'lrn' => '111222333444',
        'status' => 'Transferee',
        'grade' => 'Grade 11'
    ]
];

foreach ($students as $s) {
    $userId = DB::table('users')->insertGetId([
        'first_name' => $s['first'],
        'last_name' => $s['last'],
        'middle_name' => $s['middle'],
        'birthday' => '2008-01-01',
        'password' => Hash::make('password'),
        'role' => 'student',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $studentId = DB::table('students')->insertGetId([
        'user_id' => $userId,
        'lrn' => $s['lrn'],
        'school_year' => '2025-2026',
        'sex' => 'Female',
        'age' => 17,
        'guardian_last_name' => $s['last'],
        'guardian_first_name' => 'Guardian',
        'guardian_contact_number' => '09123456789',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('pre_enrollments')->insert([
        'student_id' => $studentId,
        'responses' => json_encode([
            'Academic Status' => $s['status'],
            'Grade Level to Enroll' => $s['grade'],
            'Track' => 'Academic',
            'Cluster of Electives' => 'STEM'
        ]),
        'status' => 'Pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

echo "Added 3 more sample students.\n";
