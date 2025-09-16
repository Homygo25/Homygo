<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'description', 'type', 'property_type', 'price', 'currency',
        'area', 'area_unit', 'bedrooms', 'bathrooms', 'parking_spaces', 'year_built',
        'address', 'city', 'state', 'postal_code', 'country', 'latitude', 'longitude',
        'user_id', 'category_id', 'location_id', 'status', 'is_featured', 'is_verified',
        'features', 'amenities', 'contact_name', 'contact_phone', 'contact_email',
        'meta_title', 'meta_description', 'meta_keywords', 'featured_until', 'published_at',
        'views'
    ];

    protected $casts = [
        'features' => 'array',
        'amenities' => 'array',
        'meta_keywords' => 'array',
        'featured_until' => 'datetime',
        'published_at' => 'datetime',
        'price' => 'decimal:2',
        'area' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_featured' => 'boolean',
        'is_verified' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(PropertyImage::class)->where('is_primary', true);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    // Scopes
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublished(Builder $query)
    {
        return $query->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query)
    {
        return $query->where('is_featured', true)
                    ->where(function($q) {
                        $q->whereNull('featured_until')
                          ->orWhere('featured_until', '>', now());
                    });
    }

    public function scopeForSale(Builder $query)
    {
        return $query->where('type', 'sale');
    }

    public function scopeForRent(Builder $query)
    {
        return $query->where('type', 'rent');
    }

    public function scopeInPriceRange(Builder $query, $min = null, $max = null)
    {
        if ($min) {
            $query->where('price', '>=', $min);
        }
        if ($max) {
            $query->where('price', '<=', $max);
        }
        return $query;
    }

    public function scopeWithBedrooms(Builder $query, $bedrooms)
    {
        return $query->where('bedrooms', '>=', $bedrooms);
    }

    public function scopeWithBathrooms(Builder $query, $bathrooms)
    {
        return $query->where('bathrooms', '>=', $bathrooms);
    }

    public function scopeByPropertyType(Builder $query, $types)
    {
        if (is_array($types)) {
            return $query->whereIn('property_type', $types);
        }
        return $query->where('property_type', $types);
    }

    public function scopeNearLocation(Builder $query, $lat, $lng, $radius = 10)
    {
        return $query->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->selectRaw('*, ( 3959 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance', [$lat, $lng, $lat])
                    ->having('distance', '<', $radius)
                    ->orderBy('distance');
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 0);
    }

    public function getFormattedAreaAttribute()
    {
        return number_format($this->area, 0) . ' ' . $this->area_unit;
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ]);
        return implode(', ', $parts);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->where('is_approved', true)->avg('rating') ?? 0;
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->where('is_approved', true)->count();
    }

    public function getIsFavoritedByUserAttribute()
    {
        if (!Auth::check()) {
            return false;
        }
        return $this->favorites()->where('user_id', Auth::id())->exists();
    }

    // Methods
    public function markAsFeatured($until = null)
    {
        $this->update([
            'is_featured' => true,
            'featured_until' => $until
        ]);
    }

    public function removeFeatured()
    {
        $this->update([
            'is_featured' => false,
            'featured_until' => null
        ]);
    }

    public function isAvailable()
    {
        return in_array($this->status, ['active']);
    }

    public function isSold()
    {
        return in_array($this->status, ['sold', 'rented']);
    }
}
