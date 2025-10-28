<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Duration extends Model
{
    protected $fillable = [
        'minutes',
        'price',
        'duration_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGeneral($query)
    {
        return $query->where('duration_type', 'general');
    }

    public function scopeSpecialist($query)
    {
        return $query->where('duration_type', 'specialist');
    }

    /**
     * Get the price for this duration
     */
    public function getPrice(): float
    {
        return $this->price ?? 0;
    }

    /**
     * Get the price for this duration based on doctor specialization (legacy compatibility)
     */
    public function getPriceForDoctor($doctor = null): float
    {
        return $this->getPrice();
    }
}
