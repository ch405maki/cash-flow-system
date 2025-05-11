<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signatory extends Model
{
    protected $fillable = [
        'full_name', 
        'position',
        'status'
    ];
    
    protected $attributes = [
        'status' => 'active'
    ];
}