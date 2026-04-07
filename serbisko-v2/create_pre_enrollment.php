<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Get the sample student we just created
$student = DB::table('students')->where('lrn', '006346555172')->first();

if ($student) {
    // Create pre-enrollment entry with Pending status
    DB::table('pre_enrollments')->insert([
        'student_lrn' => $student->lrn,
        'responses' => json_encode([
            'grade_level' => 'Grade 11',
            'strand' => 'STEM',
            'school_year' => '2026-2027',
            'submitted_at' => now()->toDateTimeString(),
            'notes' => 'Sample pre-enrollment application'
        ]),
        'status' => 'Pending', // Not officially enrolled yet
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "Pre-enrollment application created successfully!\n";
    echo "Student LRN: {$student->lrn}\n";
    echo "Status: Pending (waiting for admin approval)\n";
    echo "The student is now in the pre-enrollment queue for you to review and officially enroll.\n";
} else {
    echo "Sample student not found. Please run create_sample_student.php first.\n";
}
