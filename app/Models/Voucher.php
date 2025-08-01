<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = [
        'po_id', 'voucher_no', 'voucher_date', 'issue_date', 'payment_date', 'type',
        'payee', 'check_no', 'check_date', 'check_amount', 'check_payable_to',
        'delivery_date', 'purpose', 'status', 'user_id', 'remarks', 'receipt'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(VoucherDetail::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(VoucherApproval::class);
    }
    
}