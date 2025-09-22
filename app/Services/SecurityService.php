<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class SecurityService
{
    /**
     * Rate limiting keys
     */
    const SEARCH_RATE_LIMIT = 'search';
    const API_RATE_LIMIT = 'api';
    const CONTACT_RATE_LIMIT = 'contact';
    const AUTH_RATE_LIMIT = 'auth';
    const UPLOAD_RATE_LIMIT = 'upload';

    /**
     * Validate and sanitize property search input
     */
    public function validateSearchInput(Request $request): array
    {
        $rules = [
            'location' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9\s\-_]+$/',
            'category' => 'nullable|string|max:50|regex:/^[a-zA-Z0-9\-_]+$/',
            'property_type' => 'nullable|in:house,apartment,condo,commercial,land',
            'listing_type' => 'nullable|in:sale,rent',
            'min_price' => 'nullable|numeric|min:0|max:1000000000',
            'max_price' => 'nullable|numeric|min:0|max:1000000000|gte:min_price',
            'bedrooms' => 'nullable|integer|min:0|max:20',
            'bathrooms' => 'nullable|integer|min:0|max:20',
            'min_area' => 'nullable|numeric|min:0|max:10000',
            'max_area' => 'nullable|numeric|min:0|max:10000|gte:min_area',
            'sort_by' => 'nullable|in:price,created_at,updated_at,views,featured_at',
            'sort_order' => 'nullable|in:asc,desc',
            'page' => 'nullable|integer|min:1|max:1000',
            'per_page' => 'nullable|integer|min:1|max:50',
        ];

        $validated = $request->validate($rules);
        
        // Sanitize string inputs
        foreach (['location', 'category'] as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = $this->sanitizeString($validated[$field]);
            }
        }

        // Log suspicious activity
        if ($this->detectSuspiciousSearchPatterns($validated)) {
            Log::warning('Suspicious search patterns detected', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'input' => $validated,
            ]);
        }

        return $validated;
    }

    /**
     * Validate property creation/update input
     */
    public function validatePropertyInput(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'title' => 'required|string|max:200|min:5',
            'description' => 'required|string|max:5000|min:20',
            'property_type' => 'required|in:house,apartment,condo,commercial,land',
            'listing_type' => 'required|in:sale,rent',
            'price' => 'required|numeric|min:1|max:1000000000',
            'bedrooms' => 'nullable|integer|min:0|max:20',
            'bathrooms' => 'nullable|integer|min:0|max:20',
            'area' => 'nullable|numeric|min:1|max:10000',
            'address' => 'required|string|max:300|min:10',
            'location_id' => 'nullable|exists:locations,id',
            'category_id' => 'nullable|exists:categories,id',
            'features' => 'nullable|array|max:20',
            'features.*' => 'string|max:100',
            'contact_phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\-\(\)\s]+$/',
            'contact_email' => 'nullable|email|max:100',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
        ];

        if ($isUpdate) {
            // Make some fields optional for updates
            $rules['title'] = 'sometimes|' . $rules['title'];
            $rules['description'] = 'sometimes|' . $rules['description'];
            $rules['property_type'] = 'sometimes|' . $rules['property_type'];
            $rules['listing_type'] = 'sometimes|' . $rules['listing_type'];
            $rules['price'] = 'sometimes|' . $rules['price'];
            $rules['address'] = 'sometimes|' . $rules['address'];
        }

        $validated = $request->validate($rules);

        // Sanitize text inputs
        foreach (['title', 'description', 'address'] as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = $this->sanitizeHtml($validated[$field]);
            }
        }

        // Sanitize features array
        if (isset($validated['features'])) {
            $validated['features'] = array_map([$this, 'sanitizeString'], $validated['features']);
        }

        return $validated;
    }

    /**
     * Validate contact form input
     */
    public function validateContactInput(Request $request): array
    {
        $rules = [
            'name' => 'required|string|max:100|min:2|regex:/^[a-zA-Z\s\-\.]+$/',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\-\(\)\s]+$/',
            'message' => 'required|string|max:2000|min:10',
            'property_id' => 'nullable|exists:properties,id',
            'subject' => 'nullable|string|max:200',
        ];

        $validated = $request->validate($rules);

        // Sanitize inputs
        $validated['name'] = $this->sanitizeString($validated['name']);
        $validated['message'] = $this->sanitizeHtml($validated['message']);
        
        if (isset($validated['subject'])) {
            $validated['subject'] = $this->sanitizeString($validated['subject']);
        }

        // Check for spam patterns
        if ($this->detectSpamPatterns($validated['message'])) {
            Log::warning('Potential spam message detected', [
                'ip' => $request->ip(),
                'email' => $validated['email'],
                'message_preview' => Str::limit($validated['message'], 100),
            ]);
            
            throw ValidationException::withMessages([
                'message' => 'Your message appears to contain spam content. Please try again.'
            ]);
        }

        return $validated;
    }

    /**
     * Apply rate limiting
     */
    public function checkRateLimit(Request $request, string $type): bool
    {
        $key = $this->generateRateLimitKey($request, $type);
        $limits = $this->getRateLimits($type);

        $executed = RateLimiter::attempt(
            $key,
            $limits['max_attempts'],
            function() {},
            $limits['decay_seconds']
        );

        if (!$executed) {
            Log::warning('Rate limit exceeded', [
                'type' => $type,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $executed;
    }

    /**
     * Get remaining rate limit attempts
     */
    public function getRemainingAttempts(Request $request, string $type): int
    {
        $key = $this->generateRateLimitKey($request, $type);
        $limits = $this->getRateLimits($type);
        
        return RateLimiter::remaining($key, $limits['max_attempts']);
    }

    /**
     * Validate file upload security
     */
    public function validateFileUpload(Request $request): array
    {
        $rules = [
            'files' => 'required|array|max:10',
            'files.*' => 'required|file|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
        ];

        $validated = $request->validate($rules);

        // Additional security checks
        foreach ($validated['files'] as $file) {
            // Check file content type matches extension
            if (!$this->validateFileType($file)) {
                throw ValidationException::withMessages([
                    'files' => 'One or more files have invalid content type.'
                ]);
            }

            // Check for executable content
            if ($this->containsExecutableContent($file)) {
                throw ValidationException::withMessages([
                    'files' => 'Files cannot contain executable content.'
                ]);
            }
        }

        return $validated;
    }

    /**
     * Sanitize string input
     */
    private function sanitizeString(string $input): string
    {
        // Remove HTML tags
        $cleaned = strip_tags($input);
        
        // Remove extra whitespace
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        
        // Trim
        $cleaned = trim($cleaned);
        
        return $cleaned;
    }

    /**
     * Sanitize HTML input (allows basic formatting)
     */
    private function sanitizeHtml(string $input): string
    {
        // Allow only basic HTML tags
        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li>';
        
        $cleaned = strip_tags($input, $allowedTags);
        
        // Remove dangerous attributes
        $cleaned = preg_replace('/\s*(on\w+|style|script|javascript)="[^"]*"/i', '', $cleaned);
        
        return trim($cleaned);
    }

    /**
     * Detect suspicious search patterns
     */
    private function detectSuspiciousSearchPatterns(array $input): bool
    {
        $suspicious = false;

        // Check for SQL injection patterns
        $sqlPatterns = [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/delete\s+from/i',
            '/insert\s+into/i',
            '/update\s+set/i',
            '/\'\s*or\s*\'/i',
            '/\'\s*and\s*\'/i',
        ];

        foreach ($input as $value) {
            if (is_string($value)) {
                foreach ($sqlPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $suspicious = true;
                        break 2;
                    }
                }
            }
        }

        // Check for excessively long strings
        foreach ($input as $value) {
            if (is_string($value) && strlen($value) > 1000) {
                $suspicious = true;
                break;
            }
        }

        return $suspicious;
    }

    /**
     * Detect spam patterns in text
     */
    private function detectSpamPatterns(string $text): bool
    {
        $spamPatterns = [
            '/\b(viagra|cialis|casino|poker|lottery|winner)\b/i',
            '/\$\d+.*guaranteed/i',
            '/click\s+here\s+now/i',
            '/limited\s+time\s+offer/i',
            '/\b(urgent|immediate|asap)\b.*\b(response|reply)\b/i',
            '/\b\d{4,}\s*\$\b/', // Large money amounts
        ];

        foreach ($spamPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        // Check for excessive URLs
        if (preg_match_all('/https?:\/\//', $text) > 3) {
            return true;
        }

        // Check for repetitive content
        $words = str_word_count($text, 1);
        if (count($words) > 10) {
            $wordCounts = array_count_values($words);
            $maxRepeats = max($wordCounts);
            if ($maxRepeats > count($words) * 0.3) { // 30% repetition threshold
                return true;
            }
        }

        return false;
    }

    /**
     * Generate rate limit key
     */
    private function generateRateLimitKey(Request $request, string $type): string
    {
        $identifier = $request->user() ? 'user:' . $request->user()->id : 'ip:' . $request->ip();
        return "rate_limit:{$type}:{$identifier}";
    }

    /**
     * Get rate limit configuration
     */
    private function getRateLimits(string $type): array
    {
        $limits = [
            self::SEARCH_RATE_LIMIT => [
                'max_attempts' => 60,
                'decay_seconds' => 60, // 60 requests per minute
            ],
            self::API_RATE_LIMIT => [
                'max_attempts' => 100,
                'decay_seconds' => 60, // 100 requests per minute
            ],
            self::CONTACT_RATE_LIMIT => [
                'max_attempts' => 5,
                'decay_seconds' => 300, // 5 requests per 5 minutes
            ],
            self::AUTH_RATE_LIMIT => [
                'max_attempts' => 5,
                'decay_seconds' => 300, // 5 attempts per 5 minutes
            ],
            self::UPLOAD_RATE_LIMIT => [
                'max_attempts' => 10,
                'decay_seconds' => 300, // 10 uploads per 5 minutes
            ],
        ];

        return $limits[$type] ?? [
            'max_attempts' => 30,
            'decay_seconds' => 60,
        ];
    }

    /**
     * Validate file type matches content
     */
    private function validateFileType($file): bool
    {
        $allowedMimes = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/webp' => ['webp'],
        ];

        $fileMime = $file->getMimeType();
        $fileExtension = strtolower($file->getClientOriginalExtension());

        return isset($allowedMimes[$fileMime]) && 
               in_array($fileExtension, $allowedMimes[$fileMime]);
    }

    /**
     * Check for executable content in uploaded files
     */
    private function containsExecutableContent($file): bool
    {
        $content = file_get_contents($file->getRealPath());
        
        // Check for PHP tags
        if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
            return true;
        }

        // Check for script tags
        if (preg_match('/<script[^>]*>/i', $content)) {
            return true;
        }

        // Check for suspicious binary signatures
        $signatures = [
            'MZ', // PE executable
            'PK', // ZIP/JAR
            '7z', // 7zip
        ];

        foreach ($signatures as $sig) {
            if (strpos($content, $sig) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, array $context = [])
    {
        Log::channel('security')->warning("Security Event: {$event}", $context);
    }

    /**
     * Generate secure token
     */
    public function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Validate CSRF token manually (for API endpoints)
     */
    public function validateCsrfToken(Request $request): bool
    {
        $token = $request->header('X-CSRF-TOKEN') ?: $request->input('_token');
        $sessionToken = $request->session()->token();

        return $token && hash_equals($sessionToken, $token);
    }
}