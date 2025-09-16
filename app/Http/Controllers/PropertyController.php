<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties with advanced filtering and search
     */
    public function index(Request $request)
    {
        $query = Property::with(['category', 'location', 'user']);
        
        // Apply filters
        $this->applyFilters($query, $request);
        
        // Apply search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('address', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('location', function ($q) use ($searchTerm) {
                      $q->where('name', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }
        
        // Apply sorting
        $this->applySorting($query, $request);
        
        // Paginate results
        $properties = $query->paginate(12);
        
        // Get filter options for the view
        $categories = Category::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $priceRange = $this->getPriceRange();
        
        return view('properties.index', compact('properties', 'categories', 'locations', 'priceRange'));
    }
    
    /**
     * Advanced search API endpoint for AJAX requests
     */
    public function search(Request $request): JsonResponse
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
            'sort_by' => 'nullable|string|in:price_asc,price_desc,newest,oldest,bedrooms,bathrooms',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $query = Property::with(['category', 'location', 'user'])
                        ->where('status', 'active');
        
        // Apply filters
        $this->applyFilters($query, $request);
        
        // Apply search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('address', 'LIKE', "%{$searchTerm}%");
            });
        }
        
        // Apply sorting
        $this->applySorting($query, $request);
        
        // Get results with pagination
        $perPage = $request->get('per_page', 12);
        $properties = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => [
                'properties' => $properties->items(),
                'pagination' => [
                    'current_page' => $properties->currentPage(),
                    'last_page' => $properties->lastPage(),
                    'per_page' => $properties->perPage(),
                    'total' => $properties->total(),
                    'from' => $properties->firstItem(),
                    'to' => $properties->lastItem()
                ]
            ]
        ]);
    }
    
    /**
     * User's property dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        $properties = Property::where('user_id', $user->id)
            ->with(['category', 'location'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $stats = [
            'total_properties' => $properties->count(),
            'active_properties' => $properties->where('status', 'active')->count(),
            'pending_properties' => $properties->where('status', 'pending')->count(),
            'total_views' => $properties->sum('views'),
            'avg_price' => $properties->avg('price'),
            'total_value' => $properties->sum('price')
        ];
        
        return view('properties.dashboard', compact('properties', 'stats'));
    }
    
    /**
     * Bulk operations for properties
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|string|in:activate,deactivate,delete',
            'property_ids' => 'required|array|min:1',
            'property_ids.*' => 'exists:properties,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $propertyIds = $request->property_ids;
        $action = $request->action;
        
        DB::beginTransaction();
        
        try {
            switch ($action) {
                case 'activate':
                    Property::whereIn('id', $propertyIds)->update(['status' => 'active']);
                    $message = 'Properties activated successfully';
                    break;
                    
                case 'deactivate':
                    Property::whereIn('id', $propertyIds)->update(['status' => 'draft']);
                    $message = 'Properties deactivated successfully';
                    break;
                    
                case 'delete':
                    Property::whereIn('id', $propertyIds)->delete();
                    $message = 'Properties deleted successfully';
                    break;
            }
            
            DB::commit();
            
            return response()->json(['success' => true, 'message' => $message]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to perform bulk action'], 500);
        }
    }
    
    // Private helper methods
    
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }
        
        if ($request->filled('bathrooms')) {
            $query->where('bathrooms', '>=', $request->bathrooms);
        }
        
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'active'); // Default to active properties
        }
    }
    
    private function applySorting($query, Request $request)
    {
        $sortBy = $request->get('sort_by', 'newest');
        
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
                $query->orderBy('bedrooms', 'desc');
                break;
            case 'bathrooms':
                $query->orderBy('bathrooms', 'desc');
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
    
    private function getPriceRange()
    {
        return Cache::remember('property_price_range', 3600, function () {
            return [
                'min' => Property::where('status', 'active')->min('price') ?? 0,
                'max' => Property::where('status', 'active')->max('price') ?? 1000000
            ];
        });
    }
}