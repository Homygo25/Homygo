<!-- CDO Property card -->
<div class="card h-100 border-0 shadow-sm property-card-cdo">
  
  <!-- Property badges -->
  <div class="position-relative">
    @if($property->is_featured ?? false)
    <div class="position-absolute top-0 start-0 z-2 mt-3 ms-3">
      <span class="badge bg-warning text-dark">⭐ Featured CDO Property</span>
    </div>
    @endif
    
    @if(($property->status ?? 'available') === 'sold')
    <div class="position-absolute top-0 end-0 z-2 mt-3 me-3">
      <span class="badge bg-danger">Sold</span>
    </div>
    @elseif(($property->status ?? 'available') === 'pending')
    <div class="position-absolute top-0 end-0 z-2 mt-3 me-3">
      <span class="badge bg-warning text-dark">Pending</span>
    </div>
    @elseif(($property->status ?? 'available') === 'for_rent')
    <div class="position-absolute top-0 end-0 z-2 mt-3 me-3">
      <span class="badge bg-success">For Rent</span>
    </div>
    @else
    <div class="position-absolute top-0 end-0 z-2 mt-3 me-3">
      <span class="badge bg-primary">For Sale</span>
    </div>
    @endif

    <!-- Favorite button -->
    @auth
    <button type="button" class="btn btn-icon btn-light btn-xs position-absolute top-0 end-0 z-2 mt-3 me-3{{ ($property->is_favorited_by_user ?? false) ? ' active' : '' }}" 
            onclick="toggleFavorite('{{ $property->id ?? '' }}', this)"
            data-bs-toggle="tooltip" data-bs-placement="left" title="Save this CDO property">
      <i class="fi-heart{{ ($property->is_favorited_by_user ?? false) ? '-filled text-danger' : '' }}"></i>
    </button>
    @endauth

    <!-- Property image -->
    <a href="{{ route('properties.show', $property->id ?? '#') }}" class="d-block position-relative">
      @if(isset($property->images) && is_array($property->images) && count($property->images) > 0)
        <img src="{{ is_string($property->images[0]) && str_starts_with($property->images[0], 'http') ? $property->images[0] : asset('storage/' . $property->images[0]) }}" 
             class="card-img-top property-image" 
             alt="{{ $property->title ?? 'Property in CDO' }}" 
             style="height: 220px; object-fit: cover;">
        @if(count($property->images) > 1)
        <div class="position-absolute bottom-0 end-0 z-2 mb-3 me-3">
          <span class="badge bg-dark bg-opacity-60">
            <i class="fi-image me-1"></i>
            {{ count($property->images) }}
          </span>
        </div>
        @endif
      @else
        <div class="card-img-top bg-light d-flex align-items-center justify-content-center flex-column text-muted" style="height: 220px;">
          <i class="fi-home fs-1 mb-2"></i>
          <small>Property in CDO</small>
        </div>
      @endif
    </a>
  </div>

  <!-- Property details -->
  <div class="card-body pb-3">
    
    <!-- Property type & category -->
    <div class="d-flex align-items-center text-body-secondary fs-sm mb-2">
      <span class="badge bg-secondary me-2">{{ ucfirst($property->property_type ?? 'house') }}</span>
      @if(isset($property->category) && $property->category)
        <span>{{ $property->category->name ?? 'Residential' }}</span>
      @else
        <span>CDO Property</span>
      @endif
    </div>

    <!-- Property title -->
    <h3 class="h6 card-title mb-2">
      <a href="{{ route('properties.show', $property->id ?? '#') }}" class="nav-link p-0">
        {{ $property->title ?? 'Beautiful Property in CDO' }}
      </a>
    </h3>

    <!-- Location -->
    <p class="text-body-secondary fs-sm mb-2">
      <i class="fi-map-pin text-primary me-1"></i>
      {{ $property->location->name ?? $property->location ?? $property->address ?? 'Cagayan de Oro City' }}
    </p>

    <!-- Property specs -->
    <div class="d-flex align-items-center text-body-secondary fs-sm mb-3">
      @if(isset($property->bedrooms) && $property->bedrooms > 0)
      <span class="me-3">
        <i class="fi-bed me-1"></i>
        {{ $property->bedrooms }} bed{{ $property->bedrooms > 1 ? 's' : '' }}
      </span>
      @endif
      
      @if(isset($property->bathrooms) && $property->bathrooms > 0)
      <span class="me-3">
        <i class="fi-bath me-1"></i>
        {{ $property->bathrooms }} bath{{ $property->bathrooms > 1 ? 's' : '' }}
      </span>
      @endif
      
      @if(isset($property->area) && $property->area > 0)
      <span>
        <i class="fi-calculator me-1"></i>
        {{ number_format($property->area) }} m²
      </span>
      @endif
    </div>

    <!-- Price -->
    <div class="d-flex align-items-center justify-content-between">
      <div class="text-primary fs-lg fw-semibold">
        ₱{{ number_format($property->price ?? 0) }}
        @if(($property->status ?? 'for_sale') === 'for_rent')
          <small class="text-body-secondary fs-sm fw-normal">/month</small>
        @endif
      </div>
      
      <!-- Rating or View Count -->
      @if(isset($property->avg_rating) && $property->avg_rating > 0)
      <div class="d-flex align-items-center">
        <i class="fi-star-filled text-warning me-1"></i>
        <span class="text-body-secondary fs-sm">
          {{ number_format($property->avg_rating, 1) }} ({{ $property->reviews_count ?? 0 }})
        </span>
      </div>
      @elseif(isset($property->views_count) && $property->views_count > 0)
      <div class="d-flex align-items-center">
        <i class="fi-eye text-muted me-1"></i>
        <span class="text-body-secondary fs-sm">{{ $property->views_count }} views</span>
      </div>
      @endif
    </div>

  </div>

  <!-- Card footer -->
  <div class="card-footer bg-transparent border-0 pt-0 pb-3">
    <div class="d-flex align-items-center justify-content-between">
      
      <!-- Agent info -->
      @if(isset($property->user) && $property->user)
      <div class="d-flex align-items-center">
        @if(isset($property->user->profile) && $property->user->profile && $property->user->profile->avatar)
          <img src="{{ asset('storage/' . $property->user->profile->avatar) }}" class="rounded-circle me-2" width="24" height="24" alt="{{ $property->user->name }}">
        @else
          <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
            <i class="fi-user fs-xs"></i>
          </div>
        @endif
        <span class="fs-sm text-body-secondary">{{ $property->user->name ?? 'CDO Agent' }}</span>
      </div>
      @else
      <div class="d-flex align-items-center">
        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
          <i class="fi-home fs-xs text-white"></i>
        </div>
        <span class="fs-sm text-body-secondary">Homygo CDO</span>
      </div>
      @endif

      <!-- Action buttons -->
      <div class="d-flex align-items-center gap-2">
        @auth
          <button type="button" class="btn btn-outline-secondary btn-sm contact-property" data-property-id="{{ $property->id ?? '' }}">
            <i class="fi-phone me-1"></i>Contact
          </button>
        @endauth
        <a href="{{ route('properties.show', $property->id ?? '#') }}" class="btn btn-primary btn-sm">
          View Details
        </a>
      </div>
    </div>
  </div>

</div>

<style>
.property-card-cdo {
  transition: all 0.3s ease;
  border-radius: 12px;
  overflow: hidden;
}

.property-card-cdo:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
}

.property-card-cdo .property-image {
  transition: transform 0.3s ease;
}

.property-card-cdo:hover .property-image {
  transform: scale(1.03);
}

.property-card-cdo .btn-icon:hover {
  transform: scale(1.1);
}
</style>
