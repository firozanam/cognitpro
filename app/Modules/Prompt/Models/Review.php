<?php

namespace App\Modules\Prompt\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\ReviewFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'prompt_id',
        'user_id',
        'purchase_id',
        'rating',
        'title',
        'content',
        'helpful_count',
        'seller_response',
        'seller_responded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'helpful_count' => 'integer',
            'seller_responded_at' => 'datetime',
        ];
    }

    /**
     * Get the prompt that was reviewed.
     */
    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    /**
     * Get the user who wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the purchase associated with this review.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Scope to get reviews by rating.
     */
    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope to get reviews with seller response.
     */
    public function scopeWithSellerResponse($query)
    {
        return $query->whereNotNull('seller_response');
    }

    /**
     * Scope to order by helpful count.
     */
    public function scopeMostHelpful($query)
    {
        return $query->orderBy('helpful_count', 'desc');
    }

    /**
     * Scope to order by recent.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Check if the review has a seller response.
     */
    public function hasSellerResponse(): bool
    {
        return !empty($this->seller_response);
    }

    /**
     * Increment the helpful count.
     */
    public function incrementHelpfulCount(): void
    {
        $this->increment('helpful_count');
    }

    /**
     * Add seller response.
     */
    public function addSellerResponse(string $response): void
    {
        $this->seller_response = $response;
        $this->seller_responded_at = now();
        $this->save();
    }
}
