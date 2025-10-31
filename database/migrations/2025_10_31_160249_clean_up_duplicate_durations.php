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
        // Clean up duplicate durations based on minutes and duration_type
        // Keep the record with the smallest id for each duplicate group
        // Using SQLite-compatible syntax
        DB::statement('
            DELETE FROM durations 
            WHERE id NOT IN (
                SELECT MIN(id) 
                FROM durations 
                GROUP BY minutes, duration_type
            )
        ');
    }

    /**
     * Reverse the migrations.
     * Note: This migration deletes data and cannot be fully reversed.
     */
    public function down(): void
    {
        // Data deletion cannot be reversed
    }
};
