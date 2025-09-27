<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'petty_cash_id',
        'account_name',
        'ammount',
        'date',
        'prepared_by',
        'approved_by',
        'audited_by',
        'paid_by',
    ];

    protected $casts = [
        'date' => 'date',
        'ammount' => 'decimal:2',
    ];

    // Relationships
    public function pettyCash()
    {
        return $this->belongsTo(PettyCash::class);
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function auditedBy()
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
