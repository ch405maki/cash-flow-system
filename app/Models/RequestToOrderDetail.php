<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestToOrderDetail extends Model
{
    protected $fillable = [
        'request_to_order_id',
        'request_detail_id',
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

    public function releases(): HasMany
    {
        return $this->hasMany(RequestToOrderRelease::class, 'request_to_order_detail_id');
    }

    public function getTotalReleasedAttribute()
    {
        return $this->releases->sum('quantity_released');
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - ($this->releases_sum_quantity_released ?? $this->releases()->sum('quantity_released'));
    }

    public function requestDetail(): BelongsTo
    {
        return $this->belongsTo(RequestDetail::class, 'request_detail_id');
    }
}