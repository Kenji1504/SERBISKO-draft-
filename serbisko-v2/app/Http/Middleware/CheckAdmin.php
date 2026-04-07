<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Skip checks for public routes
        if ($request->is('/') || $request->is('login') || $request->is('logout')) {
            return $next($request);
        }

        $userId = Session::get('user_id');
        $userRole = strtolower(Session::get('user_role')); // Normalize to lowercase

        // 2. Auth Check
        if (!$userId) {
            return redirect('/')->withErrors(['message' => 'Please login first.']);
        }

        // 3. Path-Based Permission Check
        if ($request->is('student/*') || $request->is('api/*')) {
            // Students, Admins, and Facilitators can all access the kiosk
            if (!in_array($userRole, ['student', 'admin', 'super_admin', 'facilitator'])) {
                return redirect('/')->withErrors(['message' => 'Unauthorized student access.']);
            }
        } else {
            // STRICT: Only Staff can access non-student routes (Dashboard, etc.)
            if (!in_array($userRole, ['admin', 'super_admin', 'facilitator'])) {
                // If a student tries to go to /dashboard, send them back to the kiosk
                return redirect('/student/grade-selection')->withErrors(['message' => 'Access Denied.']);
            }
        }

        // 4. The "Kill Switch"
        $user = DB::table('users')->where('id', $userId)->whereNull('deleted_at')->first();
        if (!$user) {
            Session::flush();
            return redirect('/')->withErrors(['message' => 'Your access has been revoked.']);
        }

        return $next($request);
    }   
}