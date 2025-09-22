<?php

namespace App\Http\Middleware;

use App\Services\SecurityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class RateLimitMiddleware
{
    protected SecurityService $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'api'): SymfonyResponse
    {
        // Check rate limit
        if (!$this->securityService->checkRateLimit($request, $type)) {
            $remaining = $this->securityService->getRemainingAttempts($request, $type);
            
            return response()->json([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => 60, // seconds
            ], Response::HTTP_TOO_MANY_REQUESTS)->withHeaders([
                'X-RateLimit-Remaining' => $remaining,
                'X-RateLimit-Retry-After' => 60,
            ]);
        }

        $response = $next($request);

        // Add rate limit headers to response
        $remaining = $this->securityService->getRemainingAttempts($request, $type);
        
        if ($response instanceof \Illuminate\Http\JsonResponse || 
            $response instanceof \Illuminate\Http\Response) {
            $response->withHeaders([
                'X-RateLimit-Remaining' => $remaining,
            ]);
        }

        return $response;
    }
}