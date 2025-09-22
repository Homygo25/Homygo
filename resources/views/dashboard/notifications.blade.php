@extends('layouts.main')

@section('title', 'Notifications - Dashboard')

@section('content')
<main class="content-wrapper">
    <!-- Header -->
    <section class="bg-light py-4">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-3">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Notifications</li>
                </ol>
            </nav>
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="h3 mb-2">Notifications</h1>
                    <p class="text-muted mb-0">Stay updated with your property activity and system alerts</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex gap-2 justify-content-lg-end mt-3 mt-lg-0">
                        @if($unreadCount > 0)
                            <button class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                                <i class="fi-check me-1"></i>Mark All Read
                            </button>
                        @endif
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fi-filter me-1"></i>Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?filter=all">All Notifications</a></li>
                                <li><a class="dropdown-item" href="?filter=unread">Unread Only</a></li>
                                <li><a class="dropdown-item" href="?filter=high">High Priority</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="?type=property">Property Related</a></li>
                                <li><a class="dropdown-item" href="?type=inquiry">Inquiries</a></li>
                                <li><a class="dropdown-item" href="?type=system">System Updates</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container py-4">
        <!-- Notification Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3 mx-auto mb-2" style="width: fit-content;">
                            <i class="fi-bell text-primary fs-4"></i>
                        </div>
                        <h4 class="mb-1">{{ $totalCount }}</h4>
                        <p class="text-muted mb-0">Total</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3 mx-auto mb-2" style="width: fit-content;">
                            <i class="fi-alert-circle text-warning fs-4"></i>
                        </div>
                        <h4 class="mb-1">{{ $unreadCount }}</h4>
                        <p class="text-muted mb-0">Unread</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <div class="bg-danger bg-opacity-10 rounded-3 p-3 mx-auto mb-2" style="width: fit-content;">
                            <i class="fi-trending-up text-danger fs-4"></i>
                        </div>
                        <h4 class="mb-1">{{ $highPriorityCount }}</h4>
                        <p class="text-muted mb-0">High Priority</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3 mx-auto mb-2" style="width: fit-content;">
                            <i class="fi-clock text-success fs-4"></i>
                        </div>
                        <h4 class="mb-1">{{ $todayCount }}</h4>
                        <p class="text-muted mb-0">Today</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    Notifications 
                    @if(request('filter'))
                        <span class="badge bg-secondary ms-2">{{ ucfirst(request('filter')) }}</span>
                    @endif
                </h5>
            </div>
            <div class="card-body p-0">
                @if($notifications->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notification)
                            <div class="list-group-item notification-item {{ $notification->isRead() ? 'read' : 'unread' }}" 
                                 data-notification-id="{{ $notification->id }}">
                                <div class="d-flex align-items-center">
                                    <!-- Priority Indicator -->
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-{{ $notification->priority_color }} bg-opacity-10 rounded-circle p-2">
                                            @switch($notification->type)
                                                @case('property_view')
                                                    <i class="fi-eye text-{{ $notification->priority_color }}"></i>
                                                    @break
                                                @case('property_inquiry')
                                                    <i class="fi-mail text-{{ $notification->priority_color }}"></i>
                                                    @break
                                                @case('saved_search_alert')
                                                    <i class="fi-search text-{{ $notification->priority_color }}"></i>
                                                    @break
                                                @case('system_update')
                                                    <i class="fi-settings text-{{ $notification->priority_color }}"></i>
                                                    @break
                                                @default
                                                    <i class="fi-bell text-{{ $notification->priority_color }}"></i>
                                            @endswitch
                                        </div>
                                    </div>

                                    <!-- Notification Content -->
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="mb-0 {{ $notification->isRead() ? 'text-muted' : '' }}">
                                                {{ $notification->title }}
                                            </h6>
                                            <div class="d-flex align-items-center">
                                                @if($notification->priority === 'high')
                                                    <span class="badge bg-danger me-2">High</span>
                                                @endif
                                                @if(!$notification->isRead())
                                                    <span class="badge bg-primary me-2">New</span>
                                                @endif
                                                <small class="text-muted">{{ $notification->time_ago }}</small>
                                            </div>
                                        </div>
                                        
                                        <p class="text-muted mb-2 {{ $notification->isRead() ? 'opacity-75' : '' }}">
                                            {{ Str::limit($notification->message, 120) }}
                                        </p>

                                        <!-- Action Buttons -->
                                        <div class="d-flex gap-2">
                                            @if(!$notification->isRead())
                                                <button class="btn btn-sm btn-primary" onclick="markAsRead({{ $notification->id }})">
                                                    <i class="fi-check me-1"></i>Mark Read
                                                </button>
                                            @endif
                                            
                                            @if($notification->action_url)
                                                <a href="{{ $notification->action_url }}" class="btn btn-sm btn-outline-primary">
                                                    {{ $notification->action_text ?? 'View Details' }}
                                                </a>
                                            @endif
                                            
                                            <button class="btn btn-sm btn-outline-secondary" onclick="viewNotification({{ $notification->id }})">
                                                <i class="fi-eye"></i>
                                            </button>
                                            
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification({{ $notification->id }})">
                                                <i class="fi-trash-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($notifications->hasPages())
                        <div class="card-footer">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fi-bell text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 mb-2">No notifications found</h4>
                        <p class="text-muted mb-4">
                            @if(request('filter') || request('type'))
                                No notifications match your current filter.
                            @else
                                You don't have any notifications yet.
                            @endif
                        </p>
                        @if(request('filter') || request('type'))
                            <a href="{{ route('dashboard.notifications') }}" class="btn btn-primary">View All Notifications</a>
                        @else
                            <a href="{{ route('dashboard.index') }}" class="btn btn-primary">Back to Dashboard</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Notification Preferences</h5>
            </div>
            <div class="card-body">
                <form id="notificationPreferences">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Email Notifications</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="emailInquiries" checked>
                                <label class="form-check-label" for="emailInquiries">
                                    New property inquiries
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="emailViews">
                                <label class="form-check-label" for="emailViews">
                                    Daily view summaries
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="emailSearchAlerts" checked>
                                <label class="form-check-label" for="emailSearchAlerts">
                                    Saved search alerts
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="emailSystem">
                                <label class="form-check-label" for="emailSystem">
                                    System updates and maintenance
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Browser Notifications</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="browserInquiries" checked>
                                <label class="form-check-label" for="browserInquiries">
                                    Instant inquiry alerts
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="browserHighPriority" checked>
                                <label class="form-check-label" for="browserHighPriority">
                                    High priority notifications
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="browserSearchAlerts">
                                <label class="form-check-label" for="browserSearchAlerts">
                                    New property matches
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                        <button type="button" class="btn btn-outline-secondary ms-2" onclick="requestNotificationPermission()">
                            Enable Browser Notifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<!-- Notification Detail Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notification Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="notificationContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.notification-item.unread {
    background-color: rgba(99, 102, 241, 0.05);
    border-left: 3px solid #6366f1;
}

