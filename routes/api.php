<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\PropertyApiController;
use App\Http\Controllers\PropertyController as WebPropertyController;
use App\Http\Controllers\ImageUploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API routes
Route::prefix('v1')->group(function () {
    // Property search and listing (public)
    Route::controller(PropertyApiController::class)->group(function () {
        Route::get('/properties', 'index')->name('api.properties.index');
        Route::get('/properties/filters', 'filters')->name('api.properties.filters');
        Route::get('/properties/statistics', 'statistics')->name('api.properties.statistics');
        Route::get('/properties/autocomplete', 'autocomplete')->name('api.properties.autocomplete');
        Route::get('/properties/{property}', 'show')->name('api.properties.show');
    // Location suggestions for search bar autocomplete
    Route::get('/location-suggestions', 'locationSuggestions')->name('api.location.suggestions');
    });
});

// Authenticated API routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Property management
    Route::controller(WebPropertyController::class)->group(function () {
        Route::get('/my-properties', 'dashboard')->name('api.properties.dashboard');
        Route::post('/properties/bulk-action', 'bulkAction')->name('api.properties.bulk-action');
        Route::get('/properties/{property}/analytics', 'analytics')->name('api.properties.analytics');
    });

    // Image management
    Route::controller(ImageUploadController::class)->group(function () {
        Route::post('/upload/property-images', 'uploadPropertyImages')->name('api.upload.property-images');
        Route::post('/upload/single-image', 'uploadSingleImage')->name('api.upload.single-image');
        Route::delete('/upload/delete-image', 'deleteImage')->name('api.upload.delete-image');
        Route::post('/upload/reorder-images', 'reorderImages')->name('api.upload.reorder-images');
        Route::get('/upload/progress', 'getUploadProgress')->name('api.upload.progress');
    });
});

// Simple favorites route for frontend
Route::middleware('auth:sanctum')->post('/properties/{property}/favorite', function (\App\Models\Property $property, Request $request) {
    $user = $request->user();
    $favorite = \App\Models\Favorite::where('user_id', $user->id)
        ->where('favoritable_id', $property->id)
        ->where('favoritable_type', \App\Models\Property::class)
        ->first();

    if ($favorite) {
        $favorite->delete();
        return response()->json(['success' => true, 'favorited' => false]);
    } else {
        \App\Models\Favorite::create([
            'user_id' => $user->id,
            'favoritable_id' => $property->id,
            'favoritable_type' => \App\Models\Property::class
        ]);
        return response()->json(['success' => true, 'favorited' => true]);
    }
});

// Public API routes
Route::prefix('v1')->group(function () {
    // Properties API
    Route::controller(PropertyController::class)->group(function () {
        Route::get('/properties', 'index');
        Route::get('/properties/search', 'search');
        Route::get('/properties/featured', 'featured');
        Route::get('/properties/nearby', 'nearby');
        Route::get('/properties/{property}', 'show');
    });

    // Categories API
    Route::get('/categories', function () {
        return \App\Models\Category::with('children')
            ->whereNull('parent_id')
            ->get();
    });

    // Locations API
    Route::get('/locations', function () {
        return \App\Models\Location::with('children')
            ->whereNull('parent_id')
            ->get();
    });
});

// Authenticated API routes
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Properties management
    Route::controller(PropertyController::class)->group(function () {
        Route::post('/properties', 'store');
        Route::put('/properties/{property}', 'update');
        Route::delete('/properties/{property}', 'destroy');
    });

    // User favorites
    Route::post('/properties/{property}/favorite', function (\App\Models\Property $property, Request $request) {
        $user = $request->user();
        $favorite = $user->favorites()->where('favoritable_id', $property->id)
            ->where('favoritable_type', \App\Models\Property::class)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['favorited' => false]);
        } else {
            $user->favorites()->create([
                'favoritable_id' => $property->id,
                'favoritable_type' => \App\Models\Property::class
            ]);
            return response()->json(['favorited' => true]);
        }
    });

    // User reviews
    Route::post('/properties/{property}/review', function (\App\Models\Property $property, Request $request) {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000'
        ]);

        $review = $request->user()->reviews()->create([
            'reviewable_id' => $property->id,
            'reviewable_type' => \App\Models\Property::class,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return response()->json($review->load('user'), 201);
    });
});

// Notification API routes
use App\Http\Controllers\NotificationController;

Route::post('/newsletter/subscribe', [NotificationController::class, 'subscribeNewsletter'])->middleware('throttle:5,1');
Route::post('/newsletter/unsubscribe', [NotificationController::class, 'unsubscribeNewsletter'])->middleware('throttle:5,1');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('v1')->group(function () {
    Route::controller(NotificationController::class)->group(function () {
        Route::get('/notifications', 'index');
        Route::post('/notifications/{id}/read', 'markAsRead');
        Route::post('/notifications/read-all', 'markAllAsRead');
        Route::delete('/notifications/{id}', 'destroy');
        Route::put('/notifications/preferences', 'updatePreferences');
        Route::get('/notifications/statistics', 'getStatistics');
        Route::post('/notifications/test', 'sendTestNotification');

        // Property alerts API
        Route::post('/property-alerts', 'createPropertyAlert');
        Route::get('/property-alerts', 'getAlerts');
        Route::delete('/property-alerts/{id}', 'deleteAlert');
    });
});
