<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyView;
use App\Models\PropertyInquiry;
use App\Models\SavedSearch;
use App\Models\UserNotification;
use App\Services\CacheService;
use App\Services\SecurityService;
use App\Services\ErrorHandlingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    protected CacheService $cacheService;
    protected SecurityService $securityService;
    protected ErrorHandlingService $errorHandlingService;

    public function __construct(
        CacheService $cacheService,
        SecurityService $securityService,
        ErrorHandlingService $errorHandlingService
    ) {
        $this->middleware('auth');
        $this->cacheService = $cacheService;
        parent::__construct();
        $this->middleware('auth');
        $this->cacheService = $cacheService;
        $this->securityService = $securityService;
        $this->errorHandlingService = $errorHandlingService;
    }

    /**
     * Display the user dashboard
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            // Get dashboard statistics
            $stats = $this->getDashboardStats($user);

            // Get recent activities
            $recentActivity = $user->recent_activity;

            // Get notifications
            $notifications = $user->notifications()
                                 ->latest()
                                 ->limit(5)
                                 ->get();

            // Get saved searches with recent results
            $savedSearches = $user->savedSearches()
                                 ->active()
                                 ->with(['user'])
                                 ->latest()
                                 ->limit(5)
                                 ->get();

            // Get favorite properties
            $favoriteProperties = $user->favorites()
                                      ->with(['property.location', 'property.category'])
                                      ->latest()
                                      ->limit(6)
                                      ->get();

            // Get user's properties if they're a seller/agent
            $myProperties = [];
            if ($user->isAgent() || $user->isSeller() || $user->isAdmin()) {
                $myProperties = $user->properties()
                                    ->with(['location', 'category'])
                                    ->latest()
                                    ->limit(6)
                                    ->get();
            }

            // Log user activity
            $this->errorHandlingService->logUserActivity('dashboard_view', $request);

            return view('dashboard.index', compact(
                'user',
                'stats',
                'recentActivity',
                'notifications',
                'savedSearches',
                'favoriteProperties',
                'myProperties'
            ));

        } catch (\Exception $e) {
            $errorData = $this->errorHandlingService->handleError($e, $request);

            return view('errors.dashboard-error', [
                'error_id' => $errorData['error_id'],
                'message' => 'Unable to load dashboard. Please try again.',
            ]);
        }
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats($user): array
    {
        $cacheKey = "user_dashboard_stats_{$user->id}";

        return cache()->remember($cacheKey, 300, function () use ($user) {
            $stats = [
                'total_views' => 0,
                'total_inquiries' => 0,
                'active_listings' => 0,
                'favorite_properties' => $user->favorites()->count(),
                'saved_searches' => $user->savedSearches()->active()->count(),
                'unread_notifications' => $user->unreadNotifications()->count(),
            ];

            // If user has properties (seller/agent)
            if ($user->properties()->exists()) {
                $stats['active_listings'] = $user->properties()
                                                 ->where('status', 'active')
                                                 ->count();

                $stats['total_views'] = $user->properties()->sum('views');

                $stats['total_inquiries'] = PropertyInquiry::whereHas('property', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->count();

                // Monthly stats
                $stats['views_this_month'] = PropertyView::whereHas('property', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->where('viewed_at', '>=', now()->startOfMonth())->count();

                $stats['inquiries_this_month'] = PropertyInquiry::whereHas('property', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->where('created_at', '>=', now()->startOfMonth())->count();
            }

            return $stats;
        });
    }

    /**
     * Get property analytics for property owners
     */
    public function propertyAnalytics(Request $request, Property $property)
    {
        // Check if user owns this property
        if ($property->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'You do not have permission to view analytics for this property.');
        }

        try {
            $period = $request->get('period', '30'); // days
            $startDate = now()->subDays($period);

            // Get view analytics
            $viewsData = $this->getPropertyViewAnalytics($property, $startDate);

            // Get inquiry analytics
            $inquiriesData = $this->getPropertyInquiryAnalytics($property, $startDate);

            // Get performance metrics
            $performance = $this->getPropertyPerformanceMetrics($property, $startDate);

            return view('dashboard.property-analytics', compact(
                'property',
                'viewsData',
                'inquiriesData',
                'performance',
                'period'
            ));

        } catch (\Exception $e) {
            $errorData = $this->errorHandlingService->handleError($e, $request);

            return view('errors.analytics-error', [
                'error_id' => $errorData['error_id'],
                'message' => 'Unable to load property analytics. Please try again.',
            ]);
        }
    }

    /**
     * Get property view analytics
     */
    private function getPropertyViewAnalytics(Property $property, Carbon $startDate): array
    {
        $views = $property->views()
                         ->where('viewed_at', '>=', $startDate)
                         ->get();

        // Daily views chart data
        $dailyViews = $views->groupBy(function ($view) {
            return $view->viewed_at->format('Y-m-d');
        })->map(function ($dayViews) {
            return $dayViews->count();
        });

        // Device breakdown
        $deviceBreakdown = $views->groupBy('device_type')
                                 ->map(function ($deviceViews) {
                                     return $deviceViews->count();
                                 });

        // Unique vs total views
        $totalViews = $views->count();
        $uniqueViews = $views->unique(function ($view) {
            return $view->ip_address . '_' . $view->user_id;
        })->count();

        return [
            'daily_views' => $dailyViews,
            'device_breakdown' => $deviceBreakdown,
            'total_views' => $totalViews,
            'unique_views' => $uniqueViews,
            'return_visitor_rate' => $uniqueViews > 0 ? (($totalViews - $uniqueViews) / $totalViews) * 100 : 0,
        ];
    }

    /**
     * Get property inquiry analytics
     */
    private function getPropertyInquiryAnalytics(Property $property, Carbon $startDate): array
    {
        $inquiries = $property->inquiries()
                             ->where('created_at', '>=', $startDate)
                             ->get();

        // Daily inquiries
        $dailyInquiries = $inquiries->groupBy(function ($inquiry) {
            return $inquiry->created_at->format('Y-m-d');
        })->map(function ($dayInquiries) {
            return $dayInquiries->count();
        });

        // Inquiry type breakdown
        $typeBreakdown = $inquiries->groupBy('inquiry_type')
                                  ->map(function ($typeInquiries) {
                                      return $typeInquiries->count();
                                  });

        // Response rate
        $totalInquiries = $inquiries->count();
        $respondedInquiries = $inquiries->where('status', 'responded')->count();
        $responseRate = $totalInquiries > 0 ? ($respondedInquiries / $totalInquiries) * 100 : 0;

        return [
            'daily_inquiries' => $dailyInquiries,
            'type_breakdown' => $typeBreakdown,
            'total_inquiries' => $totalInquiries,
            'response_rate' => $responseRate,
            'pending_inquiries' => $inquiries->where('status', 'pending')->count(),
        ];
    }

    /**
     * Get property performance metrics
     */
    private function getPropertyPerformanceMetrics(Property $property, Carbon $startDate): array
    {
        $views = $property->views()->where('viewed_at', '>=', $startDate)->count();
        $inquiries = $property->inquiries()->where('created_at', '>=', $startDate)->count();

        // Conversion rate (inquiries / views)
        $conversionRate = $views > 0 ? ($inquiries / $views) * 100 : 0;

        // Compare with similar properties
        $similarProperties = Property::where('property_type', $property->property_type)
                                   ->where('location_id', $property->location_id)
                                   ->where('id', '!=', $property->id)
                                   ->withCount(['views' => function ($query) use ($startDate) {
                                       $query->where('viewed_at', '>=', $startDate);
                                   }])
                                   ->get();

        $avgViews = $similarProperties->avg('views_count') ?? 0;
        $performanceScore = $avgViews > 0 ? ($views / $avgViews) * 100 : 100;

        return [
            'conversion_rate' => $conversionRate,
            'performance_score' => min($performanceScore, 200), // Cap at 200%
            'avg_similar_views' => $avgViews,
            'ranking' => $this->getPropertyRanking($property, $similarProperties, $views),
        ];
    }

    /**
     * Get property ranking among similar properties
     */
    private function getPropertyRanking(Property $property, $similarProperties, int $views = 0): array
    {
        $properties = $similarProperties->push($property)->sortByDesc('views_count');
        $ranking = $properties->search(function ($p) use ($property) {
            return $p->id === $property->id;
        }) + 1;

        return [
            'rank' => $ranking,
            'total' => $properties->count(),
            'percentile' => (($properties->count() - $ranking + 1) / $properties->count()) * 100,
        ];
    }
    /**
     * Get saved searches management page
     */
    public function savedSearches(Request $request)
    {
        $user = Auth::user();

        $savedSearches = $user->savedSearches()
                             ->with(['user'])
                             ->latest()
                             ->paginate(10);

        return view('dashboard.saved-searches', compact('savedSearches'));
    }

    /**
     * Get notifications management page
     */
    public function notifications(Request $request)
    {
        $user = Auth::user();

        $notifications = $user->notifications()
                             ->active()
                             ->latest()
                             ->paginate(15);

        // Mark notifications as read when viewing
        $user->unreadNotifications()->update(['read_at' => now()]);

        return view('dashboard.notifications', compact('notifications'));
    }

    /**
     * API endpoint for dashboard data
     */
    public function apiDashboardData(Request $request): JsonResponse
    {
        try {
            // Rate limiting
            if (!$this->securityService->checkRateLimit($request, SecurityService::API_RATE_LIMIT)) {
                return response()->json(['error' => 'Too many requests'], 429);
            }

            $user = Auth::user();
            $stats = $this->getDashboardStats($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_activity' => $user->recent_activity->take(5),
                    'unread_notifications' => $user->unreadNotifications()->limit(5)->get(),
                ],
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
     * Mark notification as read
     */
    public function markNotificationRead(UserNotification $notification): JsonResponse
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Toggle saved search active status
     */
    public function toggleSavedSearch(SavedSearch $savedSearch): JsonResponse
    {
        if ($savedSearch->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $savedSearch->is_active = !$savedSearch->is_active;
        $savedSearch->save();

        return response()->json([
            'success' => true,
            'is_active' => $savedSearch->is_active,
        ]);
    }
}
