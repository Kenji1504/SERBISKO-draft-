<?php

namespace App\Http\Controllers;

use Google\Client; 
use Google\Service\Sheets; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    // 1. DASHBOARD LOGIC (From Backup)
    public function index(Request $request) 
    {
        $grade = $request->grade_level;

        $applyFilter = function($query) use ($grade) {
            if (!empty($grade)) {
                $query->where(function($q) use ($grade) {
                    $q->where('kiosk_enrollments.grade_level', '=', $grade)
                    ->orWhere(function($sq) use ($grade) {
                        $sq->whereNull('kiosk_enrollments.grade_level')
                            ->where('pre_enrollments.responses', 'like', '%"Grade Level to Enroll":"' . $grade . '"%');
                    });
                });
            }
            return $query;
        };

        $totalRegistrations = $applyFilter(DB::table('students')
            ->leftJoin('kiosk_enrollments', 'students.lrn', '=', 'kiosk_enrollments.student_lrn')
            ->leftJoin('pre_enrollments', 'students.lrn', '=', 'pre_enrollments.student_lrn'))
            ->count();

        $totalSubmissions = $applyFilter(DB::table('students')
            ->join('kiosk_enrollments', 'students.lrn', '=', 'kiosk_enrollments.student_lrn')
            ->leftJoin('pre_enrollments', 'students.lrn', '=', 'pre_enrollments.student_lrn'))
            ->count();

        $totalEnrolled = $applyFilter(DB::table('students')
            ->join('kiosk_enrollments', 'students.lrn', '=', 'kiosk_enrollments.student_lrn')
            ->leftJoin('pre_enrollments', 'students.lrn', '=', 'pre_enrollments.student_lrn')
            ->where('kiosk_enrollments.academic_status', '=', 'Officially Enrolled')) 
            ->count();

        $max = $totalRegistrations > 0 ? $totalRegistrations : 1;
        $percVerified = ($totalSubmissions / $max) * 100;
        $percEnrolled = ($totalEnrolled / $max) * 100;

        $electives = ['STEM', 'ASSH', 'BE', 'TechPro'];
        $electiveCounts = [];

        foreach ($electives as $elective) {
            $electiveCounts[$elective] = $applyFilter(DB::table('students')
                ->join('pre_enrollments', 'students.lrn', '=', 'pre_enrollments.student_lrn')
                ->leftJoin('kiosk_enrollments', 'students.lrn', '=', 'kiosk_enrollments.student_lrn')
                ->where(function($q) use ($elective) {
                    $lowerElective = strtolower($elective);
                    $q->where('kiosk_enrollments.cluster', $elective)
                    ->orWhere(function($sub) use ($lowerElective) {
                        $sub->whereNull('kiosk_enrollments.cluster')
                            ->whereRaw('LOWER(pre_enrollments.responses) LIKE ?', ['%"Cluster":"' . $lowerElective . '"%']);
                    });
                }))
                ->count();
        }

        $recentKioskQuery = DB::table('kiosk_enrollments')
            ->join('students', 'kiosk_enrollments.student_lrn', '=', 'students.lrn')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->select(
                'users.first_name', 'users.middle_name', 'users.last_name',
                'students.extension_name', 'kiosk_enrollments.grade_level',
                'kiosk_enrollments.track', 'kiosk_enrollments.cluster',
                'kiosk_enrollments.completed_at', 'kiosk_enrollments.academic_status as status'
            );

        if (!empty($grade)) {
            $recentKioskQuery->where('kiosk_enrollments.grade_level', $grade);
        }

        $recentKioskSubmissions = $recentKioskQuery->orderBy('kiosk_enrollments.completed_at', 'desc')->limit(5)->get();

        $lastSync = DB::table('sync_histories')->where('status', 'Success')->latest()->first();
        $lastSyncTime = $lastSync ? \Carbon\Carbon::parse($lastSync->created_at)->diffForHumans() : 'Never';

        $totalElectives = array_sum($electiveCounts) ?: 1;
        $pSTEM = ($electiveCounts['STEM'] / $totalElectives) * 100;
        $pASSH = ($electiveCounts['ASSH'] / $totalElectives) * 100;
        $pBE   = ($electiveCounts['BE'] / $totalElectives) * 100;
        $pTech = ($electiveCounts['TechPro'] / $totalElectives) * 100;

        $stop1 = $pSTEM;
        $stop2 = $stop1 + $pASSH;
        $stop3 = $stop2 + $pBE;

        $donutGradient = "conic-gradient(
            #00568d 0% {$stop1}%, 
            #00897b {$stop1}% {$stop2}%, 
            #1a8a44 {$stop2}% {$stop3}%, 
            #facc15 {$stop3}% 100%
        )";

        return view('admin.dashboardpage.dashboard', compact(
            'totalRegistrations', 'totalSubmissions', 'totalEnrolled',
            'percVerified', 'percEnrolled', 'electiveCounts',
            'donutGradient', 'lastSyncTime', 'recentKioskSubmissions'
        ));
    }

    // 2. STUDENT LIST LOGIC (From Backup)
    public function students(Request $request)
    {
        $query = DB::table('users')
            ->join('students', 'users.id', '=', 'students.user_id')
            ->leftJoin('pre_enrollments', 'students.lrn', '=', 'pre_enrollments.student_lrn')
            ->leftJoin('kiosk_enrollments', 'students.lrn', '=', 'kiosk_enrollments.student_lrn')
            ->where('users.role', 'student')
            ->select(
                'users.first_name', 'users.last_name', 'users.middle_name',
                'users.created_at', 'users.id as user_primary_id',
                'students.lrn', 'students.extension_name',
                'pre_enrollments.responses',
                'kiosk_enrollments.grade_level as kiosk_grade',
                'kiosk_enrollments.track as kiosk_track',
                'kiosk_enrollments.cluster as kiosk_cluster',
                'kiosk_enrollments.strand as kiosk_strand',
                'kiosk_enrollments.academic_status as kiosk_status'
            );

        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function($q) use ($searchTerm) {
                $q->where('users.first_name', 'like', "%{$searchTerm}%")
                ->orWhere('users.last_name', 'like', "%{$searchTerm}%")
                ->orWhere('users.middle_name', 'like', "%{$searchTerm}%")
                ->orWhere('students.lrn', 'like', "%{$searchTerm}%");
            });
        }

        $filters = [
            'student_type' => ['kiosk' => 'kiosk_enrollments.academic_status', 'json' => 'Academic Status'],
            'grade_level'  => ['kiosk' => 'kiosk_enrollments.grade_level',     'json' => 'Grade Level to Enroll'],
            'track'        => ['kiosk' => 'kiosk_enrollments.track',           'json' => 'Track'],
            'cluster'      => ['kiosk' => 'kiosk_enrollments.cluster',         'json' => 'Strand/Specialization']
        ];

        foreach ($filters as $requestKey => $keys) {
            if ($request->filled($requestKey)) {
                $val = $request->$requestKey;
                $query->where(function($q) use ($keys, $val) {
                    $q->where($keys['kiosk'], '=', $val)
                    ->orWhere(function($sq) use ($keys, $val) {
                        $sq->whereNull($keys['kiosk'])
                            ->where('pre_enrollments.responses', 'like', '%"' . $keys['json'] . '":"' . $val . '"%');
                    });
                });
            }
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'Registered') {
                $query->whereNull('kiosk_enrollments.grade_level');
            } elseif ($status === 'Document Verified') {
                $query->whereNotNull('kiosk_enrollments.grade_level');
            }
        }

        switch ($request->get('sort')) {
            case 'za': $query->orderBy('users.last_name', 'desc'); break;
            case 'newest': $query->orderBy('users.created_at', 'desc'); break;
            case 'oldest': $query->orderBy('users.created_at', 'asc'); break;
            default: $query->orderBy('users.last_name', 'asc'); break;
        }

        $students = $query->get()->map(function($student) {
            $raw = json_decode($student->responses, true) ?? [];
            $details = [];
            foreach ($raw as $key => $value) $details[trim($key)] = $value;

            $student->display_type    = $student->kiosk_status ?? ($details['Academic Status'] ?? '—');
            $student->display_grade   = $student->kiosk_grade  ?? ($details['Grade Level to Enroll'] ?? '—');
            $student->display_track   = $student->kiosk_track  ?? ($details['Track'] ?? '—');
            $student->display_cluster = $student->kiosk_cluster ?? $student->kiosk_strand ?? ($details['Strand/Specialization'] ?? '—');

            if (!empty($student->kiosk_grade)) {
                $student->enrollment_category = 'Document Verified';
                $student->status_style = 'bg-[#00923F] text-white border-green-200';
            } else {
                $student->enrollment_category = 'Registered';
                $student->status_style = 'bg-[#048F81] text-white border-[#048F81]';
            }
            return $student;
        });

        if ($request->ajax()) return view('admin.studentpage.partials.student-table', compact('students'))->render();
        return view('admin.studentpage.students', compact('students'));
    }

    // 3. STUDENT PROFILE LOGIC (From Backup)
    public function profilepage($lrn)
    {
        $student = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('pre_enrollments', 'students.lrn', '=', 'pre_enrollments.student_lrn')
            ->leftJoin('kiosk_enrollments', 'students.lrn', '=', 'kiosk_enrollments.student_lrn') 
            ->select(
                'users.first_name', 'users.last_name', 'users.middle_name', 'users.birthday', 
                'students.*', 'pre_enrollments.responses',
                'kiosk_enrollments.grade_level as kiosk_grade', 'kiosk_enrollments.track as kiosk_track',
                'kiosk_enrollments.cluster as kiosk_cluster', 'kiosk_enrollments.academic_status as kiosk_status'
            )
            ->where('students.lrn', $lrn)
            ->first();

        if (!$student) abort(404);

        $rawDetails = json_decode($student->responses, true) ?? [];
        $details = [];
        foreach ($rawDetails as $key => $value) $details[trim($key)] = $value;

        $finalGrade   = $student->kiosk_grade   ?? ($details['Grade Level to Enroll'] ?? '—');
        $finalTrack   = $student->kiosk_track   ?? ($details['Track'] ?? '—');
        $finalCluster = $student->kiosk_cluster ?? ($details['Cluster of Electives'] ?? '—');
        $finalStatus  = $student->kiosk_status  ?? ($details['Academic Status'] ?? '—');

        $fixedKeys = [
            'School Year', 'Grade Level to Enroll', 'Track', 'Cluster of Electives', 
            'Academic Status', 'Last School Year Completed', 'Last Grade Level Completed', 
            'Last School Attended', 'School ID'
        ];
        $dynamicDetails = array_diff_key($details, array_flip($fixedKeys));

        return view('admin.studentpage.profilepage', compact(
            'student', 'details', 'dynamicDetails', 
            'finalGrade', 'finalTrack', 'finalCluster', 'finalStatus'
        ));
    }

    // 4. SYNC AND UTILITY LOGIC (From Thesis/Backup)
    public function systemsync()
    {
        $history = DB::table('sync_histories')->orderBy('created_at', 'desc')->get();
        $lastSync = DB::table('sync_histories')->where('status', 'Success')->latest()->first();

        $isConnected = false;
        try {
            $client = new \Google\Client();
            $client->setAuthConfig(storage_path('app/google-credentials.json'));
            $client->addScope(\Google\Service\Sheets::SPREADSHEETS_READONLY);
            
            $service = new \Google\Service\Sheets($client);
            $spreadsheetId = '1pUdqUbAMQEZ4Kg2V6A05orHY9xnDCJLp2QWLQaXXmSk';
            $service->spreadsheets->get($spreadsheetId);
            $isConnected = true;
        } catch (\Exception $e) {
            $isConnected = false;
        }

        $formUrl = "https://forms.gle/7wrtrGWf2nDCWcz9A";

        return view('admin.systemsync', compact('history', 'lastSync', 'formUrl', 'isConnected'));
    }

    public function verification(){ return view('admin.verification'); }
    public function requirementhub(){ return view('admin.requirementhub');}
    public function accountsettings(){ return view('admin.accountsettings'); }

    public function performSync() {
        // Keeps your exact Google Sheets Sync logic intact here
        set_time_limit(120);
        $spreadsheetId = '1pUdqUbAMQEZ4Kg2V6A05orHY9xnDCJLp2QWLQaXXmSk'; 
        $range = 'Form_Responses2!A1:ZZ'; 

        try {
            $client = new \Google\Client(); 
            $client->setAuthConfig(storage_path('app/google-credentials.json'));
            $client->addScope(\Google\Service\Sheets::SPREADSHEETS_READONLY);

            $service = new \Google\Service\Sheets($client);
            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues();

            if (empty($values)) {
                return back()->with('info', 'The Google Sheet is currently empty.');
            }

            $headers = $values[0];
            $dataRows = array_slice($values, 1);

            $newCount = 0;
            $updatedCount = 0;

            foreach ($dataRows as $row) { 
                $row = array_pad($row, count($headers), null);
                if (empty(array_filter($row))) continue; 
                if (empty(trim($row[1])) || empty(trim($row[2])) || empty(trim($row[3])) || empty(trim($row[6]))) continue; 

                try {
                    $formattedDob = Carbon::parse($row[6])->format('Y-m-d');
                } catch (\Exception $e) {
                    continue; 
                }

                $lrn = trim($row[1]);

                DB::transaction(function () use ($row, $headers, $formattedDob, $lrn, &$newCount, &$updatedCount) {
                    $existingStudent = DB::table('students')->where('lrn', $lrn)->first();
                    $userId = $existingStudent ? $existingStudent->user_id : null;

                    $dynamicResponses = [];
                    for ($i = 41; $i < count($headers); $i++) {
                        $question = $headers[$i] ?? "Field " . $i;
                        $dynamicResponses[$question] = $row[$i] ?? null;
                    }
                    $newJson = json_encode($dynamicResponses);
                    $hasChanged = false;

                    if ($existingStudent) {
                        $existingUser = DB::table('users')->where('id', $userId)->first();
                        $existingEnrollment = DB::table('pre_enrollments')->where('student_lrn', $lrn)->first();

                        if (
                            ($existingUser && $existingUser->first_name !== trim($row[3])) ||
                            ($existingUser && $existingUser->last_name !== trim($row[2])) ||
                            ($existingUser && $existingUser->birthday !== $formattedDob) ||
                            $existingStudent->sex !== ($row[9] ?? null) || 
                            $existingStudent->age !== (is_numeric($row[8]) ? (int)$row[8] : null) ||
                            ($existingEnrollment && $existingEnrollment->responses !== $newJson)
                        ) {
                            $hasChanged = true;
                        }
                    }

                    if ($userId) {
                        DB::table('users')->where('id', $userId)->update([
                            'last_name'  => trim($row[2]),
                            'first_name' => trim($row[3]),
                            'middle_name'=> $row[4] ?? null,
                            'birthday'   => $formattedDob,
                            'updated_at' => $hasChanged ? now() : DB::raw('updated_at'),
                        ]);
                    } else {
                        $userId = DB::table('users')->insertGetId([
                            'last_name'  => trim($row[2]),
                            'first_name' => trim($row[3]),
                            'middle_name'=> $row[4] ?? null,
                            'birthday'   => $formattedDob,
                            'role'       => 'student',
                            'password'   => bcrypt($lrn),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('students')->updateOrInsert(
                        ['lrn' => $lrn],
                        [
                            'user_id'              => $userId,
                            'extension_name'       => $row[5] ?? null,
                            'sex'                  => $row[9] ?? null,
                            'age'                  => is_numeric($row[8]) ? (int)$row[8] : null,
                            'updated_at'           => ($hasChanged || !$existingStudent) ? now() : DB::raw('updated_at'),
                            'created_at'           => $existingStudent ? DB::raw('created_at') : now(), 
                        ]
                    );

                    DB::table('pre_enrollments')->updateOrInsert(
                        ['student_lrn' => $lrn],
                        [
                            'responses'  => $newJson,
                            'updated_at' => ($hasChanged || !$existingStudent) ? now() : DB::raw('updated_at'),
                        ]
                    );

                    if (!$existingStudent) $newCount++;
                    elseif ($hasChanged) $updatedCount++;
                });
            }

            DB::table('sync_histories')->insert([
                'records_synced' => $newCount + $updatedCount,
                'new_records' => $newCount,
                'updated_records' => $updatedCount,
                'status' => 'Success',
                'created_at' => now()
            ]);

            return back()->with('success', "Sync Complete! $newCount new and $updatedCount updated.");

        } catch (\Exception $e) {
            Log::error("Sync Error: " . $e->getMessage());
            return back()->with('error', 'Sync Failed: ' . $e->getMessage());
        }
    }
}