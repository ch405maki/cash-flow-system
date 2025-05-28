<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherDetail extends Model
{
    protected $fillable = [
        'voucher_id', 'amount', 'charging_tag', 'hours', 'rate', 'account_id'
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class)->withDefault([
            'account_title' => 'Unspecified Account'
        ]);
    }

    
}