<?php

namespace App\Http\Controllers;

use App\Services\CacheService;
use App\Services\ErrorHandlingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    protected ErrorHandlingService $errorHandlingService;
    protected CacheService $cacheService;

    public function __construct(
        ErrorHandlingService $errorHandlingService,
        CacheService $cacheService
    ) {
        $this->errorHandlingService = $errorHandlingService;
        $this->cacheService = $cacheService;
    }

    /**
     * System health check endpoint
     */
    public function check(): JsonResponse
    {
        $health = $this->errorHandlingService->checkSystemHealth();
        
        $statusCode = $health['status'] === 'healthy' ? 200 : 503;
        
        return response()->json([
            'status' => $health['status'],
            'timestamp' => now()->toISOString(),
            'checks' => $health['checks'],
            'version' => config('app.version', '1.0.0'),
            'environment' => app()->environment(),
        ], $statusCode);
    }

    /**
     * Detailed system status with performance metrics
     */
    public function status(): JsonResponse
    {
        $startTime = microtime(true);
        
        $health = $this->errorHandlingService->checkSystemHealth();
        
        // Get cache statistics
        $cacheStats = $this->getCacheStatistics();
        
        // Database stats
        $dbStats = $this->getDatabaseStatistics();
        
        $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        
        return response()->json([
            'status' => $health['status'],
            'timestamp' => now()->toISOString(),
            'response_time_ms' => round($responseTime, 2),
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'environment' => app()->environment(),
                'timezone' => config('app.timezone'),
                'memory_usage' => [
                    'current' => memory_get_usage(true),
                    'peak' => memory_get_peak_usage(true),
                    'limit' => ini_get('memory_limit'),
                ],
            ],
            'checks' => $health['checks'],
            'cache' => $cacheStats,
            'database' => $dbStats,
        ]);
    }

    /**
     * Application performance metrics
     */
    public function metrics(): JsonResponse
    {
        return response()->json([
            'timestamp' => now()->toISOString(),
            'metrics' => [
                'properties' => [
                    'total' => \App\Models\Property::count(),
                    'active' => \App\Models\Property::where('status', 'active')->count(),
                    'recent' => \App\Models\Property::where('created_at', '>=', now()->subDays(7))->count(),
                ],
                'cache' => $this->getCacheStatistics(),
                'performance' => [
                    'memory_usage' => memory_get_usage(true),
                    'peak_memory' => memory_get_peak_usage(true),
                    'execution_time' => microtime(true) - LARAVEL_START,
                ],
            ],
        ]);
    }

    /**
     * Get cache statistics
     */
    private function getCacheStatistics(): array
    {
        try {
            // Test cache write/read
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 1);
            $testRead = Cache::get($testKey);
            Cache::forget($testKey);
            
            return [
                'status' => $testRead === 'test' ? 'operational' : 'error',
                'driver' => config('cache.default'),
                'redis_status' => $this->getRedisStatus(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get Redis status if available
     */
    private function getRedisStatus(): array
    {
        try {
            if (config('cache.stores.redis.driver') === 'redis') {
                $redis = Cache::store('redis');
                $redis->put('redis_health_check', 'test', 1);
                $result = $redis->get('redis_health_check');
                $redis->forget('redis_health_check');
                
                return [
                    'available' => true,
                    'responsive' => $result === 'test',
                ];
            }
            
            return ['available' => false];
        } catch (\Exception $e) {
            return [
                'available' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get database statistics
     */
    private function getDatabaseStatistics(): array
    {
        try {
            $startTime = microtime(true);
            $result = DB::select('SELECT 1 as test');
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            return [
                'status' => 'operational',
                'response_time_ms' => round($responseTime, 2),
                'driver' => config('database.default'),
                'connection' => config('database.connections.' . config('database.default') . '.driver'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test endpoint for monitoring
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'message' => 'CDO Real Estate Platform is running',
        ]);
    }
}