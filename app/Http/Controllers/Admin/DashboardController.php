<?php

namespace App\Http\Controllers\Admin; 

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request) 
    {
        $settings = DB::table('system_settings')->first();
        $grade = $request->grade_level;

        // Reusable filter - REMOVED school_year as it doesn't exist in students table in DB
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

        // --- Core Enrollment Stats ---
        // Joined using actual DB columns (students.id, students.user_id)
        $baseQuery = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id') 
            ->whereNull('users.deleted_at')
            ->leftJoin('pre_enrollments', 'students.id', '=', 'pre_enrollments.student_id')
            ->leftJoin('kiosk_enrollments', 'students.id', '=', 'kiosk_enrollments.student_id');

        $totalRegistrations = $applyFilter(clone $baseQuery)->count('students.lrn');

        $totalSubmissions = $applyFilter(clone $baseQuery)
            ->whereNotNull('kiosk_enrollments.student_id')
            ->count('students.lrn');

        $totalEnrolled = $applyFilter(clone $baseQuery)
            ->where('kiosk_enrollments.academic_status', '=', 'Officially Enrolled')
            ->count('students.lrn');

        // --- Stats Calculation ---
        $max = $totalRegistrations > 0 ? $totalRegistrations : 1;
        $percVerified = ($totalSubmissions / $max) * 100;
        $percEnrolled = ($totalEnrolled / $max) * 100;

        // --- Elective Counting ---
        $rawCounts = DB::table('kiosk_enrollments')
            ->join('students', 'kiosk_enrollments.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->whereNull('users.deleted_at')
            ->when(!empty($grade), function($query) use ($grade) {
                return $query->where('kiosk_enrollments.grade_level', $grade);
            })
            ->whereIn('cluster', ['STEM', 'ASSH', 'BE', 'TechPro'])
            ->select('cluster', DB::raw('count(*) as count'))
            ->groupBy('cluster')
            ->pluck('count', 'cluster')
            ->toArray();

        $electiveCounts = [
            'STEM'    => $rawCounts['STEM'] ?? 0,
            'ASSH'    => $rawCounts['ASSH'] ?? 0,
            'BE'      => $rawCounts['BE'] ?? 0,
            'TechPro' => $rawCounts['TechPro'] ?? 0
        ];

        // --- Recent Submissions ---
        $recentKioskSubmissions = DB::table('kiosk_enrollments')
            ->join('students', 'kiosk_enrollments.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->whereNull('users.deleted_at')
            ->select(
                'users.first_name', 'users.middle_name', 'users.last_name',
                'users.extension_name', 'kiosk_enrollments.grade_level',
                'kiosk_enrollments.track', 'kiosk_enrollments.cluster',
                'kiosk_enrollments.completed_at', 'kiosk_enrollments.academic_status as status'
            )
            ->when(!empty($grade), function($q) use ($grade) {
                return $q->where('kiosk_enrollments.grade_level', $grade);
            })
            ->orderBy('kiosk_enrollments.completed_at', 'desc')
            ->limit(5)
            ->get();

        // --- Sync & Gradient logic ---
        $lastSync = DB::table('sync_histories')->where('status', 'Success')->latest()->first();
        $lastSyncTime = $lastSync ? Carbon::parse($lastSync->created_at)->diffForHumans() : 'Never';
        $activeSY = $settings ? $settings->active_school_year : '2025-2026';

        $totalElectives = array_sum($electiveCounts) ?: 1;
        $pSTEM = ($electiveCounts['STEM'] / $totalElectives) * 100;
        $pASSH = ($electiveCounts['ASSH'] / $totalElectives) * 100;
        $pBE   = ($electiveCounts['BE'] / $totalElectives) * 100;
        $pTech = ($electiveCounts['TechPro'] / $totalElectives) * 100;

        $stop1 = $pSTEM;
        $stop2 = $stop1 + $pASSH;
        $stop3 = $stop2 + $pBE;

        $donutGradient = "conic-gradient(#00568d 0% {$stop1}%, #00897b {$stop1}% {$stop2}%, #1a8a44 {$stop2}% {$stop3}%, #facc15 {$stop3}% 100%)";

        $data = compact(
            'totalRegistrations', 'totalSubmissions', 'totalEnrolled',
            'percVerified', 'percEnrolled', 'electiveCounts',
            'donutGradient', 'lastSyncTime', 'recentKioskSubmissions', 'activeSY'
        );

        if ($request->ajax()) {
            return view('admin.dashboardpage.partials._dashboard_wrapper', $data)->render();
        }

        return view('admin.dashboardpage.dashboard', $data);
    }

    public function checkUserStatus($id)
    {
        $isOnline = Cache::has('user-is-online-' . $id);
        return response()->json(['online' => $isOnline]);
    }
}