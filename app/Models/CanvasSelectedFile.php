<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanvasSelectedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'canvas_id',
        'canvas_file_id',
        'approval_id',
        'remarks'
    ];

    public function canvas(): BelongsTo
    {
        return $this->belongsTo(Canvas::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(CanvasFile::class, 'canvas_file_id');
    }

    public function approval(): BelongsTo
    {
        return $this->belongsTo(CanvasApproval::class);
    }
}