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
        // 1) Add the email column as nullable
        Schema::table('otps', function (Blueprint $table) {
            $table->string('email')->nullable();
        });

        // 2) Drop the composite index that references school_id before dropping the column (needed for SQLite)
        // Index name created in create_otps_table: otps_school_id_code_index
        try {
            Schema::table('otps', function (Blueprint $table) {
                $table->dropIndex('otps_school_id_code_index');
            });
        } catch (\Throwable $e) {
            // ignore if it doesn't exist (driver differences)
        }

        // 3) Drop foreign key and column if it exists
        if (Schema::hasColumn('otps', 'school_id')) {
            Schema::table('otps', function (Blueprint $table) {
                try {
                    $table->dropForeign(['school_id']);
                } catch (\Throwable $e) {
                    // ignore if driver doesn't support or FK name differs
                }
                // For cross-driver compatibility (esp. SQLite) ensure doctrine/dbal is installed (it is in composer.json)
                $table->dropColumn('school_id');
            });
        }

        // 4) Update existing records with a placeholder email if null
        DB::table('otps')->whereNull('email')->update(['email' => 'placeholder@example.com']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate school_id and composite index, then drop email
        if (!Schema::hasColumn('otps', 'school_id')) {
            Schema::table('otps', function (Blueprint $table) {
                $table->foreignId('school_id')->constrained()->onDelete('cascade');
            });
        }

        // Attempt to restore the composite index
        try {
            Schema::table('otps', function (Blueprint $table) {
                $table->index(['school_id', 'code']);
            });
        } catch (\Throwable $e) {
            // ignore if index already exists
        }

        if (Schema::hasColumn('otps', 'email')) {
            Schema::table('otps', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }
    }
};