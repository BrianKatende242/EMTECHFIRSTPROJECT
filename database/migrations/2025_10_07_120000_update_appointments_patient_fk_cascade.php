<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Drop existing FK and re-add with cascade on delete
            try {
                $table->dropForeign(['patient_id']);
            } catch (\Throwable $e) {
                // If the foreign key does not exist under the conventional name, ignore
            }
            $table->foreign('patient_id')
                ->references('id')->on('patients')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            try {
                $table->dropForeign(['patient_id']);
            } catch (\Throwable $e) {
                // ignore if not present
            }
            $table->foreign('patient_id')
                ->references('id')->on('patients'); // no cascade
        });
    }
};
