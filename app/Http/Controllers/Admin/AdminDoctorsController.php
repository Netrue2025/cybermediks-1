<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentDispute;
use App\Models\Conversation;
use App\Models\Country;
use App\Models\DoctorCredential;
use App\Models\LabworkRequest;
use App\Models\Message;
use App\Models\Prescription;
use App\Models\User;
use App\Models\WalletHold;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminDoctorsController extends Controller
{
    public function index(Request $r)
    {
        $q           = trim((string) $r->query('q', ''));
        $countryId   = $r->query('country_id');                 // preferred (FK)
        $countryName = trim((string) $r->query('country', '')); // legacy text filter (optional)

        // Countries that actually have doctors (for the dropdown)
        $countries = Country::get(['id', 'name', 'iso2']);

        $doctors = User::with([
            'doctorProfile:id,doctor_id,is_available,title,consult_fee,avg_duration',
            'specialties',
            'country:id,name,iso2', // optional, if you want to show country names/flags
        ])
            ->where('role', 'doctor')

            // search by name/title (/optionally specialties)
            ->when($q !== '', function ($w) use ($q) {
                $like = "%{$q}%";
                $w->where(function ($x) use ($like) {
                    $x->where('first_name', 'like', $like)
                        ->orWhere('last_name',  'like', $like)
                        ->orWhereHas('doctorProfile', fn($p) => $p->where('title', 'like', $like));
                    // ->orWhereHas('specialties', fn ($s) => $s->where('name', 'like', $like));
                });
            })

            // preferred: filter by country FK
            ->when(filled($countryId), fn($w) => $w->where('country_id', $countryId))

            // legacy: accept free-text country name or ISO2 if no country_id provided
            ->when($countryName !== '' && empty($countryId), function ($w) use ($countryName) {
                $needle = strtolower($countryName);
                $w->where(function ($x) use ($needle) {
                    $x->whereHas('country', function ($c) use ($needle) {
                        $c->whereRaw('LOWER(name) = ?', [$needle])
                            ->orWhereRaw('LOWER(iso2) = ?', [$needle]);
                    });

                    // if you still have a legacy users.country string column and want to honor it, uncomment:
                    // ->orWhereRaw('LOWER(country) = ?', [$needle]);
                });
            })

            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        // Pending credentials (scope to chosen country if provided)
        $credentials = \App\Models\DoctorCredential::with('doctor')
            ->where('status', 'pending')
            ->when(
                filled($countryId),
                fn($q2) =>
                $q2->whereHas('doctor', fn($d) => $d->where('country_id', $countryId))
            )
            ->latest()
            ->take(10)
            ->get();

        return view('admin.doctors.index', [
            'doctors'     => $doctors,
            'q'           => $q,
            'credentials' => $credentials,
            'country'     => $countryName,   // keep legacy param for the form if you still show it
            'countryId'   => $countryId,
            'countries'   => $countries,
        ]);
    }



    public function availability($id)
    {
        $doc = User::where('role', 'doctor')->with('doctorProfile')->findOrFail($id);
        $doc->doctorProfile?->update(['is_available' => ! (bool) $doc->doctorProfile->is_available]);
        return back()->with('success', 'Availability toggled');
    }

    public function approveCredential(Request $r, $id)
    {
        // Expecting ->input('credential_id')
        $credId = (int) $r->input('credential_id');
        $cred = DoctorCredential::where('doctor_id', $id)->findOrFail($credId);
        $cred->update(['status' => 'approved']); // requires status column
        return back()->with('success', 'Credential approved');
    }

    public function credentials(User $doctor)
    {
        // authorize admin as you already do
        $docs = DoctorCredential::where('doctor_id', $doctor->id)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.doctors._credentials', compact('doctor', 'docs'));
    }

    public function deleteDoctor($id)
    {
        $doctor = User::where('id', $id)
            ->where('role', 'doctor')
            ->first();
        
        if (!$doctor) {
            return response()->json(['ok' => false, 'message' => 'Doctor not found'], 404);
        }
        
        // Prevent deleting yourself if you're a doctor (though admins shouldn't be doctors)
        if ($doctor->id === Auth::id()) {
            return response()->json(['ok' => false, 'message' => 'You cannot delete yourself'], 422);
        }
        
        try {
            DB::transaction(function () use ($doctor) {
                $doctorId = $doctor->id;
                
                // First, handle foreign key constraint: if this doctor is referenced as a hospital,
                // set hospital_id to null for all doctors that reference it
                DB::table('users')
                    ->where('hospital_id', $doctorId)
                    ->update(['hospital_id' => null]);
                
                // Also set this doctor's hospital_id to null if it has one
                if ($doctor->hospital_id) {
                    $doctor->hospital_id = null;
                    $doctor->save();
                }
                
                // Delete doctor-specific data
                DB::table('doctor_profiles')->where('doctor_id', $doctorId)->delete();
                DB::table('doctor_schedules')->where('doctor_id', $doctorId)->delete();
                DB::table('doctor_timeoffs')->where('doctor_id', $doctorId)->delete();
                DB::table('doctor_credentials')->where('doctor_id', $doctorId)->delete();
                DB::table('doctor_specialty')->where('doctor_id', $doctorId)->delete();
                
                // Delete appointments where doctor is involved
                $appointmentIds = DB::table('appointments')->where('doctor_id', $doctorId)->pluck('id');
                if ($appointmentIds->isNotEmpty()) {
                    // Delete appointment disputes
                    DB::table('appointment_disputes')->whereIn('appointment_id', $appointmentIds)->delete();
                    // Delete appointments
                    DB::table('appointments')->where('doctor_id', $doctorId)->delete();
                }
                
                // Delete prescriptions where doctor is involved
                $prescriptionIds = DB::table('prescriptions')->where('doctor_id', $doctorId)->pluck('id');
                if ($prescriptionIds->isNotEmpty()) {
                    // Delete prescription items
                    DB::table('prescription_items')->whereIn('prescription_id', $prescriptionIds)->delete();
                    // Delete orders related to prescriptions
                    $orderIds = DB::table('orders')->whereIn('prescription_id', $prescriptionIds)->pluck('id');
                    if ($orderIds->isNotEmpty()) {
                        DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
                        DB::table('orders')->whereIn('prescription_id', $prescriptionIds)->delete();
                    }
                    // Delete prescriptions
                    DB::table('prescriptions')->where('doctor_id', $doctorId)->delete();
                }
                
                // Delete conversations where doctor is involved
                $conversationIds = DB::table('conversations')->where('doctor_id', $doctorId)->pluck('id');
                if ($conversationIds->isNotEmpty()) {
                    // Delete messages in those conversations
                    DB::table('messages')->whereIn('conversation_id', $conversationIds)->delete();
                    // Delete conversations
                    DB::table('conversations')->where('doctor_id', $doctorId)->delete();
                }
                
                // Delete messages sent by doctor
                DB::table('messages')->where('sender_id', $doctorId)->delete();
                
                // Delete wallet transactions
                DB::table('wallet_transactions')->where('user_id', $doctorId)->delete();
                
                // Delete wallet holds where doctor is source or target
                DB::table('wallet_holds')->where('source_user_id', $doctorId)->orWhere('target_user_id', $doctorId)->delete();
                
                // Delete withdrawal requests
                DB::table('withdrawal_requests')->where('user_id', $doctorId)->delete();
                
                // Delete labwork requests where doctor is labtech
                DB::table('labwork_requests')->where('labtech_id', $doctorId)->delete();
                
                // Finally, delete the doctor user record
                $doctor->delete();
            });
            
            return response()->json(['ok' => true, 'message' => 'Doctor and all related data deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => 'Failed to delete doctor: ' . $e->getMessage()], 500);
        }
    }
}
