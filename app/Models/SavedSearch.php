<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearch extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'search_criteria',
        'email_alerts',
        'alert_frequency',
        'last_alert_sent',
        'is_active',
        'results_count',
        'last_results_check',
    ];

    protected $casts = [
        'search_criteria' => 'array',
        'email_alerts' => 'boolean',
        'is_active' => 'boolean',
        'last_alert_sent' => 'datetime',
        'last_results_check' => 'datetime',
    ];

    const FREQUENCY_IMMEDIATE = 'immediate';
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';

    /**
     * Get the user who owns this saved search
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active searches
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for searches with email alerts enabled
     */
    public function scopeWithEmailAlerts($query)
    {
        return $query->where('email_alerts', true);
    }

    /**
     * Scope for searches ready for alerts
     */
    public function scopeReadyForAlert($query)
    {
        return $query->active()
            ->withEmailAlerts()
            ->where(function ($q) {
                $q->whereNull('last_alert_sent')
                  ->orWhere('last_alert_sent', '<=', now()->subDay());
            });
    }

    /**
     * Get search criteria as formatted string
     */
    public function getFormattedCriteriaAttribute()
    {
        $criteria = $this->search_criteria;
        $formatted = [];

        if (isset($criteria['location'])) {
            $formatted[] = "Location: {$criteria['location']}";
        }

        if (isset($criteria['property_type'])) {
            $formatted[] = "Type: " . ucfirst($criteria['property_type']);
        }

        if (isset($criteria['min_price']) || isset($criteria['max_price'])) {
            $priceRange = 'Price: ';
            if (isset($criteria['min_price'])) {
                $priceRange .= '₱' . number_format($criteria['min_price']);
            }
            if (isset($criteria['min_price']) && isset($criteria['max_price'])) {
                $priceRange .= ' - ';
            }
            if (isset($criteria['max_price'])) {
                $priceRange .= '₱' . number_format($criteria['max_price']);
            }
            $formatted[] = $priceRange;
        }

        if (isset($criteria['bedrooms'])) {
            $formatted[] = "Bedrooms: {$criteria['bedrooms']}+";
        }

        return implode(', ', $formatted);
    }

    /**
     * Update last alert sent timestamp
     */
    public function markAlertSent()
    {
        $this->update(['last_alert_sent' => now()]);
    }

    /**
     * Update results count and check timestamp
     */
    public function updateResultsCount(int $count)
    {
        $this->update([
            'results_count' => $count,
            'last_results_check' => now(),
        ]);
    }

    /**
     * Generate search URL
     */
    public function getSearchUrlAttribute()
    {
        $params = http_build_query($this->search_criteria);
        return route('properties.index') . '?' . $params;
    }
}