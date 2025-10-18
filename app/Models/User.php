<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'country_id',
        'password',
        'role',
        'hospital_id',
        'lat',
        'lng',
        'phone',
        'gender',
        'dob',
        'address',
        'wallet_balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'dob' => 'date',
            'password' => 'hashed',
        ];
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function doctorProfile()
    {
        return $this->hasOne(DoctorProfile::class, 'doctor_id');
    }
    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'doctor_specialty', 'doctor_id');
    }
    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id');
    }
    public function timeoffs()
    {
        return $this->hasMany(DoctorTimeoff::class, 'doctor_id');
    }

    public function doctorAppointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }
    public function patientAppointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function conversationsAsDoctor()
    {
        return $this->hasMany(Conversation::class, 'doctor_id');
    }
    public function conversationsAsPatient()
    {
        return $this->hasMany(Conversation::class, 'patient_id');
    }

    public function prescriptionsAuthored()
    {
        return $this->hasMany(Prescription::class, 'doctor_id');
    }
    public function prescriptionsOwned()
    {
        return $this->hasMany(Prescription::class, 'patient_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
    public function credentials()
    {
        return $this->hasMany(DoctorCredential::class, 'doctor_id');
    }
    public function scopePharmacists($q)
    {
        return $q->where('role', 'pharmacist');
    }

    public function pharmacyProfile()
    {
        return $this->hasOne(PharmacyProfile::class, 'user_id');
    }

    public function appointmentsAsPatient()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }
    public function appointmentsAsDoctor()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    // Nice helper for your name fields
    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function getNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function getCountryNameAttribute(): string
    {
        return $this->country?->name;
    }

    public function scopeInCountry($q, $id)
    {
        return $q->where('country_id', $id);
    }

    public function scopeDoctors($q)
    {
        return $q->where('role', 'doctor');
    }

    public function scopeHospitals($q)
    {
        return $q->where('role', 'hospital');
    }

    public function scopePharmacies($q)
    {
        return $q->where('role', 'pharmacy');
    }
};
