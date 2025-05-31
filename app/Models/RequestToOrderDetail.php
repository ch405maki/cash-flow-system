<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestToOrderDetail extends Model
{
    protected $fillable = [
        'request_to_order_id',
        'quantity',
        'unit',
        'item_description',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RequestToOrder::class, 'request_to_order_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->unit_price !== null) {
                $model->total_price = $model->quantity * $model->unit_price;
            }
        });
    }
}