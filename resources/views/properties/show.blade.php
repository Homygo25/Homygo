@extends('partials.master2')

@section('title')
{{ $property->title }} | Finder
@endsection

@section('css')
<!-- Vendor styles -->
<link rel="stylesheet" href="build/libs/glightbox/dist/css/glightbox.min.css">
<link rel="stylesheet" href="build/libs/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="build/libs/swiper/swiper-bundle.min.css">
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

  <!-- Tour booking form modal -->
  <div class="modal fade" id="tourBooking" tabindex="-1" aria-labelledby="tourBookingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px">
      <form class="modal-content needs-validation" novalidate>
        <div class="modal-header border-0">
          <h5 class="modal-title" id="tourBookingLabel">Schedule a tour</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pb-4 pt-0">
          <div class="vstack gap-3">
            <div class="position-relative">
              <i class="fi-calendar position-absolute top-50 start-0 translate-middle-y ms-3"></i>
              <input type="text" class="form-control form-icon-start bg-transparent" data-datepicker='{"dateFormat": "M j, Y"}' placeholder="Choose date *" required>
            </div>
            <div class="position-relative">
              <i class="fi-clock position-absolute top-50 start-0 translate-middle-y ms-3"></i>
              <input type="text" class="form-control form-icon-start" id="time-12" data-datepicker='{
                "enableTime": true,
                "noCalendar": true,
                "dateFormat": "h:i K"
              }' placeholder="Choose time *" required>
            </div>
            <input type="text" class="form-control" placeholder="Name *" required>
            <input type="email" class="form-control" placeholder="Email *" required>
            <input type="tel" class="form-control" placeholder="Phone number">
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="submit" class="btn btn-lg btn-primary w-100 m-0 mb-3">Schedule a tour</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Contact form modal -->
  <div class="modal fade" id="contactAgent" tabindex="-1" aria-labelledby="contactAgentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form action="{{ route('properties.contact', $property) }}" method="POST" class="modal-content needs-validation" novalidate>
        @csrf
        <div class="modal-header border-0">
          <h5 class="modal-title" id="contactAgentLabel">Contact Agent</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pb-4 pt-0">
          <div class="vstack gap-3">
            <input type="text" name="name" class="form-control" placeholder="Your name *" required>
            <input type="email" name="email" class="form-control" placeholder="Your email *" required>
            <input type="tel" name="phone" class="form-control" placeholder="Phone number">
            <textarea name="message" class="form-control" rows="4" placeholder="Your message *" required>I'm interested in {{ $property->title }}</textarea>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="submit" class="btn btn-lg btn-primary w-100 m-0">Send Message</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Property images -->
  @if($property->images && count($property->images) > 0)
  <section class="container-fluid p-0 mb-4 mb-lg-5">
    <div class="row g-0">
      <div class="col-lg-7">
        <div class="position-relative overflow-hidden bg-light rounded-end-3" style="height: 400px;">
          <img src="{{ asset('storage/' . $property->images[0]) }}" class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover;" alt="{{ $property->title }}">
          @if(count($property->images) > 1)
          <div class="position-absolute bottom-0 start-0 z-2 m-3">
            <a href="{{ asset('storage/' . $property->images[0]) }}" class="btn btn-light btn-icon btn-lg rounded-circle" data-gallery="property-gallery">
              <i class="fi-image"></i>
            </a>
          </div>
          @endif
        </div>
      </div>
      @if(count($property->images) > 1)
      <div class="col-lg-5">
        <div class="row g-0 h-100">
          @foreach(array_slice($property->images, 1, 4) as $index => $image)
          <div class="col-6">
            <div class="position-relative overflow-hidden bg-light {{ $index == 0 ? 'rounded-start-3-lg' : '' }} {{ $index == 1 ? 'rounded-end-3' : '' }} {{ $index == 3 ? 'rounded-end-3-lg' : '' }}" style="height: 200px;">
              <img src="{{ asset('storage/' . $image) }}" class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover;" alt="{{ $property->title }}">
              <a href="{{ asset('storage/' . $image) }}" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white text-decoration-none" data-gallery="property-gallery" style="background: rgba(0,0,0,.2);">
                @if($index == 3 && count($property->images) > 5)
                <div class="text-center">
                  <i class="fi-image fs-3 mb-2"></i>
                  <div>+{{ count($property->images) - 5 }} more</div>
                </div>
                @endif
              </a>
            </div>
          </div>
          @endforeach
        </div>
      </div>
      @endif
    </div>
    
    <!-- Hidden images for gallery -->
    @if(count($property->images) > 5)
      @foreach(array_slice($property->images, 5) as $image)
        <a href="{{ asset('storage/' . $image) }}" data-gallery="property-gallery" class="d-none"></a>
      @endforeach
    @endif
  </section>
  @endif

  <!-- Property details -->
  <section class="container mb-5 pb-2 pb-lg-4">
    <div class="row">
      
      <!-- Property info -->
      <div class="col-lg-8 mb-md-4 mb-lg-0">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-3">
            <li class="breadcrumb-item">
              <a href="{{ route('home') }}">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a href="{{ route('properties.index') }}">Properties</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ $property->title }}</li>
          </ol>
        </nav>

        <!-- Title and badges -->
        <div class="d-sm-flex align-items-start justify-content-between mb-4">
          <div class="me-sm-3 mb-3 mb-sm-0">
            <h1 class="h2 text-primary mb-1">${{ number_format($property->price) }}</h1>
            <h2 class="h4 mb-2">{{ $property->title }}</h2>
            <p class="text-body-secondary mb-2">
              <i class="fi-map-pin me-1"></i>
              {{ $property->address }}
            </p>
            <div class="d-flex flex-wrap gap-2">
              <span class="badge bg-primary">{{ ucfirst($property->property_type) }}</span>
              @if($property->category)
                <span class="badge bg-secondary">{{ $property->category->name }}</span>
              @endif
              @if($property->is_featured)
                <span class="badge bg-warning text-dark">Featured</span>
              @endif
              <span class="badge bg-success">{{ ucfirst($property->status) }}</span>
            </div>
          </div>
          
          <!-- Action buttons -->
          <div class="d-flex gap-2 flex-nowrap">
            @auth
            <button type="button" class="btn btn-outline-primary btn-icon{{ $property->is_favorited_by_user ? ' active' : '' }}" 
                    onclick="toggleFavorite('{{ $property->id }}', this)"
                    data-bs-toggle="tooltip" title="Add to favorites">
              <i class="fi-heart"></i>
            </button>
            @endauth
            <button type="button" class="btn btn-outline-primary btn-icon" data-bs-toggle="tooltip" title="Share">
              <i class="fi-share"></i>
            </button>
          </div>
        </div>

        <!-- Property specs -->
        <div class="row row-cols-2 row-cols-sm-4 g-3 g-sm-4 mb-4 pb-2">
          @if($property->bedrooms)
          <div class="col">
            <div class="bg-secondary rounded-3 text-center p-3">
              <i class="fi-bed text-primary fs-3 mb-2"></i>
              <div class="h6 mb-1">{{ $property->bedrooms }}</div>
              <div class="fs-sm text-body-secondary">Bedroom{{ $property->bedrooms > 1 ? 's' : '' }}</div>
            </div>
          </div>
          @endif
          @if($property->bathrooms)
          <div class="col">
            <div class="bg-secondary rounded-3 text-center p-3">
              <i class="fi-bath text-primary fs-3 mb-2"></i>
              <div class="h6 mb-1">{{ $property->bathrooms }}</div>
              <div class="fs-sm text-body-secondary">Bathroom{{ $property->bathrooms > 1 ? 's' : '' }}</div>
            </div>
          </div>
          @endif
          @if($property->area)
          <div class="col">
            <div class="bg-secondary rounded-3 text-center p-3">
              <i class="fi-calculator text-primary fs-3 mb-2"></i>
              <div class="h6 mb-1">{{ number_format($property->area) }}</div>
              <div class="fs-sm text-body-secondary">Sq ft</div>
            </div>
          </div>
          @endif
          @if($property->garage_spaces)
          <div class="col">
            <div class="bg-secondary rounded-3 text-center p-3">
              <i class="fi-car text-primary fs-3 mb-2"></i>
              <div class="h6 mb-1">{{ $property->garage_spaces }}</div>
              <div class="fs-sm text-body-secondary">Garage</div>
            </div>
          </div>
          @endif
        </div>

        <!-- Description -->
        <div class="mb-4 pb-md-3">
          <h3 class="h5 mb-3">Overview</h3>
          <p class="mb-0">{{ $property->description }}</p>
        </div>

        <!-- Features -->
        @if($property->features && count($property->features) > 0)
        <div class="mb-4 pb-md-3">
          <h3 class="h5 mb-3">Property Features</h3>
          <div class="row row-cols-2 row-cols-md-3 g-2">
            @foreach($property->features as $feature)
            <div class="col">
              <div class="d-flex align-items-center">
                <i class="fi-check-circle text-primary me-2"></i>
                <span>{{ $feature }}</span>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endif

        <!-- Reviews -->
        <div class="mb-4 pb-md-3">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="h5 mb-0">Reviews</h3>
            @auth
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addReview">
              Add Review
            </button>
            @endauth
          </div>
          
          @if($property->reviews && $property->reviews->count() > 0)
          <div class="mb-3">
            <div class="d-flex align-items-center mb-2">
              <div class="me-3">
                <div class="h4 mb-0">{{ number_format($property->avg_rating, 1) }}</div>
                <div class="fs-sm text-body-secondary">{{ $property->reviews->count() }} review{{ $property->reviews->count() > 1 ? 's' : '' }}</div>
              </div>
              <div class="text-warning">
                @for($i = 1; $i <= 5; $i++)
                  <i class="fi-star{{ $i <= $property->avg_rating ? '-filled' : '' }}"></i>
                @endfor
              </div>
            </div>
          </div>
          
          @foreach($property->reviews->take(3) as $review)
          <div class="border-bottom pb-3 mb-3">
            <div class="d-flex align-items-start">
              <div class="flex-shrink-0 me-3">
                @if($review->user->profile && $review->user->profile->avatar)
                  <img src="{{ asset('storage/' . $review->user->profile->avatar) }}" class="rounded-circle" width="48" height="48" alt="{{ $review->user->name }}">
                @else
                  <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="fi-user text-body-secondary"></i>
                  </div>
                @endif
              </div>
              <div class="flex-grow-1">
                <div class="d-flex align-items-center mb-1">
                  <h6 class="mb-0 me-3">{{ $review->user->name }}</h6>
                  <div class="text-warning">
                    @for($i = 1; $i <= 5; $i++)
                      <i class="fi-star{{ $i <= $review->rating ? '-filled' : '' }} fs-sm"></i>
                    @endfor
                  </div>
                </div>
                <p class="text-body-secondary fs-sm mb-1">{{ $review->created_at->format('M j, Y') }}</p>
                <p class="mb-0">{{ $review->comment }}</p>
              </div>
            </div>
          </div>
          @endforeach
          
          @if($property->reviews->count() > 3)
          <button type="button" class="btn btn-outline-secondary">View all reviews</button>
          @endif
          @else
          <p class="text-body-secondary">No reviews yet. Be the first to review this property!</p>
          @endif
        </div>

      </div>

      <!-- Sidebar -->
      <div class="col-lg-4">
        <div class="ps-lg-4">
          
          <!-- Agent card -->
          @if($property->user)
          <div class="card border-0 bg-secondary mb-4">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                @if($property->user->profile && $property->user->profile->avatar)
                  <img src="{{ asset('storage/' . $property->user->profile->avatar) }}" class="rounded-circle me-3" width="60" height="60" alt="{{ $property->user->name }}">
                @else
                  <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                    <i class="fi-user text-white fs-4"></i>
                  </div>
                @endif
                <div>
                  <h6 class="mb-1">{{ $property->user->name }}</h6>
                  <p class="text-body-secondary fs-sm mb-0">Property Agent</p>
                  @if($property->user->profile && $property->user->profile->phone)
                    <p class="text-body-secondary fs-sm mb-0">{{ $property->user->profile->phone }}</p>
                  @endif
                </div>
              </div>
              <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactAgent">
                  <i class="fi-chat-left me-2"></i>
                  Contact Agent
                </button>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tourBooking">
                  <i class="fi-calendar me-2"></i>
                  Schedule Tour
                </button>
              </div>
            </div>
          </div>
          @endif

          <!-- Property details -->
          <div class="card border-0">
            <div class="card-header bg-transparent border-0 pb-0">
              <h6 class="mb-0">Property Details</h6>
            </div>
            <div class="card-body pt-1">
              <div class="vstack gap-2">
                <div class="d-flex justify-content-between">
                  <span class="text-body-secondary">Property ID:</span>
                  <span>{{ $property->id }}</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-body-secondary">Property Type:</span>
                  <span>{{ ucfirst($property->property_type) }}</span>
                </div>
                @if($property->year_built)
                <div class="d-flex justify-content-between">
                  <span class="text-body-secondary">Year Built:</span>
                  <span>{{ $property->year_built }}</span>
                </div>
                @endif
                @if($property->lot_size)
                <div class="d-flex justify-content-between">
                  <span class="text-body-secondary">Lot Size:</span>
                  <span>{{ number_format($property->lot_size) }} sqft</span>
                </div>
                @endif
                <div class="d-flex justify-content-between">
                  <span class="text-body-secondary">Status:</span>
                  <span class="badge bg-success">{{ ucfirst($property->status) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-body-secondary">Listed:</span>
                  <span>{{ $property->created_at->format('M j, Y') }}</span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>

  <!-- Similar properties -->
  @if(isset($similarProperties) && $similarProperties->count() > 0)
  <section class="container mb-5 pb-2 pb-lg-4">
    <h2 class="h3 mb-4">Similar Properties</h2>
    <div class="row gy-4 gx-3">
      @foreach($similarProperties as $similar)
      <div class="col-md-6 col-xl-4">
        @include('properties.partials.card', ['property' => $similar])
      </div>
      @endforeach
    </div>
  </section>
  @endif

</main>

<!-- Add Review Modal -->
@auth
<div class="modal fade" id="addReview" tabindex="-1" aria-labelledby="addReviewLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('properties.review', $property) }}" method="POST" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title" id="addReviewLabel">Add Your Review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Rating</label>
          <div class="rating-input">
            @for($i = 5; $i >= 1; $i--)
            <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}" required>
            <label for="star{{ $i }}" class="text-warning">
              <i class="fi-star"></i>
            </label>
            @endfor
          </div>
        </div>
        <div class="mb-3">
          <label for="comment" class="form-label">Your Review</label>
          <textarea name="comment" id="comment" class="form-control" rows="4" placeholder="Share your experience with this property..." required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Submit Review</button>
      </div>
    </form>
  </div>
</div>
@endauth

@endsection

@section('scripts')
<!-- Vendor scripts -->
<script src="build/libs/glightbox/dist/js/glightbox.min.js"></script>
<script src="build/libs/flatpickr/dist/flatpickr.min.js"></script>

<script>
// Initialize gallery
if (typeof GLightbox !== 'undefined') {
  GLightbox({
    selector: '[data-gallery="property-gallery"]'
  });
}

// Initialize date picker
flatpickr('[data-datepicker]', {
  enableTime: false,
  dateFormat: "M j, Y",
  minDate: "today"
});

// Initialize time picker
flatpickr('#time-12', {
  enableTime: true,
  noCalendar: true,
  dateFormat: "h:i K"
});

// Toggle favorite
function toggleFavorite(propertyId, button) {
  fetch('/api/v1/properties/' + propertyId + '/favorite', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.favorited) {
      button.classList.add('active');
    } else {
      button.classList.remove('active');
    }
  })
  .catch(error => {
    console.error('Error:', error);
  });
}

