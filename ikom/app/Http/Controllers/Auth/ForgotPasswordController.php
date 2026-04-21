<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /** Show the email request form */
    public function showLinkRequestForm(): View
    {
        return view('forgotpassword');
    }

    /** Handle the email submission */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:pengguna,fld_user_email',
        ]);

        try {
            $token = Str::random(60);

            // Delete existing reset tokens
            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                ['token' => Hash::make($token),
                 'created_at' => now()]
            );

            // Store new reset token (hashed)
            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]);


            // Send email
            Mail::send('auth.verify', ['token' => $token, 'email' => $request->email], function ($message) use ($request) {
                $message->from(config('mail.from.address'), config('mail.from.name'));
                $message->to($request->email)->subject('Pemberitahuan Penetapan Semula Kata Laluan');
            });

            return back()->with('success', 'Pautan penetapan semula kata laluan telah dihantar ke e-mel anda. Sila semak e-mel anda.');

        } catch (\Exception $e) {
            Log::error('Terdapat kesalahan semasa menghantar e-mel penetapan semula kata laluan: '.$e->getMessage());
            return back()->with('error', 'Terdapat kesalahan semasa menghantar e-mel penetapan semula kata laluan. Sila cuba lagi.');
        }
        
    }
}