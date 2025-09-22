<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PropertyView;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class TrackPropertyViews
{
    /**
     * Handle an incoming request to track property views.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Only track GET requests to property detail pages
        if ($request->isMethod('GET') && 
            $request->route() && 
            $request->route()->getName() === 'properties.show') {
            
            $property = $request->route('property');
            
            if ($property instanceof Property) {
                $this->recordView($request, $property);
            }
        }
        
        return $response;
    }
    
    /**
     * Record a property view
     */
    private function recordView(Request $request, Property $property): void
    {
        try {
            $agent = new Agent();
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();
            $userId = Auth::id();
            
            // Don't track views from the property owner
            if ($userId && $property->user_id === $userId) {
                return;
            }
            
            // Check if this IP has viewed this property in the last hour to avoid spam
            $recentView = PropertyView::where('property_id', $property->id)
                ->where('ip_address', $ipAddress)
                ->where('created_at', '>', now()->subHours(1))
                ->exists();
                
            if ($recentView) {
                return;
            }
            
            // Determine device type
            $deviceType = 'other';
            if ($agent->isMobile()) {
                $deviceType = 'mobile';
            } elseif ($agent->isTablet()) {
                $deviceType = 'tablet';
            } elseif ($agent->isDesktop()) {
                $deviceType = 'desktop';
            }
            
            // Get browser name
            $browser = $agent->browser() ?: 'unknown';
            
            // Get referrer
            $referrer = $request->header('referer');
            
            // Create the view record
            PropertyView::create([
                'property_id' => $property->id,
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'device_type' => $deviceType,
                'browser' => strtolower($browser),
                'referrer' => $referrer,
                'session_id' => session()->getId(),
                'viewed_at' => now(),
            ]);
            
            // Update property view count cache
            $property->increment('views');
            
        } catch (\Exception $e) {
            // Log the error but don't break the request
            Log::error('Failed to record property view: ' . $e->getMessage(), [
                'property_id' => $property->id,
                'ip_address' => $request->ip(),
                'error' => $e->getMessage()
            ]);
        }
    }
}