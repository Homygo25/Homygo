<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SavedSearch;
use App\Models\Property;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Date;

class ProcessSavedSearchNotifications extends Command
{
    protected $signature = 'homygo:process-saved-searches {--frequency=all : Process specific frequency (instant|daily|weekly|all)}';
    protected $description = 'Process saved search notifications and send alerts for new matching properties';

    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $frequency = $this->option('frequency');
        
        $this->info("Processing saved search notifications for frequency: {$frequency}");
        
        $processedCount = 0;
        $notificationsSent = 0;
        
        // Get saved searches that need processing
        $savedSearches = $this->getSavedSearchesToProcess($frequency);
        
        if ($savedSearches->isEmpty()) {
            $this->info('No saved searches to process.');
            return Command::SUCCESS;
        }
        
        $this->info("Found {$savedSearches->count()} saved searches to process.");
        
        foreach ($savedSearches as $savedSearch) {
            try {
                $this->line("Processing: {$savedSearch->name} (User: {$savedSearch->user->email})");
                
                // Find new properties matching the search criteria
                $newMatches = $this->findNewMatches($savedSearch);
                
                if ($newMatches->count() > 0) {
                    $this->info("  Found {$newMatches->count()} new matches");
                    
                    // Send notification
                    $this->sendSearchNotification($savedSearch, $newMatches);
                    $notificationsSent++;
                    
                    // Update search metadata
                    $savedSearch->update([
                        'last_notification_sent' => now(),
                        'last_run' => now(),
                        'total_matches' => $savedSearch->total_matches + $newMatches->count(),
                        'new_matches' => $newMatches->count(),
                    ]);
                } else {
                    $this->line('  No new matches found');
                    $savedSearch->update(['last_run' => now()]);
                }
                
                $processedCount++;
                
            } catch (\Exception $e) {
                $this->error("Failed to process saved search {$savedSearch->id}: {$e->getMessage()}");
                continue;
            }
        }
        
        $this->info("Processing complete!");
        $this->info("Processed: {$processedCount} searches");
        $this->info("Notifications sent: {$notificationsSent}");
        
        return Command::SUCCESS;
    }

    private function getSavedSearchesToProcess(string $frequency): Collection
    {
        $query = SavedSearch::with('user')
            ->where('notifications_enabled', true)
            ->whereHas('user', function ($q) {
                $q->whereNotNull('email_verified_at');
            });
            
        switch ($frequency) {
            case 'instant':
                return $query->where('email_frequency', 'instant')
                    ->where(function ($q) {
                        $q->whereNull('last_notification_sent')
                            ->orWhere('last_notification_sent', '<', now()->subMinutes(30));
                    })
                    ->get();
                    
            case 'daily':
                return $query->where('email_frequency', 'daily')
                    ->where(function ($q) {
                        $q->whereNull('last_notification_sent')
                            ->orWhere('last_notification_sent', '<', now()->subDay());
                    })
                    ->get();
                    
            case 'weekly':
                return $query->where('email_frequency', 'weekly')
                    ->where(function ($q) {
                        $q->whereNull('last_notification_sent')
                            ->orWhere('last_notification_sent', '<', now()->subWeek());
                    })
                    ->get();
                    
            default: // 'all'
                return $query->where(function ($q) {
                    $q->where(function ($subQ) {
                        // Instant - every 30 minutes
                        $subQ->where('email_frequency', 'instant')
                            ->where(function ($timeQ) {
                                $timeQ->whereNull('last_notification_sent')
                                    ->orWhere('last_notification_sent', '<', now()->subMinutes(30));
                            });
                    })
                    ->orWhere(function ($subQ) {
                        // Daily - once per day
                        $subQ->where('email_frequency', 'daily')
                            ->where(function ($timeQ) {
                                $timeQ->whereNull('last_notification_sent')
                                    ->orWhere('last_notification_sent', '<', now()->subDay());
                            });
                    })
                    ->orWhere(function ($subQ) {
                        // Weekly - once per week
                        $subQ->where('email_frequency', 'weekly')
                            ->where(function ($timeQ) {
                                $timeQ->whereNull('last_notification_sent')
                                    ->orWhere('last_notification_sent', '<', now()->subWeek());
                            });
                    });
                })
                ->get();
        }
    }

    private function findNewMatches(SavedSearch $savedSearch): Collection
    {
        $criteria = is_array($savedSearch->criteria) ? $savedSearch->criteria : json_decode($savedSearch->criteria, true);
        $lastNotification = $savedSearch->last_notification_sent ?? $savedSearch->created_at;
        
        $query = Property::active()
            ->where('created_at', '>', $lastNotification)
            ->with(['images', 'location']);
            
        // Apply search criteria
        if (!empty($criteria['location'])) {
            $query->whereHas('location', function ($q) use ($criteria) {
                $q->where('name', 'like', '%' . $criteria['location'] . '%');
            });
        }
        
        if (!empty($criteria['property_type'])) {
            $query->where('property_type', $criteria['property_type']);
        }
        
        if (!empty($criteria['min_price'])) {
            $query->where('price', '>=', $criteria['min_price']);
        }
        
        if (!empty($criteria['max_price'])) {
            $query->where('price', '<=', $criteria['max_price']);
        }
        
        if (!empty($criteria['bedrooms'])) {
            $query->where('bedrooms', '>=', $criteria['bedrooms']);
        }
        
        if (!empty($criteria['bathrooms'])) {
            $query->where('bathrooms', '>=', $criteria['bathrooms']);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    private function sendSearchNotification(SavedSearch $savedSearch, $matches): void
    {
        // Create in-app notification
        UserNotification::create([
            'user_id' => $savedSearch->user_id,
            'type' => 'saved_search_alert',
            'title' => "New Properties Match Your Search: {$savedSearch->name}",
            'message' => "We found {$matches->count()} new properties that match your saved search criteria in Cagayan de Oro.",
            'data' => [
                'saved_search_id' => $savedSearch->id,
                'match_count' => $matches->count(),
                'matches' => $matches->take(3)->map(function ($property) {
                    return [
                        'id' => $property->id,
                        'title' => $property->title,
                        'price' => $property->price,
                        'location' => $property->location->name ?? 'CDO',
                    ];
                }),
            ],
            'priority' => $matches->count() >= 5 ? 'high' : 'medium',
            'action_url' => route('dashboard.saved-searches.matches', ['saved_search' => $savedSearch->id]),
            'action_text' => 'View Matches',
        ]);
        
        // Send email notification
        try {
            $this->notificationService->sendSavedSearchEmail($savedSearch, $matches);
            $this->line("  Email sent to {$savedSearch->user->email}");
        } catch (\Exception $e) {
            $this->error("  Failed to send email: {$e->getMessage()}");
        }
    }
}
