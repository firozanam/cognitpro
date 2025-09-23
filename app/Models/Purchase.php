<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Purchase extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'prompt_id',
        'price_paid',
        'payment_gateway',
        'transaction_id',
        'metadata',
        'purchased_at',
    ];

    protected $casts = [
        'price_paid' => 'decimal:2',
        'metadata' => 'array',
        'purchased_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {
            if (empty($purchase->uuid)) {
                $purchase->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user who made this purchase.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the prompt that was purchased.
     */
    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
