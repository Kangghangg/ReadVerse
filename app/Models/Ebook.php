<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Ebook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'file_path',
        'price',
        'pages',
        'view_count',
        'status',
        'rejection_reason',
        'rejection_note',
        'rejected_at',
        'category_id',
        'author_id',
        'user_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'pages' => 'integer',
        'view_count' => 'integer',
        'rejected_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getCoverUrlAttribute(): string
    {
        if (!$this->cover_image) {
            return 'https://placehold.co/400x560?text=ReadVerse';
        }

        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
            return $this->cover_image;
        }

        if (str_starts_with($this->cover_image, '/storage/')) {
            return asset(ltrim($this->cover_image, '/'));
        }

        return asset('storage/' . $this->cover_image);
    }

    public function isUnlockedBy(?User $user): bool
    {
        if ((float) $this->price <= 0) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->isAdmin() || (int) $this->user_id === (int) $user->id) {
            return true;
        }

        if ($user->relationLoaded('unlockedEbooks') && $user->unlockedEbooks->contains('id', $this->id)) {
            return true;
        }

        if ($user->unlockedEbooks()->where('ebooks.id', $this->id)->exists()) {
            return true;
        }

        if (Schema::hasTable('orders')) {
            return Order::where('user_id', $user->id)
                ->where('ebook_id', $this->id)
                ->where('status', 'completed')
                ->exists();
        }

        return false;
    }

    public function isInCartOf(?User $user): bool
    {
        if (!$user || !Schema::hasTable('cart_items')) {
            return false;
        }

        return CartItem::where('user_id', $user->id)
            ->where('ebook_id', $this->id)
            ->exists();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function bookmarkedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookmarks')->withTimestamps();
    }

    public function unlockedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'unlocked_ebooks')->withTimestamps();
    }

    public function readingHistories(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }
}
