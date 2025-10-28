<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use App\Services\OneTimeCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showRegister()
    {
        $countries = Country::all();
        return view('auth.register', compact('countries'));
    }
    public function showLogin()
    {
        return view('auth.login');
    }

    public function register(Request $r)
    {
        $facilityRoles = ['hospital', 'pharmacy', 'labtech'];
        $isFacility = in_array($r->input('role'), $facilityRoles, true);

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'       => ['required', 'string', 'in:hospital,pharmacy,dispatcher,patient,labtech,health,transport'],
            'country_id' => ['required', 'string', 'exists:countries,id'],
            'password'   => ['required', 'confirmed', Password::min(6)],
        ];

        // Facility-only fields
        $facilityRules = [
            'facility_name'       => Rule::requiredIf($isFacility),
            'address_building_no' => Rule::requiredIf($isFacility),
            'address_street'      => Rule::requiredIf($isFacility),
            'city'                => Rule::requiredIf($isFacility),
            'state'               => Rule::requiredIf($isFacility),
        ];

        $data = $r->validate(array_merge($rules, $facilityRules));

        $user = User::create([
            'first_name'  => $data['first_name'],
            'last_name'   => $data['last_name'],
            'email'       => strtolower($data['email']),
            'role'        => $data['role'],
            'country_id'  => $data['country_id'],
            'password'    => Hash::make($data['password']),

            // If you're storing on the users table (make columns nullable in a migration)
            'facility_name'       => $data['facility_name']       ?? null,
            'address_building_no' => $data['address_building_no'] ?? null,
            'address'      => $data['address_street']      ?? null,
            'city'                => $data['city']                ?? null,
            'state'               => $data['state']               ?? null,
        ]);

        Auth::login($user);
        (new VerificationController())->sendVerifyCode();

        return response()->json([
            'ok'       => true,
            'message'  => 'Account created. Verification code sent to your email.',
            'redirect' => route('verify.show')
        ]);
    }

    public function login(Request $r)
    {
        $r->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($r->only('email', 'password'), true)) {
            $r->session()->regenerate();
            $role = auth()->user()->role;
            switch ($role) {
                case 'admin':
                    $redirect = 'admin.dashboard';
                    break;
                case 'doctor':
                    $redirect = 'doctor.dashboard';
                    break;
                case 'hospital':
                    $redirect = 'hospital.dashboard';
                    break;
                case 'pharmacy':
                    $redirect = 'pharmacy.dashboard';
                    break;
                case 'dispatcher':
                    $redirect = 'dispatcher.dashboard';
                    break;
                case 'health':
                    $redirect = 'health.dashboard';
                    break;
                case 'transport':
                    $redirect = 'transport.dashboard';
                    break;
                default:
                    $redirect = 'patient.dashboard';
            }
            if (!auth()->user()->email_verified_at) {
                (new VerificationController())->sendVerifyCode();
                return response()->json([
                    'ok' => true,
                    'redirect' => route('verify.show')
                ]);
            }
            return response()->json([
                'ok' => true,
                'redirect' => route($redirect)
            ]);
        }
        return response()->json(['ok' => false, 'message' => 'Invalid credentials'], 422);
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('home');
    }
}
