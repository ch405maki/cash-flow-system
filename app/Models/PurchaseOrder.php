<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class PurchaseOrder extends Model
{
    protected $casts = [
        'tagging' => 'string',
    ];

    protected $fillable = [
        'po_no', 'payee', 'check_payable_to', 'date', 'amount',
        'purpose', 'status', 'remarks', 'user_id', 'account_id', 'department_id', 'canvas_id', 'tagging'
    ];

    public function user(): BelongsTo   
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'po_id');
    }

    public function canvas(): BelongsTo
    {
        return $this->belongsTo(Canvas::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PurchaseOrderApproval::class);
    }

    public function voucher(): HasOne
    {
        return $this->hasOne(Voucher::class, 'po_id');
    }


}