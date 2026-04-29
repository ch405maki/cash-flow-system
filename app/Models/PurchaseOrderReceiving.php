<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderReceiving extends Model
{
    protected $fillable = [
        'po_id',
        'po_detail_id',
        'received_by',
        'quantity_ordered',
        'quantity_received',
        'condition',
        'received_date',
        'remarks',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function detail(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderDetail::class, 'po_detail_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}