<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FavoritesController extends Controller
{
    /**
     * Get user's favorite properties
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        $favorites = $user->favoriteProperties()
            ->with(['category', 'location'])
            ->orderBy('pivot.created_at', 'desc')
            ->get();
        
        $transformedFavorites = $favorites->map(function ($property) {
            return [
                'id' => $property->id,
                'title' => $property->title,
                'price' => $property->price,
                'formatted_price' => '$' . number_format($property->price),
                'address' => $property->address,
                'bedrooms' => $property->bedrooms,
                'bathrooms' => $property->bathrooms,
                'property_type' => $property->property_type,
                'status' => $property->status,
                'category' => $property->category->name ?? null,
                'location' => $property->location->name ?? null,
                'images' => $this->getPropertyImages($property),
                'favorited_at' => $property->pivot->created_at,
                'url' => route('properties.show', $property)
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'favorites' => $transformedFavorites,
                'count' => $transformedFavorites->count()
            ]
        ]);
    }
    
    /**
     * Add property to favorites
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $propertyId = $request->property_id;
        
        // Check if already favorited
        if ($user->favoriteProperties()->where('property_id', $propertyId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Property is already in favorites'
            ], 409);
        }
        
        // Add to favorites
        $user->favoriteProperties()->attach($propertyId, [
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Get property details for response
        $property = Property::with(['category', 'location'])->find($propertyId);
        
        return response()->json([
            'success' => true,
            'message' => 'Property added to favorites',
            'data' => [
                'property' => [
                    'id' => $property->id,
                    'title' => $property->title,
                    'price' => $property->price,
                    'formatted_price' => '$' . number_format($property->price),
                    'images' => $this->getPropertyImages($property)
                ],
                'favorites_count' => $user->favoriteProperties()->count()
            ]
        ]);
    }
    
    /**
     * Remove property from favorites
     */
    public function destroy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $propertyId = $request->property_id;
        
        // Check if in favorites
        if (!$user->favoriteProperties()->where('property_id', $propertyId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Property is not in favorites'
            ], 404);
        }
        
        // Remove from favorites
        $user->favoriteProperties()->detach($propertyId);
        
        return response()->json([
            'success' => true,
            'message' => 'Property removed from favorites',
            'data' => [
                'favorites_count' => $user->favoriteProperties()->count()
            ]
        ]);
    }
    
    /**
     * Toggle favorite status
     */
    public function toggle(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $propertyId = $request->property_id;
        
        $isFavorited = $user->favoriteProperties()->where('property_id', $propertyId)->exists();
        
        if ($isFavorited) {
            // Remove from favorites
            $user->favoriteProperties()->detach($propertyId);
            $action = 'removed';
            $message = 'Property removed from favorites';
        } else {
            // Add to favorites
            $user->favoriteProperties()->attach($propertyId, [
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $action = 'added';
            $message = 'Property added to favorites';
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'action' => $action,
                'is_favorited' => !$isFavorited,
                'favorites_count' => $user->favoriteProperties()->count()
            ]
        ]);
    }
    
    /**
     * Get favorite status for multiple properties
     */
    public function checkMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_ids' => 'required|array',
            'property_ids.*' => 'exists:properties,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $propertyIds = $request->property_ids;
        
        $favoriteIds = $user->favoriteProperties()
            ->whereIn('property_id', $propertyIds)
            ->pluck('property_id')
            ->toArray();
        
        $results = [];
        foreach ($propertyIds as $propertyId) {
            $results[$propertyId] = in_array($propertyId, $favoriteIds);
        }
        
        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }
    
    /**
     * Get user's favorites statistics
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();
        
        $favorites = $user->favoriteProperties();
        
        $stats = [
            'total_favorites' => $favorites->count(),
            'total_value' => $favorites->sum('price'),
            'average_price' => $favorites->avg('price'),
            'by_type' => $favorites->groupBy('property_type')
                ->selectRaw('property_type, count(*) as count')
                ->pluck('count', 'property_type'),
            'by_price_range' => [
                'under_100k' => $favorites->where('price', '<', 100000)->count(),
                '100k_500k' => $favorites->whereBetween('price', [100000, 500000])->count(),
                '500k_1m' => $favorites->whereBetween('price', [500000, 1000000])->count(),
                'over_1m' => $favorites->where('price', '>', 1000000)->count(),
            ],
            'recent_favorites' => $favorites->orderBy('pivot.created_at', 'desc')
                ->limit(5)
                ->get(['id', 'title', 'price', 'images'])
                ->map(function ($property) {
                    return [
                        'id' => $property->id,
                        'title' => $property->title,
                        'price' => $property->price,
                        'formatted_price' => '$' . number_format($property->price),
                        'image' => $this->getPropertyImages($property)[0] ?? null
                    ];
                })
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Export favorites list
     */
    public function export(): JsonResponse
    {
        $user = Auth::user();
        
        $favorites = $user->favoriteProperties()
            ->with(['category', 'location'])
            ->orderBy('pivot.created_at', 'desc')
            ->get();
        
        $exportData = $favorites->map(function ($property) {
            return [
                'Property ID' => $property->id,
                'Title' => $property->title,
                'Price' => '$' . number_format($property->price),
                'Address' => $property->address,
                'Type' => ucfirst($property->property_type),
                'Bedrooms' => $property->bedrooms,
                'Bathrooms' => $property->bathrooms,
                'Category' => $property->category->name ?? '',
                'Location' => $property->location->name ?? '',
                'Status' => ucfirst($property->status),
                'Added to Favorites' => $property->pivot->created_at->format('Y-m-d H:i:s'),
                'Property URL' => route('properties.show', $property)
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'favorites' => $exportData,
                'export_info' => [
                    'total_count' => $exportData->count(),
                    'exported_at' => now()->toISOString(),
                    'user' => $user->name
                ]
            ],
            'meta' => [
                'filename' => 'favorites_export_' . now()->format('Y_m_d_H_i_s') . '.json'
            ]
        ]);
    }
    
    private function getPropertyImages($property)
    {
        if (!$property->images) {
            return [];
        }
        
        $images = json_decode($property->images, true) ?? [];
        
        return array_map(function ($image) {
            return [
                'url' => asset('storage/' . $image['path']),
                'thumbnail_url' => isset($image['thumbnail_path']) 
                    ? asset('storage/' . $image['thumbnail_path']) 
                    : asset('storage/' . $image['path'])
            ];
        }, array_slice($images, 0, 3)); // Return max 3 images
    }
}