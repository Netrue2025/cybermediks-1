<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UniversalMail;
use App\Models\User;
use App\Services\OneTimeCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordController extends Controller
{
    public function showForgot()
    {
        return view('auth.forgot');
    }
    public function showReset()
    {
        return view('auth.reset');
    }

    public function sendResetCode(Request $r)
    {
        $r->validate(['email' => ['required', 'email']]);
        $email = strtolower($r->email);

        // Generate regardless (avoid user enumeration)
        $code = OneTimeCode::make($email, 'reset', 15);

        // If user exists, email the code (silently)
        if (User::where('email', $email)->exists()) {
            $mailData = [
                'subject' => 'Your Password Reset Code',
                'view'    => 'emails.universal_code',
                'title'   => 'Reset your password',
                'intro'   => 'Enter this 6-digit code to reset your password. It expires in 15 minutes.',
                'code'    => $code,
                'footer'  => 'If you didn’t request this, you can ignore this email.'
            ];
            Mail::to($email)->send(new UniversalMail($mailData));
        }
        return response()->json(['ok' => true, 'message' => 'If the email exists, a reset code has been sent.']);
    }

    public function reset(Request $r)
    {
        $data = $r->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $email = strtolower($data['email']);

        // Validate code
        if (!OneTimeCode::check($email, 'reset', $data['code'])) {
            return response()->json(['ok' => false, 'message' => 'Invalid or expired code'], 422);
        }

        $user = User::where('email', $email)->first();
        if (!$user) return response()->json(['ok' => false, 'message' => 'Account not found'], 404);

        $user->update(['password' => Hash::make($data['password'])]);

        return response()->json(['ok' => true, 'message' => 'Password updated', 'redirect' => route('login.show')]);
    }
}
