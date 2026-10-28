<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getAvatarUrlAttribute(): string
    {
        $fallback = 'https://ui-avatars.com/api/?background=4B3828&color=F8F3EA&bold=true&name=' . urlencode($this->name);

        if (!$this->avatar) {
            return $fallback;
        }

        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://') || str_starts_with($this->avatar, 'data:image/')) {
            return $this->avatar;
        }

        $relPath = ltrim($this->avatar, '/\\');
        $publicFile = public_path('storage/' . $relPath);

        if (!is_file($publicFile) && Storage::disk('public')->exists($relPath)) {
            $destDir = dirname($publicFile);
            if (!is_dir($destDir)) {
                @mkdir($destDir, 0775, true);
            }
            @copy(Storage::disk('public')->path($relPath), $publicFile);
        }

        if (!is_file($publicFile) && !Storage::disk('public')->exists($relPath)) {
            return $fallback;
        }

        return asset('storage/' . str_replace('\\', '/', $relPath));
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function ebooks(): HasMany
    {
        return $this->hasMany(Ebook::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(Ebook::class, 'bookmarks')->withTimestamps();
    }

    public function unlockedEbooks(): BelongsToMany
    {
        return $this->belongsToMany(Ebook::class, 'unlocked_ebooks')->withTimestamps();
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function cartEbooks(): BelongsToMany
    {
        return $this->belongsToMany(Ebook::class, 'cart_items')->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function readingHistories(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAuthor(): bool
    {
        return $this->role === 'author';
    }

    public function isReader(): bool
    {
        return $this->role === 'reader';
    }

    public function isBanned(): bool
    {
        return ($this->status ?? 'active') === 'banned';
    }
}
