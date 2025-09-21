<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UniversalMail;
use App\Services\OneTimeCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{
    public function showVerify()
    {
        abort_unless(Auth::check(), 403);
        // If already verified, go dashboard
        if (auth()->user()->email_verified_at) {
            $role = auth()->user()->role;
            switch ($role) {
                case 'admin':
                    $redirect = 'admin.dashboard';
                    break;
                case 'doctor':
                    $redirect = 'doctor.dashboard';
                    break;
                case 'pharmacist':
                    $redirect = 'pharmacist.dashboard';
                    break;
                case 'dispatcher':
                    $redirect = 'dispatcher.dashboard';
                    break;
                default:
                    $redirect = 'patient.dashboard';
            }
            return redirect()->route($redirect);
        }
        return view('auth.verify');
    }

    public function sendVerifyCode(Request $r)
    {
        abort_unless(Auth::check(), 403);

        $email = auth()->user()->email;
        $code = OneTimeCode::make($email, 'verify', 15);

        // TODO: send mail
        $mailData = [
            'subject' => 'Your Email Verification Code',
            'view'    => 'emails.universal_code',
            'title'   => 'Verify your email',
            'intro'   => 'Use the 6-digit code below to verify your email. It expires in 15 minutes.',
            'code'    => $code,
            'footer'  => 'If you didn’t request this, you can ignore this email.'
        ];
        Mail::to($email)->send(new UniversalMail($mailData));

        return response()->json(['ok' => true, 'message' => 'Verification code sent']);
    }

    public function verify(Request $r)
    {
        abort_unless(Auth::check(), 403);

        $r->validate(['code' => ['required', 'digits:6']]);
        $email = auth()->user()->email;

        if (!OneTimeCode::check($email, 'verify', $r->code)) {
            return response()->json(['ok' => false, 'message' => 'Invalid or expired code'], 422);
        }

        auth()->user()->forceFill(['email_verified_at' => now()])->save();
        $role = auth()->user()->role;
            switch ($role) {
                case 'admin':
                    $redirect = 'admin.dashboard';
                    break;
                case 'doctor':
                    $redirect = 'doctor.dashboard';
                    break;
                case 'pharmacist':
                    $redirect = 'pharmacist.dashboard';
                    break;
                case 'dispatcher':
                    $redirect = 'dispatcher.dashboard';
                    break;
                default:
                    $redirect = 'patient.dashboard';
            }
        return response()->json(['ok' => true, 'message' => 'Email verified', 'redirect' => route($redirect)]);
    }
}
