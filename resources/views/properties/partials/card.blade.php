<!-- Property card -->
<div class="card h-100 border-0 shadow-sm">
  
  <!-- Property badges -->
  <div class="position-relative">
    @if($property->is_featured)
    <div class="position-absolute top-0 start-0 z-2 mt-3 ms-3">
      <span class="badge bg-primary">Featured</span>
    </div>
    @endif
    
    @if($property->status === 'sold')
    <div class="position-absolute top-0 end-0 z-2 mt-3 me-3">
      <span class="badge bg-danger">Sold</span>
    </div>
    @elseif($property->status === 'pending')
    <div class="position-absolute top-0 end-0 z-2 mt-3 me-3">
      <span class="badge bg-warning text-dark">Pending</span>
    </div>
    @endif

    <!-- Favorite button -->
    @auth
    <button type="button" class="btn btn-icon btn-light btn-xs position-absolute top-0 end-0 z-2 mt-3 me-3{{ $property->is_favorited_by_user ? ' active' : '' }}" 
            onclick="toggleFavorite('{{ $property->id }}', this)"
            data-bs-toggle="tooltip" data-bs-placement="left" title="Add to favorites">
      <i class="fi-heart"></i>
    </button>
    @endauth

    <!-- Property image -->
    <a href="{{ route('properties.show', $property) }}" class="d-block position-relative">
      @if($property->images && count($property->images) > 0)
        <img src="{{ asset('storage/' . $property->images[0]) }}" class="card-img-top" alt="{{ $property->title }}" style="height: 200px; object-fit: cover;">
        @if(count($property->images) > 1)
        <div class="position-absolute bottom-0 end-0 z-2 mb-3 me-3">
          <span class="badge bg-dark bg-opacity-60">
            <i class="fi-image me-1"></i>
            {{ count($property->images) }}
          </span>
        </div>
        @endif
      @else
        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
          <i class="fi-home text-body-secondary fs-1"></i>
        </div>
      @endif
    </a>
  </div>

  <!-- Property details -->
  <div class="card-body pb-3">
    
    <!-- Property type & category -->
    <div class="d-flex align-items-center text-body-secondary fs-sm mb-2">
      <span class="badge bg-secondary me-2">{{ ucfirst($property->property_type) }}</span>
      @if($property->category)
        <span>{{ $property->category->name }}</span>
      @endif
    </div>

    <!-- Property title -->
    <h3 class="h6 card-title mb-2">
      <a href="{{ route('properties.show', $property) }}" class="nav-link p-0">
        {{ $property->title }}
      </a>
    </h3>

    <!-- Location -->
    <p class="text-body-secondary fs-sm mb-2">
      <i class="fi-map-pin text-primary me-1"></i>
      {{ $property->location->name ?? $property->address }}
    </p>

    <!-- Property specs -->
    <div class="d-flex align-items-center text-body-secondary fs-sm mb-3">
      @if($property->bedrooms)
      <span class="me-3">
        <i class="fi-bed me-1"></i>
        {{ $property->bedrooms }} bed{{ $property->bedrooms > 1 ? 's' : '' }}
      </span>
      @endif
      
      @if($property->bathrooms)
      <span class="me-3">
        <i class="fi-bath me-1"></i>
        {{ $property->bathrooms }} bath{{ $property->bathrooms > 1 ? 's' : '' }}
      </span>
      @endif
      
      @if($property->area)
      <span>
        <i class="fi-calculator me-1"></i>
        {{ number_format($property->area) }} sqft
      </span>
      @endif
    </div>

    <!-- Price -->
    <div class="d-flex align-items-center justify-content-between">
      <div class="text-primary fs-lg fw-semibold">
        ${{ number_format($property->price) }}
        @if($property->price_type === 'rent')
          <small class="text-body-secondary fs-sm fw-normal">/month</small>
        @endif
      </div>
      
      <!-- Rating -->
      @if($property->avg_rating)
      <div class="d-flex align-items-center">
        <i class="fi-star-filled text-warning me-1"></i>
        <span class="text-body-secondary fs-sm">
          {{ number_format($property->avg_rating, 1) }} ({{ $property->reviews_count }})
        </span>
      </div>
      @endif
    </div>

  </div>

  <!-- Card footer -->
  <div class="card-footer bg-transparent border-0 pt-0 pb-3">
    <div class="d-flex align-items-center justify-content-between">
      
      <!-- Agent info -->
      @if($property->user)
      <div class="d-flex align-items-center">
        @if($property->user->profile && $property->user->profile->avatar)
          <img src="{{ asset('storage/' . $property->user->profile->avatar) }}" class="rounded-circle me-2" width="24" height="24" alt="{{ $property->user->name }}">
        @else
          <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
            <i class="fi-user fs-xs"></i>
          </div>
        @endif
        <span class="fs-sm text-body-secondary">{{ $property->user->name }}</span>
      </div>
      @endif

      <!-- Action buttons -->
      <div class="d-flex align-items-center">
        <a href="{{ route('properties.show', $property) }}" class="btn btn-outline-primary btn-sm">
          View Details
        </a>
      </div>
    </div>
  </div>

</div>
