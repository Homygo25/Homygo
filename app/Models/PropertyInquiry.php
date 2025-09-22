<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyInquiry extends Model
{
    protected $fillable = [
        'property_id',
        'user_id',
        'name',
        'email',
        'phone',
        'message',
        'inquiry_type',
        'status',
        'response',
        'responded_at',
        'ip_address',
        'source',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_RESPONDED = 'responded';
    const STATUS_CLOSED = 'closed';

    const TYPE_GENERAL = 'general';
    const TYPE_VIEWING = 'viewing_request';
    const TYPE_PRICE = 'price_inquiry';
    const TYPE_FINANCING = 'financing';
    const TYPE_AVAILABILITY = 'availability';

    /**
     * Get the property for this inquiry
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the user who made the inquiry (if authenticated)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for pending inquiries
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for responded inquiries
     */
    public function scopeResponded($query)
    {
        return $query->where('status', self::STATUS_RESPONDED);
    }

    /**
     * Mark inquiry as responded
     */
    public function markAsResponded(string $response = null)
    {
        $this->update([
            'status' => self::STATUS_RESPONDED,
            'response' => $response,
            'responded_at' => now(),
        ]);
    }

    /**
     * Get formatted inquiry type
     */
    public function getFormattedTypeAttribute()
    {
        return match ($this->inquiry_type) {
            self::TYPE_GENERAL => 'General Inquiry',
            self::TYPE_VIEWING => 'Viewing Request',
            self::TYPE_PRICE => 'Price Inquiry',
            self::TYPE_FINANCING => 'Financing Question',
            self::TYPE_AVAILABILITY => 'Availability Check',
            default => 'General Inquiry',
        };
    }
}