<?php

namespace App\Modules\Prompt\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Prompt extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\PromptFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'seller_id',
        'category_id',
        'title',
        'slug',
        'description',
        'content',
        'ai_model',
        'price',
        'pricing_model',
        'min_price',
        'version',
        'status',
        'featured',
        'views_count',
        'purchases_count',
        'rating',
        'rating_count',
        'rejection_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'min_price' => 'decimal:2',
            'version' => 'integer',
            'featured' => 'boolean',
            'views_count' => 'integer',
            'purchases_count' => 'integer',
            'rating' => 'decimal:2',
            'rating_count' => 'integer',
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'content', // Hide the actual prompt content from listings
    ];

    /**
     * Get the seller (user) who created this prompt.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the category this prompt belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the tags for this prompt.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'prompt_tag', 'prompt_id', 'tag_id')
            ->withTimestamps();
    }

    /**
     * Get the purchases for this prompt.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the reviews for this prompt.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Scope to get only approved prompts.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get only featured prompts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope to get prompts by AI model.
     */
    public function scopeByModel($query, string $model)
    {
        return $query->where('ai_model', $model);
    }

    /**
     * Scope to get prompts by category.
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to get prompts by seller.
     */
    public function scopeBySeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    /**
     * Scope for full-text search.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->whereFullText(['title', 'description'], $term);
    }

    /**
     * Scope to order by popularity (purchases count).
     */
    public function scopePopular($query)
    {
        return $query->orderBy('purchases_count', 'desc');
    }

    /**
     * Scope to order by rating.
     */
    public function scopeTopRated($query)
    {
        return $query->orderBy('rating', 'desc');
    }

    /**
     * Scope to order by recent.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Check if the prompt is free.
     */
    public function isFree(): bool
    {
        return $this->pricing_model === 'free' || $this->price <= 0;
    }

    /**
     * Check if the prompt is pay-what-you-want.
     */
    public function isPayWhatYouWant(): bool
    {
        return $this->pricing_model === 'pay_what_you_want';
    }

    /**
     * Check if the prompt is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the prompt is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Increment the view count.
     */
    public function incrementViewCount(): void
    {
        $this->increment('views_count');
    }

    /**
     * Increment the purchase count.
     */
    public function incrementPurchaseCount(): void
    {
        $this->increment('purchases_count');
    }

    /**
     * Update the rating based on new review.
     */
    public function updateRating(float $newRating): void
    {
        $totalRating = ($this->rating * $this->rating_count) + $newRating;
        $this->rating_count++;
        $this->rating = round($totalRating / $this->rating_count, 2);
        $this->save();
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Prompt $prompt) {
            if (empty($prompt->slug)) {
                $prompt->slug = Str::slug($prompt->title);
            }
        });

        static::updating(function (Prompt $prompt) {
            if ($prompt->isDirty('title') && empty($prompt->slug)) {
                $prompt->slug = Str::slug($prompt->title);
            }
        });
    }
}
