@extends('partials.master2')

@section('title')
@extends('partials.master2')

@section('title')
Property Listings - Find Your Perfect Property
@endsection

@section('css')
<!-- Add any additional CSS here -->
@endsection
@endsection

@section('css')
    <!-- Vendor styles -->
    <link rel="stylesheet" href="build/libs/leaflet/dist/leaflet.css">
    <link rel="stylesheet" href="build/libs/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="build/libs/choices.js/public/assets/styles/choices.min.css">
@endsection

@section('content')

    <!-- Navigation bar (Page header) -->
    <header class="navbar navbar-expand-lg bg-body navbar-sticky sticky-top z-fixed px-0" data-sticky-element>
      <div class="container">

        <!-- Mobile offcanvas menu toggler (Hamburger) -->
        <button type="button" class="navbar-toggler me-3 me-lg-0" data-bs-toggle="offcanvas" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar brand (Logo) -->
        <a class="navbar-brand py-1 py-md-2 py-xl-1 me-2 me-sm-n4 me-md-n5 me-lg-0" href="{{ route('home') }}">
          <span class="d-none d-sm-flex flex-shrink-0 text-primary rtl-flip me-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="35" height="34"><path d="M34.5 16.894v10.731c0 3.506-2.869 6.375-6.375 6.375H17.5h-.85C7.725 33.575.5 26.138.5 17c0-9.35 7.65-17 17-17s17 7.544 17 16.894z" fill="currentColor"/><g fill-rule="evenodd"><path d="M17.5 13.258c-3.101 0-5.655 2.554-5.655 5.655s2.554 5.655 5.655 5.655 5.655-2.554 5.655-5.655-2.554-5.655-5.655-5.655zm-9.433 5.655c0-5.187 4.246-9.433 9.433-9.433s9.433 4.246 9.433 9.433a9.36 9.36 0 0 1-1.569 5.192l2.397 2.397a1.89 1.89 0 0 1 0 2.671 1.89 1.89 0 0 1-2.671 0l-2.397-2.397a9.36 9.36 0 0 1-5.192 1.569c-5.187 0-9.433-4.246-9.433-9.433z" fill="#000" fill-opacity=".05"/><g fill="#fff"><path d="M17.394 10.153c-3.723 0-6.741 3.018-6.741 6.741s3.018 6.741 6.741 6.741 6.741-3.018 6.741-6.741-3.018-6.741-6.741-6.741zM7.347 16.894A10.05 10.05 0 0 1 17.394 6.847 10.05 10.05 0 0 1 27.44 16.894 10.05 10.05 0 0 1 17.394 26.94 10.05 10.05 0 0 1 7.347 16.894z"/><path d="M23.025 22.525c.645-.645 1.692-.645 2.337 0l3.188 3.188c.645.645.645 1.692 0 2.337s-1.692.645-2.337 0l-3.187-3.187c-.645-.646-.645-1.692 0-2.337z"/></g></g><path d="M23.662 14.663c2.112 0 3.825-1.713 3.825-3.825s-1.713-3.825-3.825-3.825-3.825 1.713-3.825 3.825 1.713 3.825 3.825 3.825z" fill="#fff"/><path fill-rule="evenodd" d="M23.663 8.429a2.41 2.41 0 0 0-2.408 2.408 2.41 2.41 0 0 0 2.408 2.408 2.41 2.41 0 0 0 2.408-2.408 2.41 2.41 0 0 0-2.408-2.408zm-5.242 2.408c0-2.895 2.347-5.242 5.242-5.242s5.242 2.347 5.242 5.242-2.347 5.242-5.242 5.242-5.242-2.347-5.242-5.242z" fill="currentColor"/></svg>
          </span>
          Finder
        </a>

        <!-- Main navigation -->
        <nav class="offcanvas offcanvas-start" id="navbarNav" tabindex="-1" aria-labelledby="navbarNavLabel">
          <div class="offcanvas-header py-3">
            <h5 class="offcanvas-title" id="navbarNavLabel">Browse Finder</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body pt-2 pb-4 py-lg-0 mx-lg-auto">
            <ul class="navbar-nav position-relative">
              <li class="nav-item py-lg-2 me-lg-n1 me-xl-0">
                <a class="nav-link" href="{{ route('home') }}">Home</a>
              </li>
              <li class="nav-item py-lg-2 me-lg-n1 me-xl-0">
                <a class="nav-link active" href="{{ route('properties.index') }}">Properties</a>
              </li>
              @auth
                <li class="nav-item py-lg-2 me-lg-n1 me-xl-0">
                  <a class="nav-link" href="{{ route('properties.create') }}">Add Property</a>
                </li>
              @endauth
            </ul>
          </div>
        </nav>

        <!-- Account dropdown -->
        <div class="nav-item dropdown d-lg-flex">
          @auth
            <a class="nav-link dropdown-toggle p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="{{ auth()->user()->avatar_url ?? asset('build/img/avatars/default.jpg') }}" width="32" height="32" class="rounded-circle" alt="{{ auth()->user()->name }}">
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a></li>
              <li><a class="dropdown-item" href="{{ route('auth.profile') }}">Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="dropdown-item">Logout</button>
                </form>
              </li>
            </ul>
          @else
            <a class="btn btn-sm btn-primary rounded-pill ms-3" href="{{ route('login') }}">
              <i class="fi-user me-2"></i>Sign In
            </a>
          @endauth
        </div>
      </div>
    </header>

    <!-- Filters offcanvas -->
    <div class="offcanvas offcanvas-end" id="filters" tabindex="-1" style="width: 820px">

      <!-- Header -->
      <div class="offcanvas-header px-sm-5">
        <h4 class="h5 offcanvas-title">All filters</h4>
        <button class="btn-close" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <!-- Body (Filter inputs) -->
      <div class="offcanvas-body pt-2 pb-3 px-sm-5">
        <form action="{{ route('properties.search') }}" method="GET" id="filterForm">
          <div class="row row-cols-1 row-cols-md-2 g-4 pb-3 mb-3">
            <div class="col">
              <h6>Choose your location</h6>
              <div class="position-relative w-100">
                <i class="fi-map-pin position-absolute top-50 start-0 translate-middle-y z-1 ms-3"></i>
                <input type="search" name="location" class="form-control form-icon-start" 
                       value="{{ request('location') }}" placeholder="Search for location">
              </div>
            </div>
            <div class="col">
              <h6>Property type</h6>
              <div class="row g-3">
                <div class="col-5">
                  <select name="property_type" class="form-select" aria-label="Property type select">
                    <option value="">All Types</option>
                    <option value="house" {{ request('property_type') == 'house' ? 'selected' : '' }}>House</option>
                    <option value="apartment" {{ request('property_type') == 'apartment' ? 'selected' : '' }}>Apartment</option>
                    <option value="condo" {{ request('property_type') == 'condo' ? 'selected' : '' }}>Condo</option>
                    <option value="townhouse" {{ request('property_type') == 'townhouse' ? 'selected' : '' }}>Townhouse</option>
                    <option value="land" {{ request('property_type') == 'land' ? 'selected' : '' }}>Land</option>
                  </select>
                </div>
                <div class="col-7">
                  <select name="category_id" class="form-select" aria-label="Category select">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $category)
                      <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="col">
              <h6>Price range</h6>
              <div class="row g-3">
                <div class="col">
                  <input type="number" name="price_min" class="form-control" 
                         value="{{ request('price_min') }}" placeholder="Min price">
                </div>
                <div class="col">
                  <input type="number" name="price_max" class="form-control" 
                         value="{{ request('price_max') }}" placeholder="Max price">
                </div>
              </div>
            </div>
            <div class="col">
              <h6>Bedrooms / Bathrooms</h6>
              <div class="row g-3">
                <div class="col">
                  <select name="bedrooms" class="form-select" aria-label="Bedrooms">
                    <option value="">Any</option>
                    @for($i = 1; $i <= 5; $i++)
                      <option value="{{ $i }}" {{ request('bedrooms') == $i ? 'selected' : '' }}>
                        {{ $i }}{{ $i == 5 ? '+' : '' }}
                      </option>
                    @endfor
                  </select>
                </div>
                <div class="col">
                  <select name="bathrooms" class="form-select" aria-label="Bathrooms">
                    <option value="">Any</option>
                    @for($i = 1; $i <= 4; $i++)
                      <option value="{{ $i }}" {{ request('bathrooms') == $i ? 'selected' : '' }}>
                        {{ $i }}{{ $i == 4 ? '+' : '' }}
                      </option>
                    @endfor
                  </select>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Filter buttons -->
          <div class="row row-cols-1 row-cols-sm-2 gy-2 gx-3 mt-3 pt-2 border-top">
            <div class="col">
              <button type="button" class="btn btn-secondary w-100" onclick="clearFilters()">
                <i class="fi-filter-alt-horizontal fs-base me-2"></i>
                Clear all
              </button>
            </div>
            <div class="col">
              <button type="submit" class="btn btn-primary w-100">
                <i class="fi-search fs-base me-2"></i>
                Show results
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Page container -->
    <div class="container-fluid container-xl pt-xl-2 px-0 px-xl-3">
      <div class="row">

        <!-- Sidebar -->
        <div class="col-lg-4 col-xl-3 pe-lg-4 pe-xl-0 d-none d-lg-block">
          <div class="position-sticky top-0">
            <div class="offcanvas-lg offcanvas-start" id="sidebarFilters">
              
              <!-- Header -->
              <div class="offcanvas-header d-flex d-lg-none align-items-center">
                <h5 class="offcanvas-title">Filters</h5>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="offcanvas" data-bs-target="#sidebarFilters" aria-label="Close"></button>
              </div>

              <!-- Body (Filters) -->
              <div class="offcanvas-body flex-column p-0 p-lg-0">
                
                <!-- Search form -->
                <div class="card border-0 rounded-3 p-sm-3 p-md-4 mb-4">
                  <div class="card-body p-0">
                    <form action="{{ route('properties.search') }}" method="GET">
                      <div class="input-group">
                        <i class="fi-search position-absolute top-50 start-0 translate-middle-y text-body-secondary fs-base ms-3"></i>
                        <input type="search" name="q" class="form-control form-icon-start" 
                               value="{{ request('q') }}" placeholder="Search properties...">
                        <button type="submit" class="btn btn-primary ms-sm-n5">
                          <i class="fi-search fs-sm me-sm-2"></i>
                          <span class="d-none d-sm-inline">Search</span>
                        </button>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Quick stats -->
                <div class="card border-0 bg-secondary mb-4">
                  <div class="card-body">
                    <div class="row text-center">
                      <div class="col-6">
                        <h6 class="fs-sm text-body-secondary mb-1">Properties</h6>
                        <span class="h5 mb-0">{{ $properties->total() ?? 0 }}</span>
                      </div>
                      <div class="col-6 border-start">
                        <h6 class="fs-sm text-body-secondary mb-1">Featured</h6>
                        <span class="h5 mb-0">{{ $featuredCount ?? 0 }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Featured properties -->
                @if(isset($featuredProperties) && $featuredProperties->count() > 0)
                <div class="card border-0">
                  <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="mb-0">Featured Properties</h6>
                  </div>
                  <div class="card-body pt-1 pb-2">
                    @foreach($featuredProperties->take(3) as $featured)
                    <div class="d-flex align-items-center border-bottom pb-3 mb-3">
                      <a href="{{ route('properties.show', $featured) }}" class="flex-shrink-0 me-3">
                        @if($featured->images && count($featured->images) > 0)
                        <img src="{{ asset('storage/' . $featured->images[0]) }}" class="rounded" width="60" alt="{{ $featured->title }}">
                        @else
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                          <i class="fi-home text-body-secondary"></i>
                        </div>
                        @endif
                      </a>
                      <div class="flex-grow-1">
                        <h6 class="fs-sm mb-1">
                          <a href="{{ route('properties.show', $featured) }}" class="nav-link p-0">
                            {{ Str::limit($featured->title, 40) }}
                          </a>
                        </h6>
                        <p class="fs-xs text-body-secondary mb-1">
                          <i class="fi-map-pin me-1"></i>
                          {{ $featured->location->name ?? 'Location' }}
                        </p>
                        <div class="fs-sm fw-semibold text-primary">
                          ${{ number_format($featured->price) }}
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>
                </div>
                @endif

              </div>
            </div>
          </div>
        </div>

        <!-- Main content -->
        <div class="col-lg-8 col-xl-9">
          
          <!-- Breadcrumb -->
          <nav class="mb-3" aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Home</a>
              </li>
              <li class="breadcrumb-item active" aria-current="page">Properties</li>
            </ol>
          </nav>

          <!-- Results header -->
          <div class="d-sm-flex align-items-center justify-content-between pb-3 mb-4">
            <h1 class="h2 mb-sm-0">
              {{ $title ?? 'Property Listings' }}
              @if(request('q'))
                <small class="text-body-secondary">for "{{ request('q') }}"</small>
              @endif
            </h1>
            <div class="d-flex align-items-center">
              <label class="fs-sm me-2 pe-1 text-nowrap" for="sortby">Sort by:</label>
              <select class="form-select form-select-sm w-auto" id="sortby" onchange="updateSort(this.value)">
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="featured" {{ request('sort') == 'featured' ? 'selected' : '' }}>Featured</option>
              </select>
              <button type="button" class="btn btn-outline-secondary btn-sm ms-2 d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebarFilters">
                <i class="fi-filter-alt-horizontal me-2"></i>
                Filters
              </button>
            </div>
          </div>

          <!-- Properties grid -->
          @if($properties->count() > 0)
          <div class="row gy-4 gx-3 gx-xxl-4">
            @foreach($properties as $property)
            <div class="col-md-6 col-xl-4">
              @include('properties.partials.card', ['property' => $property])
            </div>
            @endforeach
          </div>

          <!-- Pagination -->
          <div class="d-flex justify-content-center pt-4 mt-3">
            {{ $properties->appends(request()->query())->links() }}
          </div>
          @else
          <!-- No results -->
          <div class="text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-secondary rounded-circle mb-4" style="width: 80px; height: 80px;">
              <i class="fi-home text-body-secondary fs-2"></i>
            </div>
            <h4>No properties found</h4>
            <p class="text-body-secondary mb-4">
              Try adjusting your search criteria or browse all properties.
            </p>
            <a href="{{ route('properties.index') }}" class="btn btn-primary">
              View All Properties
            </a>
          </div>
          @endif

        </div>
      </div>
    </div>

@endsection

@section('scripts')
<!-- Vendor scripts -->
<script src="build/libs/leaflet/dist/leaflet.js"></script>
<script src="build/libs/swiper/swiper-bundle.min.js"></script>
<script src="build/libs/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
function updateSort(value) {
    const url = new URL(window.location);
    url.searchParams.set('sort', value);
    window.location = url.toString();
}

function clearFilters() {
    window.location = '{{ route("properties.index") }}';
}

// Initialize Choices.js for selects
document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('.form-select[data-select]');
    selects.forEach(select => {
        new Choices(select, {
            removeItemButton: false,
            searchEnabled: false
        });
    });
});

// Toggle favorite functionality
function toggleFavorite(propertyId, element) {
    fetch(`/api/properties/${propertyId}/favorite`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const icon = element.querySelector('i');
            if (data.favorited) {
                icon.classList.remove('fi-heart');
                icon.classList.add('fi-heart-filled');
                element.classList.add('text-danger');
            } else {
                icon.classList.remove('fi-heart-filled');
                icon.classList.add('fi-heart');
                element.classList.remove('text-danger');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Please login to add favorites');
    });
}
</script>
@endsection
