<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'action_url',
        'priority',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    const TYPE_PROPERTY_ALERT = 'property_alert';
    const TYPE_PRICE_DROP = 'price_drop';
    const TYPE_NEW_LISTING = 'new_listing';
    const TYPE_INQUIRY_RESPONSE = 'inquiry_response';
    const TYPE_FAVORITE_UPDATE = 'favorite_update';
    const TYPE_SYSTEM = 'system';

    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Get the user who owns this notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for active notifications (not expired)
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for high priority notifications
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Check if notification is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get formatted type
     */
    public function getFormattedTypeAttribute()
    {
        return match ($this->type) {
            self::TYPE_PROPERTY_ALERT => 'Property Alert',
            self::TYPE_PRICE_DROP => 'Price Drop',
            self::TYPE_NEW_LISTING => 'New Listing',
            self::TYPE_INQUIRY_RESPONSE => 'Inquiry Response',
            self::TYPE_FAVORITE_UPDATE => 'Favorite Update',
            self::TYPE_SYSTEM => 'System Notification',
            default => 'Notification',
        };
    }

    /**
     * Get priority color class
     */
    public function getPriorityColorAttribute()
    {
        return match ($this->priority) {
            self::PRIORITY_URGENT => 'danger',
            self::PRIORITY_HIGH => 'warning',
            self::PRIORITY_NORMAL => 'primary',
            self::PRIORITY_LOW => 'secondary',
            default => 'primary',
        };
    }

    /**
     * Get time ago formatted
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}