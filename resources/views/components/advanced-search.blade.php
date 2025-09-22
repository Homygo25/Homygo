@props([
    'categories' => collect(),
    'locations' => collect(),
    'minPrice' => 0,
    'maxPrice' => 5000000,
    'currentFilters' => []
])

<div class="advanced-search-component">
    <form method="GET" action="{{ route('properties.index') }}" class="bg-body border rounded-4 p-2" id="property-search-form">
        <div class="row g-0 p-1 align-items-center">
            <div class="col-lg-8">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-0 ps-3"><i class="fi-map-pin"></i></span>
                    <input type="text" name="location" id="location-autocomplete" class="form-control border-0" autocomplete="off" placeholder="Enter location (e.g. CDO, Carmen, Lapasan)" value="{{ request('location') }}">
                    <div id="location-suggestions" class="list-group position-absolute w-100" style="z-index: 1000; display: none;"></div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('location-autocomplete');
    const suggestions = document.getElementById('location-suggestions');
    let debounceTimeout;

    input.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(debounceTimeout);
        if (query.length < 2) {
            suggestions.style.display = 'none';
            suggestions.innerHTML = '';
            return;
        }
        debounceTimeout = setTimeout(() => {
            fetch(`/api/location-suggest?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    suggestions.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(loc => {
                            const item = document.createElement('button');
                            item.type = 'button';
                            item.className = 'list-group-item list-group-item-action';
                            item.textContent = loc;
                            item.onclick = () => {
                                input.value = loc;
                                suggestions.style.display = 'none';
                                suggestions.innerHTML = '';
                            };
                            suggestions.appendChild(item);
                        });
                        suggestions.style.display = 'block';
                    } else {
                        suggestions.style.display = 'none';
                    }
                });
        }, 200);
    });

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.style.display = 'none';
        }
    });
});
</script>
@endpush
                </div>
            </div>
            <div class="col-lg-3 mt-3 mt-lg-0">
                <div class="d-flex align-items-center w-100 gap-2">
                    <span class="text-nowrap">Price Range</span>
                    <input type="number" name="price_min" class="form-control form-control-sm" placeholder="Min" value="{{ request('price_min') }}" min="0">
                    <span class="align-self-center">-</span>
                    <input type="number" name="price_max" class="form-control form-control-sm" placeholder="Max" value="{{ request('price_max') }}" min="0">
                </div>
            </div>
            <div class="col-lg-1 mt-3 mt-lg-0 d-flex align-items-center justify-content-end">
                <button type="submit" class="btn btn-lg btn-primary w-100">
                    <i class="fi-search fs-lg me-2"></i>Search
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
