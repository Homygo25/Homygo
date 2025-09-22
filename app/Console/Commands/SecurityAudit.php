<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SecurityAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:audit {--fix : Attempt to fix security issues automatically}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform a comprehensive security audit of the Finder Laravel application';

    /**
     * Security check results
     *
     * @var array
     */
    protected $results = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔒 Starting Finder Laravel Security Audit...');
        $this->newLine();

        $this->results = [];

        // Run all security checks
        $this->checkEnvironmentConfiguration();
        $this->checkFilePermissions();
        $this->checkSecurityHeaders();
        $this->checkDatabaseSecurity();
        $this->checkAuthenticationSecurity();
        $this->checkFileUploadSecurity();
        $this->checkCsrfProtection();
        $this->checkSqlInjectionVulnerabilities();
        $this->checkXssProtection();
        $this->checkSessionSecurity();
        $this->checkDependencyVulnerabilities();
        $this->checkLoggingSecurity();

        // Display results
        $this->displayResults();

        return Command::SUCCESS;
    }

    /**
     * Check environment configuration
     */
    protected function checkEnvironmentConfiguration()
    {
        $this->info('🔍 Checking Environment Configuration...');

        $checks = [
            'APP_DEBUG' => [
                'current' => config('app.debug'),
                'current' => \config('app.debug'),
                'severity' => 'CRITICAL',
                'message' => 'APP_DEBUG should be false in production'
            ],
            'APP_ENV' => [
                'current' => config('app.env'),
                'current' => \config('app.env'),
                'severity' => 'HIGH',
                'message' => 'APP_ENV should be "production" in production'
            ],
            'APP_KEY' => [
                'current' => !empty(config('app.key')),
                'current' => !empty(\config('app.key')),
                'severity' => 'CRITICAL',
                'message' => 'APP_KEY must be set'
            ],
            'SESSION_SECURE_COOKIE' => [
                'current' => config('session.secure'),
                'current' => \config('session.secure'),
                'severity' => 'HIGH',
                'message' => 'SESSION_SECURE_COOKIE should be true for HTTPS'
            ],
            'SESSION_HTTP_ONLY' => [
                'current' => config('session.http_only'),
                'current' => \config('session.http_only'),
                'severity' => 'MEDIUM',
                'message' => 'SESSION_HTTP_ONLY should be true'
            ]
        ];

        foreach ($checks as $key => $check) {
            $passed = $check['current'] === $check['expected'];
            $this->addResult('Environment', $key, $passed, $check['severity'], $check['message']);
            if (!$passed && $this->option('fix')) {
                $this->attemptFix('environment', $key);
            }
        }
    }

    /**
     * Check file permissions
     */
    protected function checkFilePermissions()
    {
        $this->info('🔍 Checking File Permissions...');

        $checks = [
            storage_path() => ['writable' => true, 'severity' => 'CRITICAL'],
            \storage_path('logs') => ['writable' => true, 'severity' => 'HIGH'],
            \base_path('bootstrap/cache') => ['writable' => true, 'severity' => 'HIGH'],
            \base_path('.env') => ['readable' => true, 'public' => false, 'severity' => 'CRITICAL'],
            \public_path() => ['public' => true, 'severity' => 'INFO'],
        ];

        foreach ($checks as $path => $requirements) {
            if (!File::exists($path)) {
                $this->addResult('Permissions', $path, false, 'HIGH', 'Path does not exist');
                continue;
            }

            $passed = true;
            $messages = [];

            if (isset($requirements['writable']) && $requirements['writable']) {
                if (!File::isWritable($path)) {
                    $passed = false;
                    $messages[] = 'Should be writable';
                }
            }

            if (isset($requirements['readable']) && $requirements['readable']) {
                if (!File::isReadable($path)) {
                    $passed = false;
                    $messages[] = 'Should be readable';
                }
            }

            $this->addResult(
                'Permissions',
                $path,
                $passed,
                $requirements['severity'],
                implode(', ', $messages)
            );
        }
    }

    /**
     * Check security headers middleware
     */
    protected function checkSecurityHeaders()
    {
        $this->info('🔍 Checking Security Headers...');

        $middlewareFile = app_path('Http/Middleware/SecurityHeaders.php');
        $middlewareFile = \app_path('Http/Middleware/SecurityHeaders.php');

        $this->addResult(
            'Security Headers',
            'SecurityHeaders Middleware',
            $exists,
            'HIGH',
            'SecurityHeaders middleware should exist'
        );

        if ($exists) {
            $content = File::get($middlewareFile);

            $headers = [
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'SAMEORIGIN',
                'X-XSS-Protection' => '1; mode=block',
                'Strict-Transport-Security' => 'max-age=31536000',
                'Content-Security-Policy' => 'default-src'
            ];

            foreach ($headers as $header => $_) {
                $hasHeader = strpos($content, $header) !== false;
                $this->addResult(
                    'Security Headers',
                    $header,
                    $hasHeader,
                    'MEDIUM',
                    "Header {$header} should be set"
                );
            }
        }
    }

    /**
     * Check database security
     */
    protected function checkDatabaseSecurity()
    {
        $this->info('🔍 Checking Database Security...');

        try {
            // Check database connection
            DB::connection()->getPdo();
            $this->addResult('Database', 'Connection', true, 'INFO', 'Database connection successful');

            // Check for default passwords or weak configurations
            $dbConfig = config('database.connections.' . config('database.default'));
            $dbConfig = \config('database.connections.' . \config('database.default'));
            if (isset($dbConfig['password']) && empty($dbConfig['password'])) {
                $this->addResult('Database', 'Password', false, 'CRITICAL', 'Database password is empty');
            } else {
                $this->addResult('Database', 'Password', true, 'INFO', 'Database password is set');
            }

            // Check for SQL injection prevention measures
            $this->checkQueryBuilder();

        } catch (\Exception $e) {
            $this->addResult('Database', 'Connection', false, 'CRITICAL', 'Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Check query builder for SQL injection prevention
     */
    protected function checkQueryBuilder()
    {
        // This is a basic check - in a real audit, you'd scan all model files
        $modelFiles = File::glob(app_path('Models/*.php'));
        $modelFiles = File::glob(\app_path('Models/*.php'));
        foreach ($modelFiles as $file) {
            $content = File::get($file);

            // Check for raw queries that might be vulnerable
            if (preg_match('/DB::raw\s*\(\s*[\'"][^\']*\$.*[\'"]/', $content)) {
                $this->addResult(
                    'Database',
                    basename($file),
                    false,
                    'HIGH',
                    'Potentially unsafe raw query detected'
                );
            }
        }
    }

    /**
     * Check authentication security
     */
    protected function checkAuthenticationSecurity()
    {
        $this->info('🔍 Checking Authentication Security...');

        // Check password hashing
        $hashDriver = config('hashing.driver');
        $hashDriver = \config('hashing.driver');
        $this->addResult(
            'Authentication',
            'Password Hashing',
            $hashDriver === 'bcrypt',
            'HIGH',
            'Should use bcrypt for password hashing'
        );

        // Check bcrypt rounds
        $rounds = config('hashing.bcrypt.rounds');
        $rounds = \config('hashing.bcrypt.rounds');
        $this->addResult(
            'Authentication',
            'Bcrypt Rounds',
            $rounds >= 12,
            'MEDIUM',
            'Bcrypt rounds should be at least 12'
        );

        // Check rate limiting
        $this->checkRateLimiting();
    }

    /**
     * Check rate limiting configuration
     */
    protected function checkRateLimiting()
    {
        $webRoutes = File::get(base_path('routes/web.php'));
        $webRoutes = File::get(\base_path('routes/web.php'));
        $hasLoginThrottling = strpos($webRoutes, 'throttle:') !== false;
        $this->addResult(
            'Authentication',
            'Rate Limiting',
            $hasLoginThrottling,
            'HIGH',
            'Authentication routes should have rate limiting'
        );
    }

    /**
     * Check file upload security
     */
    protected function checkFileUploadSecurity()
    {
        $this->info('🔍 Checking File Upload Security...');

        $controllerFiles = File::glob(app_path('Http/Controllers/*.php'));
        $controllerFiles = File::glob(\app_path('Http/Controllers/*.php'));
        foreach ($controllerFiles as $file) {
            $content = File::get($file);

            // Check for file upload validation
            if (strpos($content, 'UploadedFile') !== false) {
                $hasValidation = strpos($content, 'mimes:') !== false || strpos($content, 'image') !== false;
                $this->addResult(
                    'File Upload',
                    basename($file),
                    $hasValidation,
                    'HIGH',
                    'File uploads should have proper validation'
                );
            }
        }
    }

    /**
     * Check CSRF protection
     */
    protected function checkCsrfProtection()
    {
        $this->info('🔍 Checking CSRF Protection...');

        // In Laravel 11, CSRF is enabled by default for web routes
        // Check if it's been explicitly disabled
        $middlewareConfig = File::get(base_path('bootstrap/app.php'));
        $middlewareConfig = File::get(\base_path('bootstrap/app.php'));
                          strpos($middlewareConfig, 'VerifyCsrfToken') !== false;

        $this->addResult(
            'CSRF',
            'Middleware',
            !$hasDisabledCsrf,
            'HIGH',
            'CSRF protection is enabled by default in Laravel 11'
        );
    }

    /**
     * Check for SQL injection vulnerabilities
     */
    protected function checkSqlInjectionVulnerabilities()
    {
        $this->info('🔍 Checking SQL Injection Vulnerabilities...');

        $phpFiles = File::allFiles(app_path());
        $phpFiles = File::allFiles(\app_path());

        foreach ($phpFiles as $file) {
            $content = File::get($file);

            // Check for potentially dangerous patterns
            if (preg_match('/DB::raw\s*\(\s*[\'"][^\']*\$/', $content) ||
                preg_match('/\$.*\s*\.\s*[\'"].*SQL/', $content)) {
                $vulnerableFiles[] = $file->getRelativePathname();
            }
        }

        $this->addResult(
            'SQL Injection',
            'File Scan',
            empty($vulnerableFiles),
            'CRITICAL',
            empty($vulnerableFiles) ? 'No obvious SQL injection vulnerabilities found' : 'Potential vulnerabilities in: ' . implode(', ', $vulnerableFiles)
        );
    }

    /**
     * Check XSS protection
     */
    protected function checkXssProtection()
    {
        $this->info('🔍 Checking XSS Protection...');

        $bladeFiles = File::glob(resource_path('views/**/*.blade.php'));
        $bladeFiles = File::glob(\resource_path('views/**/*.blade.php'));

        foreach ($bladeFiles as $file) {
            $content = File::get($file);

            // Check for unescaped output
            if (preg_match('/\{\!\!\s*\$/', $content)) {
                $vulnerableFiles[] = basename($file);
            }
        }

        $this->addResult(
            'XSS Protection',
            'Blade Templates',
            empty($vulnerableFiles),
            'HIGH',
            empty($vulnerableFiles) ? 'No unescaped output found' : 'Unescaped output in: ' . implode(', ', $vulnerableFiles)
        );
    }

    /**
     * Check session security
     */
    protected function checkSessionSecurity()
    {
        $this->info('🔍 Checking Session Security...');

        $sessionDriver = config('session.driver');
        $sessionDriver = \config('session.driver');

        $this->addResult(
            'Session',
            'Driver',
            $secureSessions,
            'MEDIUM',
            'Session driver should be redis or database for production'
        );

        $sessionLifetime = config('session.lifetime');
        $sessionLifetime = \config('session.lifetime');
        $this->addResult(
            'Session',
            'Lifetime',
            $sessionLifetime <= 120,
            'MEDIUM',
            'Session lifetime should be reasonable (≤120 minutes)'
        );
    }

    /**
     * Check dependency vulnerabilities
     */
    protected function checkDependencyVulnerabilities()
    {
        $this->info('🔍 Checking Dependency Vulnerabilities...');

        // Check if composer audit command exists
        $composerLock = base_path('composer.lock');
        $composerLock = \base_path('composer.lock');

        $this->addResult(
            'Dependencies',
            'Composer Lock',
            $hasComposerLock,
            'MEDIUM',
            'composer.lock should exist for dependency integrity'
        );

        if ($hasComposerLock) {
            $lockContent = json_decode(File::get($composerLock), true);
            $packages = $lockContent['packages'] ?? [];

            $this->addResult(
                'Dependencies',
                'Package Count',
                count($packages) > 0,
                'INFO',
                'Found ' . count($packages) . ' packages'
            );
        }
    }

    /**
     * Check logging security
     */
    protected function checkLoggingSecurity()
    {
        $this->info('🔍 Checking Logging Security...');

        // Check the actual LOG_LEVEL environment variable
        $logLevel = env('LOG_LEVEL', 'debug');
        $productionLevels = ['error', 'critical', 'alert', 'emergency'];

        $this->addResult(
            'Logging',
            'Log Level',
            in_array($logLevel, $productionLevels),
            'MEDIUM',
            "Production log level should be error or higher (current: {$logLevel})"
        );

        $logPath = storage_path('logs');
        $logPath = \storage_path('logs');
        $this->addResult(
            'Logging',
            'Log Directory',
            File::isWritable($logPath),
            'HIGH',
            'Log directory should be writable'
        );
    }

    /**
     * Add a result to the audit
     */
    protected function addResult($category, $check, $passed, $severity, $message)
    {
        $this->results[] = [
            'category' => $category,
            'check' => $check,
            'passed' => $passed,
            'severity' => $severity,
            'message' => $message
        ];

        $icon = $passed ? '✅' : '❌';
        $color = $passed ? 'green' : ($severity === 'CRITICAL' ? 'red' : ($severity === 'HIGH' ? 'yellow' : 'blue'));

        $this->line("  {$icon} <fg={$color}>[{$severity}]</fg> {$check}: {$message}");
    }

    /**
     * Display audit results summary
     */
    protected function displayResults()
    {
        $this->newLine();
        $this->info('📊 Security Audit Summary');
        $this->line(str_repeat('=', 50));

        $total = count($this->results);
        $passed = count(array_filter($this->results, fn($r) => $r['passed']));
        $failed = $total - $passed;

        $critical = count(array_filter($this->results, fn($r) => !$r['passed'] && $r['severity'] === 'CRITICAL'));
        $high = count(array_filter($this->results, fn($r) => !$r['passed'] && $r['severity'] === 'HIGH'));
        $medium = count(array_filter($this->results, fn($r) => !$r['passed'] && $r['severity'] === 'MEDIUM'));

        $this->line("Total Checks: {$total}");
        $this->line("<fg=green>Passed: {$passed}</fg>");
        $this->line("<fg=red>Failed: {$failed}</fg>");
        $this->newLine();

        if ($failed > 0) {
            $this->line("Failed by Severity:");
            $this->line("<fg=red>Critical: {$critical}</fg>");
            $this->line("<fg=yellow>High: {$high}</fg>");
            $this->line("<fg=blue>Medium: {$medium}</fg>");
        }

        $this->newLine();

        if ($critical > 0) {
            $this->error('🚨 CRITICAL security issues found! Please address immediately.');
        } elseif ($high > 0) {
            $this->warn('⚠️  HIGH severity security issues found. Please review and fix.');
        } elseif ($medium > 0) {
            $this->info('ℹ️  Some security improvements recommended.');
        } else {
            $this->info('🎉 All security checks passed!');
        }

        $this->newLine();
        $this->line('Audit completed at: ' . now());
        $this->line('Audit completed at: ' . \now());
    }

    /**
     * Attempt to fix security issues automatically
     */
    protected function attemptFix($type, $key, $check = null)
    {
        if ($type === 'environment') {
            $this->warn("⚠️  Manual fix required for {$key}. Please update your .env file.");
        }
    }
}
