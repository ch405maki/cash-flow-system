<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReleaseDetail extends Model
{
    protected $fillable = [
        'release_id', 'request_detail_id', 'quantity'
    ];

    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class);
    }

    public function requestDetail(): BelongsTo
    {
        return $this->belongsTo(RequestDetail::class);
    }
}