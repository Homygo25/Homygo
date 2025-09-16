<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['subscribeNewsletter']);
    }

    /**
     * Display notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get user's notification preferences from cache or database
        $preferences = Cache::remember("user_notifications_{$user->id}", 3600, function () use ($user) {
            return $user->notification_preferences ?? [
                'property_updates' => true,
                'price_alerts' => true,
                'favorites_updates' => true,
                'newsletter' => true,
                'email' => true,
                'push' => false,
                'sms' => false
            ];
        });

        // Mock notifications for demonstration
        $notifications = [
            [
                'id' => 1,
                'type' => 'property_update',
                'title' => 'New Property Match',
                'message' => 'A new property matching your criteria has been listed in Downtown.',
                'data' => ['property_id' => 1, 'location' => 'Downtown'],
                'created_at' => now()->subHours(2),
                'read_at' => null,
                'priority' => 'high'
            ],
            [
                'id' => 2,
                'type' => 'price_alert',
                'title' => 'Price Drop Alert',
                'message' => 'A property in your favorites has reduced its price by 15%.',
                'data' => ['property_id' => 2, 'old_price' => 450000, 'new_price' => 382500],
                'created_at' => now()->subHours(6),
                'read_at' => null,
                'priority' => 'medium'
            ],
            [
                'id' => 3,
                'type' => 'favorites_update',
                'title' => 'Favorite Property Update',
                'message' => 'One of your favorite properties has new photos.',
                'data' => ['property_id' => 3, 'update_type' => 'photos'],
                'created_at' => now()->subDay(),
                'read_at' => now()->subHours(12),
                'priority' => 'low'
            ]
        ];

        // Filter by type if requested
        if ($request->has('type') && $request->type !== 'all') {
            $notifications = array_filter($notifications, function ($notification) use ($request) {
                return $notification['type'] === $request->type;
            });
        }

        // Filter by read status
        if ($request->has('read')) {
            $isRead = $request->read === 'true';
            $notifications = array_filter($notifications, function ($notification) use ($isRead) {
                return $isRead ? $notification['read_at'] !== null : $notification['read_at'] === null;
            });
        }

        // Sort by priority and date
        usort($notifications, function ($a, $b) {
            $priorities = ['high' => 3, 'medium' => 2, 'low' => 1];
            $priorityDiff = $priorities[$b['priority']] - $priorities[$a['priority']];
            
            if ($priorityDiff === 0) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            }
            
            return $priorityDiff;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'preferences' => $preferences,
                'unread_count' => count(array_filter($notifications, fn($n) => $n['read_at'] === null))
            ]);
        }

        return view('notifications.index', compact('notifications', 'preferences'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        // In a real application, this would update the database
        // For now, we'll just return success response
        
        Session::flash('success', 'Notification marked as read.');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        }

        return redirect()->back();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        
        // In a real application, this would update all unread notifications
        // For now, we'll just return success response
        
        Session::flash('success', 'All notifications marked as read.');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        }

        return redirect()->back();
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, $id)
    {
        // In a real application, this would delete the notification from database
        
        Session::flash('success', 'Notification deleted.');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
        }

        return redirect()->back();
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_updates' => 'boolean',
            'price_alerts' => 'boolean',
            'favorites_updates' => 'boolean',
            'newsletter' => 'boolean',
            'email' => 'boolean',
            'push' => 'boolean',
            'sms' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $preferences = $request->only([
            'property_updates', 'price_alerts', 'favorites_updates', 
            'newsletter', 'email', 'push', 'sms'
        ]);

        // Convert string values to boolean
        foreach ($preferences as $key => $value) {
            $preferences[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        // In a real application, save to database
        // $user->update(['notification_preferences' => $preferences]);
        
        // Clear cache
        Cache::forget("user_notifications_{$user->id}");
        
        Session::flash('success', 'Notification preferences updated successfully.');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Preferences updated successfully',
                'preferences' => $preferences
            ]);
        }

        return redirect()->back();
    }

    /**
     * Create property alert
     */
    public function createPropertyAlert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'nullable|string|max:255',
            'property_type' => 'nullable|string|max:100',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'min_bedrooms' => 'nullable|integer|min:0',
            'max_bedrooms' => 'nullable|integer|min:0',
            'keywords' => 'nullable|string|max:500',
            'alert_frequency' => 'required|in:immediate,daily,weekly'
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $alertData = $request->only([
            'location', 'property_type', 'min_price', 'max_price',
            'min_bedrooms', 'max_bedrooms', 'keywords', 'alert_frequency'
        ]);

        // In a real application, save alert to database
        $alertId = rand(1000, 9999); // Mock alert ID

        Session::flash('success', 'Property alert created successfully. You will receive notifications when matching properties are found.');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Property alert created successfully',
                'alert_id' => $alertId,
                'alert_data' => $alertData
            ]);
        }

        return redirect()->back();
    }

    /**
     * Get user's property alerts
     */
    public function getAlerts(Request $request)
    {
        $user = Auth::user();
        
        // Mock alerts for demonstration
        $alerts = [
            [
                'id' => 1,
                'location' => 'Downtown',
                'property_type' => 'apartment',
                'min_price' => 200000,
                'max_price' => 500000,
                'min_bedrooms' => 2,
                'max_bedrooms' => null,
                'keywords' => 'modern, parking',
                'alert_frequency' => 'immediate',
                'created_at' => now()->subDays(7),
                'is_active' => true,
                'matches_count' => 3
            ],
            [
                'id' => 2,
                'location' => 'Suburbs',
                'property_type' => 'house',
                'min_price' => 300000,
                'max_price' => 800000,
                'min_bedrooms' => 3,
                'max_bedrooms' => 5,
                'keywords' => 'garden, garage',
                'alert_frequency' => 'weekly',
                'created_at' => now()->subDays(14),
                'is_active' => true,
                'matches_count' => 1
            ]
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'alerts' => $alerts
            ]);
        }

        return view('notifications.alerts', compact('alerts'));
    }

    /**
     * Delete property alert
     */
    public function deleteAlert(Request $request, $id)
    {
        // In a real application, delete alert from database
        
        Session::flash('success', 'Property alert deleted successfully.');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alert deleted successfully'
            ]);
        }

        return redirect()->back();
    }

    /**
     * Subscribe to newsletter (public endpoint)
     */
    public function subscribeNewsletter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
            'interests' => 'nullable|array',
            'interests.*' => 'string|in:properties,market_updates,tips,news'
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $email = $request->email;
        $name = $request->name ?? '';
        $interests = $request->interests ?? ['properties', 'market_updates'];

        // Check if already subscribed (mock check)
        $existingSubscription = Cache::get("newsletter_subscription_{$email}");
        
        if ($existingSubscription) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already subscribed to our newsletter.'
                ], 409);
            }
            
            return redirect()->back()->with('error', 'This email is already subscribed to our newsletter.');
        }

        // Save subscription (mock save)
        Cache::put("newsletter_subscription_{$email}", [
            'email' => $email,
            'name' => $name,
            'interests' => $interests,
            'subscribed_at' => now(),
            'is_active' => true
        ], 86400 * 365); // Cache for 1 year

        // Send welcome email (mock)
        try {
            // Mail::send('emails.newsletter-welcome', compact('name', 'email'), function ($message) use ($email, $name) {
            //     $message->to($email, $name)->subject('Welcome to Finder Newsletter!');
            // });
        } catch (\Exception $e) {
            // Log error but don't fail the subscription
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully subscribed to newsletter! Welcome email sent.',
                'subscription' => [
                    'email' => $email,
                    'interests' => $interests
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Successfully subscribed to newsletter! Welcome email sent.');
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribeNewsletter(Request $request)
    {
        $email = $request->input('email');
        
        if (!$email) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address is required.'
                ], 400);
            }
            
            return redirect()->back()->with('error', 'Email address is required.');
        }

        // Remove subscription
        Cache::forget("newsletter_subscription_{$email}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully unsubscribed from newsletter.'
            ]);
        }

        return redirect()->back()->with('success', 'Successfully unsubscribed from newsletter.');
    }

    /**
     * Get notification statistics
     */
    public function getStatistics(Request $request)
    {
        $user = Auth::user();
        
        // Mock statistics
        $stats = [
            'total_notifications' => 15,
            'unread_notifications' => 3,
            'notifications_this_week' => 8,
            'active_alerts' => 2,
            'newsletter_subscribed' => true,
            'most_frequent_type' => 'property_updates',
            'notification_types' => [
                'property_updates' => 6,
                'price_alerts' => 4,
                'favorites_updates' => 3,
                'system_updates' => 2
            ],
            'preferences' => [
                'email' => true,
                'push' => false,
                'sms' => false
            ]
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);
        }

        return view('notifications.statistics', compact('stats'));
    }

    /**
     * Send test notification
     */
    public function sendTestNotification(Request $request)
    {
        $user = Auth::user();
        
        // Mock sending test notification
        $testNotification = [
            'id' => 9999,
            'type' => 'test',
            'title' => 'Test Notification',
            'message' => 'This is a test notification to verify your notification settings are working correctly.',
            'data' => ['test' => true],
            'created_at' => now(),
            'read_at' => null,
            'priority' => 'medium'
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully',
                'notification' => $testNotification
            ]);
        }

        return redirect()->back()->with('success', 'Test notification sent successfully! Check your notifications.');
    }
}