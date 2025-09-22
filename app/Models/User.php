<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\UserProfile;
use App\Models\Property;
use App\Models\Car;
use App\Models\Contractor;
use App\Models\Review;
use App\Models\Favorite;
use App\Models\SearchFilter;
use App\Models\SavedSearch;
use App\Models\UserNotification;
use App\Models\PropertyView;
use App\Models\PropertyInquiry;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use function collect;
use function asset;
use function route;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function userProfile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    public function contractors()
    {
        return $this->hasMany(Contractor::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteProperties()
    {
        return $this->belongsToMany(Property::class, 'property_favorites', 'user_id', 'property_id')
                    ->withTimestamps();
    }

    public function searchFilters()
    {
        return $this->hasMany(SearchFilter::class);
    }

    // Helper methods
    public function getFullNameAttribute()
    {
        return $this->userProfile ?
            $this->userProfile->first_name . ' ' . $this->userProfile->last_name :
            $this->name;
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->userProfile && $this->userProfile->avatar) {
            return asset('storage/' . $this->userProfile->avatar);
        }

        // Default avatar
        return asset('build/img/avatars/default.jpg');
    }

    public function hasRole($role)
    {
        return $this->userProfile && $this->userProfile->role === $role;
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isAgent()
    {
        return $this->hasRole('agent');
    }

    public function isSeller()
    {
        return $this->hasRole('seller');
    }
    // New relationships for Phase 6
    public function savedSearches()
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class)->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->hasMany(UserNotification::class)->whereNull('read_at')->orderBy('created_at', 'desc');
    }

    public function propertyViews()
    {
        return $this->hasMany(PropertyView::class);
    }

    public function propertyInquiries()
    {
        return $this->hasMany(PropertyInquiry::class);
    }

    // Dashboard analytics methods
    public function getUnreadNotificationsCountAttribute()
    {
        return $this->unreadNotifications()->count();
    }

    public function getActiveSavedSearchesCountAttribute()
    {
        return $this->savedSearches()->active()->count();
    }

    public function getFavoritePropertiesCountAttribute()
    {
        return $this->favorites()->count();
    }

    public function getMyPropertiesCountAttribute()
    {
        return $this->properties()->count();
    }

    public function getTotalPropertyViewsAttribute()
    {
        return $this->properties()->sum('views');
    }

    public function getTotalInquiriesReceivedAttribute()
    {
        return PropertyInquiry::whereHas('property', function ($query) {
            $query->where('user_id', $this->id);
        })->count();
    }

    public function getRecentActivityAttribute()
    {
        $activities = collect();

        // Recent property views
        $recentViews = $this->propertyViews()
            ->with('property')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($view) {
                return [
                    'type' => 'view',
                    'message' => "Viewed {$view->property->title}",
                    'created_at' => $view->viewed_at,
                    'url' => route('properties.show', $view->property),
                ];
            });

        // Recent inquiries sent
        $recentInquiries = $this->propertyInquiries()
            ->with('property')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($inquiry) {
                return [
                    'type' => 'inquiry',
                    'message' => "Inquired about {$inquiry->property->title}",
                    'created_at' => $inquiry->created_at,
                    'url' => route('properties.show', $inquiry->property),
                ];
            });

        return $activities->merge($recentViews)
                         ->merge($recentInquiries)
                         ->sortByDesc('created_at')
                         ->take(10);
    }
}
