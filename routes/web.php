<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyApiController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\SocialSharingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserDashboardController;
// Disabled for Real Estate focus only
// use App\Http\Controllers\CarController;
// use App\Http\Controllers\ContractorController;

// Home route - Redirect to Real Estate
Route::get('/', function () {
    return view('home-real-estate');
})->name('home');

// Real Estate specific routes
Route::get('/real-estate', function () {
    return view('home-real-estate');
})->name('real-estate.home');

// Authentication routes
Route::controller(AuthController::class)->group(function () {
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', 'showLogin')->name('login');
        Route::post('/login', 'login')->name('auth.login')
            ->middleware('throttle:5,1'); // 5 attempts per minute
        Route::get('/register', 'showRegister')->name('register');
        Route::post('/register', 'register')->name('auth.register')
            ->middleware('throttle:3,1'); // 3 attempts per minute
        
        // Password Reset Routes  
        Route::get('/forgot-password', 'showPasswordReset')->name('password.request');
        Route::post('/forgot-password', 'sendPasswordResetLink')->name('password.email')
            ->middleware('throttle:3,1'); // 3 attempts per minute
        Route::get('/reset-password/{token}', 'showResetForm')->name('password.reset');
        Route::post('/reset-password', 'resetPassword')->name('password.store')
            ->middleware('throttle:3,1'); // 3 attempts per minute
    });
    
    // Authenticated routes
    Route::middleware('auth')->group(function () {
        Route::post('/logout', 'logout')->name('logout');
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        
        // Email Verification Routes
        Route::get('/verify-email', 'showVerifyEmailForm')->name('verification.notice');
        Route::get('/verify-email/{id}/{hash}', 'verifyEmail')
            ->middleware('signed')
            ->name('verification.verify');
        Route::post('/email/verification-notification', 'resendVerification')
            ->middleware('throttle:6,1')
            ->name('verification.send');
        
        // Password Confirmation
        Route::get('/confirm-password', 'showConfirmPasswordForm')->name('password.confirm');
        Route::post('/confirm-password', 'confirmPassword');
        
        // Profile Management
        Route::get('/profile', 'profile')->name('profile.edit');
        Route::patch('/profile', 'updateProfile')->name('profile.update');
        Route::delete('/profile', 'deleteProfile')->name('profile.destroy');
        
        // Account Management
        Route::get('/my-listings', 'listings')->name('auth.listings');
        Route::get('/favorites', 'favorites')->name('auth.favorites');
        Route::get('/settings', 'settings')->name('auth.settings');
        Route::put('/password', 'updatePassword')->name('auth.password.update');
    });
});

// Property routes
Route::controller(PropertyController::class)->group(function () {
    Route::get('/properties', 'index')->name('properties.index');
    Route::get('/properties/search', 'search')->middleware('rate.limit:search')->name('properties.search');
    Route::get('/properties/{property}', 'show')->name('properties.show');
    Route::post('/properties/{property}/contact', 'contact')->middleware('rate.limit:contact')->name('properties.contact');
    
    // Authenticated property routes
    Route::middleware('auth')->group(function () {
        Route::get('/add-property', 'create')->name('properties.create');
        Route::post('/properties', 'store')->name('properties.store');
        Route::get('/properties/{property}/edit', 'edit')->name('properties.edit');
        Route::put('/properties/{property}', 'update')->name('properties.update');
        Route::delete('/properties/{property}', 'destroy')->name('properties.destroy');
        Route::put('/properties/{property}/feature', 'feature')->name('properties.feature');
        
        // Property dashboard and bulk operations
        Route::get('/properties/dashboard', 'dashboard')->name('properties.dashboard');
        Route::post('/properties/bulk-action', 'bulkAction')->name('properties.bulk-action');
        Route::get('/properties/{property}/analytics', 'analytics')->name('properties.analytics');
    });
});

