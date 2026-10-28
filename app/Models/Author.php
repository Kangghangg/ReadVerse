<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'bio',
        'avatar',
    ];

    public function getAvatarUrlAttribute(): string
    {
        $fallback = 'https://ui-avatars.com/api/?background=4B3828&color=F8F3EA&name=' . urlencode($this->name);

        if (!$this->avatar) {
            return $fallback;
        }

        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://') || str_starts_with($this->avatar, 'data:image/')) {
            return $this->avatar;
        }

        $relPath = ltrim($this->avatar, '/\\');
        $publicFile = public_path('storage/' . $relPath);
        $storageFile = storage_path('app/public/' . $relPath);

        if (!is_file($publicFile) && is_file($storageFile)) {
            $destDir = dirname($publicFile);
            if (!is_dir($destDir)) {
                @mkdir($destDir, 0775, true);
            }
            @copy($storageFile, $publicFile);
        }

        if (!is_file($publicFile) && !is_file($storageFile)) {
            return $fallback;
        }

        return asset('storage/' . str_replace('\\', '/', $relPath));
    }

    public function ebooks(): HasMany
    {
        return $this->hasMany(Ebook::class);
    }
}
