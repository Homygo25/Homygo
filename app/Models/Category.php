<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'color', 'type',
        'parent_id', 'sort_order', 'is_active', 'meta_data'
    ];

    protected $casts = [
        'meta_data' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
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

    // Scopes
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMainCategories(Builder $query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSubCategories(Builder $query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeForType(Builder $query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function hasChildren()
    {
        return $this->children()->exists();
    }

    public function getDepthAttribute()
    {
        $depth = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        
        return $depth;
    }

    public function getIconUrlAttribute()
    {
        if ($this->icon) {
            return asset('storage/' . $this->icon);
        }
        return asset('build/img/icons/default-category.svg');
    }

    public function getFullNameAttribute()
    {
        if ($this->parent) {
            return $this->parent->name . ' > ' . $this->name;
        }
        return $this->name;
    }
}
