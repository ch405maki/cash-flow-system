<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Canvas extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'status',
        'note',
        'remarks',
        'file_path',
        'original_filename',
        'created_by',
        'request_to_order_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function request_to_order() 
    {
        return $this->belongsTo(RequestToOrder::class, 'request_to_order_id');
    }
}