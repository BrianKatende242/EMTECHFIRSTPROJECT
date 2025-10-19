<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabTest extends Model
{
    protected $fillable = [
        'school_id',
        'patient_id',
        'test_type',
        'notes',
        'status',
        'results'
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}