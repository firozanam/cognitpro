<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is a buyer.
     */
    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }

    /**
     * Check if user is a seller.
     */
    public function isSeller(): bool
    {
        return $this->role === 'seller';
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user can sell prompts.
     */
    public function canSell(): bool
    {
        return $this->role === 'seller' || $this->role === 'admin';
    }

    /**
     * Check if user can buy prompts.
     */
    public function canBuy(): bool
    {
        return $this->role === 'buyer' || $this->role === 'seller' || $this->role === 'admin';
    }

    /**
     * Get the user's profile.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get all prompts created by this user.
     */
    public function prompts()
    {
        return $this->hasMany(Prompt::class);
    }

    /**
     * Get published prompts created by this user.
     */
    public function publishedPrompts()
    {
        return $this->hasMany(Prompt::class)->where('status', 'published');
    }

    /**
     * Get all purchases made by this user.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get all reviews written by this user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get all payments made by this user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all payouts for this user.
     */
    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }
}
