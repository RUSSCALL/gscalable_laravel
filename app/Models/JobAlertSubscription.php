<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A visitor asking to hear about new roles.
 *
 * Subscriptions are double opt-in: a row exists from the moment the form is
 * submitted, but nothing is ever sent to it until is_confirmed is true.
 */
class JobAlertSubscription extends Model
{
    use HasFactory;

    /**
     * Tokens are deliberately excluded -- they are generated in booted() and
     * must never be settable from request input.
     */
    protected $fillable = [
        'email',
        'category_id',
        'location_id',
        'keywords',
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
        'confirmed_at' => 'datetime',
        'last_notified_at' => 'datetime',
    ];

    protected $hidden = [
        'confirmation_token',
        'unsubscribe_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $subscription) {
            $subscription->confirmation_token ??= static::freshToken();
            $subscription->unsubscribe_token ??= static::freshToken();
        });
    }

    /** 64 hex characters -- fits the column exactly and is URL-safe. */
    public static function freshToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(JobLocation::class, 'location_id');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('is_confirmed', true);
    }

    /**
     * Mark the subscription live and burn the confirmation token so the link
     * in the email cannot be replayed.
     */
    public function confirm(): void
    {
        $this->forceFill([
            'is_confirmed' => true,
            'confirmed_at' => now(),
            'confirmation_token' => null,
        ])->save();
    }

    /**
     * Human-readable description of what this subscription covers, for the
     * emails and the admin table.
     */
    public function getTargetDescriptionAttribute(): string
    {
        $parts = [];

        if ($this->category) {
            $parts[] = $this->category->name;
        }

        if ($this->location) {
            $parts[] = $this->location->is_remote
                ? 'Remote'
                : trim($this->location->city . ', ' . $this->location->country, ', ');
        }

        if ($this->keywords) {
            $parts[] = '"' . $this->keywords . '"';
        }

        return $parts ? implode(' · ', $parts) : 'All new roles';
    }

    public function confirmationUrl(): string
    {
        return route('careers.alerts.confirm', $this->confirmation_token);
    }

    public function unsubscribeUrl(): string
    {
        return route('careers.alerts.unsubscribe', $this->unsubscribe_token);
    }
}
