<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestDetail extends Model
{
    protected $fillable = [
        'request_id', 'quantity', 'unit', 'item_description'
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }
}