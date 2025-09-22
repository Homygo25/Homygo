<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Property;
use App\Models\Location;
use App\Models\Category;

class CacheService
{
    // Cache TTL constants (in seconds)
    const PROPERTY_CACHE_TTL = 3600; // 1 hour
    const SEARCH_CACHE_TTL = 1800; // 30 minutes
    const STATS_CACHE_TTL = 7200; // 2 hours
    const LOCATION_CACHE_TTL = 86400; // 24 hours
    const CATEGORY_CACHE_TTL = 86400; // 24 hours

    /**
     * Get cached property data
     */
    public function getProperty(int $propertyId)
    {
        $cacheKey = "property:{$propertyId}";
        
        return Cache::remember($cacheKey, self::PROPERTY_CACHE_TTL, function () use ($propertyId) {
            Log::info("Cache miss for property: {$propertyId}");
            
            return Property::with([
                'category',
                'location',
                'user.profile',
                'images',
                'features'
            ])->find($propertyId);
        });
    }

    /**
     * Get cached property search results
     */
    public function getSearchResults(array $filters, int $page = 1, int $perPage = 12)
    {
        $cacheKey = $this->generateSearchCacheKey($filters, $page, $perPage);
        
        return Cache::remember($cacheKey, self::SEARCH_CACHE_TTL, function () use ($filters, $page, $perPage) {
            Log::info("Cache miss for search with filters", $filters);
            
            $query = Property::query()
                ->with(['category', 'location', 'images'])
                ->where('status', 'active');

            // Apply filters
            if (!empty($filters['location'])) {
                $query->whereHas('location', function ($q) use ($filters) {
                    $q->where('slug', $filters['location'])
                      ->orWhere('name', 'like', '%' . $filters['location'] . '%');
                });
            }

            if (!empty($filters['category'])) {
                $query->whereHas('category', function ($q) use ($filters) {
                    $q->where('slug', $filters['category']);
                });
            }

            if (!empty($filters['property_type'])) {
                $query->where('property_type', $filters['property_type']);
            }

            if (!empty($filters['min_price'])) {
                $query->where('price', '>=', $filters['min_price']);
            }

            if (!empty($filters['max_price'])) {
                $query->where('price', '<=', $filters['max_price']);
            }

            if (!empty($filters['bedrooms'])) {
                $query->where('bedrooms', '>=', $filters['bedrooms']);
            }

            if (!empty($filters['bathrooms'])) {
                $query->where('bathrooms', '>=', $filters['bathrooms']);
            }

            // Apply sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $query->paginate($perPage, ['*'], 'page', $page);
        });
    }

