<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentDispute;
use App\Models\Conversation;
use App\Models\Country;
use App\Models\LabworkRequest;
use App\Models\Message;
use App\Models\Order;
use App\Models\Prescription;
use App\Models\User;
use App\Models\WalletHold;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminUsersController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->query('q', ''));
        $rawRole = $r->query('role');                 // optional
        $countryId = $r->query('country_id');         // preferred filter (FK)
        $countryName = trim((string) $r->query('country', '')); // legacy text filter (optional)

        // Optional: constrain role to a known set; default to 'patient'
        $allowedRoles = ['patient', 'doctor', 'pharmacy', 'dispatcher', 'admin'];
        $role = in_array($rawRole, $allowedRoles, true) ? $rawRole : 'patient';

        // For a dropdown in the UI
        $countries = Country::orderBy('name')->get(['id', 'name', 'iso2']);

        $users = User::query()
            ->with(['country:id,name,iso2'])                 // if you have a belongsTo relation `country()`
            ->where('role', $role)
            ->when($q !== '', function ($w) use ($q) {
                $like = "%{$q}%";
                $w->where(function ($x) use ($like) {
                    $x->where('first_name', 'like', $like)
                        ->orWhere('last_name',  'like', $like)
                        ->orWhere('email',      'like', $like)
                        ->orWhere('phone',      'like', $like); // optional if you store phone
                });
            })
            // Preferred: filter by foreign key
            ->when(filled($countryId), fn($w) => $w->where('country_id', $countryId))

            // Legacy fallback: if you still have a plain text `country` column OR want name-based matching
            ->when($countryName !== '' && empty($countryId), function ($w) use ($countryName) {
                $w->where(function ($x) use ($countryName) {
                    // If you have a countries table + relation:
                    $x->whereHas(
                        'country',
                        fn($c) =>
                        $c->whereRaw('LOWER(name) = ?', [strtolower($countryName)])
                            ->orWhereRaw('LOWER(iso2) = ?', [strtolower($countryName)])
                    );
                    // If you still keep a legacy users.country text column, uncomment:
                    // ->orWhereRaw('LOWER(country) = ?', [strtolower($countryName)]);
                });
            })

            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users'      => $users,
            'q'          => $q,
            'role'       => $role,
            'countryId'  => $countryId,
            'country'    => $countryName, // for the legacy input, if you keep it in the form
            'countries'  => $countries,
        ]);
    }




    public function toggleActive(User $user)
    {
        $user->is_active = ! (bool)$user->is_active;
        $user->save();
        return back()->with('success', 'User status updated');
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'User not found'], 404);
        }
        
        // Prevent admin from deleting themselves
        if ($user->id === Auth::id()) {
            return response()->json(['ok' => false, 'message' => 'You cannot delete yourself'], 422);
        }
        
        try {
            DB::transaction(function () use ($user) {
                $userId = $user->id;
                $role = $user->role;
                
                // Handle role-specific deletions
                if ($role === 'doctor') {
                    // Delete doctor-specific data
                    DB::table('doctor_profiles')->where('doctor_id', $userId)->delete();
                    DB::table('doctor_schedules')->where('doctor_id', $userId)->delete();
                    DB::table('doctor_timeoffs')->where('doctor_id', $userId)->delete();
                    DB::table('doctor_credentials')->where('doctor_id', $userId)->delete();
                    DB::table('doctor_specialty')->where('doctor_id', $userId)->delete();
                }
                
                if ($role === 'pharmacy') {
                    // Delete pharmacy-specific data
                    DB::table('pharmacy_profiles')->where('user_id', $userId)->delete();
                    // Delete inventory items (no FK constraint)
                    DB::table('inventory_items')->where('pharmacy_id', $userId)->delete();
                }
                
                // Handle hospital relationships
                if ($role === 'hospital') {
                    // Set hospital_id to null for doctors belonging to this hospital
                    DB::table('users')
                        ->where('hospital_id', $userId)
                        ->where('role', 'doctor')
                        ->update(['hospital_id' => null]);
                }
                
                // Delete appointments where user is patient or doctor
                $appointmentIds = DB::table('appointments')
                    ->where(function($query) use ($userId) {
                        $query->where('patient_id', $userId)
                              ->orWhere('doctor_id', $userId);
                    })
                    ->pluck('id');
                
                if ($appointmentIds->isNotEmpty()) {
                    // Delete appointment disputes
                    DB::table('appointment_disputes')->whereIn('appointment_id', $appointmentIds)->delete();
                }
                // Delete appointments
                DB::table('appointments')
                    ->where(function($query) use ($userId) {
                        $query->where('patient_id', $userId)
                              ->orWhere('doctor_id', $userId);
                    })
                    ->delete();
                
                // Delete prescriptions where user is patient or doctor
                $prescriptionIds = DB::table('prescriptions')
                    ->where(function($query) use ($userId) {
                        $query->where('patient_id', $userId)
                              ->orWhere('doctor_id', $userId);
                    })
                    ->pluck('id');
                
                if ($prescriptionIds->isNotEmpty()) {
                    // Delete prescription items
                    DB::table('prescription_items')->whereIn('prescription_id', $prescriptionIds)->delete();
                    // Delete orders related to prescriptions
                    $orderIds = DB::table('orders')->whereIn('prescription_id', $prescriptionIds)->pluck('id');
                    if ($orderIds->isNotEmpty()) {
                        DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
                        DB::table('orders')->whereIn('prescription_id', $prescriptionIds)->delete();
                    }
                }
                // Delete prescriptions
                DB::table('prescriptions')
                    ->where(function($query) use ($userId) {
                        $query->where('patient_id', $userId)
                              ->orWhere('doctor_id', $userId);
                    })
                    ->delete();
                
                // Delete orders where user is patient or pharmacy
                $orderIds = DB::table('orders')
                    ->where(function($query) use ($userId) {
                        $query->where('patient_id', $userId)
                              ->orWhere('pharmacy_id', $userId);
                    })
                    ->pluck('id');
                
                if ($orderIds->isNotEmpty()) {
                    DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
                }
                DB::table('orders')
                    ->where(function($query) use ($userId) {
                        $query->where('patient_id', $userId)
                              ->orWhere('pharmacy_id', $userId);
                    })
                    ->delete();
                
                // Delete conversations where user is patient or doctor
                $conversationIds = DB::table('conversations')
                    ->where(function($query) use ($userId) {
                        $query->where('patient_id', $userId)
                              ->orWhere('doctor_id', $userId);
                    })
                    ->pluck('id');
                
                if ($conversationIds->isNotEmpty()) {
                    // Delete messages in those conversations
                    DB::table('messages')->whereIn('conversation_id', $conversationIds)->delete();
                }
                // Delete conversations
                DB::table('conversations')
                    ->where(function($query) use ($userId) {
                        $query->where('patient_id', $userId)
                              ->orWhere('doctor_id', $userId);
                    })
                    ->delete();
                
                // Delete messages sent by user
                DB::table('messages')->where('sender_id', $userId)->delete();
                
                // Delete wallet transactions
                DB::table('wallet_transactions')->where('user_id', $userId)->delete();
                
                // Delete wallet holds where user is source or target
                DB::table('wallet_holds')
                    ->where(function($query) use ($userId) {
                        $query->where('source_user_id', $userId)
                              ->orWhere('target_user_id', $userId);
                    })
                    ->delete();
                
                // Delete withdrawal requests
                DB::table('withdrawal_requests')->where('user_id', $userId)->delete();
                
                // Delete labwork requests where user is patient or labtech
                DB::table('labwork_requests')
                    ->where(function($query) use ($userId) {
                        $query->where('patient_id', $userId)
                              ->orWhere('labtech_id', $userId);
                    })
                    ->delete();
                
                // Finally, delete the user record
                $user->delete();
            });
            
            return response()->json(['ok' => true, 'message' => 'User and all related data deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => 'Failed to delete user: ' . $e->getMessage()], 500);
        }
    }
}
