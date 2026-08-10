<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function TorS(Request $request)
    {
        // Retrieve the 'role' parameter from the request
        $role = $request->input('role');

        // Redirect based on the role
        if ($role === 'teacher') {
            return view('teacher.teacherauth'); // teacher.blade.php
        } elseif ($role === 'student') {
            return redirect()->route('student.app');
        } else {
            return redirect()->back()->with('error', 'Invalid selection.');
        }
    }

    // public function showLogin()
    // {
    //     return view('auth.login'); // Separate login view
    // }
}