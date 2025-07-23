<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestToOrderRelease extends Model
{
    protected $fillable = [
        'request_to_order_id',
        'request_to_order_detail_id',
        'quantity_released',
        'release_date',
        'notes',
        'released_by'
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RequestToOrder::class, 'request_to_order_id');
    }

    public function detail(): BelongsTo
    {
        return $this->belongsTo(RequestToOrderDetail::class, 'request_to_order_detail_id');
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}