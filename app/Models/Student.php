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
     * Boot the model and add deleting handler to cascade-delete related records.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Student $student) {
            // Delete related appointments so model events on Appointment run
            $student->appointments()->get()->each(function ($appt) {
                $appt->delete();
            });

            // Delete related lab tests to prevent foreign key constraint errors
            $student->labTests()->get()->each(function ($lab) {
                $lab->delete();
            });
        });
    }

   
}


