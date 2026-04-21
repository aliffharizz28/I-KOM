<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; 
use App\Models\pengguna;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {   
        $request->validate([
        'email' => ['required','email'],
        'password' => ['required'],
    ]);

    $user = pengguna::where('fld_user_email', $request->email)->first();

    if (!$user) {
        return back()->withErrors([
            'email' => 'E-mel atau kata laluan tidak sah.'
        ])->onlyInput('email');
    }

    if (!Hash::check($request->password, $user->fld_user_pass)) {
        return back()->withErrors([
            'email' => 'E-mel atau kata laluan tidak sah.'
        ])->onlyInput('email');
    }

    // Log the user in through Laravel's authentication system
    Auth::login($user);
    $request->session()->regenerate();

    return redirect()->intended('dashboard');
        
    }
}