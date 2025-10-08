<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['department_name', 'department_description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function pettyCashFunds()
    {
        return $this->hasMany(PettyCashFund::class);
    }

}