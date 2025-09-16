@props([
    'categories' => collect(),
    'locations' => collect(),
    'minPrice' => 0,
    'maxPrice' => 5000000,
    'currentFilters' => []
])

<div class="advanced-search-component">
    <form method="GET" action="{{ route('properties.index') }}" class="bg-body border rounded-4 p-2" id="property-search-form">
        <div class="row g-0 p-1">
            <div class="col-lg-7">
                <div class="row g-0">
                    <!-- Sale/Rent Type -->
                    <div class="col-6 col-md-4 d-flex">
                        <div class="w-100">
                            <select name="type" class="form-select form-select-lg border-0 ps-3" aria-label="Rent or sale select">
                                <option value="">Any Type</option>
                                <option value="rent" {{ request('type') == 'rent' ? 'selected' : '' }}>For Rent</option>
                                <option value="sale" {{ request('type') == 'sale' ? 'selected' : '' }}>For Sale</option>
                                <option value="lease" {{ request('type') == 'lease' ? 'selected' : '' }}>For Lease</option>
                            </select>
                        </div>
                        <hr class="vr m-0">
                    </div>
                    
                    <!-- Location -->
                    <div class="col-6 col-md-4 d-flex">
                        <div class="w-100">
                            <select name="location_id" class="form-select form-select-lg border-0" aria-label="Location select">
                                <option value="">Any Location</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <hr class="vr d-none d-md-block m-0">
                    </div>
                    
                    <!-- Property Type -->
                    <div class="col-md-4 d-flex">
                        <div class="w-100">
                            <hr class="d-md-none my-2">
                            <select name="property_type" class="form-select form-select-lg border-0 ps-3" aria-label="Property type select">
                                <option value="">Any Property</option>
                                <option value="house" {{ request('property_type') == 'house' ? 'selected' : '' }}>Houses</option>
                                <option value="apartment" {{ request('property_type') == 'apartment' ? 'selected' : '' }}>Apartments</option>
                                <option value="condo" {{ request('property_type') == 'condo' ? 'selected' : '' }}>Condos</option>
                                <option value="townhouse" {{ request('property_type') == 'townhouse' ? 'selected' : '' }}>Townhouses</option>
                                <option value="commercial" {{ request('property_type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                                <option value="office" {{ request('property_type') == 'office' ? 'selected' : '' }}>Offices</option>
                                <option value="land" {{ request('property_type') == 'land' ? 'selected' : '' }}>Land</option>
                            </select>
                        </div>
                        <hr class="vr d-none d-lg-block m-0">
                    </div>
                </div>
            </div>
            
            <hr class="d-lg-none mt-2 mb-0">
            
            <!-- Advanced Filters -->
            <div class="col-lg-5 d-flex flex-column flex-sm-row align-items-sm-center gap-2 gap-sm-3 pt-3 pt-lg-0 ps-lg-3">
                <div class="d-flex align-items-center w-100 gap-2 ps-2 ps-lg-0">
                    <span class="text-nowrap">Price Range</span>
                    <div class="price-range-inputs d-flex gap-2 w-100">
                        <input type="number" name="price_min" class="form-control form-control-sm" 
                               placeholder="Min" value="{{ request('price_min') }}" min="0">
                        <span class="align-self-center">-</span>
                        <input type="number" name="price_max" class="form-control form-control-sm" 
                               placeholder="Max" value="{{ request('price_max') }}" min="0">
                    </div>
                </div>
                <button type="submit" class="btn btn-lg btn-primary">
                    Search
                    <i class="fi-search fs-lg ms-2 me-n1"></i>
                </button>
            </div>
        </div>
        
        <!-- Advanced Filters Collapse -->
        <div class="collapse" id="advancedFilters">
            <hr class="my-3">
            <div class="row g-3">
                <!-- Bedrooms -->
                <div class="col-md-3">
                    <label class="form-label">Bedrooms</label>
                    <select name="bedrooms" class="form-select">
                        <option value="">Any</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ request('bedrooms') == $i ? 'selected' : '' }}>
                                {{ $i }}{{ $i == 5 ? '+' : '' }}
                            </option>
                        @endfor
                    </select>
                </div>
                
                <!-- Bathrooms -->
                <div class="col-md-3">
                    <label class="form-label">Bathrooms</label>
                    <select name="bathrooms" class="form-select">
                        <option value="">Any</option>
                        @for($i = 1; $i <= 4; $i++)
                            <option value="{{ $i }}" {{ request('bathrooms') == $i ? 'selected' : '' }}>
                                {{ $i }}{{ $i == 4 ? '+' : '' }}
                            </option>
                        @endfor
                    </select>
                </div>
                
                <!-- Area Range -->
                <div class="col-md-3">
                    <label class="form-label">Min Area (sqft)</label>
                    <input type="number" name="area_min" class="form-control" 
                           placeholder="Min sqft" value="{{ request('area_min') }}" min="0">
                </div>
                
                <!-- Category -->
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">Any Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Keyword Search -->
                <div class="col-md-6">
                    <label class="form-label">Keywords</label>
                    <input type="text" name="q" class="form-control" 
                           placeholder="Search by title, description, or address..." 
                           value="{{ request('q') }}">
                </div>
                
                <!-- Sort By -->
                <div class="col-md-3">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-select">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="area_large" {{ request('sort') == 'area_large' ? 'selected' : '' }}>Largest Area</option>
                    </select>
                </div>
                
                <!-- Clear Filters -->
                <div class="col-md-3 d-flex align-items-end">
                    <a href="{{ route('properties.index') }}" class="btn btn-outline-secondary w-100">
                        Clear Filters
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Toggle Advanced Filters -->
        <div class="text-center mt-3">
            <button type="button" class="btn btn-link text-decoration-none p-0" 
                    data-bs-toggle="collapse" data-bs-target="#advancedFilters" 
                    aria-expanded="false" aria-controls="advancedFilters">
                <span class="advanced-toggle-text">More Filters</span>
                <i class="fi-chevron-down ms-1"></i>
            </button>
        </div>
    </form>
</div>

<style>
.advanced-search-component .price-range-inputs input {
    font-size: 0.875rem;
}

.advanced-search-component [data-bs-toggle="collapse"][aria-expanded="true"] .advanced-toggle-text:after {
    content: " Less Filters";
}

.advanced-search-component [data-bs-toggle="collapse"][aria-expanded="false"] .advanced-toggle-text:after {
    content: " More Filters";
}

.advanced-search-component [data-bs-toggle="collapse"][aria-expanded="true"] .fi-chevron-down {
    transform: rotate(180deg);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle advanced filters toggle text
    const toggleButton = document.querySelector('[data-bs-toggle="collapse"]');
    const toggleText = document.querySelector('.advanced-toggle-text');
    
    if (toggleButton && toggleText) {
        document.getElementById('advancedFilters').addEventListener('shown.bs.collapse', function() {
            toggleText.textContent = 'Less';
        });
        
        document.getElementById('advancedFilters').addEventListener('hidden.bs.collapse', function() {
            toggleText.textContent = 'More';
        });
    }
});
</script>