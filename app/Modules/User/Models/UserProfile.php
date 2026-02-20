<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserProfileFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'bio',
        'avatar',
        'website',
        'social_links',
        'seller_verified',
        'total_sales',
        'total_earnings',
        'payout_method',
        'payout_details',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'payout_details' => 'array',
            'seller_verified' => 'boolean',
            'total_sales' => 'integer',
            'total_earnings' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns this profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get verified sellers.
     */
    public function scopeVerifiedSellers($query)
    {
        return $query->where('seller_verified', true);
    }

    /**
     * Check if the user is a verified seller.
     */
    public function isVerifiedSeller(): bool
    {
        return $this->seller_verified;
    }

    /**
     * Increment total sales.
     */
    public function incrementSales(): void
    {
        $this->increment('total_sales');
    }

    /**
     * Add to total earnings.
     */
    public function addEarnings(float $amount): void
    {
        $this->increment('total_earnings', $amount);
    }

    /**
     * Get social links as a formatted array.
     */
    public function getSocialLinksArray(): array
    {
        return $this->social_links ?? [];
    }

    /**
     * Get avatar URL.
     */
    public function getAvatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // Return default avatar or Gravatar
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->user->name) . '&color=7F9CF5&background=EBF4FF';
    }
}
