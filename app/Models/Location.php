<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'type', 'parent_id', 'latitude', 'longitude',
        'country_code', 'state_code', 'postal_code', 'description',
        'is_active', 'sort_order', 'bounds'
    ];

    protected $casts = [
        'bounds' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id')->orderBy('sort_order');
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

    public function userProfiles()
    {
        return $this->hasMany(UserProfile::class);
    }

    // Scopes
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCountries(Builder $query)
    {
        return $query->where('type', 'country');
    }

    public function scopeStates(Builder $query)
    {
        return $query->where('type', 'state');
    }

    public function scopeCities(Builder $query)
    {
        return $query->where('type', 'city');
    }

    public function scopeByCountry(Builder $query, $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    public function scopeByState(Builder $query, $stateCode)
    {
        return $query->where('state_code', $stateCode);
    }

    // Methods
    public function getFullNameAttribute()
    {
        $parts = [];
        
        if ($this->type === 'city') {
            $parts[] = $this->name;
            if ($this->parent && $this->parent->type === 'state') {
                $parts[] = $this->parent->name;
                if ($this->parent->parent && $this->parent->parent->type === 'country') {
                    $parts[] = $this->parent->parent->name;
                }
            }
        } elseif ($this->type === 'state') {
            $parts[] = $this->name;
            if ($this->parent && $this->parent->type === 'country') {
                $parts[] = $this->parent->name;
            }
        } else {
            $parts[] = $this->name;
        }
        
        return implode(', ', $parts);
    }

    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }
}
