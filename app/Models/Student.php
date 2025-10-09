<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'grade',
        'birth_date',
        'parent_contact'
    ];

    /**
     * Get the school that owns the student
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function labTests(): HasMany
    {
        return $this->hasMany(LabTest::class);
    }

    /**
     * Get the age of the student calculated from birth_date
     */
    public function getAgeAttribute()
    {
        return $this->birth_date ? \Carbon\Carbon::parse($this->birth_date)->age : null;
    }
}