// Rating input styling
document.addEventListener('DOMContentLoaded', function() {
  const ratingInputs = document.querySelectorAll('.rating-input input[type="radio"]');
  const ratingLabels = document.querySelectorAll('.rating-input label');
  
  ratingLabels.forEach((label, index) => {
    label.addEventListener('mouseenter', function() {
      for (let i = 0; i <= index; i++) {
        ratingLabels[ratingLabels.length - 1 - i].classList.add('hovered');
      }
    });
    
    label.addEventListener('mouseleave', function() {
      ratingLabels.forEach(l => l.classList.remove('hovered'));
    });
    
    label.addEventListener('click', function() {
      ratingLabels.forEach(l => l.classList.remove('active'));
      for (let i = 0; i <= index; i++) {
        ratingLabels[ratingLabels.length - 1 - i].classList.add('active');
      }
    });
  });
});
</script>

<style>
.rating-input {
  display: flex;
  flex-direction: row-reverse;
  justify-content: flex-end;
}

.rating-input input[type="radio"] {
  display: none;
}

.rating-input label {
  cursor: pointer;
  font-size: 1.25rem;
  color: #dee2e6;
  margin: 0 2px;
  transition: color 0.2s;
}

.rating-input label:hover,
.rating-input label.hovered {
  color: #ffc107;
}

.rating-input input[type="radio"]:checked ~ label,
.rating-input label.active {
  color: #ffc107;
}
</style>

@endsection
