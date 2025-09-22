<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Category;
use App\Models\Location;
use App\Services\CacheService;
use App\Services\SecurityService;
use App\Services\ErrorHandlingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    protected CacheService $cacheService;
    protected SecurityService $securityService;
    protected ErrorHandlingService $errorHandlingService;

    public function __construct(
        CacheService $cacheService,
        SecurityService $securityService,
        ErrorHandlingService $errorHandlingService
    ) {
        $this->cacheService = $cacheService;
        $this->securityService = $securityService;
        $this->errorHandlingService = $errorHandlingService;
    }

    /**
     * Display a listing of properties with advanced filtering and search
     */
    public function index(Request $request)
    {
        try {
            $startTime = microtime(true);
            
            // Rate limiting
            if (!$this->securityService->checkRateLimit($request, SecurityService::SEARCH_RATE_LIMIT)) {
                return response()->json([
                    'error' => 'Too many requests. Please try again later.'
                ], 429);
            }

            // Validate and sanitize input
            $validated = $this->securityService->validateSearchInput($request);
            
            // Generate cache key for search results
            $cacheKey = 'property_search_' . md5(serialize($validated));
            
            // Try to get cached results
            $result = Cache::remember($cacheKey, 1800, function () use ($validated) { // 30 minutes
                $query = Property::with(['category', 'location', 'user']);
                
                // Apply filters from validated array
                $this->applyFiltersFromArray($query, $validated);
                
                // Apply search
                if (isset($validated['search'])) {
                    $this->applySearchFromString($query, $validated['search']);
                }
                
                // Apply sorting from validated array
                $this->applySortingFromArray($query, $validated);
                
                // Paginate results
                $properties = $query->paginate($validated['per_page'] ?? 12);
                
                return [
                    'properties' => $properties,
                    'cached_at' => now(),
                ];
            });
            
            // Get filter options (cached)
            $categories = $this->cacheService->getCategories();
            $locations = $this->cacheService->getCdoLocations();
            $priceRange = $this->getPriceRange();
            
            // Log performance
            $executionTime = microtime(true) - $startTime;
            $this->errorHandlingService->logPerformanceIssue('property_search', $executionTime, $request);
            
            // Log user activity
            $this->errorHandlingService->logUserActivity('search_properties', $request, [
                'filters' => $validated,
                'results_count' => $result['properties']->total(),
            ]);
            
            return view('properties.index', [
                'properties' => $result['properties'],
                'categories' => $categories,
                'locations' => $locations,
                'priceRange' => $priceRange,
                'cached_at' => $result['cached_at'],
            ]);
            
        } catch (\Exception $e) {
            $errorData = $this->errorHandlingService->handleError($e, $request);
            
            // Return error view for web requests
            return view('errors.search-error', [
                'error_id' => $errorData['error_id'],
                'message' => 'Unable to load properties. Please try again.',
            ]);
        }
    }
    
    /**
     * Advanced search API endpoint for AJAX requests
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $startTime = microtime(true);

            // Rate limiting for API
            if (!$this->securityService->checkRateLimit($request, SecurityService::API_RATE_LIMIT)) {
                return response()->json([
                    'error' => 'Too many requests. Please try again later.'
                ], 429);
            }

            // Validate and sanitize input using SecurityService
            $validated = $this->securityService->validateSearchInput($request);
            
            // Generate cache key
            $cacheKey = 'api_search_' . md5(serialize($validated));
            
            // Get cached results
            $result = Cache::remember($cacheKey, 1800, function () use ($validated) {
                $query = Property::with(['category', 'location', 'user'])
                               ->where('status', 'active');
                
                // Apply filters from validated array
                $this->applyFiltersFromArray($query, $validated);
                
                // Apply search
                if (isset($validated['search'])) {
                    $this->applySearchFromString($query, $validated['search']);
                }
                
                // Apply sorting
                $this->applySortingFromArray($query, $validated);
                
                // Paginate
                $properties = $query->paginate($validated['per_page'] ?? 12);
                
                return [
                    'properties' => $properties->items(),
                    'pagination' => [
                        'current_page' => $properties->currentPage(),
                        'last_page' => $properties->lastPage(),
                        'per_page' => $properties->perPage(),
                        'total' => $properties->total(),
                        'from' => $properties->firstItem(),
                        'to' => $properties->lastItem(),
                    ],
                    'cached_at' => now(),
                ];
            });
            
            // Log performance
            $executionTime = microtime(true) - $startTime;
            $this->errorHandlingService->logPerformanceIssue('api_search', $executionTime, $request);
            
            return response()->json([
                'success' => true,
                'data' => $result['properties'],
                'pagination' => $result['pagination'],
                'cached_at' => $result['cached_at'],
            ]);
            
        } catch (\Exception $e) {
            $errorData = $this->errorHandlingService->handleApiError($e, $request);
            
            return response()->json([
                'success' => false,
                'error' => $errorData['message'],
                'error_id' => $errorData['error_id'],
            ], $errorData['status_code']);
        }
    }

    /**
     * Legacy search method - keeping for compatibility
     */
    public function legacySearch(Request $request): JsonResponse
    {
        // Rate limiting
        if (!$this->securityService->checkRateLimit($request, SecurityService::SEARCH_RATE_LIMIT)) {
            return response()->json(['error' => 'Too many requests'], 429);
        }

        $query = Property::with(['category', 'location', 'user'])
                        ->where('status', 'active');
        
        // Apply filters
        $this->applyFilters($query, $request);
        
        // Apply search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('address', 'LIKE', "%{$searchTerm}%");
            });
        }
        
        // Apply sorting
        $this->applySorting($query, $request);
        
        // Get results with pagination
        $perPage = $request->get('per_page', 12);
        $properties = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => [
                'properties' => $properties->items(),
                'pagination' => [
                    'current_page' => $properties->currentPage(),
                    'last_page' => $properties->lastPage(),
                    'per_page' => $properties->perPage(),
                    'total' => $properties->total(),
                    'from' => $properties->firstItem(),
                    'to' => $properties->lastItem()
                ]
            ]
        ]);
    }
    
    /**
     * User's property dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        $properties = Property::where('user_id', $user->id)
            ->with(['category', 'location'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $stats = [
            'total_properties' => $properties->count(),
            'active_properties' => $properties->where('status', 'active')->count(),
            'pending_properties' => $properties->where('status', 'pending')->count(),
            'total_views' => $properties->sum('views'),
            'avg_price' => $properties->avg('price'),
            'total_value' => $properties->sum('price')
        ];
        
        return view('properties.dashboard', compact('properties', 'stats'));
    }
    
    /**
     * Bulk operations for properties
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|string|in:activate,deactivate,delete',
            'property_ids' => 'required|array|min:1',
            'property_ids.*' => 'exists:properties,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $propertyIds = $request->property_ids;
        $action = $request->action;
        
        DB::beginTransaction();
        
        try {
            switch ($action) {
                case 'activate':
                    Property::whereIn('id', $propertyIds)->update(['status' => 'active']);
                    $message = 'Properties activated successfully';
                    break;
                    
                case 'deactivate':
                    Property::whereIn('id', $propertyIds)->update(['status' => 'draft']);
                    $message = 'Properties deactivated successfully';
                    break;
                    
                case 'delete':
                    Property::whereIn('id', $propertyIds)->delete();
                    $message = 'Properties deleted successfully';
                    break;
            }
            
            DB::commit();
            
            return response()->json(['success' => true, 'message' => $message]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to perform bulk action'], 500);
        }
    }
    
    // Private helper methods
    
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        
        // Handle location filtering by barangay name for CDO
        if ($request->filled('location')) {
            $query->whereHas('location', function($q) use ($request) {
                $q->where('slug', strtolower(str_replace('_', '-', $request->location)));
            });
        }
        
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }
        
        if ($request->filled('bathrooms')) {
            $query->where('bathrooms', '>=', $request->bathrooms);
        }
        
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'active'); // Default to active properties
        }
    }
    
    private function applySorting($query, Request $request)
    {
        $sortBy = $request->get('sort_by', 'newest');
        
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'bedrooms':
                $query->orderBy('bedrooms', 'desc');
                break;
            case 'bathrooms':
                $query->orderBy('bathrooms', 'desc');
                break;
            case 'views':
                $query->orderBy('views', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }
    
    private function getPriceRange()
    {
        return Cache::remember('property_price_range', 3600, function () {
            return [
                'min' => Property::where('status', 'active')->min('price') ?? 0,
                'max' => Property::where('status', 'active')->max('price') ?? 1000000
            ];
        });
    }

    /**
     * Apply filters from validated array instead of Request object
     */
    private function applyFiltersFromArray($query, array $filters)
    {
        if (isset($filters['location']) && $filters['location']) {
            // Search by location name or barangay
            $query->whereHas('location', function ($q) use ($filters) {
                $q->where('name', 'LIKE', "%{$filters['location']}%");
            });
        }
        
        if (isset($filters['category']) && $filters['category']) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }
        
        if (isset($filters['property_type']) && $filters['property_type']) {
            $query->where('property_type', $filters['property_type']);
        }
        
        if (isset($filters['listing_type']) && $filters['listing_type']) {
            $query->where('listing_type', $filters['listing_type']);
        }
        
        if (isset($filters['min_price']) && $filters['min_price']) {
            $query->where('price', '>=', $filters['min_price']);
        }
        
        if (isset($filters['max_price']) && $filters['max_price']) {
            $query->where('price', '<=', $filters['max_price']);
        }
        
        if (isset($filters['bedrooms']) && $filters['bedrooms']) {
            $query->where('bedrooms', '>=', $filters['bedrooms']);
        }
        
        if (isset($filters['bathrooms']) && $filters['bathrooms']) {
            $query->where('bathrooms', '>=', $filters['bathrooms']);
        }
        
        if (isset($filters['min_area']) && $filters['min_area']) {
            $query->where('area', '>=', $filters['min_area']);
        }
        
        if (isset($filters['max_area']) && $filters['max_area']) {
            $query->where('area', '<=', $filters['max_area']);
        }
        
        // Default to active properties
        $query->where('status', 'active');
    }

    /**
     * Apply search from string instead of Request object
     */
    private function applySearchFromString($query, string $searchTerm)
    {
        $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'LIKE', "%{$searchTerm}%")
              ->orWhere('description', 'LIKE', "%{$searchTerm}%")
              ->orWhere('address', 'LIKE', "%{$searchTerm}%")
              ->orWhereHas('location', function ($q) use ($searchTerm) {
                  $q->where('name', 'LIKE', "%{$searchTerm}%");
              })
              ->orWhereHas('category', function ($q) use ($searchTerm) {
                  $q->where('name', 'LIKE', "%{$searchTerm}%");
              });
        });
    }

    /**
     * Apply sorting from validated array instead of Request object
     */
    private function applySortingFromArray($query, array $filters)
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        switch ($sortBy) {
            case 'price':
                $query->orderBy('price', $sortOrder);
                break;
            case 'views':
                $query->orderBy('views', $sortOrder);
                break;
            case 'updated_at':
                $query->orderBy('updated_at', $sortOrder);
                break;
            case 'featured_at':
                $query->orderBy('featured_at', $sortOrder);
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }
    }
}