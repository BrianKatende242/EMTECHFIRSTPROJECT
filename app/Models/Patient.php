<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'health_facility_id',
        'name',
        'gender',
        'birth_date',
        'contact_number',
        'medical_history',
        'patient_id',
        'school_id',
        'parent_contact',
        'grade'
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function healthFacility()
    {
        return $this->belongsTo(HealthFacility::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    public function maternalDocuments()
{
    return $this->hasMany(MaternalDocument::class);
}

    /**
     * Generate a unique patient ID
     */
    public static function generatePatientId()
    {
        do {
            $patientId = 'P' . str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('patient_id', $patientId)->exists());

        return $patientId;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (empty($patient->patient_id)) {
                $patient->patient_id = self::generatePatientId();
            }
        });
    }
}