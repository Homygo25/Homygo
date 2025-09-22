@extends('layouts.main')

@section('title', 'Saved Searches - Dashboard')

@section('content')
<main class="content-wrapper">
    <!-- Header -->
    <section class="bg-light py-4">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-3">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Saved Searches</li>
                </ol>
            </nav>
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="h3 mb-2">Saved Searches</h1>
                    <p class="text-muted mb-0">Manage your search alerts and get notified of new matching properties</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('properties.index') }}" class="btn btn-primary">
                        <i class="fi-plus me-1"></i>Create New Search
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="container py-4">
        <!-- Search Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3 mx-auto mb-2" style="width: fit-content;">
                            <i class="fi-search text-primary fs-4"></i>
                        </div>
                        <h4 class="mb-1">{{ $savedSearches->count() }}</h4>
                        <p class="text-muted mb-0">Total Searches</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3 mx-auto mb-2" style="width: fit-content;">
                            <i class="fi-bell text-success fs-4"></i>
                        </div>
                        <h4 class="mb-1">{{ $savedSearches->where('notifications_enabled', true)->count() }}</h4>
                        <p class="text-muted mb-0">Active Alerts</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3 mx-auto mb-2" style="width: fit-content;">
                            <i class="fi-trending-up text-info fs-4"></i>
                        </div>
                        <h4 class="mb-1">{{ $totalMatches ?? 0 }}</h4>
                        <p class="text-muted mb-0">Total Matches</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3 mx-auto mb-2" style="width: fit-content;">
                            <i class="fi-clock text-warning fs-4"></i>
                        </div>
                        <h4 class="mb-1">{{ $newMatchesToday ?? 0 }}</h4>
                        <p class="text-muted mb-0">Today's Matches</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Saved Searches List -->
        @if($savedSearches->count() > 0)
            <div class="row g-4">
                @foreach($savedSearches as $search)
                    <div class="col-lg-6">
                        <div class="card h-100 search-card {{ $search->notifications_enabled ? 'border-success' : 'border-secondary' }}">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">{{ $search->name }}</h5>
                                <div class="d-flex align-items-center">
                                    @if($search->notifications_enabled)
                                        <span class="badge bg-success me-2">
                                            <i class="fi-bell me-1"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary me-2">
                                            <i class="fi-bell-off me-1"></i>Paused
                                        </span>
                                    @endif
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fi-more-horizontal"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ $search->search_url }}">
                                                <i class="fi-search me-2"></i>Run Search
                                            </a></li>
                                            <li><button class="dropdown-item" onclick="editSearch({{ $search->id }})">
                                                <i class="fi-edit me-2"></i>Edit
                                            </button></li>
                                            <li><button class="dropdown-item" onclick="toggleNotifications({{ $search->id }}, {{ $search->notifications_enabled ? 'false' : 'true' }})">
                                                <i class="fi-bell{{ $search->notifications_enabled ? '-off' : '' }} me-2"></i>
                                                {{ $search->notifications_enabled ? 'Disable' : 'Enable' }} Alerts
                                            </button></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><button class="dropdown-item text-danger" onclick="deleteSearch({{ $search->id }})">
                                                <i class="fi-trash-2 me-2"></i>Delete
                                            </button></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Search Criteria -->
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Search Criteria</h6>
                                    <div class="row g-2">
                                        @if($search->location)
                                            <div class="col-auto">
                                                <span class="badge bg-light text-dark">
                                                    <i class="fi-map-pin me-1"></i>{{ $search->location }}
                                                </span>
                                            </div>
                                        @endif
                                        @if($search->property_type)
                                            <div class="col-auto">
                                                <span class="badge bg-light text-dark">
                                                    <i class="fi-home me-1"></i>{{ ucfirst($search->property_type) }}
                                                </span>
                                            </div>
                                        @endif
                                        @if($search->min_price || $search->max_price)
                                            <div class="col-auto">
                                                <span class="badge bg-light text-dark">
                                                    <i class="fi-dollar-sign me-1"></i>
                                                    @if($search->min_price && $search->max_price)
                                                        ₱{{ number_format($search->min_price) }} - ₱{{ number_format($search->max_price) }}
                                                    @elseif($search->min_price)
                                                        From ₱{{ number_format($search->min_price) }}
                                                    @else
                                                        Up to ₱{{ number_format($search->max_price) }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endif
                                        @if($search->bedrooms)
                                            <div class="col-auto">
                                                <span class="badge bg-light text-dark">
                                                    <i class="fi-bed me-1"></i>{{ $search->bedrooms }}+ beds
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Search Statistics -->
                                <div class="row g-3 mb-3">
                                    <div class="col-4 text-center">
                                        <h6 class="text-primary mb-1">{{ $search->total_matches ?? 0 }}</h6>
                                        <small class="text-muted">Total Matches</small>
                                    </div>
                                    <div class="col-4 text-center">
                                        <h6 class="text-success mb-1">{{ $search->new_matches ?? 0 }}</h6>
                                        <small class="text-muted">New This Week</small>
                                    </div>
                                    <div class="col-4 text-center">
                                        <h6 class="text-info mb-1">{{ $search->last_run ? $search->last_run->diffForHumans() : 'Never' }}</h6>
                                        <small class="text-muted">Last Run</small>
                                    </div>
                                </div>

                                <!-- Email Frequency -->
                                @if($search->notifications_enabled)
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fi-mail me-1"></i>
                                            Email alerts: {{ ucfirst($search->email_frequency) }}
                                        </small>
                                        @if($search->last_notification_sent)
                                            <br><small class="text-muted">
                                                Last alert: {{ $search->last_notification_sent->diffForHumans() }}
                                            </small>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Created {{ $search->created_at->diffForHumans() }}
                                    </small>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ $search->search_url }}" class="btn btn-primary">
                                            <i class="fi-search me-1"></i>Run Search
                                        </a>
                                        @if($search->new_matches > 0)
                                            <button class="btn btn-success" onclick="viewNewMatches({{ $search->id }})">
                                                <i class="fi-eye me-1"></i>View New ({{ $search->new_matches }})
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($savedSearches->hasPages())
                <div class="mt-4">
                    {{ $savedSearches->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fi-search text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 mb-2">No saved searches yet</h4>
                    <p class="text-muted mb-4">Create your first saved search to get notified when new properties match your criteria in Cagayan de Oro.</p>
                    <a href="{{ route('properties.index') }}" class="btn btn-primary">
                        <i class="fi-plus me-2"></i>Create Your First Search
                    </a>
                </div>
            </div>
        @endif

        <!-- Tips Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fi-info-circle me-2"></i>Search Alert Tips
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-2">
                                    <i class="fi-bell text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Stay Informed</h6>
                                <p class="text-muted mb-0">Get email alerts when properties matching your criteria are listed.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded-3 p-2">
                                    <i class="fi-settings text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Customize Frequency</h6>
                                <p class="text-muted mb-0">Choose how often you want to receive notifications: instant, daily, or weekly.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-3 p-2">
                                    <i class="fi-trending-up text-info"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Market Insights</h6>
                                <p class="text-muted mb-0">Track price trends and availability in your preferred areas.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Edit Search Modal -->
<div class="modal fade" id="editSearchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Saved Search</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSearchForm">
                <div class="modal-body">
                    <input type="hidden" id="searchId" name="search_id">
                    
                    <div class="mb-3">
                        <label for="searchName" class="form-label">Search Name</label>
                        <input type="text" class="form-control" id="searchName" name="name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="searchLocation" class="form-label">Location</label>
                                <select class="form-select" id="searchLocation" name="location">
                                    <option value="">Any Location</option>
                                    <option value="Downtown">Downtown CDO</option>
                                    <option value="Lapasan">Lapasan</option>
                                    <option value="Carmen">Carmen</option>
                                    <option value="Gusa">Gusa</option>
                                    <option value="Kauswagan">Kauswagan</option>
                                    <option value="Macasandig">Macasandig</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="searchPropertyType" class="form-label">Property Type</label>
                                <select class="form-select" id="searchPropertyType" name="property_type">
                                    <option value="">Any Type</option>
                                    <option value="house">House</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="condo">Condominium</option>
                                    <option value="lot">Lot</option>
                                    <option value="commercial">Commercial</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="searchMinPrice" class="form-label">Min Price (₱)</label>
                                <input type="number" class="form-control" id="searchMinPrice" name="min_price" min="0" step="1000">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="searchMaxPrice" class="form-label">Max Price (₱)</label>
                                <input type="number" class="form-control" id="searchMaxPrice" name="max_price" min="0" step="1000">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="searchBedrooms" class="form-label">Minimum Bedrooms</label>
                                <select class="form-select" id="searchBedrooms" name="bedrooms">
                                    <option value="">Any</option>
                                    <option value="1">1+</option>
                                    <option value="2">2+</option>
                                    <option value="3">3+</option>
                                    <option value="4">4+</option>
                                    <option value="5">5+</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="searchEmailFrequency" class="form-label">Email Frequency</label>
                                <select class="form-select" id="searchEmailFrequency" name="email_frequency">
                                    <option value="instant">Instant</option>
                                    <option value="daily">Daily Summary</option>
                                    <option value="weekly">Weekly Summary</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="searchNotifications" name="notifications_enabled">
                            <label class="form-check-label" for="searchNotifications">
                                Enable email notifications for this search
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.search-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.search-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.search-card.border-success {
    border-width: 2px !important;
}
</style>

<script>
function editSearch(searchId) {
    fetch(`/dashboard/saved-searches/${searchId}`)
        .then(response => response.json())
        .then(data => {
            // Populate form fields
            document.getElementById('searchId').value = data.id;
            document.getElementById('searchName').value = data.name;
            document.getElementById('searchLocation').value = data.location || '';
            document.getElementById('searchPropertyType').value = data.property_type || '';
            document.getElementById('searchMinPrice').value = data.min_price || '';
            document.getElementById('searchMaxPrice').value = data.max_price || '';
            document.getElementById('searchBedrooms').value = data.bedrooms || '';
            document.getElementById('searchEmailFrequency').value = data.email_frequency || 'daily';
            document.getElementById('searchNotifications').checked = data.notifications_enabled;
            
            new bootstrap.Modal(document.getElementById('editSearchModal')).show();
        })
        .catch(error => console.error('Error:', error));
}

function toggleNotifications(searchId, enabled) {
    fetch(`/dashboard/saved-searches/${searchId}/notifications`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ notifications_enabled: enabled === 'true' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteSearch(searchId) {
    if (confirm('Are you sure you want to delete this saved search? This action cannot be undone.')) {
        fetch(`/dashboard/saved-searches/${searchId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function viewNewMatches(searchId) {
    // Redirect to properties search with this saved search criteria
    window.open(`/dashboard/saved-searches/${searchId}/matches`, '_blank');
}

// Handle edit form submission
document.getElementById('editSearchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const searchId = formData.get('search_id');
    
    fetch(`/dashboard/saved-searches/${searchId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editSearchModal')).hide();
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>
@endsection