<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'payment_id',
        'reference_id',
        'amount',
        'status',
        'transaction_id',
        'response_data',
        // MarzPay specific fields
        'provider',
        'provider_reference',
        'transaction_type',
        'webhook_event_type',
        'marzpay_uuid',
        'country',
        'description',
        'collection_data',
        'disbursement_data',
        'processed_at',
    ];

    protected $casts = [
        'response_data' => 'array',
        'amount' => 'decimal:2',
        'collection_data' => 'array',
        'disbursement_data' => 'array',
        'processed_at' => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
