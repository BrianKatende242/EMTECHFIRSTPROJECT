<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, ensure all students have corresponding patient records
        $studentsWithoutPatients = DB::table('students')
            ->leftJoin('patients', 'students.school_id', '=', 'patients.school_id')
            ->whereNull('patients.id')
            ->orWhereRaw('patients.name != students.name')
            ->select('students.*')
            ->get();

        foreach ($studentsWithoutPatients as $student) {
            // Check if patient already exists with matching details
            $existingPatient = DB::table('patients')
                ->where('name', $student->name)
                ->where('birth_date', $student->birth_date)
                ->where('school_id', $student->school_id)
                ->first();

            if (!$existingPatient) {
                // Create new patient record for the student
                $patientId = DB::table('patients')->insertGetId([
                    'patient_id' => 'P' . str_pad($student->id, 6, '0', STR_PAD_LEFT),
                    'name' => $student->name,
                    'gender' => 'Not Specified', // Default gender since student data doesn't include it
                    'birth_date' => $student->birth_date,
                    'parent_contact' => $student->parent_contact,
                    'grade' => $student->grade,
                    'school_id' => $student->school_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update appointments for this student to use the new patient_id
                DB::table('appointments')
                    ->where('student_id', $student->id)
                    ->whereNull('patient_id')
                    ->update(['patient_id' => $patientId]);
            } else {
                // Update appointments to use existing patient
                DB::table('appointments')
                    ->where('student_id', $student->id)
                    ->whereNull('patient_id')
                    ->update(['patient_id' => $existingPatient->id]);
            }
        }

        // Now make patient_id not nullable
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Make patient_id nullable again
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->change();
        });

        // Note: We don't delete the patient records created during up() migration
        // as they might contain additional data. The system should handle
        // patient-student relationships appropriately.
    }
};
