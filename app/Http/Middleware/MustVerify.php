<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\VerificationController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MustVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check())
        {
            $isVerified = !empty(Auth::user()->email_verified_at);

            if (!$isVerified)
            {
                (new VerificationController())->sendVerifyCode();
                return redirect()->route('verify.show');
            }
        }
        return $next($request);
    }
}
