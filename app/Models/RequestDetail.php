<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestDetail extends Model
{
    protected $fillable = [
        'request_id', 'quantity', 'released_quantity', 'unit', 'item_description', 'released_at', 'tagging'
    ];
    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }
}