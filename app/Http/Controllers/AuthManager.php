<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;

class AuthManager extends Controller
{
   
  

    public function registration()
    {
        return view('teacher.registration');
    }

    public function loginPost(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('teacher.view'));
        }

        return redirect()->back()->withInput($request->only('email'))->with('error', 'Invalid email or password.');
    }

    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('teacher.view');
        }
        return view('teacher.teacherauth');
    }

    public function registrationPost(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:4',
            'room_name' => 'required|string|max:255|unique:users,room_name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'room_name' => $request->room_name,
        ]);

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Registration failed. Please try again.');
        }

        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('teacher.view');
    }


    
}