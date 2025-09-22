<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyView extends Model
{
    protected $fillable = [
        'property_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referrer',
        'session_id',
        'viewed_at',
        'time_spent',
        'device_type',
        'location_data',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'location_data' => 'array',
    ];

    /**
     * Get the property that was viewed
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the user who viewed the property (if authenticated)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for views within date range
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('viewed_at', [$startDate, $endDate]);
    }

    /**
     * Scope for unique views (distinct by IP or user)
     */
    public function scopeUnique($query)
    {
        return $query->distinct(['ip_address', 'user_id']);
    }

    /**
     * Get device type from user agent
     */
    public function getDeviceTypeAttribute($value)
    {
        if ($value) {
            return $value;
        }

        $userAgent = $this->user_agent;
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/Tablet/', $userAgent)) {
            return 'tablet';
        }
        
        return 'desktop';
    }
}