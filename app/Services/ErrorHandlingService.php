<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ErrorHandlingService
{
    /**
     * Handle and log application errors
     */
    public function handleError(Throwable $exception, Request $request = null): array
    {
        $errorId = $this->generateErrorId();
        
        // Log error details
        Log::error('Application Error', [
            'error_id' => $errorId,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'url' => $request ? $request->fullUrl() : null,
            'method' => $request ? $request->method() : null,
            'ip' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'user_id' => $request && $request->user() ? $request->user()->id : null,
        ]);

        // Determine appropriate response based on exception type
        return $this->formatErrorResponse($exception, $errorId);
    }

    /**
     * Handle validation errors
     */
    public function handleValidationError(array $errors, Request $request = null): array
    {
        $errorId = $this->generateErrorId();

        Log::info('Validation Error', [
            'error_id' => $errorId,
            'errors' => $errors,
            'url' => $request ? $request->fullUrl() : null,
            'input' => $request ? $request->except(['password', 'password_confirmation', '_token']) : null,
            'ip' => $request ? $request->ip() : null,
        ]);

        return [
            'error_id' => $errorId,
            'type' => 'validation_error',
            'message' => 'Validation failed',
            'errors' => $errors,
            'status_code' => 422,
        ];
    }

    /**
     * Handle database errors
     */
    public function handleDatabaseError(Throwable $exception, Request $request = null): array
    {
        $errorId = $this->generateErrorId();

        Log::error('Database Error', [
            'error_id' => $errorId,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => $request ? $request->fullUrl() : null,
            'user_id' => $request && $request->user() ? $request->user()->id : null,
        ]);

        // Don't expose database details in production
        $message = app()->environment('production') 
            ? 'A database error occurred. Please try again later.'
            : $exception->getMessage();

        return [
            'error_id' => $errorId,
            'type' => 'database_error',
            'message' => $message,
            'status_code' => 500,
        ];
    }

    /**
     * Handle file upload errors
     */
    public function handleFileUploadError(Throwable $exception, Request $request = null): array
    {
        $errorId = $this->generateErrorId();

        Log::error('File Upload Error', [
            'error_id' => $errorId,
            'message' => $exception->getMessage(),
            'files' => $request ? $request->files->keys() : null,
            'user_id' => $request && $request->user() ? $request->user()->id : null,
        ]);

        return [
            'error_id' => $errorId,
            'type' => 'upload_error',
            'message' => 'File upload failed. Please check file size and format.',
            'status_code' => 400,
        ];
    }

    /**
     * Handle API errors
     */
    public function handleApiError(Throwable $exception, Request $request = null): array
    {
        $errorId = $this->generateErrorId();

        Log::error('API Error', [
            'error_id' => $errorId,
            'message' => $exception->getMessage(),
            'endpoint' => $request ? $request->path() : null,
            'method' => $request ? $request->method() : null,
            'parameters' => $request ? $request->except(['password', 'password_confirmation']) : null,
            'headers' => $request ? $request->headers->all() : null,
        ]);

        return [
            'error_id' => $errorId,
            'type' => 'api_error',
            'message' => 'API request failed',
            'status_code' => 500,
        ];
    }

    /**
     * Handle security-related errors
     */
    public function handleSecurityError(string $securityEvent, Request $request = null, array $context = []): array
    {
        $errorId = $this->generateErrorId();

        Log::channel('security')->error('Security Error', array_merge([
            'error_id' => $errorId,
            'event' => $securityEvent,
            'ip' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'url' => $request ? $request->fullUrl() : null,
            'user_id' => $request && $request->user() ? $request->user()->id : null,
        ], $context));

        return [
            'error_id' => $errorId,
            'type' => 'security_error',
            'message' => 'Security violation detected',
            'status_code' => 403,
        ];
    }

    /**
     * Log performance issues
     */
    public function logPerformanceIssue(string $operation, float $executionTime, Request $request = null, array $context = [])
    {
        if ($executionTime > 5.0) { // Log operations taking more than 5 seconds
            Log::warning('Performance Issue', array_merge([
                'operation' => $operation,
                'execution_time' => $executionTime,
                'url' => $request ? $request->fullUrl() : null,
                'method' => $request ? $request->method() : null,
                'memory_usage' => memory_get_peak_usage(true),
                'user_id' => $request && $request->user() ? $request->user()->id : null,
            ], $context));
        }
    }

    /**
     * Log user activity
     */
    public function logUserActivity(string $action, Request $request, array $context = [])
    {
        if ($request->user()) {
            Log::info('User Activity', array_merge([
                'action' => $action,
                'user_id' => $request->user()->id,
                'user_email' => $request->user()->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ], $context));
        }
    }

    /**
     * Generate unique error ID
     */
    private function generateErrorId(): string
    {
        return 'ERR_' . strtoupper(uniqid()) . '_' . time();
    }

    /**
     * Format error response based on exception type
     */
    private function formatErrorResponse(Throwable $exception, string $errorId): array
    {
        $statusCode = 500;
        $type = 'server_error';
        $message = 'An unexpected error occurred';

        // Handle specific exception types
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $statusCode = 422;
            $type = 'validation_error';
            $message = 'Validation failed';
        } elseif ($exception instanceof \Illuminate\Database\QueryException) {
            $statusCode = 500;
            $type = 'database_error';
            $message = app()->environment('production') 
                ? 'Database operation failed'
                : $exception->getMessage();
        } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            $statusCode = 404;
            $type = 'not_found';
            $message = 'Resource not found';
        } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException) {
            $statusCode = 401;
            $type = 'unauthorized';
            $message = 'Authentication required';
        } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
            $statusCode = 403;
            $type = 'forbidden';
            $message = 'Access denied';
        } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException) {
            $statusCode = 429;
            $type = 'rate_limit';
            $message = 'Too many requests';
        }

        return [
            'error_id' => $errorId,
            'type' => $type,
            'message' => $message,
            'status_code' => $statusCode,
        ];
    }

    /**
     * Send error notification for critical errors
     */
    public function sendErrorNotification(array $errorData)
    {
        // Only send notifications for critical errors in production
        if (!app()->environment('production') || $errorData['status_code'] < 500) {
            return;
        }

        try {
            // Here you could integrate with services like:
            // - Email notifications
            // - Slack notifications
            // - Error tracking services (Sentry, Bugsnag)
            // - SMS alerts for critical errors
            
            Log::critical('Critical Error Notification', $errorData);
            
        } catch (Throwable $e) {
            // Don't let notification failures break the app
            Log::error('Failed to send error notification', [
                'original_error' => $errorData,
                'notification_error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get error statistics
     */
    public function getErrorStatistics(int $hours = 24): array
    {
        // This would typically query a database or log aggregation service
        // For now, return empty stats - implement based on your logging solution
        
        return [
            'period_hours' => $hours,
            'total_errors' => 0,
            'error_rate' => 0.0,
            'top_errors' => [],
            'affected_users' => 0,
        ];
    }

    /**
     * Check system health
     */
    public function checkSystemHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'checks' => [],
        ];

        // Database connectivity
        try {
            DB::connection()->getPdo();
            $health['checks']['database'] = ['status' => 'ok', 'response_time' => 0];
        } catch (Throwable $e) {
            $health['status'] = 'unhealthy';
            $health['checks']['database'] = ['status' => 'error', 'message' => 'Database connection failed'];
        }

        // Cache connectivity (Redis)
        try {
            Cache::store('redis')->put('health_check', 'ok', 1);
            Cache::store('redis')->get('health_check');
            $health['checks']['cache'] = ['status' => 'ok'];
        } catch (Throwable $e) {
            $health['checks']['cache'] = ['status' => 'warning', 'message' => 'Cache not available'];
        }

        // Storage accessibility
        try {
            $testFile = storage_path('logs/health_check.tmp');
            file_put_contents($testFile, 'test');
            unlink($testFile);
            $health['checks']['storage'] = ['status' => 'ok'];
        } catch (Throwable $e) {
            $health['status'] = 'unhealthy';
            $health['checks']['storage'] = ['status' => 'error', 'message' => 'Storage not writable'];
        }

        // Memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $memoryPercent = ($memoryUsage / $memoryLimit) * 100;

        $health['checks']['memory'] = [
            'status' => $memoryPercent > 90 ? 'warning' : 'ok',
            'usage_percent' => round($memoryPercent, 2),
            'usage_bytes' => $memoryUsage,
        ];

        return $health;
    }

    /**
     * Parse memory limit string to bytes
     */
    private function parseMemoryLimit(string $limit): int
    {
        $unit = strtolower(substr($limit, -1));
        $value = (int) $limit;

        switch ($unit) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }

        return $value;
    }
}