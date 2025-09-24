<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Review extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'user_id',
        'prompt_id',
        'rating',
        'title',
        'review_text',
        'verified_purchase',
        'is_approved',
        'helpful_count',
    ];

    protected $casts = [
        'rating' => 'integer',
        'verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'helpful_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($review) {
            if (empty($review->uuid)) {
                $review->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user who wrote this review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the prompt this review is for.
     */
    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    /**
     * Scope for approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope for verified purchase reviews.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified_purchase', true);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Check if review is approved.
     */
    public function isApproved(): bool
    {
        return $this->is_approved;
    }

    /**
     * Check if review is from verified purchase.
     */
    public function isVerifiedPurchase(): bool
    {
        return $this->verified_purchase;
    }
}
