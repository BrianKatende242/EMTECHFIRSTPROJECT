<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'payment_id',
        'reference_id',
        'amount',
        'status',
        'transaction_id',
        'response_data',

        // Provider-specific fields (e.g. MarzPay)
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

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'response_data' => 'array',
        'collection_data' => 'array',
        'disbursement_data' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * A transaction belongs to a payment.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Determine if the transaction was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }

    /**
     * Determine if the transaction failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Determine if the transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
