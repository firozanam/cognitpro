<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'website',
        'location',
        'social_links',
        'payout_method',
        'payout_details',
    ];

    protected $casts = [
        'social_links' => 'array',
        'payout_details' => 'array',
    ];

    /**
     * Get the user that owns this profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get social link by platform.
     */
    public function getSocialLink(string $platform): ?string
    {
        return $this->social_links[$platform] ?? null;
    }

    /**
     * Set social link for platform.
     */
    public function setSocialLink(string $platform, string $url): void
    {
        $socialLinks = $this->social_links ?? [];
        $socialLinks[$platform] = $url;
        $this->social_links = $socialLinks;
    }
}
