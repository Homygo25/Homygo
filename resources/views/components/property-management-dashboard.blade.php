{{-- Property Management Dashboard Component --}}
@props([
    'user' => null,
    'properties' => [],
    'stats' => null
])

<div class="property-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header bg-primary text-white rounded-3 p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1">
                    <i class="fi-home me-2"></i>Property Dashboard
                </h2>
                <p class="mb-0 opacity-75">Manage your property listings and track performance</p>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
                    <i class="fi-plus me-2"></i>Add Property
                </button>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2">
                        <i class="fi-home"></i>
                    </div>
                    <h3 class="h2 mb-1" id="total-properties">{{ count($properties) }}</h3>
                    <p class="text-muted mb-0">Total Properties</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2">
                        <i class="fi-check-circle"></i>
                    </div>
                    <h3 class="h2 mb-1" id="active-properties">{{ collect($properties)->where('status', 'active')->count() }}</h3>
                    <p class="text-muted mb-0">Active Listings</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2">
                        <i class="fi-clock"></i>
                    </div>
                    <h3 class="h2 mb-1" id="pending-properties">{{ collect($properties)->where('status', 'pending')->count() }}</h3>
                    <p class="text-muted mb-0">Pending Review</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2">
                        <i class="fi-eye"></i>
                    </div>
                    <h3 class="h2 mb-1" id="total-views">{{ $stats['total_views'] ?? '0' }}</h3>
                    <p class="text-muted mb-0">Total Views</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters and Actions -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label for="property-status-filter" class="form-label">Filter by Status</label>
                    <select class="form-select" id="property-status-filter">
                        <option value="">All Properties</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="draft">Draft</option>
                        <option value="sold">Sold</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="property-type-filter" class="form-label">Filter by Type</label>
                    <select class="form-select" id="property-type-filter">
                        <option value="">All Types</option>
                        <option value="apartment">Apartment</option>
                        <option value="house">House</option>
                        <option value="condo">Condo</option>
                        <option value="townhouse">Townhouse</option>
                        <option value="commercial">Commercial</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="property-sort" class="form-label">Sort by</label>
                    <select class="form-select" id="property-sort">
                        <option value="created_desc">Newest First</option>
                        <option value="created_asc">Oldest First</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="views_desc">Most Viewed</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary flex-fill" id="export-properties">
                            <i class="fi-download me-1"></i>Export
                        </button>
                        <button class="btn btn-outline-secondary flex-fill" id="bulk-actions-toggle">
                            <i class="fi-check-square me-1"></i>Bulk
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Properties List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Your Properties</h5>
                </div>
                <div class="col-auto">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary active" id="view-list">
                            <i class="fi-list"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="view-grid">
                            <i class="fi-grid"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <!-- Bulk Actions Bar (Hidden by default) -->
            <div class="bulk-actions-bar d-none p-3 bg-light border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <span class="selected-count">0 selected</span>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-success" id="bulk-activate">
                                <i class="fi-check me-1"></i>Activate
                            </button>
                            <button class="btn btn-sm btn-warning" id="bulk-deactivate">
                                <i class="fi-pause me-1"></i>Deactivate
                            </button>
                            <button class="btn btn-sm btn-danger" id="bulk-delete">
                                <i class="fi-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Properties Table View -->
            <div id="properties-list-view">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="bulk-checkbox d-none">
                                    <input type="checkbox" class="form-check-input" id="select-all">
                                </th>
                                <th>Property</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="properties-table-body">
                            @forelse($properties as $property)
                            <tr data-property-id="{{ $property['id'] ?? uniqid() }}" 
                                data-status="{{ $property['status'] ?? 'draft' }}" 
                                data-type="{{ $property['type'] ?? 'apartment' }}">
                                <td class="bulk-checkbox d-none">
                                    <input type="checkbox" class="form-check-input property-checkbox" 
                                           value="{{ $property['id'] ?? uniqid() }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $property['image'] ?? 'https://via.placeholder.com/60x40' }}" 
                                             alt="Property" class="rounded me-3" style="width: 60px; height: 40px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-1">{{ $property['title'] ?? 'Untitled Property' }}</h6>
                                            <small class="text-muted">{{ $property['address'] ?? 'No address' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($property['type'] ?? 'apartment') }}</span>
                                </td>
                                <td>
                                    <strong class="text-primary">${{ number_format($property['price'] ?? 0) }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $property['status'] === 'active' ? 'success' : ($property['status'] === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($property['status'] ?? 'draft') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $property['views'] ?? 0 }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ isset($property['created_at']) ? date('M j, Y', strtotime($property['created_at'])) : 'N/A' }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" title="Edit">
                                            <i class="fi-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-info" title="View">
                                            <i class="fi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fi-more-horizontal"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#"><i class="fi-copy me-2"></i>Duplicate</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="fi-share me-2"></i>Share</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="fi-bar-chart me-2"></i>Analytics</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#"><i class="fi-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fi-home display-4 mb-3"></i>
                                        <p>No properties found. <a href="#" data-bs-toggle="modal" data-bs-target="#addPropertyModal">Add your first property</a></p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Properties Grid View (Hidden by default) -->
            <div id="properties-grid-view" class="d-none p-3">
                <div class="row g-4" id="properties-grid-container">
                    @foreach($properties as $property)
                    <div class="col-md-6 col-lg-4" data-property-id="{{ $property['id'] ?? uniqid() }}" 
                         data-status="{{ $property['status'] ?? 'draft' }}" 
                         data-type="{{ $property['type'] ?? 'apartment' }}">
                        <div class="card h-100 property-card">
                            <div class="position-relative">
                                <img src="{{ $property['image'] ?? 'https://via.placeholder.com/400x250' }}" 
                                     class="card-img-top" alt="Property" style="height: 200px; object-fit: cover;">
                                <div class="position-absolute top-0 start-0 p-2">
                                    <span class="badge bg-{{ $property['status'] === 'active' ? 'success' : ($property['status'] === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($property['status'] ?? 'draft') }}
                                    </span>
                                </div>
                                <div class="position-absolute top-0 end-0 p-2 bulk-checkbox d-none">
                                    <input type="checkbox" class="form-check-input property-checkbox" 
                                           value="{{ $property['id'] ?? uniqid() }}">
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title">{{ $property['title'] ?? 'Untitled Property' }}</h6>
                                <p class="card-text text-muted small">{{ $property['address'] ?? 'No address' }}</p>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="text-primary">${{ number_format($property['price'] ?? 0) }}</strong>
                                    <small class="text-muted">{{ $property['views'] ?? 0 }} views</small>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="btn-group w-100">
                                    <button class="btn btn-outline-primary btn-sm">
                                        <i class="fi-edit me-1"></i>Edit
                                    </button>
                                    <button class="btn btn-outline-info btn-sm">
                                        <i class="fi-eye me-1"></i>View
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fi-more-horizontal"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#"><i class="fi-copy me-2"></i>Duplicate</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fi-share me-2"></i>Share</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fi-bar-chart me-2"></i>Analytics</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#"><i class="fi-trash me-2"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="card-footer bg-light">
            <nav aria-label="Properties pagination">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Add Property Modal -->
