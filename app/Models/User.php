<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    public function isContractor()
    {
        return $this->hasRole('contractor');
    }
}
