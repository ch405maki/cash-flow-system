<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $casts = [
        'tagging' => 'string',
    ];

    protected $fillable = [
        'po_no', 'payee', 'check_payable_to', 'date', 'amount',
        'purpose', 'status', 'remarks', 'user_id', 'department_id', 
        'account_id', 'canvas_id', 'tagging'
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

    // Add this new relationship
    public function canvas(): BelongsTo
    {
        return $this->belongsTo(Canvas::class);
    }
}