<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    public function verification()
    { 
        // Query the actual scans table for manual verification requests
        $pendingScans = DB::table('scans')
            ->join('users', 'scans.user_id', '=', 'users.id')
            ->leftJoin('students', 'users.id', '=', 'students.user_id')
            ->leftJoin('kiosk_enrollments', 'students.id', '=', 'kiosk_enrollments.student_id')
            ->leftJoin('pre_enrollments as pe', 'students.id', '=', 'pe.student_id')
            ->where('scans.status', 'manual_verification')
            ->select(
                'scans.id',
                'scans.document_type',
                'scans.file_path',
                'scans.created_at',
                'users.first_name', 
                'users.last_name',
                'kiosk_enrollments.grade_level as kiosk_grade',
                'pe.responses'
            )
            ->get()
            ->map(function($scan) {
                $details = json_decode($scan->responses, true) ?? [];
                $scan->display_grade = $scan->kiosk_grade ?? ($details['Grade Level to Enroll'] ?? '—');
                return $scan;
            });

        // Current database schema is missing rejected_papers column in kiosk_enrollments
        $rejectedPapers = collect();

        if (request()->ajax()) {
            return view('admin.partials.verification-table', compact('pendingScans'))->render();
        }

        return view('admin.verification', compact('pendingScans', 'rejectedPapers')); 
    }

    public function handleVerificationAction(Request $request) 
    {
        $scanId = $request->input('scan_id'); 
        $action = $request->input('action'); 
        
        $finalStatus = ($action === 'approve') ? 'verified' : 'failed';

        // Update the actual scan record
        DB::table('scans')->where('id', $scanId)->update([
            'status' => $finalStatus,
            'remarks' => 'Manually ' . $action . 'd by Admin',
            'updated_at' => now()
        ]);

        return back()->with('success', 'Document has been ' . $action . 'd.');
    }

    public function collectRejectedPaper(Request $request)
    {
        // This feature is currently disabled due to missing schema columns
        return back()->with('error', 'Physical collection feature is currently unavailable.');
    }
}