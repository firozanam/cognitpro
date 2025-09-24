<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Prompt extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'description',
        'content',
        'excerpt',
        'category_id',
        'price_type',
        'price',
        'minimum_price',
        'status',
        'featured',
        'version',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'minimum_price' => 'decimal:2',
        'featured' => 'boolean',
        'version' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($prompt) {
            if (empty($prompt->uuid)) {
                $prompt->uuid = (string) Str::uuid();
            }
            if (empty($prompt->excerpt)) {
                $prompt->excerpt = Str::limit($prompt->description, 200);
            }
        });

        static::updating(function ($prompt) {
            if ($prompt->isDirty('description') && empty($prompt->excerpt)) {
                $prompt->excerpt = Str::limit($prompt->description, 200);
            }
        });
    }

    /**
     * Get the user who created this prompt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category this prompt belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all tags associated with this prompt.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'prompt_tags');
    }

    /**
     * Get all purchases of this prompt.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get all reviews for this prompt.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get approved reviews for this prompt.
     */
    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    /**
     * Check if prompt is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && !is_null($this->published_at);
    }

    /**
     * Check if prompt is free.
     */
    public function isFree(): bool
    {
        return $this->price_type === 'free';
    }

    /**
     * Check if user has purchased this prompt.
     */
    public function isPurchasedBy(User $user): bool
    {
        return $this->purchases()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the effective price for this prompt.
     */
    public function getEffectivePrice(): float
    {
        if ($this->price_type === 'free') {
            return 0.00;
        }

        return $this->price ? (float) $this->price : 0.00;
    }

    /**
     * Get average rating for this prompt.
     */
    public function getAverageRating(): float
    {
        $rating = $this->approvedReviews()->avg('rating');
        return $rating ? (float) $rating : 0.0;
    }

    /**
     * Get total purchase count.
     */
    public function getPurchaseCount(): int
    {
        return $this->purchases()->count();
    }

    /**
     * Scope for published prompts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    /**
     * Scope for featured prompts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
