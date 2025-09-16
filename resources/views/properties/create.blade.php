@extends('partials.master2')

@section('title')
Add New Property - Finder
@endsection

@section('css')
<!-- Additional CSS for form styling -->
<style>
.step-content {
    display: none;
}
.step-content.active {
    display: block;
}
.step-indicator {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
}
.step-indicator .step {
    flex: 1;
    text-align: center;
    padding: 1rem 0;
    border-bottom: 3px solid #e9ecef;
    position: relative;
}
.step-indicator .step.active {
    border-bottom-color: #0d6efd;
    color: #0d6efd;
}
.step-indicator .step.completed {
    border-bottom-color: #198754;
    color: #198754;
}
</style>
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
            <svg xmlns="http://www.w3.org/2000/svg" width="35" height="34"><path d="M34.5 16.894v10.731c0 3.506-2.869 6.375-6.375 6.375H17.5h-.85C7.725 33.575.5 26.138.5 17c0-9.35 7.65-17 17-17s17 7.544 17 16.894z" fill="currentColor"/><g fill-rule="evenodd"><path d="M17.5 13.258c-3.101 0-5.655 2.554-5.655 5.655s2.554 5.655 5.655 5.655 5.655-2.554 5.655-5.655-2.554-5.655-5.655-5.655zm-9.433 5.655c0-5.187 4.246-9.433 9.433-9.433s9.433 4.246 9.433 9.433a9.36 9.36 0 0 1-1.569 5.192l2.397 2.397a1.89 1.89 0 0 1 0 2.671 1.89 1.89 0 0 1-2.671 0l-2.397-2.397a9.36 9.36 0 0 1-5.192 1.569c-5.187 0-9.433-4.246-9.433-9.433z" fill="#000" fill-opacity=".05"/><g fill="#fff"><path d="M17.394 10.153c-3.723 0-6.741 3.018-6.741 6.741s3.018 6.741 6.741 6.741 6.741-3.018 6.741-6.741-3.018-6.741-6.741-6.741zM7.347 16.894A10.05 10.05 0 0 1 17.394 6.847 10.05 10.05 0 0 1 27.44 16.894 10.05 10.05 0 0 1 17.394 26.940 10.05 10.05 0 0 1 7.347 16.894z"/><path d="M23.025 22.525c.645-.645 1.692-.645 2.337 0l3.188 3.188c.645.645.645 1.692 0 2.337s-1.692.645-2.337 0l-3.187-3.187c-.645-.646-.645-1.692 0-2.337z"/></g></g><path d="M23.662 14.663c2.112 0 3.825-1.713 3.825-3.825s-1.713-3.825-3.825-3.825-3.825 1.713-3.825 3.825 1.713 3.825 3.825 3.825z" fill="#fff"/><path fill-rule="evenodd" d="M23.663 8.429a2.41 2.41 0 0 0-2.408 2.408 2.41 2.41 0 0 0 2.408 2.408 2.41 2.41 0 0 0 2.408-2.408 2.41 2.41 0 0 0-2.408-2.408zm-5.242 2.408c0-2.895 2.347-5.242 5.242-5.242s5.242 2.347 5.242 5.242-2.347 5.242-5.242 5.242-5.242-2.347-5.242-5.242z" fill="currentColor"/></svg>
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
                <a class="nav-link" href="{{ route('properties.index') }}">Properties</a>
              </li>
              <li class="nav-item py-lg-2 me-lg-n1 me-xl-0">
                <a class="nav-link active" href="{{ route('properties.create') }}">Add Property</a>
              </li>
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

    <!-- Page content -->
    <main class="page-wrapper">
      <div class="container py-5 my-lg-2">
        <div class="row">
          <div class="col-lg-8 mx-auto">
            
            <!-- Page title -->
            <div class="text-center mb-5">
              <h1 class="h2 mb-2">Add New Property</h1>
              <p class="text-body-secondary">Fill out the form below to list your property</p>
            </div>

            <!-- Step indicator -->
            <div class="step-indicator">
              <div class="step active" data-step="1">
                <div class="fw-medium">1. Basic Info</div>
                <small class="text-body-secondary">Property details</small>
              </div>
              <div class="step" data-step="2">
                <div class="fw-medium">2. Location</div>
                <small class="text-body-secondary">Address & area</small>
              </div>
              <div class="step" data-step="3">
                <div class="fw-medium">3. Features</div>
                <small class="text-body-secondary">Amenities & specs</small>
              </div>
              <div class="step" data-step="4">
                <div class="fw-medium">4. Contact</div>
                <small class="text-body-secondary">Contact details</small>
              </div>
              <div class="step" data-step="5">
                <div class="fw-medium">5. Photos</div>
                <small class="text-body-secondary">Property images</small>
              </div>
            </div>

            <!-- Property creation form -->
            <form action="{{ route('properties.store') }}" method="POST" enctype="multipart/form-data" id="property-form">
              @csrf

              <!-- Step 1: Basic Information -->
              <div class="step-content active" data-step="1">
                <div class="card border-0 shadow-sm">
                  <div class="card-body p-4">
                    <h3 class="h5 mb-4">Basic Information</h3>
                    
                    <div class="row g-3">
                      <div class="col-12">
                        <label for="title" class="form-label">Property Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" 
                               placeholder="e.g., Luxury Downtown Apartment">
                        @error('title')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-6">
                        <label for="type" class="form-label">Listing Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
                          <option value="">Select type</option>
                          <option value="sale" {{ old('type') == 'sale' ? 'selected' : '' }}>For Sale</option>
                          <option value="rent" {{ old('type') == 'rent' ? 'selected' : '' }}>For Rent</option>
                          <option value="lease" {{ old('type') == 'lease' ? 'selected' : '' }}>For Lease</option>
                        </select>
                        @error('type')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-6">
                        <label for="property_type" class="form-label">Property Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('property_type') is-invalid @enderror" id="property_type" name="property_type">
                          <option value="">Select property type</option>
                          <option value="house" {{ old('property_type') == 'house' ? 'selected' : '' }}>House</option>
                          <option value="apartment" {{ old('property_type') == 'apartment' ? 'selected' : '' }}>Apartment</option>
                          <option value="condo" {{ old('property_type') == 'condo' ? 'selected' : '' }}>Condo</option>
                          <option value="townhouse" {{ old('property_type') == 'townhouse' ? 'selected' : '' }}>Townhouse</option>
                          <option value="land" {{ old('property_type') == 'land' ? 'selected' : '' }}>Land</option>
                          <option value="commercial" {{ old('property_type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                          <option value="office" {{ old('property_type') == 'office' ? 'selected' : '' }}>Office</option>
                        </select>
                        @error('property_type')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-6">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                          <option value="">Select category</option>
                          @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                              {{ $category->name }}
                            </option>
                          @endforeach
                        </select>
                        @error('category_id')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-6">
                        <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                          <span class="input-group-text">$</span>
                          <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                 id="price" name="price" value="{{ old('price') }}" 
                                 placeholder="0.00" step="0.01" min="0">
                          @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                      </div>

                      <div class="col-12">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Describe your property...">{{ old('description') }}</textarea>
                        @error('description')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 2: Location -->
              <div class="step-content" data-step="2">
                <div class="card border-0 shadow-sm">
                  <div class="card-body p-4">
                    <h3 class="h5 mb-4">Location Details</h3>
                    
                    <div class="row g-3">
                      <div class="col-12">
                        <label for="address" class="form-label">Full Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror" 
                               id="address" name="address" value="{{ old('address') }}" 
                               placeholder="e.g., 123 Main Street, Downtown">
                        @error('address')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-6">
                        <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror" 
                               id="city" name="city" value="{{ old('city') }}" 
                               placeholder="e.g., New York">
                        @error('city')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-3">
                        <label for="state" class="form-label">State</label>
                        <input type="text" class="form-control @error('state') is-invalid @enderror" 
                               id="state" name="state" value="{{ old('state') }}" 
                               placeholder="e.g., NY">
                        @error('state')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-3">
                        <label for="postal_code" class="form-label">Postal Code</label>
                        <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                               id="postal_code" name="postal_code" value="{{ old('postal_code') }}" 
                               placeholder="e.g., 10001">
                        @error('postal_code')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-6">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="number" class="form-control @error('latitude') is-invalid @enderror" 
                               id="latitude" name="latitude" value="{{ old('latitude') }}" 
                               placeholder="40.7128" step="any">
                        @error('latitude')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-6">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="number" class="form-control @error('longitude') is-invalid @enderror" 
                               id="longitude" name="longitude" value="{{ old('longitude') }}" 
                               placeholder="-74.0060" step="any">
                        @error('longitude')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 3: Features & Specifications -->
              <div class="step-content" data-step="3">
                <div class="card border-0 shadow-sm">
                  <div class="card-body p-4">
                    <h3 class="h5 mb-4">Property Features</h3>
                    
                    <div class="row g-3">
                      <div class="col-md-4">
                        <label for="bedrooms" class="form-label">Bedrooms</label>
                        <select class="form-select @error('bedrooms') is-invalid @enderror" id="bedrooms" name="bedrooms">
                          <option value="">Select</option>
                          @for($i = 0; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ old('bedrooms') == $i ? 'selected' : '' }}>{{ $i }}</option>
                          @endfor
                        </select>
                        @error('bedrooms')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-4">
                        <label for="bathrooms" class="form-label">Bathrooms</label>
                        <select class="form-select @error('bathrooms') is-invalid @enderror" id="bathrooms" name="bathrooms">
                          <option value="">Select</option>
                          @for($i = 0; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ old('bathrooms') == $i ? 'selected' : '' }}>{{ $i }}</option>
                          @endfor
                        </select>
                        @error('bathrooms')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-4">
                        <label for="parking_spaces" class="form-label">Parking Spaces</label>
                        <select class="form-select @error('parking_spaces') is-invalid @enderror" id="parking_spaces" name="parking_spaces">
                          <option value="">Select</option>
                          @for($i = 0; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ old('parking_spaces') == $i ? 'selected' : '' }}>{{ $i }}</option>
                          @endfor
                        </select>
                        @error('parking_spaces')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-6">
                        <label for="area" class="form-label">Area (sq ft)</label>
                        <input type="number" class="form-control @error('area') is-invalid @enderror" 
                               id="area" name="area" value="{{ old('area') }}" 
                               placeholder="1200" step="1" min="0">
                        @error('area')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-6">
                        <label for="year_built" class="form-label">Year Built</label>
                        <input type="number" class="form-control @error('year_built') is-invalid @enderror" 
                               id="year_built" name="year_built" value="{{ old('year_built') }}" 
                               placeholder="2020" min="1800" max="{{ date('Y') }}">
                        @error('year_built')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-12">
                        <label class="form-label">Property Features</label>
                        <div class="row">
                          @php
                            $features = ['Air Conditioning', 'Balcony', 'City Views', 'Modern Kitchen', 'Backyard', 'Garage', 'Fireplace', 'Walk-in Closet', 'Ocean Views', 'Floor-to-Ceiling Windows', 'Modern Appliances', 'Efficient Layout', 'Near Transit', 'Private Terrace', 'Panoramic Views', 'Luxury Finishes', 'Smart Home'];
                          @endphp
                          @foreach($features as $feature)
                            <div class="col-md-4 col-lg-3">
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" value="{{ $feature }}" id="feature_{{ $loop->index }}">
                                <label class="form-check-label" for="feature_{{ $loop->index }}">
                                  {{ $feature }}
                                </label>
                              </div>
                            </div>
                          @endforeach
                        </div>
                      </div>

                      <div class="col-12">
                        <label class="form-label">Amenities</label>
                        <div class="row">
                          @php
                            $amenities = ['Gym', 'Pool', 'Concierge', 'Rooftop Deck', 'Swimming Pool', 'Garden', 'Patio', 'Beach Access', 'Spa', 'Valet Parking', 'Laundry', 'Fitness Center', 'Doorman', 'Private Elevator', 'Wine Cellar'];
                          @endphp
                          @foreach($amenities as $amenity)
                            <div class="col-md-4 col-lg-3">
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $amenity }}" id="amenity_{{ $loop->index }}">
                                <label class="form-check-label" for="amenity_{{ $loop->index }}">
                                  {{ $amenity }}
                                </label>
                              </div>
                            </div>
                          @endforeach
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 4: Contact Information -->
              <div class="step-content" data-step="4">
                <div class="card border-0 shadow-sm">
                  <div class="card-body p-4">
                    <h3 class="h5 mb-4">Contact Information</h3>
                    
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label for="contact_name" class="form-label">Contact Name</label>
                        <input type="text" class="form-control @error('contact_name') is-invalid @enderror" 
                               id="contact_name" name="contact_name" value="{{ old('contact_name', auth()->user()->name) }}" 
                               placeholder="John Doe">
                        @error('contact_name')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-6">
                        <label for="contact_email" class="form-label">Contact Email</label>
                        <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                               id="contact_email" name="contact_email" value="{{ old('contact_email', auth()->user()->email) }}" 
                               placeholder="john@example.com">
                        @error('contact_email')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-6">
                        <label for="contact_phone" class="form-label">Contact Phone</label>
                        <input type="tel" class="form-control @error('contact_phone') is-invalid @enderror" 
                               id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}" 
                               placeholder="+1 (555) 123-4567">
                        @error('contact_phone')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-6">
                        <label for="location_id" class="form-label">Location</label>
                        <select class="form-select @error('location_id') is-invalid @enderror" id="location_id" name="location_id">
                          <option value="">Select location</option>
                          <!-- Will be populated with JavaScript or server-side -->
                        </select>
                        @error('location_id')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-12">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured">
                          <label class="form-check-label" for="is_featured">
                            Feature this property (additional fee may apply)
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 5: Photos -->
              <div class="step-content" data-step="5">
                <div class="row">
                  <div class="col-lg-8">
                    <h2 class="h4 mb-4">Property Photos</h2>
                    <p class="text-body-secondary mb-4">
                      Add high-quality photos to showcase your property. The first image will be used as the primary photo.
                    </p>
                    
                    <!-- Photos Upload Area -->
                    <div class="border rounded p-4 mb-4">
                      <div class="text-center">
                        <i class="fi-image display-6 text-primary mb-3"></i>
                        <h5>Upload Property Photos</h5>
                        <p class="text-body-secondary mb-4">
                          Drag and drop your images here, or click to browse.<br>
                          Maximum file size: 8MB. Supported formats: JPG, PNG, WEBP
                        </p>
                        
                        <!-- File Upload Input -->
                        <input type="file" id="property-images" name="images[]" multiple accept="image/*" class="d-none">
                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('property-images').click()">
                          <i class="fi-plus me-2"></i>Choose Photos
                        </button>
                      </div>
                      
                      <!-- Image Preview Grid -->
                      <div id="image-preview-grid" class="row g-3 mt-3" style="display: none;">
                        <!-- Images will be dynamically added here -->
                      </div>
                    </div>
                    
                    <!-- Video Tour Link -->
                    <div class="mb-4">
                      <label for="video_tour_url" class="form-label">Video Tour URL (Optional)</label>
                      <input type="url" class="form-control @error('video_tour_url') is-invalid @enderror" 
                             id="video_tour_url" name="video_tour_url" value="{{ old('video_tour_url') }}" 
                             placeholder="https://www.youtube.com/watch?v=...">
                      <div class="form-text">Add a YouTube, Vimeo, or other video tour link</div>
                      @error('video_tour_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                </div>
              </div>

              <!-- Navigation buttons -->
              <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary" id="prev-step" style="display: none;">
                  <i class="fi-chevron-left me-2"></i>Previous
                </button>
                <button type="button" class="btn btn-primary" id="next-step">
                  Next<i class="fi-chevron-right ms-2"></i>
                </button>
                <button type="submit" class="btn btn-success" id="submit-form" style="display: none;">
                  <i class="fi-check me-2"></i>Create Property
                </button>
              </div>

            </form>
          </div>
        </div>
      </div>
    </main>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 5;
    
    const stepContents = document.querySelectorAll('.step-content');
    const stepIndicators = document.querySelectorAll('.step-indicator .step');
    const prevBtn = document.getElementById('prev-step');
    const nextBtn = document.getElementById('next-step');
    const submitBtn = document.getElementById('submit-form');
    
    function showStep(step) {
        // Hide all step contents
        stepContents.forEach(content => content.classList.remove('active'));
        stepIndicators.forEach(indicator => {
            indicator.classList.remove('active', 'completed');
        });
        
        // Show current step
        document.querySelector(`[data-step="${step}"]`).classList.add('active');
        document.querySelector(`.step[data-step="${step}"]`).classList.add('active');
        
        // Mark previous steps as completed
        for (let i = 1; i < step; i++) {
            document.querySelector(`.step[data-step="${i}"]`).classList.add('completed');
        }
        
        // Update navigation buttons
        prevBtn.style.display = step === 1 ? 'none' : 'inline-block';
        nextBtn.style.display = step === totalSteps ? 'none' : 'inline-block';
        submitBtn.style.display = step === totalSteps ? 'inline-block' : 'none';
    }
    
    function validateStep(step) {
        const stepContent = document.querySelector(`[data-step="${step}"]`);
        const requiredFields = stepContent.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        return isValid;
    }
    
    nextBtn.addEventListener('click', function() {
        if (validateStep(currentStep)) {
            currentStep++;
            showStep(currentStep);
        }
    });
    
    prevBtn.addEventListener('click', function() {
        currentStep--;
        showStep(currentStep);
    });
    
    // Image Upload Handling
    const imageInput = document.getElementById('property-images');
    const imagePreviewGrid = document.getElementById('image-preview-grid');
    let selectedImages = [];
    
    imageInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        files.forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    addImagePreview(file, e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    function addImagePreview(file, src) {
        selectedImages.push(file);
        
        const imageCol = document.createElement('div');
        imageCol.className = 'col-md-4 col-lg-3';
        imageCol.innerHTML = `
            <div class="position-relative">
                <img src="${src}" class="img-fluid rounded" style="height: 150px; object-fit: cover; width: 100%;" alt="Preview">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-image" 
                        data-index="${selectedImages.length - 1}">
                    <i class="fi-x"></i>
                </button>
                ${selectedImages.length === 1 ? '<span class="badge bg-primary position-absolute top-0 start-0 m-1">Primary</span>' : ''}
            </div>
        `;
        
        // Add remove functionality
        imageCol.querySelector('.remove-image').addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            selectedImages.splice(index, 1);
            imageCol.remove();
            updateImageInput();
            updatePrimaryBadges();
            
            if (selectedImages.length === 0) {
                imagePreviewGrid.style.display = 'none';
            }
        });
        
        imagePreviewGrid.appendChild(imageCol);
        imagePreviewGrid.style.display = 'block';
    }
    
    function updateImageInput() {
        const dt = new DataTransfer();
        selectedImages.forEach(file => {
            dt.items.add(file);
        });
        imageInput.files = dt.files;
    }
    
    function updatePrimaryBadges() {
        const badges = imagePreviewGrid.querySelectorAll('.badge');
        badges.forEach(badge => badge.remove());
        
        if (selectedImages.length > 0) {
            const firstImage = imagePreviewGrid.querySelector('.col-md-4');
            if (firstImage) {
                firstImage.querySelector('.position-relative').insertAdjacentHTML('beforeend', 
                    '<span class="badge bg-primary position-absolute top-0 start-0 m-1">Primary</span>'
                );
            }
        }
    }
    
    // Initialize
    showStep(currentStep);
});
</script>
@endsection
