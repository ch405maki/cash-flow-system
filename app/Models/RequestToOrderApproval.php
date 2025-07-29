<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestToOrderApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_to_order_id',
        'user_id',
        'status',
        'remarks',
        'approved_at',
    ];

    protected $dates = ['approved_at'];

    public function requestToOrder()
    {
        return $this->belongsTo(RequestToOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