.notification-item.read {
    opacity: 0.8;
}

.notification-item:hover {
    background-color: rgba(0, 0, 0, 0.02);
}
</style>

<script>
function markAsRead(notificationId) {
    fetch(`/dashboard/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
            item.classList.remove('unread');
            item.classList.add('read');
            
            // Update the button
            const button = item.querySelector('button[onclick*="markAsRead"]');
            if (button) {
                button.remove();
            }
            
            // Update counters
            updateNotificationCounters();
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAllAsRead() {
    fetch('/dashboard/notifications/mark-all-read', {
        method: 'POST',
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

function viewNotification(notificationId) {
    fetch(`/dashboard/notifications/${notificationId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('notificationContent').innerHTML = `
                <div class="mb-3">
                    <h6>${data.title}</h6>
                    <small class="text-muted">${data.created_at}</small>
                </div>
                <div class="mb-3">
                    <p>${data.message}</p>
                </div>
                ${data.data ? `<div class="mb-3">
                    <h6>Additional Details:</h6>
                    <pre class="bg-light p-3 rounded">${JSON.stringify(data.data, null, 2)}</pre>
                </div>` : ''}
            `;
            new bootstrap.Modal(document.getElementById('notificationModal')).show();
            
            // Mark as read when viewed
            if (!data.read_at) {
                markAsRead(notificationId);
            }
        })
        .catch(error => console.error('Error:', error));
}

function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        fetch(`/dashboard/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
                item.remove();
                updateNotificationCounters();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function updateNotificationCounters() {
    // Update notification counters in the header
    const unreadItems = document.querySelectorAll('.notification-item.unread').length;
    // Update UI counters here
}

function requestNotificationPermission() {
    if ('Notification' in window) {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                new Notification('Homygo CDO', {
                    body: 'Browser notifications enabled successfully!',
                    icon: '/favicon.ico'
                });
            }
        });
    } else {
        alert('Your browser does not support notifications');
    }
}

// Form submission for preferences
document.getElementById('notificationPreferences').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const preferences = {};
    
    // Collect checkbox values
    this.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        preferences[checkbox.id] = checkbox.checked;
    });
    
    fetch('/dashboard/notification-preferences', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(preferences)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Notification preferences saved successfully!');
        }
    })
    .catch(error => console.error('Error:', error));
});

// Auto-refresh notifications every 30 seconds
setInterval(function() {
    fetch('/dashboard/notifications/count')
        .then(response => response.json())
        .then(data => {
            // Update notification indicators
            updateNotificationCounters();
        })
        .catch(error => console.error('Error:', error));
}, 30000);
</script>
@endsection