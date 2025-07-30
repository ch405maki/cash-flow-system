<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user_id',
        'status',
        'remarks',
        'approved_at',
    ];

    protected $dates = [
        'approved_at',
    ];

    // Relationships

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
