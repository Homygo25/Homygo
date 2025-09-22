@extends('layouts.main')

@section('title', 'Property Analytics - ' . $property->title)

@section('content')
<main class="content-wrapper">
    <!-- Header -->
    <section class="bg-light py-4">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-3">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Property Analytics</li>
                </ol>
            </nav>
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="h3 mb-2">{{ $property->title }}</h1>
                    <p class="text-muted mb-0">{{ $property->location->name ?? 'Cagayan de Oro' }} • {{ ucfirst($property->property_type) }}</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex gap-2 justify-content-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('properties.show', $property) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fi-eye me-1"></i>View Property
                        </a>
                        <a href="{{ route('properties.edit', $property) }}" class="btn btn-primary btn-sm">
                            <i class="fi-edit me-1"></i>Edit Property
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container py-4">
        <!-- Key Metrics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 border-primary">
                    <div class="card-body text-center">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3 mx-auto mb-3" style="width: fit-content;">
                            <i class="fi-eye text-primary fs-3"></i>
                        </div>
                        <h3 class="mb-1">{{ number_format($analytics['total_views']) }}</h3>
                        <p class="text-muted mb-0">Total Views</p>
                        @if($analytics['views_change'] != 0)
                            <small class="text-{{ $analytics['views_change'] > 0 ? 'success' : 'danger' }}">
                                <i class="fi-{{ $analytics['views_change'] > 0 ? 'trending-up' : 'trending-down' }}"></i>
                                {{ abs($analytics['views_change']) }}% vs last period
                            </small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 border-success">
                    <div class="card-body text-center">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3 mx-auto mb-3" style="width: fit-content;">
                            <i class="fi-users text-success fs-3"></i>
                        </div>
                        <h3 class="mb-1">{{ number_format($analytics['unique_views']) }}</h3>
                        <p class="text-muted mb-0">Unique Visitors</p>
                        <small class="text-muted">{{ round($analytics['unique_percentage'], 1) }}% of total views</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 border-warning">
                    <div class="card-body text-center">
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3 mx-auto mb-3" style="width: fit-content;">
                            <i class="fi-mail text-warning fs-3"></i>
                        </div>
                        <h3 class="mb-1">{{ number_format($analytics['total_inquiries']) }}</h3>
                        <p class="text-muted mb-0">Inquiries</p>
                        <small class="text-muted">{{ $analytics['conversion_rate'] }}% conversion rate</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 border-info">
                    <div class="card-body text-center">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3 mx-auto mb-3" style="width: fit-content;">
                            <i class="fi-clock text-info fs-3"></i>
                        </div>
                        <h3 class="mb-1">{{ $analytics['avg_time_spent'] }}</h3>
                        <p class="text-muted mb-0">Avg. Time on Page</p>
                        <small class="text-muted">Higher engagement</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Charts Section -->
            <div class="col-lg-8">
                <!-- Views Over Time -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Views Over Time</h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="chartPeriod" id="period7" value="7" checked>
                            <label class="btn btn-outline-primary" for="period7">7 Days</label>
                            <input type="radio" class="btn-check" name="chartPeriod" id="period30" value="30">
                            <label class="btn btn-outline-primary" for="period30">30 Days</label>
                            <input type="radio" class="btn-check" name="chartPeriod" id="period90" value="90">
                            <label class="btn btn-outline-primary" for="period90">90 Days</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="viewsChart" height="200"></canvas>
                    </div>
                </div>

                <!-- Device Breakdown -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Device & Browser Analytics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Device Types</h6>
                                <canvas id="deviceChart" height="150"></canvas>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Top Browsers</h6>
                                <div class="list-group list-group-flush">
                                    @forelse($analytics['browser_data'] as $browser => $count)
                                        <div class="list-group-item border-0 px-0 d-flex justify-content-between align-items-center">
                                            <span>{{ ucfirst($browser) }}</span>
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-2" style="width: 100px; height: 8px;">
                                                    <div class="progress-bar" style="width: {{ ($count / $analytics['total_views']) * 100 }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $count }}</small>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted">No browser data available</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inquiry Details -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Inquiry Management</h5>
                    </div>
                    <div class="card-body">
                        @if($recentInquiries && $recentInquiries->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Contact</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentInquiries as $inquiry)
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong>{{ $inquiry->name }}</strong><br>
                                                        <small class="text-muted">{{ $inquiry->email }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $inquiry->type_color }}">
                                                        {{ ucfirst($inquiry->inquiry_type) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $inquiry->status_color }}">
                                                        {{ ucfirst($inquiry->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>{{ $inquiry->created_at->format('M j, Y') }}</small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="viewInquiry({{ $inquiry->id }})">
                                                            <i class="fi-eye"></i>
                                                        </button>
                                                        @if($inquiry->status === 'pending')
                                                            <button class="btn btn-outline-success" onclick="updateInquiryStatus({{ $inquiry->id }}, 'responded')">
                                                                <i class="fi-check"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fi-mail text-muted fs-1 mb-3"></i>
                                <p class="text-muted">No inquiries yet for this property.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Property Performance -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Performance Score</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <div class="progress mx-auto" style="width: 120px; height: 120px; transform: rotate(-90deg);">
                                <div class="progress-bar progress-bar-striped bg-success" 
                                     style="width: {{ $analytics['performance_score'] }}%; transition: width 2s ease-in-out;">
                                </div>
                            </div>
                            <h3 class="mt-3 mb-0">{{ $analytics['performance_score'] }}%</h3>
                            <p class="text-muted">Overall Performance</p>
                        </div>
                        <div class="row text-center">
                            <div class="col-6">
                                <h6 class="text-success mb-1">{{ $analytics['rank_in_area'] }}</h6>
                                <small class="text-muted">Rank in Area</small>
                            </div>
                            <div class="col-6">
                                <h6 class="text-primary mb-1">{{ $analytics['similar_properties'] }}</h6>
                                <small class="text-muted">Similar Props</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Traffic Sources -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Traffic Sources</h6>
                    </div>
                    <div class="card-body">
                        @forelse($analytics['traffic_sources'] as $source => $data)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-1">{{ ucfirst($source) }}</h6>
                                    <small class="text-muted">{{ $data['views'] }} views</small>
                                </div>
                                <div class="text-end">
                                    <div class="progress" style="width: 60px; height: 8px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $data['percentage'] }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ round($data['percentage'], 1) }}%</small>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No traffic source data available</p>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-sm" onclick="shareProperty()">
                                <i class="fi-share me-2"></i>Share Property
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="downloadReport()">
                                <i class="fi-download me-2"></i>Download Report
                            </button>
                            <a href="{{ route('properties.edit', $property) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fi-edit me-2"></i>Edit Property
                            </a>
                            <button class="btn btn-outline-warning btn-sm" onclick="promoteProperty()">
                                <i class="fi-trending-up me-2"></i>Promote Property
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Inquiry Modal -->
<div class="modal fade" id="inquiryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Inquiry Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="inquiryContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="respondToInquiry()">Respond</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart initialization
document.addEventListener('DOMContentLoaded', function() {
    // Views Chart
    const viewsCtx = document.getElementById('viewsChart').getContext('2d');
    const viewsChart = new Chart(viewsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($analytics['daily_views']['labels']) !!},
            datasets: [{
                label: 'Views',
                data: {!! json_encode($analytics['daily_views']['data']) !!},
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Device Chart
    const deviceCtx = document.getElementById('deviceChart').getContext('2d');
    const deviceChart = new Chart(deviceCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($analytics['device_data'])) !!},
            datasets: [{
                data: {!! json_encode(array_values($analytics['device_data'])) !!},
                backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Period change handlers
    document.querySelectorAll('input[name="chartPeriod"]').forEach(radio => {
        radio.addEventListener('change', function() {
            updateChartData(this.value);
        });
    });
});

function updateChartData(days) {
    fetch(`/dashboard/property-analytics/{{ $property->id }}/data?days=${days}`)
        .then(response => response.json())
        .then(data => {
            // Update chart with new data
            console.log('Updated chart data for', days, 'days');
        })
        .catch(error => console.error('Error:', error));
}

function viewInquiry(inquiryId) {
    fetch(`/dashboard/inquiries/${inquiryId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('inquiryContent').innerHTML = `
                <div class="mb-3">
                    <strong>From:</strong> ${data.name} (${data.email})<br>
                    <strong>Phone:</strong> ${data.phone || 'Not provided'}<br>
                    <strong>Type:</strong> ${data.inquiry_type}<br>
                    <strong>Date:</strong> ${data.created_at}
                </div>
                <div class="mb-3">
                    <strong>Message:</strong><br>
                    <p class="border p-3 rounded">${data.message}</p>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('inquiryModal')).show();
        })
        .catch(error => console.error('Error:', error));
}

function updateInquiryStatus(inquiryId, status) {
    fetch(`/dashboard/inquiries/${inquiryId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function shareProperty() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $property->title }}',
            text: 'Check out this property in Cagayan de Oro',
            url: '{{ route("properties.show", $property) }}'
        });
    } else {
        // Fallback to copying URL
        navigator.clipboard.writeText('{{ route("properties.show", $property) }}');
        alert('Property URL copied to clipboard!');
    }
}

function downloadReport() {
    window.open(`/dashboard/property-analytics/{{ $property->id }}/report`, '_blank');
}

function promoteProperty() {
    alert('Property promotion feature coming soon!');
}

function respondToInquiry() {
    alert('Email response feature coming soon!');
}
</script>
@endsection