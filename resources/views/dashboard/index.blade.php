@extends('layouts.main')

@section('title', 'Dashboard - Homygo CDO')

@section('content')
<main class="content-wrapper">
    <!-- Dashboard Header -->
    <section class="bg-primary text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="h2 mb-2">Welcome back, {{ $user->full_name }}!</h1>
                    <p class="mb-0">Manage your properties and track your real estate activity in Cagayan de Oro</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex gap-2 justify-content-lg-end justify-content-start mt-3 mt-lg-0">
                        <a href="{{ route('properties.create') }}" class="btn btn-light btn-sm">
                            <i class="fi-plus me-1"></i>Add Property
                        </a>
                        <a href="{{ route('properties.index') }}" class="btn btn-outline-light btn-sm">
                            <i class="fi-search me-1"></i>Browse Properties
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dashboard Stats -->
    <section class="container py-4">
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                    <i class="fi-home text-primary fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="fs-sm text-muted mb-1">My Properties</h6>
                                <h3 class="mb-0">{{ $stats['active_listings'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                    <i class="fi-eye text-success fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="fs-sm text-muted mb-1">Total Views</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_views'] ?? 0) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                    <i class="fi-mail text-warning fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="fs-sm text-muted mb-1">Inquiries</h6>
                                <h3 class="mb-0">{{ $stats['total_inquiries'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                    <i class="fi-heart text-info fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="fs-sm text-muted mb-1">Favorites</h6>
                                <h3 class="mb-0">{{ $stats['favorite_properties'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Recent Activity -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Recent Activity</h5>
                        <small class="text-muted">Last 30 days</small>
                    </div>
                    <div class="card-body">
                        @if($recentActivity && $recentActivity->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($recentActivity->take(5) as $activity)
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                @switch($activity['type'])
                                                    @case('view')
                                                        <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                                            <i class="fi-eye text-primary"></i>
                                                        </div>
                                                        @break
                                                    @case('inquiry')
                                                        <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                                                            <i class="fi-mail text-warning"></i>
                                                        </div>
                                                        @break
                                                    @default
                                                        <div class="bg-secondary bg-opacity-10 rounded-circle p-2">
                                                            <i class="fi-star text-secondary"></i>
                                                        </div>
                                                @endswitch
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <p class="mb-1">{{ $activity['message'] }}</p>
                                                <small class="text-muted">{{ $activity['created_at']->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fi-clock text-muted fs-1 mb-3"></i>
                                <p class="text-muted">No recent activity to show.</p>
                                <p class="text-muted">Start browsing properties to see your activity here.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- My Properties (for sellers/agents) -->
                @if($myProperties && $myProperties->count() > 0)
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">My Properties</h5>
                        <a href="{{ route('account.properties') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($myProperties->take(3) as $property)
                                <div class="col-md-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="position-relative">
                                            @if($property->images->count() > 0)
                                                <img src="{{ asset('storage/' . $property->images->first()->path) }}" 
                                                     class="card-img-top" alt="{{ $property->title }}" style="height: 150px; object-fit: cover;">
                                            @else
                                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                                    <i class="fi-home text-muted fs-1"></i>
                                                </div>
                                            @endif
                                            <span class="badge bg-primary position-absolute top-0 start-0 m-2">
                                                {{ ucfirst($property->property_type) }}
                                            </span>
                                        </div>
                                        <div class="card-body p-3">
                                            <h6 class="card-title mb-2">{{ Str::limit($property->title, 30) }}</h6>
                                            <p class="text-primary fw-bold mb-2">₱{{ number_format($property->price, 2) }}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">{{ $property->views }} views</small>
                                                <a href="{{ route('dashboard.property-analytics', $property) }}" class="btn btn-sm btn-outline-primary">
                                                    Analytics
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Favorite Properties -->
                @if($favoriteProperties && $favoriteProperties->count() > 0)
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Favorite Properties</h5>
                        <a href="{{ route('account.favorites') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($favoriteProperties->take(3) as $favorite)
                                <div class="col-md-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="position-relative">
                                            @if($favorite->property->images->count() > 0)
                                                <img src="{{ asset('storage/' . $favorite->property->images->first()->path) }}" 
                                                     class="card-img-top" alt="{{ $favorite->property->title }}" style="height: 150px; object-fit: cover;">
                                            @else
                                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                                    <i class="fi-home text-muted fs-1"></i>
                                                </div>
                                            @endif
                                            <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2" onclick="toggleFavorite({{ $favorite->property->id }})">
                                                <i class="fi-heart text-danger"></i>
                                            </button>
                                        </div>
                                        <div class="card-body p-3">
                                            <h6 class="card-title mb-2">{{ Str::limit($favorite->property->title, 30) }}</h6>
                                            <p class="text-primary fw-bold mb-2">₱{{ number_format($favorite->property->price, 2) }}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">{{ $favorite->property->location->name ?? 'CDO' }}</small>
                                                <a href="{{ route('properties.show', $favorite->property) }}" class="btn btn-sm btn-primary">
                                                    View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Notifications -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0">Notifications</h6>
                        @if($stats['unread_notifications'] > 0)
                            <span class="badge bg-danger">{{ $stats['unread_notifications'] }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($notifications && $notifications->count() > 0)
                            @foreach($notifications->take(3) as $notification)
                                <div class="d-flex mb-3 {{ $notification->isRead() ? 'opacity-75' : '' }}">
                                    <div class="flex-shrink-0">
                                        <div class="bg-{{ $notification->priority_color }} bg-opacity-10 rounded-circle p-2">
                                            <i class="fi-bell text-{{ $notification->priority_color }}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fs-sm mb-1">{{ $notification->title }}</h6>
                                        <p class="fs-sm text-muted mb-1">{{ Str::limit($notification->message, 60) }}</p>
                                        <small class="text-muted">{{ $notification->time_ago }}</small>
                                    </div>
                                </div>
                            @endforeach
                            <div class="text-center">
                                <a href="{{ route('dashboard.notifications') }}" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="fi-bell text-muted fs-2 mb-2"></i>
                                <p class="text-muted mb-0">No notifications</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Saved Searches -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0">Saved Searches</h6>
                        <span class="text-muted">{{ $stats['saved_searches'] }}</span>
                    </div>
                    <div class="card-body">
                        @if($savedSearches && $savedSearches->count() > 0)
                            @foreach($savedSearches->take(3) as $search)
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-grow-1">
                                        <h6 class="fs-sm mb-1">{{ $search->name }}</h6>
                                        <p class="fs-xs text-muted mb-0">{{ $search->formatted_criteria }}</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <a href="{{ $search->search_url }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fi-search"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                            <div class="text-center">
                                <a href="{{ route('dashboard.saved-searches') }}" class="btn btn-sm btn-outline-primary">Manage All</a>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="fi-search text-muted fs-2 mb-2"></i>
                                <p class="text-muted mb-2">No saved searches</p>
                                <a href="{{ route('properties.index') }}" class="btn btn-sm btn-primary">Start Searching</a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('properties.create') }}" class="btn btn-primary btn-sm">
                                <i class="fi-plus me-2"></i>Add New Property
                            </a>
                            <a href="{{ route('properties.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fi-search me-2"></i>Search Properties
                            </a>
                            <a href="{{ route('account.profile') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fi-user me-2"></i>Edit Profile
                            </a>
                            <a href="{{ route('dashboard.notifications') }}" class="btn btn-outline-info btn-sm">
                                <i class="fi-bell me-2"></i>All Notifications
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- JavaScript for interactive features -->
<script>
function toggleFavorite(propertyId) {
    fetch(`/favorites/${propertyId}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to update the UI
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Auto-refresh dashboard stats every 5 minutes
setInterval(function() {
    fetch('/dashboard/api/data')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stats if needed
                console.log('Dashboard data refreshed');
            }
        })
        .catch(error => console.error('Error refreshing dashboard:', error));
}, 300000); // 5 minutes
</script>
@endsection