// User Dashboard routes (Phase 6: Advanced User Experience & Analytics)
Route::middleware('auth')->controller(UserDashboardController::class)->prefix('dashboard')->group(function () {
    // Main dashboard
    Route::get('/', 'index')->name('dashboard.index');
    Route::get('/api/data', 'getDashboardStats')->name('dashboard.api.data');
    
    // Property Analytics
    Route::get('/property-analytics/{property}', 'propertyAnalytics')->name('dashboard.property-analytics');
    Route::get('/property-analytics/{property}/data', 'getPropertyAnalyticsData')->name('dashboard.property-analytics.data');
    Route::get('/property-analytics/{property}/report', 'downloadReport')->name('dashboard.property-analytics.report');
    
    // Notifications Management
    Route::get('/notifications', 'notifications')->name('dashboard.notifications');
    Route::post('/notifications/{notification}/read', 'markNotificationAsRead')->name('dashboard.notifications.read');
    Route::post('/notifications/mark-all-read', 'markAllNotificationsAsRead')->name('dashboard.notifications.mark-all-read');
    Route::get('/notifications/{notification}', 'getNotification')->name('dashboard.notifications.show');
    Route::delete('/notifications/{notification}', 'deleteNotification')->name('dashboard.notifications.delete');
    Route::get('/notifications/count', 'getNotificationCount')->name('dashboard.notifications.count');
    Route::post('/notification-preferences', 'updateNotificationPreferences')->name('dashboard.notification-preferences');
    
    // Saved Searches Management
    Route::get('/saved-searches', 'savedSearches')->name('dashboard.saved-searches');
    Route::get('/saved-searches/{savedSearch}', 'getSavedSearch')->name('dashboard.saved-searches.show');
    Route::put('/saved-searches/{savedSearch}', 'updateSavedSearch')->name('dashboard.saved-searches.update');
    Route::delete('/saved-searches/{savedSearch}', 'deleteSavedSearch')->name('dashboard.saved-searches.delete');
    Route::put('/saved-searches/{savedSearch}/notifications', 'toggleSavedSearchNotifications')->name('dashboard.saved-searches.notifications');
    Route::get('/saved-searches/{savedSearch}/matches', 'getSavedSearchMatches')->name('dashboard.saved-searches.matches');
    
    // Inquiry Management
    Route::get('/inquiries/{inquiry}', 'getInquiry')->name('dashboard.inquiries.show');
    Route::put('/inquiries/{inquiry}/status', 'updateInquiryStatus')->name('dashboard.inquiries.status');
});

// CDO-specific routes
Route::get('/cdo-properties', function () {
    $properties = collect(); // Will be populated from database
    return view('cdo-properties', compact('properties'));
})->name('cdo.properties');

Route::get('/cdo-property/{id}', function ($id) {
    // Mock property data for demonstration
    $property = (object)[
        'id' => $id,
        'title' => 'Beautiful Family Home in Cagayan de Oro',
        'price' => 2500000,
        'status' => 'for_sale',
        'property_type' => 'house',
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 120,
        'address' => 'Cogon, Cagayan de Oro City',
        'description' => 'This beautiful family home is located in the peaceful neighborhood of Cogon, Cagayan de Oro City. Perfect for families looking for a comfortable and secure living environment.',
        'images' => [],
        'features' => [
            'Air Conditioning',
            'Parking Space',
            'Security',
            'Water Supply',
            'Electricity',
            'Internet Ready'
        ],
        'contact_phone' => '+639123456789',
        'contact_email' => 'info@homygocdo.com'
    ];
    
    return view('property-detail-cdo', compact('property'));
})->name('cdo.property.show');

// CDO Platform Demo Route
Route::get('/cdo-demo', function () {
    return view('cdo-demo');
})->name('cdo.demo');

// Enhanced Image upload routes
Route::middleware('auth')->controller(ImageUploadController::class)->group(function () {
    Route::post('/upload/property-images', 'uploadPropertyImages')->name('upload.property-images');
    Route::post('/upload/single-image', 'uploadSingleImage')->name('upload.single-image');
    Route::delete('/upload/delete-image', 'deleteImage')->name('upload.delete-image');
    Route::post('/upload/reorder-images', 'reorderImages')->name('upload.reorder-images');
    Route::get('/upload/progress', 'getUploadProgress')->name('upload.progress');
    Route::post('/upload/cleanup-temp', 'cleanupTempImages')->name('upload.cleanup-temp');
});

// Legacy Image upload routes (keeping for compatibility)
Route::middleware('auth')->controller(ImageController::class)->group(function () {
    Route::post('/properties/{property}/images', 'upload')->name('properties.images.upload');
    Route::delete('/images/{image}', 'delete')->name('images.delete');
    Route::put('/images/{image}/primary', 'setPrimary')->name('images.setPrimary');
    Route::get('/properties/{property}/images', 'getImages')->name('properties.images.get');
});

// Favorites routes
Route::middleware('auth')->controller(FavoritesController::class)->group(function () {
    Route::get('/favorites', 'index')->name('favorites.index');
    Route::post('/favorites', 'store')->name('favorites.store');
    Route::delete('/favorites', 'destroy')->name('favorites.destroy');
    Route::post('/favorites/toggle', 'toggle')->name('favorites.toggle');
    Route::post('/favorites/check-multiple', 'checkMultiple')->name('favorites.check-multiple');
    Route::get('/favorites/statistics', 'statistics')->name('favorites.statistics');
    Route::get('/favorites/export', 'export')->name('favorites.export');
});

