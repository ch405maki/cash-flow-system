<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashFund extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'fund_amount',
    ];

    /**
     * Each fund belongs to a user (custodian)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Each fund belongs to a department
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
