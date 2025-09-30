<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCash extends Model
{
    use HasFactory;

    protected $table = 'petty_cash';

    protected $fillable = [
        'pcv_no',
        'user_id',
        'paid_to',
        'status',
        'date',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PettyCashItem::class);
    }

    public function distributionExpenses()
    {
        return $this->hasMany(DistributionExpense::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PettyCashApproval::class);
    }
}
