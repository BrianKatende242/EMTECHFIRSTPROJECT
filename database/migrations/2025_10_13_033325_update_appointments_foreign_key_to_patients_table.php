<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // student_id column was already dropped, just ensure patient_id is not nullable
            $table->unsignedBigInteger('patient_id')->nullable(false)->change();
            // Foreign key constraint already exists from previous migration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Add back the student_id column
            $table->unsignedBigInteger('student_id')->nullable();
            $table->foreign('student_id')->references('id')->on('students');
            
            // Make patient_id nullable again
            $table->unsignedBigInteger('patient_id')->nullable()->change();
            // Foreign key constraint for patient_id should remain
        });
    }
};
