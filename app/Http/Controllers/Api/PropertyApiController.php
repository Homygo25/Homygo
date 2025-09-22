<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
class PropertyApiController extends Controller
{
    /**
     * Return location name suggestions for autocomplete
     */
    public function locationSuggestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:1|max:100',
            'limit' => 'nullable|integer|min:1|max:20'
        ]);

        if ($validator->fails()) {
            return Response::json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = $request->input('query');
        $limit = $request->input('limit', 8);

        $locations = Location::where('is_active', true)
            ->where('name', 'LIKE', "%{$query}%")
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name']);

        return Response::json([
            'success' => true,
            'data' => $locations
        ]);
    }

    /**
     * Get properties with advanced filtering and pagination
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'property_type' => 'nullable|string|in:apartment,house,condo,townhouse,commercial',
            'sort_by' => 'nullable|string|in:price_asc,price_desc,newest,oldest,bedrooms,bathrooms,views',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
            'featured_only' => 'nullable|boolean',
            'with_images' => 'nullable|boolean'
        ]);
        if ($validator->fails()) {
            return Response::json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Property::with(['category:id,name', 'location:id,name', 'user:id,name'])
            ->where('status', 'active');

        // Apply filters
        $this->applyFilters($query, $request);

        if ($request->filled('search')) {
            $this->applySearch($query, $request->input('search'));
        }

        if ($request->boolean('featured_only')) {
            $query->where('is_featured', true);
        }

        // Apply sorting
        $this->applySorting($query, $request);

        $perPage = $request->input('per_page', 12);
        $properties = $query->paginate($perPage);

        // Transform the data
        $transformedProperties = $properties->map(function ($property) use ($request) {
            return $this->transformProperty($property, $request->boolean('with_images', false));
        });
        return Response::json([
            'success' => true,
            'data' => [
                'properties' => $transformedProperties,
                'pagination' => [
                    'current_page' => $properties->currentPage(),
                    'last_page' => $properties->lastPage(),
                    'per_page' => $properties->perPage(),
                    'total' => $properties->total(),
                    'from' => $properties->firstItem(),
                    'to' => $properties->lastItem(),
                    'has_more_pages' => $properties->hasMorePages()
                ],
                'filters_applied' => $this->getAppliedFilters($request)
            ]
        ]);
    }

    /**
     * Get single property details
     */
    public function show(Property $property)
    {
        if ($property->status !== 'active') {
            return Response::json([
                'success' => false,
                'message' => 'Property not found or not available'
            ], 404);
        }

        // Increment view count
        $property->increment('views');

        // Load relationships
        $property->load([
            'category:id,name',
            'location:id,name',
            'user:id,name,email,phone'
        ]);

        // Get similar properties
        $similarProperties = Property::where('id', '!=', $property->id)
            ->where('category_id', $property->category_id)
            ->where('status', 'active')
            ->limit(4)
            ->get(['id', 'title', 'price', 'address', 'images', 'bedrooms', 'bathrooms']);

        return Response::json([
            'success' => true,
            'data' => [
                'property' => $this->transformProperty($property, true),
                'similar_properties' => $similarProperties->map(function ($prop) {
                    return $this->transformProperty($prop, true);
                })
            ]
        ]);
    }

    /**
     * Get property statistics and analytics
     */
    public function statistics()
    {
        $stats = Cache::remember('property_statistics', 3600, function () {
            return [
                'total_properties' => Property::where('status', 'active')->count(),
                'total_value' => Property::where('status', 'active')->sum('price'),
                'average_price' => Property::where('status', 'active')->avg('price'),
                'price_ranges' => [
                    'under_100k' => Property::where('status', 'active')->where('price', '<', 100000)->count(),
                    '100k_500k' => Property::where('status', 'active')->whereBetween('price', [100000, 500000])->count(),
                    '500k_1m' => Property::where('status', 'active')->whereBetween('price', [500000, 1000000])->count(),
                    'over_1m' => Property::where('status', 'active')->where('price', '>', 1000000)->count(),
                ],
                'by_type' => Property::where('status', 'active')
                    ->groupBy('property_type')
                    ->selectRaw('property_type, count(*) as count')
                    ->pluck('count', 'property_type'),
                'by_category' => Property::where('status', 'active')
                    ->join('categories', 'properties.category_id', '=', 'categories.id')
                    ->groupBy('categories.name')
                    ->selectRaw('categories.name, count(*) as count')
                    ->pluck('count', 'name'),
                'by_location' => Property::where('status', 'active')
                    ->join('locations', 'properties.location_id', '=', 'locations.id')
                    ->groupBy('locations.name')
                    ->selectRaw('locations.name, count(*) as count')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->pluck('count', 'name')
            ];
        });
        return Response::json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get filter options for property search
     */
    public function filters()
    {
        $filters = Cache::remember('property_filters', 3600, function () {
            return [
                'categories' => Category::orderBy('name')->get(['id', 'name']),
                'locations' => Location::orderBy('name')->get(['id', 'name']),
                'price_range' => [
                    'min' => Property::where('status', 'active')->min('price') ?? 0,
                    'max' => Property::where('status', 'active')->max('price') ?? 1000000
                ],
                'property_types' => [
                    ['value' => 'apartment', 'label' => 'Apartment'],
                    ['value' => 'house', 'label' => 'House'],
                    ['value' => 'condo', 'label' => 'Condo'],
                    ['value' => 'townhouse', 'label' => 'Townhouse'],
                    ['value' => 'commercial', 'label' => 'Commercial']
                ],
                'bedroom_options' => [
                    ['value' => 1, 'label' => '1+ Bedrooms'],
                    ['value' => 2, 'label' => '2+ Bedrooms'],
                    ['value' => 3, 'label' => '3+ Bedrooms'],
                    ['value' => 4, 'label' => '4+ Bedrooms'],
                    ['value' => 5, 'label' => '5+ Bedrooms']
                ],
                'bathroom_options' => [
                    ['value' => 1, 'label' => '1+ Bathrooms'],
                    ['value' => 2, 'label' => '2+ Bathrooms'],
                    ['value' => 3, 'label' => '3+ Bathrooms'],
                    ['value' => 4, 'label' => '4+ Bathrooms']
                ],
                'sort_options' => [
                    ['value' => 'newest', 'label' => 'Newest First'],
                    ['value' => 'oldest', 'label' => 'Oldest First'],
                    ['value' => 'price_asc', 'label' => 'Price: Low to High'],
                    ['value' => 'price_desc', 'label' => 'Price: High to Low'],
                    ['value' => 'bedrooms', 'label' => 'Most Bedrooms'],
                    ['value' => 'bathrooms', 'label' => 'Most Bathrooms'],
                    ['value' => 'views', 'label' => 'Most Viewed']
                ]
            ];
        });
        return Response::json([
            'success' => true,
            'data' => $filters
        ]);
    }

    /**
     * Search properties with autocomplete suggestions
     */
    public function autocomplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:100',
            'limit' => 'nullable|integer|min:1|max:20'
        ]);

        if ($validator->fails()) {
            return Response::json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = $request->input('query');
        $limit = $request->input('limit', 10);

        $suggestions = Cache::remember("autocomplete_{$query}_{$limit}", 300, function () use ($query, $limit) {
            $properties = Property::where('status', 'active')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('address', 'LIKE', "%{$query}%");
                })
                ->limit($limit)
                ->get(['id', 'title', 'address', 'price']);

            $locations = Location::where('name', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get(['id', 'name']);

            return [
                'properties' => $properties->map(function ($property) {
                    return [
                        'type' => 'property',
                        'id' => $property->id,
                        'title' => $property->title,
                        'subtitle' => $property->address,
                        'price' => $property->price
                    ];
                }),
                'locations' => $locations->map(function ($location) {
                    return [
                        'type' => 'location',
                        'id' => $location->id,
                        'title' => $location->name,
                        'subtitle' => 'Location'
                    ];
                })
            ];
        });

        return Response::json([
            'success' => true,
            'data' => $suggestions
        ]);
    }

    // Private helper methods

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->input('bedrooms'));
        }

        if ($request->filled('bathrooms')) {
            $query->where('bathrooms', '>=', $request->input('bathrooms'));
        }

        if ($request->filled('property_type')) {
            $query->where('property_type', $request->input('property_type'));
        }
    }

    private function applySearch($query, $searchTerm)
    {
        $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'LIKE', "%{$searchTerm}%")
              ->orWhere('description', 'LIKE', "%{$searchTerm}%")
              ->orWhere('address', 'LIKE', "%{$searchTerm}%")
              ->orWhereHas('location', function ($q) use ($searchTerm) {
                  $q->where('name', 'LIKE', "%{$searchTerm}%");
              })
              ->orWhereHas('category', function ($q) use ($searchTerm) {
                  $q->where('name', 'LIKE', "%{$searchTerm}%");
              });
        });
    }

    private function applySorting($query, Request $request)
    {
        $sortBy = $request->input('sort_by', 'newest');

        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'bedrooms':
                $query->orderBy('bedrooms', 'desc')->orderBy('bathrooms', 'desc');
                break;
            case 'bathrooms':
                $query->orderBy('bathrooms', 'desc')->orderBy('bedrooms', 'desc');
                break;
            case 'views':
                $query->orderBy('views', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }

    private function transformProperty($property, $includeImages = false)
    {
        $data = [
            'id' => $property->id,
            'title' => $property->title,
            'slug' => $property->slug ?? null,
            'description' => $property->description,
            'price' => $property->price,
            'formatted_price' => '$' . number_format($property->price),
            'address' => $property->address,
            'bedrooms' => $property->bedrooms,
            'bathrooms' => $property->bathrooms,
            'square_feet' => $property->square_feet,
            'lot_size' => $property->lot_size,
            'year_built' => $property->year_built,
            'property_type' => $property->property_type,
            'status' => $property->status,
            'views' => $property->views ?? 0,
            'is_featured' => $property->is_featured ?? false,
            'created_at' => $property->created_at,
            'updated_at' => $property->updated_at,
            'category' => $property->category,
            'location' => $property->location,
            'user' => $property->user
        ];

        if ($includeImages && $property->images) {
            $images = json_decode($property->images, true) ?? [];
            $data['images'] = array_map(function ($image) {
                return [
                    'path' => $image['path'],
                    'url' => url('storage/' . $image['path']),
                    'thumbnail_url' => isset($image['thumbnail_path']) ? asset('storage/' . $image['thumbnail_path']) : null
                ];
            }, $images);
        }

        if ($property->features) {
            $data['features'] = json_decode($property->features, true) ?? [];
        }

        return $data;
    }

    private function getAppliedFilters(Request $request)
    {
        $filters = [];
        $filterKeys = [
            'search', 'category_id', 'location_id', 'min_price', 'max_price',
            'bedrooms', 'bathrooms', 'property_type', 'sort_by', 'featured_only', 'with_images'
        ];
        foreach ($filterKeys as $filter) {
            if ($request->filled($filter)) {
                $filters[$filter] = $request->input($filter);
            }
        }
        return $filters;
    }
}
