<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestToOrder extends Model
{
    protected $fillable = [
        'user_id',
        'order_no',
        'order_date',
        'notes',
        'status'
    ];

    protected $casts = [
    'order_date' => 'date',
    'created_at' => 'datetime',
];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(RequestToOrderDetail::class);
    }

    public function canvases()
    {
        return $this->hasMany(Canvas::class);
    }
}