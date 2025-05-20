<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Access extends Model
{
    protected $table = 'accesses';
    
    protected $fillable = ['program_name', 'access_level'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}