// Social Sharing routes
Route::controller(SocialSharingController::class)->group(function () {
    Route::get('/properties/{property}/share-links', 'getShareLinks')->name('properties.share-links');
    Route::post('/properties/{property}/share-email', 'shareViaEmail')->name('properties.share-email');
    Route::post('/properties/{property}/share-track', 'trackShare')->name('properties.share-track');
    Route::get('/properties/{property}/share-stats', 'getSharingStats')->name('properties.share-stats');
    Route::post('/properties/{property}/short-url', 'generateShortUrl')->name('properties.short-url');
    Route::get('/shared/{shortCode}', 'redirectShortUrl')->name('shared.redirect');
    Route::get('/popular-shared', 'getPopularSharedProperties')->name('properties.popular-shared');
});

// Notification routes
Route::controller(NotificationController::class)->group(function () {
    // Public newsletter subscription
    Route::post('/newsletter/subscribe', 'subscribeNewsletter')->name('newsletter.subscribe');
    Route::post('/newsletter/unsubscribe', 'unsubscribeNewsletter')->name('newsletter.unsubscribe');
    
    // Authenticated notification routes
    Route::middleware('auth')->group(function () {
        Route::get('/notifications', 'index')->name('notifications.index');
        Route::post('/notifications/{id}/read', 'markAsRead')->name('notifications.read');
        Route::post('/notifications/read-all', 'markAllAsRead')->name('notifications.read-all');
        Route::delete('/notifications/{id}', 'destroy')->name('notifications.destroy');
        Route::put('/notifications/preferences', 'updatePreferences')->name('notifications.preferences');
        Route::get('/notifications/statistics', 'getStatistics')->name('notifications.statistics');
        Route::post('/notifications/test', 'sendTestNotification')->name('notifications.test');
        
        // Property alerts
        Route::post('/property-alerts', 'createPropertyAlert')->name('property-alerts.create');
        Route::get('/property-alerts', 'getAlerts')->name('property-alerts.index');
        Route::delete('/property-alerts/{id}', 'deleteAlert')->name('property-alerts.destroy');
    });
});

// Static pages mapped to existing views
Route::get('/about', function () { return view('about-v1'); })->name('about');
Route::get('/contact', function () { return view('contact-v1'); })->name('contact');
Route::get('/blog', function () { return view('blog-layout-v1'); })->name('blog');

// Account pages (existing template views)
Route::middleware('auth')->group(function () {
    Route::get('/account/profile', function () { return view('account-profile'); })->name('account.profile');
    Route::get('/account/listings', function () { return view('account-listings'); })->name('account.listings');
    Route::get('/account/favorites', function () { return view('account-favorites'); })->name('account.favorites');
    Route::get('/account/reviews', function () { return view('account-reviews'); })->name('account.reviews');
    Route::get('/account/settings', function () { return view('account-settings'); })->name('account.settings');
    Route::get('/account/payment', function () { return view('account-payment'); })->name('account.payment');
});

// Add property pages (existing template views)
Route::middleware('auth')->group(function () {
    Route::get('/add-property/type', function () { return view('add-property-type'); })->name('add-property.type');
    Route::get('/add-property/location', function () { return view('add-property-location'); })->name('add-property.location');
    Route::get('/add-property/details', function () { return view('add-property-details'); })->name('add-property.details');
    Route::get('/add-property/photos', function () { return view('add-property-photos'); })->name('add-property.photos');
    Route::get('/add-property/price', function () { return view('add-property-price'); })->name('add-property.price');
    Route::get('/add-property/contact', function () { return view('add-property-contact-info'); })->name('add-property.contact');
    Route::get('/add-property/promotion', function () { return view('add-property-promotion'); })->name('add-property.promotion');
});

// Contractor and Car routes disabled - focusing on Real Estate only
/*
// Contractor add pages
Route::middleware('auth')->group(function () {
    Route::get('/add-contractor/location', function () { return view('add-contractor-location'); })->name('add-contractor.location');
    Route::get('/add-contractor/services', function () { return view('add-contractor-services'); })->name('add-contractor.services');
    Route::get('/add-contractor/profile', function () { return view('add-contractor-profile'); })->name('add-contractor.profile');
    Route::get('/add-contractor/pricing', function () { return view('add-contractor-price-hours'); })->name('add-contractor.pricing');
    Route::get('/add-contractor/project', function () { return view('add-contractor-project'); })->name('add-contractor.project');
});

// Car add page
Route::middleware('auth')->group(function () {
    Route::get('/add-car', function () { return view('add-car'); })->name('add-car');
});
*/

// Health check and monitoring routes
Route::controller(\App\Http\Controllers\HealthController::class)->group(function () {
    Route::get('/health', 'check')->name('health.check');
    Route::get('/health/status', 'status')->name('health.status');
    Route::get('/health/metrics', 'metrics')->name('health.metrics');
    Route::get('/ping', 'ping')->name('health.ping');
});

// Error pages
Route::get('/404', function () { return view('404-icon'); })->name('404');

// Fallback for dashboard controller
Route::get('{any}', [DashboardController::class, 'index'])->where('any', '.*')->name('fallback');
