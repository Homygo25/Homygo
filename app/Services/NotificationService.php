<?php

namespace App\Services;

use App\Models\SavedSearch;
use App\Models\UserNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send email notification for saved search matches
     */
    public function sendSavedSearchEmail(SavedSearch $savedSearch, Collection $matches): void
    {
        try {
            // For now, log the notification - you can implement actual email sending later
            Log::info("Saved search email notification", [
                'user_id' => $savedSearch->user_id,
                'user_email' => $savedSearch->user->email,
                'search_name' => $savedSearch->name,
                'match_count' => $matches->count(),
                'matches' => $matches->pluck('title')->toArray()
            ]);

            // TODO: Implement actual email sending with Mail facade
            // Mail::to($savedSearch->user->email)->send(new SavedSearchMatchMail($savedSearch, $matches));

        } catch (\Exception $e) {
            Log::error('Failed to send saved search email notification', [
                'saved_search_id' => $savedSearch->id,
                'user_id' => $savedSearch->user_id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Send browser notification
     */
    public function sendBrowserNotification(UserNotification $notification): void
    {
        // This would typically integrate with a real-time notification service
        // like Pusher, Socket.io, or Firebase Cloud Messaging
        Log::info("Browser notification sent", [
            'user_id' => $notification->user_id,
            'title' => $notification->title,
            'message' => $notification->message
        ]);
    }

    /**
     * Create and send a notification
     */
    public function createNotification(array $data): UserNotification
    {
        $notification = UserNotification::create($data);

        // Send browser notification if applicable
        if ($notification->priority === 'high') {
            $this->sendBrowserNotification($notification);
        }

        return $notification;
    }
}
