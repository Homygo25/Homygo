@extends('partials.master2')

@section('title')
{{ $property->title ?? 'Property Details' }} - Homygo CDO
@endsection

@section('css')
<!-- Swiper slider -->
<link rel="stylesheet" href="build/libs/swiper/swiper-bundle.min.css">
<!-- Leaflet maps -->
<link rel="stylesheet" href="build/libs/leaflet/dist/leaflet.css">
@endsection

@section('content')
<main class="content-wrapper">
    <!-- Breadcrumb -->
    <nav class="container pt-3 my-2 my-md-3" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('properties.index') }}">Properties</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $property->title ?? 'Property Details' }}</li>
        </ol>
    </nav>

    <!-- Property Header -->
    <section class="container mb-4">
        <div class="row align-items-start">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    @if(($property->is_featured ?? false))
                        <span class="badge bg-warning text-dark">⭐ Featured</span>
                    @endif
                    <span class="badge bg-{{ ($property->status ?? 'for_sale') === 'for_rent' ? 'success' : 'primary' }}">
                        {{ ($property->status ?? 'for_sale') === 'for_rent' ? 'For Rent' : 'For Sale' }}
                    </span>
                    <span class="badge bg-secondary">{{ ucfirst($property->property_type ?? 'house') }}</span>
                </div>
                
                <h1 class="h2 mb-2">{{ $property->title ?? 'Beautiful Property in CDO' }}</h1>
                
                <div class="d-flex align-items-center text-muted mb-3">
                    <i class="fi-map-pin me-2"></i>
                    <span>{{ $property->address ?? $property->location ?? 'Cagayan de Oro City' }}</span>
                </div>
                
                <div class="d-flex align-items-center gap-4 text-muted">
                    @if(isset($property->bedrooms) && $property->bedrooms > 0)
                        <div class="d-flex align-items-center">
                            <i class="fi-bed me-1"></i>
                            <span>{{ $property->bedrooms }} bed{{ $property->bedrooms > 1 ? 's' : '' }}</span>
                        </div>
                    @endif
                    @if(isset($property->bathrooms) && $property->bathrooms > 0)
                        <div class="d-flex align-items-center">
                            <i class="fi-bath me-1"></i>
                            <span>{{ $property->bathrooms }} bath{{ $property->bathrooms > 1 ? 's' : '' }}</span>
                        </div>
                    @endif
                    @if(isset($property->area) && $property->area > 0)
                        <div class="d-flex align-items-center">
                            <i class="fi-calculator me-1"></i>
                            <span>{{ number_format($property->area) }} m²</span>
                        </div>
                    @endif
                    @if(isset($property->views_count) && $property->views_count > 0)
                        <div class="d-flex align-items-center">
                            <i class="fi-eye me-1"></i>
                            <span>{{ $property->views_count }} views</span>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-column align-items-lg-end gap-2">
                    <div class="h3 text-primary mb-2">
                        ₱{{ number_format($property->price ?? 0) }}
                        @if(($property->status ?? 'for_sale') === 'for_rent')
                            <small class="text-muted fs-6">/month</small>
                        @endif
                    </div>
                    
                    <div class="d-flex gap-2">
                        @auth
                            <button type="button" class="btn btn-outline-secondary favorite-btn" 
                                    data-property-id="{{ $property->id ?? '' }}"
                                    data-favorited="{{ ($property->is_favorited_by_user ?? false) ? 'true' : 'false' }}">
                                <i class="fi-heart{{ ($property->is_favorited_by_user ?? false) ? '-filled text-danger' : '' }} me-1"></i>
                                {{ ($property->is_favorited_by_user ?? false) ? 'Saved' : 'Save' }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary share-btn" 
                                    data-property-id="{{ $property->id ?? '' }}">
                                <i class="fi-share me-1"></i>Share
                            </button>
                        @endauth
                        
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactModal">
                            <i class="fi-phone me-1"></i>Contact Agent
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Property Images -->
    <section class="container mb-5">
        @if(isset($property->images) && is_array($property->images) && count($property->images) > 0)
            <div class="row g-2">
                <div class="col-lg-8">
                    <div class="position-relative">
                        <img src="{{ is_string($property->images[0]) && str_starts_with($property->images[0], 'http') ? $property->images[0] : asset('storage/' . $property->images[0]) }}" 
                             class="w-100 rounded-3" 
                             style="height: 400px; object-fit: cover;" 
                             alt="Main property image">
                        @if(count($property->images) > 1)
                            <button type="button" class="btn btn-dark position-absolute bottom-0 end-0 m-3" 
                                    data-bs-toggle="modal" data-bs-target="#imageGalleryModal">
                                <i class="fi-image me-1"></i>View All {{ count($property->images) }} Photos
                            </button>
                        @endif
                    </div>
                </div>
                @if(count($property->images) > 1)
                    <div class="col-lg-4">
                        <div class="row g-2 h-100">
                            @foreach(array_slice($property->images, 1, 4) as $index => $image)
                                <div class="col-6">
                                    <img src="{{ is_string($image) && str_starts_with($image, 'http') ? $image : asset('storage/' . $image) }}" 
                                         class="w-100 rounded-2" 
                                         style="height: 195px; object-fit: cover;" 
                                         alt="Property image {{ $index + 2 }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="height: 400px;">
                <div class="text-center text-muted">
                    <i class="fi-home display-1 mb-3"></i>
                    <h5>No Images Available</h5>
                    <p>Images for this property will be available soon.</p>
                </div>
            </div>
        @endif
    </section>

    <!-- Property Details -->
    <section class="container mb-5">
        <div class="row">
            <div class="col-lg-8">
                <!-- Description -->
                <div class="mb-5">
                    <h3 class="h4 mb-3">About This Property</h3>
                    @if(isset($property->description) && $property->description)
                        <div class="text-muted">
                            {!! nl2br(e($property->description)) !!}
                        </div>
                    @else
                        <p class="text-muted">
                            This beautiful property is located in the heart of Cagayan de Oro City, known as the City of Golden Friendship. 
                            With excellent access to major commercial areas, schools, and transportation, this property offers great value 
                            for families and investors alike.
                        </p>
                    @endif
                </div>

                <!-- Features & Amenities -->
                <div class="mb-5">
                    <h3 class="h4 mb-3">Features & Amenities</h3>
                    <div class="row g-3">
                        @php
                            $features = $property->features ?? [
                                'Air Conditioning',
                                'Parking Space',
                                'Security',
                                'Water Supply',
                                'Electricity',
                                'Internet Ready'
                            ];
                        @endphp
                        @foreach($features as $feature)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fi-check-circle text-success me-2"></i>
                                    <span>{{ $feature }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Property Specifications -->
                <div class="mb-5">
                    <h3 class="h4 mb-3">Property Details</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="bg-light rounded-3 p-3">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <small class="text-muted">Property Type</small>
                                            <div class="fw-medium">{{ ucfirst($property->property_type ?? 'House') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <small class="text-muted">Floor Area</small>
                                            <div class="fw-medium">{{ number_format($property->area ?? 0) }} m²</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <small class="text-muted">Bedrooms</small>
                                            <div class="fw-medium">{{ $property->bedrooms ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <small class="text-muted">Bathrooms</small>
                                            <div class="fw-medium">{{ $property->bathrooms ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light rounded-3 p-3">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <small class="text-muted">Year Built</small>
                                            <div class="fw-medium">{{ $property->year_built ?? 'Recent' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <small class="text-muted">Furnished</small>
                                            <div class="fw-medium">{{ $property->furnished ?? 'Negotiable' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <small class="text-muted">Parking</small>
                                            <div class="fw-medium">{{ $property->parking ?? 'Available' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <small class="text-muted">Pet Friendly</small>
                                            <div class="fw-medium">{{ $property->pet_friendly ?? 'Negotiable' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Contact Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            @if(isset($property->user->profile->avatar))
                                <img src="{{ asset('storage/' . $property->user->profile->avatar) }}" 
                                     class="rounded-circle me-3" width="50" height="50" alt="Agent">
                            @else
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fi-user text-white"></i>
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $property->user->name ?? 'Homygo CDO Agent' }}</h6>
                                <small class="text-muted">Licensed Real Estate Professional</small>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="tel:{{ $property->contact_phone ?? '+639123456789' }}" class="btn btn-primary">
                                <i class="fi-phone me-2"></i>Call Now
                            </a>
                            <a href="mailto:{{ $property->contact_email ?? 'info@homygocdo.com' }}" class="btn btn-outline-primary">
                                <i class="fi-mail me-2"></i>Send Email
                            </a>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#scheduleViewingModal">
                                <i class="fi-calendar me-2"></i>Schedule Viewing
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Location Map -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div id="propertyMap" style="height: 300px; border-radius: 0.375rem;"></div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex align-items-center">
                            <i class="fi-map-pin text-primary me-2"></i>
                            <span class="text-muted">{{ $property->address ?? 'Cagayan de Oro City' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Similar Properties -->
    <section class="bg-body-tertiary py-5">
        <div class="container">
            <h3 class="h4 mb-4">Similar Properties in CDO</h3>
            <div class="row g-4">
                @for($i = 0; $i < 3; $i++)
                    <div class="col-md-4">
                        @include('properties.partials.card', [
                            'property' => (object)[
                                'id' => $i + 1,
                                'title' => 'Modern House in ' . ['Cogon', 'Carmen', 'Lapasan'][$i],
                                'price' => [2500000, 3200000, 1800000][$i],
                                'bedrooms' => [3, 4, 2][$i],
                                'bathrooms' => [2, 3, 1][$i],
                                'area' => [120, 150, 80][$i],
                                'location' => ['Cogon', 'Carmen', 'Lapasan'][$i],
                                'property_type' => 'house',
                                'status' => 'for_sale',
                                'images' => []
                            ]
                        ])
                    </div>
                @endfor
            </div>
        </div>
    </section>
</main>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">Contact Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $property->id ?? '' }}">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Your Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Your Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="4" 
                                  placeholder="I'm interested in this property. Please contact me with more details."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="contactForm" class="btn btn-primary">Send Message</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<!-- Swiper slider -->
<script src="build/libs/swiper/swiper-bundle.min.js"></script>
<!-- Leaflet maps -->
<script src="build/libs/leaflet/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    const map = L.map('propertyMap').setView([8.4542, 124.6319], 14); // CDO coordinates
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add property marker
    L.marker([8.4542, 124.6319])
        .addTo(map)
        .bindPopup('{{ $property->title ?? "Property Location" }}')
        .openPopup();
    
    // Favorite functionality
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const text = this.querySelector('span') || this.childNodes[this.childNodes.length - 1];
            const isFavorited = this.dataset.favorited === 'true';
            
            if (isFavorited) {
                icon.className = 'fi-heart me-1';
                text.textContent = ' Save';
                this.dataset.favorited = 'false';
            } else {
                icon.className = 'fi-heart-filled text-danger me-1';
                text.textContent = ' Saved';
                this.dataset.favorited = 'true';
            }
        });
    });
    
    // Share functionality
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ $property->title ?? "Property in CDO" }}',
                    text: 'Check out this amazing property in Cagayan de Oro City',
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Property URL copied to clipboard!');
                });
            }
        });
    });
    
    // Contact form submission
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // Handle form submission here
        alert('Thank you for your inquiry! We will contact you soon.');
        bootstrap.Modal.getInstance(document.getElementById('contactModal')).hide();
    });
});
</script>
@endsection