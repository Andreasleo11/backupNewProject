<?php

namespace App\Models;

use Illuminate\Support\Number;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_name',
        'path',
        'mime_type',
        'size',
        'disk',
        'uploaded_by'
    ];

    protected $appends = ['size_for_humans', 'category'];

    public function getSizeForHumansAttribute()
    {
        return Number::fileSize($this->size);
    }

    public function getCategoryAttribute(): string
    {
        $m = $this->mime_type;
        return match (true) {
            Str::startsWith($m, 'image/') => 'image',
            $m === 'application/pdf' => 'pdf',
            Str::contains($m, ['wordprocessingml', 'msword', 'rtf', 'text/']) => 'doc',
            Str::contains($m, ['spreadsheetml', 'excel', 'csv']) => 'sheet',
            Str::startsWith($m, 'video/') => 'video',
            Str::startsWith($m, 'audio/') => 'audio',
            Str::contains($m, ['zip', 'x-7z', 'x-rar']) => 'archive',
            default => 'other',
        };
    }

    public function getCreatedAtWibAttribute()
    {
        return $this->created_at->timezone('Asia/Jakarta');
    }
}
