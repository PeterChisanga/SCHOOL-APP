<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'school_id',
        'amount',
        'mode_of_payment',
        'payment_method',
        'status',
        'date',
        'deposit_slip_id',
        'receipt_number',
        'parent_reference',
        'proof_of_payment_path',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Whether this transaction actually passed through the platform's Lenco
     * mobile-money account. The `payment_method` column alone isn't a safe
     * signal for this: it was added later with a default of 'mobile_money',
     * so older manually-entered transactions got backfilled with that value
     * even though they never touched the gateway. Every real Lenco collection
     * (old or new) gets a system-generated 'PAY-xxxxxxxxxxxx' receipt number
     * (see LencoService/ParentPaymentController::processPayment) and manual
     * entries never do, so that reference format is the reliable signal.
     */
    public function getIsGatewayCollectionAttribute(): bool
    {
        return str_starts_with((string) $this->receipt_number, 'PAY-');
    }

    /**
     * Human label for where this transaction came from — used across the
     * reconciliation and payment views so manual entries and gateway
     * collections are never shown as indistinguishable rows.
     */
    public function getSourceLabelAttribute(): string
    {
        if ($this->is_gateway_collection) {
            return 'Payment Gateway';
        }

        return $this->payment_method === 'manual' ? 'Manual (Bank Proof)' : 'Manual (Staff Entry)';
    }

    public function getSourceBadgeClassAttribute(): string
    {
        return $this->is_gateway_collection ? 'badge-info' : 'badge-secondary';
    }
}
