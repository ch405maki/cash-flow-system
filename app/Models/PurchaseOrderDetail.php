<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderDetail extends Model
{
    protected $fillable = [
        'po_id', 'quantity', 'unit', 'item_description', 'unit_price', 'amount'
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function receivings(): HasMany
    {
        return $this->hasMany(PurchaseOrderReceiving::class, 'po_detail_id');
    }

    public function getTotalReceivedAttribute(): int
    {
        return $this->receivings->sum('quantity_received');
    }
}