<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DoctorInvite extends Model
{
    protected $fillable = [
        'email',
        'token',
        'expires_at',
        'used',
        'specialization',
        'school_id',
        'health_facility_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Generate a new invite token
     */
    public static function generateToken()
    {
        return Str::random(64);
    }

    /**
     * Check if the invite is expired
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the invite is valid (not used and not expired)
     */
    public function isValid()
    {
        return !$this->used && !$this->isExpired();
    }

    /**
     * Mark the invite as used
     */
    public function markAsUsed()
    {
        $this->update(['used' => true]);
    }

    /**
     * Get the school that the doctor is being invited to
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the health facility that the doctor is being invited to
     */
    public function healthFacility()
    {
        return $this->belongsTo(HealthFacility::class);
    }
}