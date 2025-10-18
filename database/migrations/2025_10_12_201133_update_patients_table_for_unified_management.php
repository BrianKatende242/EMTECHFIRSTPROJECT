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
        Schema::table('patients', function (Blueprint $table) {
            // Add patient_id as nullable first to avoid NOT NULL violation
            $table->string('patient_id')->nullable()->after('id');

            // Add school relationship (nullable)
            $table->foreignId('school_id')->nullable()->after('health_facility_id')->constrained()->onDelete('set null');

            // Add student-specific fields
            $table->string('parent_contact')->nullable()->after('contact_number');
            $table->string('grade')->nullable()->after('parent_contact');

            // Make health_facility_id nullable to allow patients without health facility association
            $table->foreignId('health_facility_id')->nullable()->change();

            // Add indexes for better performance
            $table->index(['name', 'birth_date']);
            $table->index('patient_id');
        });

        // Generate unique patient IDs for existing records
        DB::table('patients')->whereNull('patient_id')->get()->each(function ($patient) {
            DB::table('patients')
                ->where('id', $patient->id)
                ->update(['patient_id' => 'PAT-' . str_pad($patient->id, 6, '0', STR_PAD_LEFT)]);
        });

        // Now make patient_id unique and not nullable
        Schema::table('patients', function (Blueprint $table) {
            $table->string('patient_id')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First make patient_id nullable before dropping
        Schema::table('patients', function (Blueprint $table) {
            $table->string('patient_id')->nullable()->change();
        });

        Schema::table('patients', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['name', 'birth_date']);
            $table->dropIndex(['patient_id']);

            // Remove new fields
            $table->dropForeign(['school_id']);
            $table->dropColumn(['patient_id', 'school_id', 'parent_contact', 'grade']);

            // Revert health_facility_id to not nullable (if it was originally)
            // Note: This assumes it was originally not nullable, adjust if needed
            $table->foreignId('health_facility_id')->nullable(false)->change();
        });
    }
};
