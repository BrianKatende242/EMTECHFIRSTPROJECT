<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Duration extends Model
{
    protected $fillable = [
        'minutes',
        'general_price',
        'specialist_price',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'general_price' => 'integer',
        'specialist_price' => 'integer',
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
        return $query->where('type', 'general');
    }

    public function scopeSpecialist($query)
    {
        return $query->where('type', 'specialist');
    }

    /**
     * Get the price for this duration based on its type
     */
    public function getPrice(): float
    {
        return $this->type === 'general' ? $this->general_price : $this->specialist_price;
    }

    /**
     * Get the price for this duration based on doctor specialization
     */
    public function getPriceForDoctor($doctor = null): float
    {
        if ($doctor && is_object($doctor)) {
            // If doctor is provided, use specialization to determine price
            $specialization = strtolower($doctor->specialization ?? '');
            $isSpecialist = str_contains($specialization, 'specialist') ||
                           (str_contains($specialization, 'surgeon') && !str_contains($specialization, 'general'));
            return $isSpecialist ? $this->specialist_price : $this->general_price;
        }

        // Fallback to the original logic
        return $this->getPrice();
    }
}
