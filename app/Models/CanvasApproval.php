<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo,
    HasMany
};

class CanvasApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'canvas_id',
        'user_id',
        'role',
        'comments',
        'approved',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'approved' => 'boolean'
    ];

    public function canvas(): BelongsTo
    {
        return $this->belongsTo(Canvas::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function selected_files(): HasMany
    {
        return $this->hasMany(CanvasSelectedFile::class);
    }
}