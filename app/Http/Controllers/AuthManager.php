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
            'email' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');
     
        if (Auth::attempt($credentials))
        return view('teacher.addquiz');
    }

    public function login()
    {
        return view('teacher.teacherauth');
    }

    public function registrationPost(Request $request)
    {
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['password'] = Hash::make($request->password);
        $data['room_name'] = $request->room_name;

        $roomExists = User::where('room_name', $request->room_name)->exists();
        if ($roomExists) {
          
            return redirect()->back()->with('error', 'Room name already in use. Please choose another room.');
        }
        else{
        $user = User::create($data);
        if (!$user) {
            return redirect(route('TorS'))->with("error", "Try Again!");
        }
         else{


        Auth::login($user);
        return view('teacher.addquiz');
          
         }

  }
    }


    
}