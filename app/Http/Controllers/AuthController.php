<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Use the Eloquent Model
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Get inputs
        $lastName = $request->input('last_name');
        $givenName = $request->input('given_name');
        $middleName = $request->input('middle_name');
        $dob = $request->input('dob');
        $password = $request->input('password');

        // 2. Find user using Eloquent (Required for Auth::login)
        $query = User::where('last_name', $lastName)
            ->where('first_name', $givenName)
            ->where('birthday', $dob)
            ->whereNull('deleted_at');

        if (!empty($middleName)) {
            $query->where('middle_name', $middleName);
        } else {
            $query->where(function($q) {
                $q->whereNull('middle_name')->orWhere('middle_name', '');
            });
        }

        $user = $query->first();

        // 3. Authenticate
        if ($user && Hash::check($password, $user->password)) {
            
            // CRITICAL: Log the user into Laravel's Auth system
            // This is what makes auth()->user() work in your header
            Auth::login($user);

            $role = strtolower($user->role); 

            // Save manual keys for your CheckAdmin middleware
            Session::put('user_id', $user->id);
            Session::put('user_role', $role); 
            Session::put('user_name', $user->first_name);

            // 4. Redirect based on Role
            if (in_array($role, ['admin', 'super_admin', 'facilitator'])) {
                return redirect('/dashboard'); 
            } else {
                return redirect('/student/grade-selection');
            }
        }

        return back()->withErrors(['message' => 'Invalid credentials. Please check your details.']);
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['user_id', 'user_role', 'user_name']);

        // Standard Laravel logout
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}