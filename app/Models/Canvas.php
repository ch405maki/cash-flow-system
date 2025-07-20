<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{
    HasMany,
    BelongsTo,
    BelongsToMany
};

class Canvas extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'note',
        'created_by',
        'request_to_order_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function request_to_order(): BelongsTo 
    {
        return $this->belongsTo(RequestToOrder::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(CanvasFile::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(CanvasApproval::class);
    }

    public function selected_files(): HasMany
    {
        return $this->hasMany(CanvasSelectedFile::class);
    }

    public function purchase_orders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // Helper method to get latest auditor comments
    public function getAuditorCommentsAttribute()
    {
        return $this->approvals()
            ->where('role', 'auditor')
            ->latest()
            ->first()?->comments;
    }

    // Helper method to get executive director approval
    public function getExecutiveApprovalAttribute()
    {
        return $this->approvals()
            ->where('role', 'executive_director')
            ->first();
    }
}