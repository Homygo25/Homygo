<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyView;
use App\Models\PropertyInquiry;
use App\Models\User;
use App\Models\SavedSearch;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get dashboard statistics for a user
     */
    public function getDashboardStats(User $user): array
    {
        $cacheKey = "dashboard_stats_{$user->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($user) { // 5 minutes cache
            $userProperties = $user->properties();
            
            return [
                'active_listings' => $userProperties->active()->count(),
                'total_views' => $this->getTotalViews($user),
                'total_inquiries' => $this->getTotalInquiries($user),
                'favorite_properties' => $user->favoriteProperties()->count(),
                'unread_notifications' => $user->unreadNotifications()->count(),
                'saved_searches' => $user->savedSearches()->count(),
                'views_this_month' => $this->getViewsThisMonth($user),
                'inquiries_this_month' => $this->getInquiriesThisMonth($user),
                'conversion_rate' => $this->getConversionRate($user),
            ];
        });
    }

    /**
     * Get property analytics data
     */
    public function getPropertyAnalytics(Property $property, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        return [
            'total_views' => $this->getPropertyViews($property),
            'unique_views' => $this->getPropertyUniqueViews($property),
            'views_change' => $this->getViewsChange($property, $days),
            'unique_percentage' => $this->getUniqueViewsPercentage($property),
            'total_inquiries' => $this->getPropertyInquiries($property),
            'conversion_rate' => $this->getPropertyConversionRate($property),
            'avg_time_spent' => $this->getAverageTimeSpent($property),
            'daily_views' => $this->getDailyViews($property, $days),
            'device_data' => $this->getDeviceData($property),
            'browser_data' => $this->getBrowserData($property),
            'traffic_sources' => $this->getTrafficSources($property),
            'performance_score' => $this->getPerformanceScore($property),
            'rank_in_area' => $this->getRankInArea($property),
            'similar_properties' => $this->getSimilarPropertiesCount($property),
        ];
    }

    /**
     * Get total views for user's properties
     */
    private function getTotalViews(User $user): int
    {
        return PropertyView::whereHas('property', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();
    }

    /**
     * Get total inquiries for user's properties
     */
    private function getTotalInquiries(User $user): int
    {
        return PropertyInquiry::whereHas('property', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();
    }

    /**
     * Get views this month for user's properties
     */
    private function getViewsThisMonth(User $user): int
    {
        return PropertyView::whereHas('property', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->whereMonth('created_at', Carbon::now()->month)->count();
    }

    /**
     * Get inquiries this month for user's properties
     */
    private function getInquiriesThisMonth(User $user): int
    {
        return PropertyInquiry::whereHas('property', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->whereMonth('created_at', Carbon::now()->month)->count();
    }

    /**
     * Get conversion rate (inquiries/views) for user's properties
     */
    private function getConversionRate(User $user): float
    {
        $totalViews = $this->getTotalViews($user);
        $totalInquiries = $this->getTotalInquiries($user);
        
        if ($totalViews === 0) {
            return 0;
        }
        
        return round(($totalInquiries / $totalViews) * 100, 2);
    }

    /**
     * Get total views for a specific property
     */
    private function getPropertyViews(Property $property): int
    {
        return $property->views()->count();
    }

    /**
     * Get unique views for a specific property
     */
    private function getPropertyUniqueViews(Property $property): int
    {
        return $property->views()
            ->select('ip_address')
            ->distinct()
            ->count();
    }

    /**
     * Get views change percentage compared to previous period
     */
    private function getViewsChange(Property $property, int $days): float
    {
        $currentPeriod = $property->views()
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->count();
            
        $previousPeriod = $property->views()
            ->where('created_at', '>=', Carbon::now()->subDays($days * 2))
            ->where('created_at', '<', Carbon::now()->subDays($days))
            ->count();
            
        if ($previousPeriod === 0) {
            return $currentPeriod > 0 ? 100 : 0;
        }
        
        return round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 1);
    }

    /**
     * Get unique views percentage
     */
    private function getUniqueViewsPercentage(Property $property): float
    {
        $totalViews = $this->getPropertyViews($property);
        $uniqueViews = $this->getPropertyUniqueViews($property);
        
        if ($totalViews === 0) {
            return 0;
        }
        
        return round(($uniqueViews / $totalViews) * 100, 1);
    }

    /**
     * Get total inquiries for a specific property
     */
    private function getPropertyInquiries(Property $property): int
    {
        return $property->inquiries()->count();
    }

    /**
     * Get conversion rate for a specific property
     */
    private function getPropertyConversionRate(Property $property): float
    {
        $views = $this->getPropertyViews($property);
        $inquiries = $this->getPropertyInquiries($property);
        
        if ($views === 0) {
            return 0;
        }
        
        return round(($inquiries / $views) * 100, 2);
    }

    /**
     * Get average time spent on property page
     */
    private function getAverageTimeSpent(Property $property): string
    {
        $avgSeconds = $property->views()
            ->whereNotNull('time_spent')
            ->avg('time_spent');
            
        if (!$avgSeconds) {
            return '0:00';
        }
        
        $minutes = floor($avgSeconds / 60);
        $seconds = $avgSeconds % 60;
        
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Get daily views data for charts
     */
    private function getDailyViews(Property $property, int $days): array
    {
        $viewsData = $property->views()
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as views'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        $labels = [];
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('M j');
            
            $viewCount = $viewsData->where('date', $date)->first();
            $data[] = $viewCount ? $viewCount->views : 0;
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get device breakdown data
     */
    private function getDeviceData(Property $property): array
    {
        $deviceData = $property->views()
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->pluck('count', 'device_type')
            ->toArray();
            
        return [
            'desktop' => $deviceData['desktop'] ?? 0,
            'mobile' => $deviceData['mobile'] ?? 0,
            'tablet' => $deviceData['tablet'] ?? 0,
            'other' => $deviceData['other'] ?? 0,
        ];
    }

    /**
     * Get browser breakdown data
     */
    private function getBrowserData(Property $property): array
    {
        return $property->views()
            ->select('browser', DB::raw('COUNT(*) as count'))
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'browser')
            ->toArray();
    }

    /**
     * Get traffic sources data
     */
    private function getTrafficSources(Property $property): array
    {
        $totalViews = $this->getPropertyViews($property);
        
        if ($totalViews === 0) {
            return [];
        }
        
        $sources = $property->views()
            ->select('referrer', DB::raw('COUNT(*) as count'))
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
            
        $result = [];
        foreach ($sources as $source) {
            $sourceName = $this->formatReferrer($source->referrer);
            $result[$sourceName] = [
                'views' => $source->count,
                'percentage' => ($source->count / $totalViews) * 100
            ];
        }
        
        return $result;
    }

    /**
     * Format referrer URL for display
     */
    private function formatReferrer(?string $referrer): string
    {
        if (!$referrer) {
            return 'Direct';
        }
        
        $domain = parse_url($referrer, PHP_URL_HOST);
        
        if (str_contains($domain, 'google')) {
            return 'Google';
        } elseif (str_contains($domain, 'facebook')) {
            return 'Facebook';
        } elseif (str_contains($domain, 'twitter')) {
            return 'Twitter';
        } else {
            return $domain ?: 'Other';
        }
    }

    /**
     * Calculate property performance score
     */
    private function getPerformanceScore(Property $property): int
    {
        $score = 0;
        
        // Views contribution (40%)
        $views = $this->getPropertyViews($property);
        $viewsScore = min(($views / 100) * 40, 40); // Max 40 points for 100+ views
        $score += $viewsScore;
        
        // Conversion rate contribution (30%)
        $conversionRate = $this->getPropertyConversionRate($property);
        $conversionScore = min($conversionRate * 6, 30); // Max 30 points for 5%+ conversion
        $score += $conversionScore;
        
        // Recency contribution (20%)
        $daysSinceCreated = Carbon::now()->diffInDays($property->created_at);
        $recencyScore = max(20 - ($daysSinceCreated / 7), 0); // Decrease over time
        $score += $recencyScore;
        
        // Completeness contribution (10%)
        $completenessScore = $this->getPropertyCompletenessScore($property);
        $score += $completenessScore;
        
        return min(round($score), 100);
    }

    /**
     * Get property completeness score
     */
    private function getPropertyCompletenessScore(Property $property): float
    {
        $score = 0;
        $maxScore = 10;
        
        // Check for required fields
        if ($property->title) $score += 2;
        if ($property->description) $score += 2;
        if ($property->price) $score += 2;
        if ($property->location_id) $score += 2;
        if ($property->images()->count() > 0) $score += 2;
        
        return $score;
    }

    /**
     * Get property rank in its area
     */
    private function getRankInArea(Property $property): string
    {
        $areaProperties = Property::where('location_id', $property->location_id)
            ->where('id', '!=', $property->id)
            ->get();
            
        if ($areaProperties->isEmpty()) {
            return '1st';
        }
        
        $propertyScore = $this->getPerformanceScore($property);
        $betterProperties = 0;
        
        foreach ($areaProperties as $areaProperty) {
            if ($this->getPerformanceScore($areaProperty) > $propertyScore) {
                $betterProperties++;
            }
        }
        
        $rank = $betterProperties + 1;
        
        return $this->formatRank($rank);
    }

    /**
     * Format numerical rank to ordinal
     */
    private function formatRank(int $rank): string
    {
        $suffix = match ($rank % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th'
        };
        
        // Handle special cases for 11th, 12th, 13th
        if (in_array($rank % 100, [11, 12, 13])) {
            $suffix = 'th';
        }
        
        return $rank . $suffix;
    }

    /**
     * Get count of similar properties
     */
    private function getSimilarPropertiesCount(Property $property): int
    {
        return Property::where('property_type', $property->property_type)
            ->where('location_id', $property->location_id)
            ->where('id', '!=', $property->id)
            ->whereBetween('price', [
                $property->price * 0.8,
                $property->price * 1.2
            ])
            ->count();
    }

    /**
     * Get recent activity for dashboard
     */
    public function getRecentActivity(User $user, int $limit = 10): \Illuminate\Support\Collection
    {
        $activities = collect();
        
        // Recent views
        $recentViews = PropertyView::whereHas('property', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with('property')->latest()->take($limit / 2)->get();
        
        foreach ($recentViews as $view) {
            $activities->push([
                'type' => 'view',
                'message' => "Someone viewed your property: {$view->property->title}",
                'created_at' => $view->created_at
            ]);
        }
        
        // Recent inquiries
        $recentInquiries = PropertyInquiry::whereHas('property', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with('property')->latest()->take($limit / 2)->get();
        
        foreach ($recentInquiries as $inquiry) {
            $activities->push([
                'type' => 'inquiry',
                'message' => "New inquiry from {$inquiry->name} for {$inquiry->property->title}",
                'created_at' => $inquiry->created_at
            ]);
        }
        
        return $activities->sortByDesc('created_at')->take($limit);
    }

    /**
     * Clear analytics cache for user
     */
    public function clearUserCache(User $user): void
    {
        Cache::forget("dashboard_stats_{$user->id}");
        
        // Clear property-specific caches
        foreach ($user->properties as $property) {
            Cache::forget("property_analytics_{$property->id}");
        }
    }
}