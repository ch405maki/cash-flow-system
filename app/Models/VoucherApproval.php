<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherApproval extends Model
{
    protected $fillable = [
        'voucher_id',
        'user_id',
        'status',
        'remarks',
        'approved_at',
    ];

    /**
     * Get the voucher this approval belongs to.
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Get the user who made the approval.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
