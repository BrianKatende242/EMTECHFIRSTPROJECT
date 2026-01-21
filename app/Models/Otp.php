<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Otp extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'school_id',
        'code',
        'expires_at',
        'used',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Relationship: OTP belongs to a School
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Check if the OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the OTP is valid (not used & not expired)
     */
    public function isValid(): bool
    {
        return !$this->used && !$this->isExpired();
    }
}