    /**
     * Get cached CDO locations
     */
    public function getCdoLocations()
    {
        $cacheKey = 'cdo_locations';
        
        return Cache::remember($cacheKey, self::LOCATION_CACHE_TTL, function () {
            Log::info("Cache miss for CDO locations");
            
            return Location::where('city', 'Cagayan de Oro')
                ->orWhere('city', 'CDO')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get cached property categories
     */
    public function getCategories()
    {
        $cacheKey = 'property_categories';
        
        return Cache::remember($cacheKey, self::CATEGORY_CACHE_TTL, function () {
            Log::info("Cache miss for property categories");
            
            return Category::with('properties_count')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get cached property statistics
     */
    public function getPropertyStats()
    {
        $cacheKey = 'property_stats';
        
        return Cache::remember($cacheKey, self::STATS_CACHE_TTL, function () {
            Log::info("Cache miss for property statistics");
            
            return [
                'total_properties' => Property::where('status', 'active')->count(),
                'for_sale' => Property::where('status', 'active')
                    ->where('listing_type', 'sale')->count(),
                'for_rent' => Property::where('status', 'active')
                    ->where('listing_type', 'rent')->count(),
                'avg_price_sale' => Property::where('status', 'active')
                    ->where('listing_type', 'sale')
                    ->avg('price'),
                'avg_price_rent' => Property::where('status', 'active')
                    ->where('listing_type', 'rent')
                    ->avg('price'),
                'popular_locations' => $this->getPopularLocations(),
                'property_types' => $this->getPropertyTypeStats(),
            ];
        });
    }

    /**
     * Get cached featured properties
     */
    public function getFeaturedProperties(int $limit = 6)
    {
        $cacheKey = "featured_properties:{$limit}";
        
        return Cache::remember($cacheKey, self::PROPERTY_CACHE_TTL, function () use ($limit) {
            Log::info("Cache miss for featured properties");
            
            return Property::with(['category', 'location', 'images'])
                ->where('status', 'active')
                ->where('is_featured', true)
                ->orderBy('featured_at', 'desc')
                ->take($limit)
                ->get();
        });
    }

    /**
     * Invalidate property-related caches
     */
    public function invalidatePropertyCache(int $propertyId = null)
    {
        $patterns = [
            'property_stats',
            'featured_properties:*',
            'search_results:*',
        ];

        if ($propertyId) {
            $patterns[] = "property:{$propertyId}";
        }

        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '*')) {
                $this->forgetByPattern($pattern);
            } else {
                Cache::forget($pattern);
            }
        }

        Log::info("Invalidated property caches", ['property_id' => $propertyId]);
    }

    /**
     * Invalidate location caches
     */
    public function invalidateLocationCache()
    {
        Cache::forget('cdo_locations');
        $this->forgetByPattern('search_results:*');
        
        Log::info("Invalidated location caches");
    }

    /**
     * Generate search cache key
     */
    private function generateSearchCacheKey(array $filters, int $page, int $perPage): string
    {
        ksort($filters); // Sort filters for consistent cache keys
        $filtersHash = md5(serialize($filters));
        
        return "search_results:{$filtersHash}:page:{$page}:per_page:{$perPage}";
    }

    /**
     * Get popular locations for statistics
     */
    private function getPopularLocations()
    {
        return Location::withCount('properties')
            ->having('properties_count', '>', 0)
            ->orderBy('properties_count', 'desc')
            ->take(10)
            ->get()
            ->pluck('name', 'properties_count');
    }

    /**
     * Get property type statistics
     */
    private function getPropertyTypeStats()
    {
        return Property::where('status', 'active')
            ->selectRaw('property_type, COUNT(*) as count')
            ->groupBy('property_type')
            ->orderBy('count', 'desc')
            ->pluck('count', 'property_type');
    }

    /**
     * Forget cache entries by pattern (Redis-specific)
     */
    private function forgetByPattern(string $pattern)
    {
        try {
            $redis = Cache::getRedis();
            $keys = $redis->keys(config('cache.prefix') . ':' . $pattern);
            
            if (!empty($keys)) {
                $redis->del($keys);
            }
        } catch (\Exception $e) {
            Log::warning("Could not clear cache by pattern: {$pattern}", [
                'error' => $e->getMessage()
            ]);
            
            // Fallback: Clear all cache if Redis pattern matching fails
            if ($pattern === 'search_results:*' || $pattern === 'featured_properties:*') {
                Cache::flush();
            }
        }
    }

    /**
     * Warm up essential caches
     */
    public function warmUpCache()
    {
        Log::info("Starting cache warm-up");
        
        // Warm up locations and categories
        $this->getCdoLocations();
        $this->getCategories();
        
        // Warm up statistics
        $this->getPropertyStats();
        
        // Warm up featured properties
        $this->getFeaturedProperties();
        
        // Warm up popular searches
        $popularFilters = [
            ['location' => 'cogon'],
            ['location' => 'carmen'],
            ['location' => 'divisoria'],
            ['property_type' => 'house'],
            ['property_type' => 'apartment'],
            ['listing_type' => 'sale'],
            ['listing_type' => 'rent'],
        ];
        
        foreach ($popularFilters as $filters) {
            $this->getSearchResults($filters);
        }
        
        Log::info("Cache warm-up completed");
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        $stats = [
            'total_keys' => 0,
            'memory_usage' => 0,
            'hit_rate' => 0,
        ];

        try {
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $redis = Cache::getRedis();
                $info = $redis->info();
                
                $stats = [
                    'total_keys' => $info['db0']['keys'] ?? 0,
                    'memory_usage' => $this->formatBytes($info['used_memory'] ?? 0),
                    'hit_rate' => $this->calculateHitRate($info),
                    'cache_type' => 'Redis',
                ];
            }
        } catch (\Exception $e) {
            Log::warning("Could not retrieve cache stats", ['error' => $e->getMessage()]);
            $stats['cache_type'] = 'File/Array';
        }

        return $stats;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * Calculate cache hit rate
     */
    private function calculateHitRate(array $info): float
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;
        
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }
}