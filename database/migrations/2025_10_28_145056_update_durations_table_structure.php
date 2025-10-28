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
        Schema::table('durations', function (Blueprint $table) {
            // Add new price column
            $table->decimal('price', 10, 2)->nullable()->after('minutes');
        });

        // Migrate existing data to the new price column
        DB::statement("UPDATE durations SET price = CASE WHEN type = 'general' THEN general_price ELSE specialist_price END WHERE general_price IS NOT NULL OR specialist_price IS NOT NULL");

        Schema::table('durations', function (Blueprint $table) {
            // Rename type to duration_type
            $table->renameColumn('type', 'duration_type');

            // Drop old price columns
            $table->dropColumn(['general_price', 'specialist_price']);

            // Add unique constraint on minutes and duration_type
            $table->unique(['minutes', 'duration_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('durations', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique(['minutes', 'duration_type']);

            // Add back old columns
            $table->decimal('general_price', 10, 2)->nullable();
            $table->decimal('specialist_price', 10, 2)->nullable();

            // Rename back
            $table->renameColumn('duration_type', 'type');
        });

        // Migrate data back
        DB::statement("UPDATE durations SET general_price = price WHERE type = 'general'");
        DB::statement("UPDATE durations SET specialist_price = price WHERE type = 'specialist'");

        Schema::table('durations', function (Blueprint $table) {
            // Drop new price column
            $table->dropColumn('price');
        });
    }
};
