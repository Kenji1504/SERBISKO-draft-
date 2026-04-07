<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$existingUser = DB::table('users')
    ->where('first_name', 'Tyrique')
    ->where('last_name', 'Brakus')
    ->first();

if (!$existingUser) {
    $userId = DB::table('users')->insertGetId([
        'first_name' => 'Tyrique',
        'last_name' => 'Brakus',
        'middle_name' => 'Kennedy',
        'extension_name' => null,
        'birthday' => '2008-08-24',
        'password' => bcrypt('Secret123!'),
        'role' => 'student',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
} else {
    $userId = $existingUser->id;
}

DB::table('students')->updateOrInsert(
    ['lrn' => '006346555172'],
    [
        'user_id' => $userId,
        'sex' => 'Male',
        'age' => 18,
        'place_of_birth' => 'Mexico, Pampanga',
        'mother_tongue' => 'Tagalog',
        'curr_house_number' => '',
        'curr_street' => '',
        'curr_barangay' => 'Santa Maria',
        'curr_city' => 'Mexico',
        'curr_province' => 'Pampanga',
        'curr_zip_code' => '2021',
        'is_perm_same_as_curr' => true,
        'perm_house_number' => '',
        'perm_street' => '',
        'perm_barangay' => 'Santa Maria',
        'perm_city' => 'Mexico',
        'perm_province' => 'Pampanga',
        'perm_zip_code' => '2021',
        'mother_last_name' => 'Brakus',
        'mother_first_name' => 'Melanie',
        'mother_middle_name' => 'Nader',
        'mother_contact_number' => '',
        'father_last_name' => 'Brakus',
        'father_first_name' => 'Wayne',
        'father_middle_name' => 'Kennedy',
        'father_contact_number' => '',
        'guardian_last_name' => 'Brakus',
        'guardian_first_name' => 'Melanie',
        'guardian_middle_name' => 'Nader',
        'guardian_contact_number' => '',
        'created_at' => now(),
        'updated_at' => now(),
    ]
);

DB::table('pre_enrollments')->updateOrInsert(
    ['student_lrn' => '006346555172'],
    [
        'responses' => json_encode([
            'lrn' => '006346555172',
            'address' => 'Santa Maria, Mexico, Pampanga, 2021',
            'barangay' => 'Santa Maria',
            'city / municipality' => 'Mexico',
            'province' => 'Pampanga',
            'zip code' => '2021',
            'citizenship' => 'Filipino',
            'email' => 'Tyrique.Brakus@hotmail.com',
            'ethnicity' => '',
            'isCctRecipient' => false,
            'isIndigenous' => false,
            'MotherTongue' => 'Tagalog',
            'religion' => 'Roman Catholic',
            'guardian' => [
                'firstname' => 'Melanie',
                'surname' => 'Brakus',
                'middlename' => 'Nader',
                'relationship' => 'Mother'
            ],
            'learner' => [
                'firstname' => 'Tyrique',
                'lastname' => 'Brakus',
                'middlename' => 'Kennedy',
                'birthdate' => '2008-08-24'
            ],
            'father' => [
                'firstname' => 'Wayne',
                'lastname' => 'Brakus',
                'middlename' => 'Kennedy',
                'extensionname' => 'Sr.'
            ],
            'mother' => [
                'firstname' => 'Melanie',
                'surname' => 'Brakus',
                'middlename' => 'Nader'
            ],
        ]),
        'status' => 'Pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]
);

echo "Student record added/updated for LRN 006346555172\n";

