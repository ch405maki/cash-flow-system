<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'petty_cash_id',
        'particulars',
        'date',
        'amount',
        'receipt',
        'type',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function pettyCash()
    {
        return $this->belongsTo(PettyCash::class);
    }
}
