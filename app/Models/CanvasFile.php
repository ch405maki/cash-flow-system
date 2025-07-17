<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanvasFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'canvas_id',
        'file_path',
        'original_filename',
        'type'
    ];

    public function canvas(): BelongsTo
    {
        return $this->belongsTo(Canvas::class);
    }

    public function selected_approvals(): HasMany
    {
        return $this->hasMany(CanvasSelectedFile::class, 'canvas_file_id');
    }
}