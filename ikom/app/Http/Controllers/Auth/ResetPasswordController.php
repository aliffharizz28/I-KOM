<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\User;

class ResetPasswordController extends Controller
{
    /**
     * Show the reset password form.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     * @param \Illuminate\Http\Request $request
     */
    public function showResetForm(Request $request, $token )
    {
        return view('resetpassword', [
            'token' => $token,
            'email' => $request->email,
            ]);
    }

    /**
     * Handle the password reset process.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        try{
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'token' => 'required'
        ]);

        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$reset) {
            return back()->with('error','Permintaan penetapan semula kata laluan tidak sah.');
        }

        if (!Hash::check($request->token, $reset->token)) {
            return back()->with('error','Token tidak sah.');
        }

        DB::table('pengguna')
            ->where('fld_user_email',$request->email)
            ->update([
                'fld_user_pass' => Hash::make($request->password)
            ]);

        DB::table('password_resets')
            ->where('email',$request->email)
            ->delete();

        return redirect('/login')->with('success','Kata laluan berjaya dikemaskini. Sila log masuk.');
        }
        catch (\Exception $e) //kalau error, log error message untuk debugging
        {
        Log::error('Terdapat kesalahan semasa mengemaskini kata laluan: '.$e->getMessage());
        return back()->with('error','Terdapat kesalahan semasa mengemaskini kata laluan. Sila cuba lagi.');
        }
        
    }
}