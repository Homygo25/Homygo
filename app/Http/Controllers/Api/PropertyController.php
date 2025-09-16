<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties via API
     */
    public function index(Request $request): JsonResponse
    {
        $query = Property::with(['category', 'location', 'user.userProfile', 'primaryImage'])
            ->active()
            ->published();

        // Apply filters
        $this->applyFilters($query, $request);

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        if ($sortBy === 'price') {
            $query->orderBy('price', $sortOrder);
        } elseif ($sortBy === 'featured') {
            $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = min($request->get('per_page', 12), 50); // Max 50 per page
        $properties = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $properties->items(),
            'pagination' => [
                'current_page' => $properties->currentPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
                'last_page' => $properties->lastPage(),
                'has_more' => $properties->hasMorePages(),
            ]
        ]);
    }

    /**
     * Display the specified property via API
     */
    public function show(Property $property): JsonResponse
    {
        $property->load(['category', 'location', 'user.userProfile', 'images', 'reviews.user']);
        
        // Get similar properties
        $similarProperties = Property::where('id', '!=', $property->id)
            ->where('category_id', $property->category_id)
            ->where('city', $property->city)
            ->active()
            ->published()
            ->with(['primaryImage'])
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'property' => $property,
                'similar_properties' => $similarProperties
            ]
        ]);
    }

    /**
     * Search properties
     */
    public function search(Request $request): JsonResponse
    {
        $query = Property::with(['primaryImage', 'category'])
            ->active()
            ->published();

        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('address', 'like', '%' . $searchTerm . '%')
                  ->orWhere('city', 'like', '%' . $searchTerm . '%');
            });
        }

        $this->applyFilters($query, $request);

        $properties = $query->limit(20)->get();

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }

    /**
     * Get properties near a location
     */
    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:100'
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;
        $radius = $request->get('radius', 10); // Default 10 miles

        $properties = Property::with(['primaryImage', 'category'])
            ->active()
            ->published()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw('*, ( 3959 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance', [$lat, $lng, $lat])
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }

    /**
     * Get featured properties
     */
    public function featured(): JsonResponse
    {
        $properties = Property::with(['primaryImage', 'category', 'location'])
            ->active()
            ->published()
            ->featured()
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }

    /**
     * Get property statistics
     */
    public function stats(): JsonResponse
    {
        $totalProperties = Property::active()->published()->count();
        $totalForSale = Property::active()->published()->forSale()->count();
        $totalForRent = Property::active()->published()->forRent()->count();
        
        $averagePrice = Property::active()->published()->avg('price');
        $priceRanges = [
            'under_100k' => Property::active()->published()->where('price', '<', 100000)->count(),
            '100k_500k' => Property::active()->published()->whereBetween('price', [100000, 500000])->count(),
            '500k_1m' => Property::active()->published()->whereBetween('price', [500000, 1000000])->count(),
            'over_1m' => Property::active()->published()->where('price', '>', 1000000)->count(),
        ];

        $citiesWithMostProperties = Property::active()
            ->published()
            ->select('city', DB::raw('count(*) as count'))
            ->groupBy('city')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_properties' => $totalProperties,
                'total_for_sale' => $totalForSale,
                'total_for_rent' => $totalForRent,
                'average_price' => round($averagePrice, 2),
                'price_ranges' => $priceRanges,
                'top_cities' => $citiesWithMostProperties
            ]
        ]);
    }

    /**
     * Get filter options for properties
     */
    public function filterOptions(): JsonResponse
    {
        $categories = Category::byType('property')
            ->active()
            ->mainCategories()
            ->with('children')
            ->get();

        $locations = Location::cities()
            ->active()
            ->select('id', 'name', 'state_code')
            ->orderBy('name')
            ->get();

        $priceRanges = [
            ['min' => 0, 'max' => 100000, 'label' => 'Under $100K'],
            ['min' => 100000, 'max' => 250000, 'label' => '$100K - $250K'],
            ['min' => 250000, 'max' => 500000, 'label' => '$250K - $500K'],
            ['min' => 500000, 'max' => 1000000, 'label' => '$500K - $1M'],
            ['min' => 1000000, 'max' => null, 'label' => 'Over $1M'],
        ];

        $propertyTypes = [
            'house' => 'House',
            'apartment' => 'Apartment',
            'condo' => 'Condo',
            'townhouse' => 'Townhouse',
            'land' => 'Land',
            'commercial' => 'Commercial',
            'office' => 'Office'
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'locations' => $locations,
                'price_ranges' => $priceRanges,
                'property_types' => $propertyTypes,
                'bedroom_options' => [1, 2, 3, 4, 5, '6+'],
                'bathroom_options' => [1, 2, 3, 4, '5+']
            ]
        ]);
    }

    /**
     * Apply search filters to query
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('address', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('property_type')) {
            if (is_array($request->property_type)) {
                $query->whereIn('property_type', $request->property_type);
            } else {
                $query->where('property_type', $request->property_type);
            }
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('bedrooms')) {
            $bedrooms = $request->bedrooms;
            if ($bedrooms === '6+') {
                $query->where('bedrooms', '>=', 6);
            } else {
                $query->where('bedrooms', '>=', $bedrooms);
            }
        }

        if ($request->filled('bathrooms')) {
            $bathrooms = $request->bathrooms;
            if ($bathrooms === '5+') {
                $query->where('bathrooms', '>=', 5);
            } else {
                $query->where('bathrooms', '>=', $bathrooms);
            }
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('features')) {
            $features = is_array($request->features) ? $request->features : [$request->features];
            foreach ($features as $feature) {
                $query->whereJsonContains('features', $feature);
            }
        }

        if ($request->filled('amenities')) {
            $amenities = is_array($request->amenities) ? $request->amenities : [$request->amenities];
            foreach ($amenities as $amenity) {
                $query->whereJsonContains('amenities', $amenity);
            }
        }
    }
}