<div class="modal fade" id="addPropertyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fi-plus me-2"></i>Add New Property
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4">
                    <i class="fi-home display-4 text-primary mb-3"></i>
                    <h6>Quick Property Setup</h6>
                    <p class="text-muted">Choose how you'd like to add your property</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <button class="btn btn-outline-primary w-100 py-3" onclick="window.location.href='/add-property/step-by-step'">
                                <i class="fi-settings display-6 d-block mb-2"></i>
                                <strong>Step-by-Step Guide</strong>
                                <small class="d-block text-muted">Guided property creation</small>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-secondary w-100 py-3" onclick="window.location.href='/add-property/quick'">
                                <i class="fi-zap display-6 d-block mb-2"></i>
                                <strong>Quick Add</strong>
                                <small class="d-block text-muted">Fast property entry</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.property-dashboard .dashboard-header {
    background: linear-gradient(135deg, var(--bs-primary), var(--bs-primary-dark));
}

.property-dashboard .property-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.property-dashboard .property-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.property-dashboard .table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.property-dashboard .bulk-actions-bar {
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.property-dashboard .btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

@media (max-width: 768px) {
    .property-dashboard .dashboard-header .col-md-4 {
        margin-top: 1rem;
        text-align: center !important;
    }
    
    .property-dashboard .stats-cards .col-md-3 {
        margin-bottom: 1rem;
    }
    
    .property-dashboard .table-responsive {
        font-size: 0.875rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dashboard = new PropertyDashboard();
});

class PropertyDashboard {
    constructor() {
        this.bulkMode = false;
        this.selectedProperties = new Set();
        this.currentView = 'list';
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupFilters();
    }
    
    setupEventListeners() {
        // View toggle
        document.getElementById('view-list').addEventListener('click', () => {
            this.switchView('list');
        });
        
        document.getElementById('view-grid').addEventListener('click', () => {
            this.switchView('grid');
        });
        
        // Bulk actions
        document.getElementById('bulk-actions-toggle').addEventListener('click', () => {
            this.toggleBulkMode();
        });
        
        document.getElementById('select-all').addEventListener('change', (e) => {
            this.selectAll(e.target.checked);
        });
        
        // Property checkboxes
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('property-checkbox')) {
                this.handlePropertySelect(e.target);
            }
        });
        
        // Bulk action buttons
        document.getElementById('bulk-activate').addEventListener('click', () => {
            this.bulkAction('activate');
        });
        
        document.getElementById('bulk-deactivate').addEventListener('click', () => {
            this.bulkAction('deactivate');
        });
        
        document.getElementById('bulk-delete').addEventListener('click', () => {
            this.bulkAction('delete');
        });
        
        // Export
        document.getElementById('export-properties').addEventListener('click', () => {
            this.exportProperties();
        });
    }
    
    setupFilters() {
        const statusFilter = document.getElementById('property-status-filter');
        const typeFilter = document.getElementById('property-type-filter');
        const sortSelect = document.getElementById('property-sort');
        
        [statusFilter, typeFilter, sortSelect].forEach(filter => {
            filter.addEventListener('change', () => {
                this.applyFilters();
            });
        });
    }
    
    switchView(view) {
        this.currentView = view;
        
        const listView = document.getElementById('properties-list-view');
        const gridView = document.getElementById('properties-grid-view');
        const listBtn = document.getElementById('view-list');
        const gridBtn = document.getElementById('view-grid');
        
        if (view === 'list') {
            listView.classList.remove('d-none');
            gridView.classList.add('d-none');
            listBtn.classList.add('active');
            gridBtn.classList.remove('active');
        } else {
            listView.classList.add('d-none');
            gridView.classList.remove('d-none');
            listBtn.classList.remove('active');
            gridBtn.classList.add('active');
        }
    }
    
    toggleBulkMode() {
        this.bulkMode = !this.bulkMode;
        const bulkCheckboxes = document.querySelectorAll('.bulk-checkbox');
        const bulkActionsBar = document.querySelector('.bulk-actions-bar');
        const bulkToggleBtn = document.getElementById('bulk-actions-toggle');
        
        if (this.bulkMode) {
            bulkCheckboxes.forEach(cb => cb.classList.remove('d-none'));
            bulkActionsBar.classList.remove('d-none');
            bulkToggleBtn.classList.add('active');
        } else {
            bulkCheckboxes.forEach(cb => cb.classList.add('d-none'));
            bulkActionsBar.classList.add('d-none');
            bulkToggleBtn.classList.remove('active');
            this.selectedProperties.clear();
            this.updateSelectedCount();
        }
    }
    
    selectAll(checked) {
        const checkboxes = document.querySelectorAll('.property-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
            if (checked) {
                this.selectedProperties.add(checkbox.value);
            } else {
                this.selectedProperties.delete(checkbox.value);
            }
        });
        this.updateSelectedCount();
    }
    
    handlePropertySelect(checkbox) {
        if (checkbox.checked) {
            this.selectedProperties.add(checkbox.value);
        } else {
            this.selectedProperties.delete(checkbox.value);
        }
        this.updateSelectedCount();
    }
    
    updateSelectedCount() {
        const countElement = document.querySelector('.selected-count');
        const count = this.selectedProperties.size;
        countElement.textContent = `${count} selected`;
    }
    
    applyFilters() {
        const statusFilter = document.getElementById('property-status-filter').value;
        const typeFilter = document.getElementById('property-type-filter').value;
        const sortValue = document.getElementById('property-sort').value;
        
        const rows = document.querySelectorAll('[data-property-id]');
        
        rows.forEach(row => {
            let show = true;
            
            if (statusFilter && row.dataset.status !== statusFilter) {
                show = false;
            }
            
            if (typeFilter && row.dataset.type !== typeFilter) {
                show = false;
            }
            
            row.style.display = show ? '' : 'none';
        });
        
        this.applySorting(sortValue);
    }
    
    applySorting(sortValue) {
        const container = this.currentView === 'list' 
            ? document.getElementById('properties-table-body')
            : document.getElementById('properties-grid-container');
        
        const rows = Array.from(container.children).filter(row => 
            row.style.display !== 'none' && row.dataset.propertyId
        );
        
        rows.sort((a, b) => {
            // Implement sorting logic based on sortValue
            // This is a simplified version
            const aId = a.dataset.propertyId;
            const bId = b.dataset.propertyId;
            return aId.localeCompare(bId);
        });
        
        rows.forEach(row => container.appendChild(row));
    }
    
    bulkAction(action) {
        if (this.selectedProperties.size === 0) {
            alert('Please select at least one property');
            return;
        }
        
        const confirmed = confirm(`Are you sure you want to ${action} ${this.selectedProperties.size} properties?`);
        if (!confirmed) return;
        
        // Simulate API call
        console.log(`Bulk ${action} for properties:`, Array.from(this.selectedProperties));
        
        // Show success message
        const toast = this.showToast(`Successfully ${action}d ${this.selectedProperties.size} properties`, 'success');
        
        // Reset selections
        this.selectedProperties.clear();
        this.selectAll(false);
        this.updateSelectedCount();
    }
    
    exportProperties() {
        // Simple CSV export simulation
        const data = [
            ['ID', 'Title', 'Type', 'Price', 'Status', 'Views', 'Created'],
            // Add property data here
        ];
        
        const csvContent = data.map(row => row.join(',')).join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'properties.csv';
        a.click();
        window.URL.revokeObjectURL(url);
        
        this.showToast('Properties exported successfully', 'success');
    }
    
    showToast(message, type = 'info') {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <i class="fi-check-circle me-2"></i>${message}
            <button type="button" class="btn-close ms-2" onclick="this.parentElement.remove()"></button>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 5000);
        
        return toast;
    }
}
</script>