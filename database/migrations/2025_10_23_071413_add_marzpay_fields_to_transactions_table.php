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
        Schema::table('transactions', function (Blueprint $table) {
            // MarzPay specific fields
            $table->string('provider')->nullable()->after('transaction_id'); // mtn, airtel
            $table->string('provider_reference')->nullable()->after('provider');
            $table->enum('transaction_type', ['collection', 'disbursement'])->nullable()->after('provider_reference');
            $table->string('webhook_event_type')->nullable()->after('transaction_type'); // collection.completed, collection.failed, etc.
            $table->string('marzpay_uuid')->nullable()->after('webhook_event_type'); // MarzPay transaction UUID
            $table->string('country')->default('UG')->after('marzpay_uuid');
            $table->text('description')->nullable()->after('country');
            $table->json('collection_data')->nullable()->after('description'); // Store collection-specific data
            $table->json('disbursement_data')->nullable()->after('collection_data'); // Store disbursement-specific data
            $table->timestamp('processed_at')->nullable()->after('disbursement_data'); // When webhook was processed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'provider',
                'provider_reference',
                'transaction_type',
                'webhook_event_type',
                'marzpay_uuid',
                'country',
                'description',
                'collection_data',
                'disbursement_data',
                'processed_at'
            ]);
        });
    }
};
