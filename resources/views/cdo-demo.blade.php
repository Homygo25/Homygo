@extends('partials.master2')

@section('title')
Homygo CDO - Real Estate Platform Demo
@endsection

@section('css')
<!-- Custom demo styles -->
<style>
    .demo-section {
        border: 2px dashed #e9ecef;
        border-radius: 0.5rem;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        background: #f8f9fa;
    }
    .demo-label {
        position: absolute;
        top: -12px;
        left: 20px;
        background: #fff;
        padding: 0 10px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #495057;
    }
    .feature-highlight {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
    .cdo-badge {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<main class="content-wrapper">
    <!-- Hero Section -->
    <section class="bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Homygo CDO Platform Demo</h1>
                    <p class="lead mb-4">
                        Experience our comprehensive real estate platform specifically designed for 
                        Cagayan de Oro City - the City of Golden Friendship.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#features" class="btn btn-light btn-lg">Explore Features</a>
                        <a href="{{ route('cdo.properties') }}" class="btn btn-outline-light btn-lg">View Properties</a>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="bg-white bg-opacity-10 rounded-3 p-4">
                        <i class="fi-home display-1 mb-3"></i>
                        <h3>CDO Focused</h3>
                        <p class="mb-0">Exclusively serving Cagayan de Oro City and surrounding areas</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Platform Overview -->
    <section class="container py-5" id="features">
        <div class="text-center mb-5">
            <h2 class="h1 mb-3">Platform Features</h2>
            <p class="lead text-muted">Comprehensive real estate solutions built specifically for CDO</p>
        </div>

        <!-- Feature Grid -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="feature-highlight text-center">
                    <i class="fi-map-pin display-4 mb-3"></i>
                    <h4>CDO Location Focus</h4>
                    <p class="mb-0">40+ Barangays mapped with local market insights</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-highlight text-center">
                    <i class="fi-search display-4 mb-3"></i>
                    <h4>Advanced Search</h4>
                    <p class="mb-0">Location-aware search with CDO-specific filters</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-highlight text-center">
                    <i class="fi-heart display-4 mb-3"></i>
                    <h4>User Features</h4>
                    <p class="mb-0">Favorites, sharing, notifications & more</p>
                </div>
            </div>
        </div>

        <!-- Demo Sections -->
        
        <!-- 1. CDO Branding Demo -->
        <div class="demo-section">
            <div class="demo-label">🏠 CDO Branding & Identity</div>
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h3>Homygo CDO <span class="cdo-badge">City Exclusive</span></h3>
                    <p class="text-muted mb-3">
                        Our platform is branded specifically for Cagayan de Oro City, incorporating local 
                        cultural elements and the "City of Golden Friendship" theme throughout the user experience.
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-2">✅ CDO-specific branding and messaging</li>
                        <li class="mb-2">✅ Local SEO optimization</li>
                        <li class="mb-2">✅ Cultural adaptation for CDO market</li>
                        <li class="mb-2">✅ Philippine Peso (₱) currency formatting</li>
                    </ul>
                    <a href="{{ route('home') }}" class="btn btn-primary">View Homepage</a>
                </div>
                <div class="col-lg-6">
                    <div class="bg-white rounded-3 p-3 shadow-sm">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary rounded-circle p-2 me-3">
                                <i class="fi-home text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Homygo CDO</h5>
                                <small class="text-muted">City of Golden Friendship</small>
                            </div>
                        </div>
                        <p class="small text-muted mb-0">
                            Find Your Perfect Home in Cagayan de Oro City - Where every property 
                            listing connects you to the heart of Mindanao's gateway city.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Location System Demo -->
        <div class="demo-section">
            <div class="demo-label">📍 CDO Location System</div>
            <div class="row">
                <div class="col-lg-6">
                    <h3>40+ Cagayan de Oro Barangays</h3>
                    <p class="text-muted mb-3">
                        Comprehensive mapping of CDO's barangays with location-aware search and filtering capabilities.
                    </p>
                    <div class="row g-2 mb-3">
                        @php
                            $sampleBarangays = [
                                'Cogon', 'Carmen', 'Divisoria', 'Lapasan', 'Nazareth', 'Gusa',
                                'Pueblo de Oro', 'Lumbia', 'Macasandig', 'Kauswagan', 'Balulang', 'Tablon'
                            ];
                        @endphp
                        @foreach($sampleBarangays as $barangay)
                            <div class="col-4">
                                <span class="badge bg-light text-dark border">{{ $barangay }}</span>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('cdo.properties') }}" class="btn btn-outline-primary">Browse by Location</a>
                </div>
                <div class="col-lg-6">
                    <div class="bg-white rounded-3 p-3 shadow-sm">
                        <h6 class="mb-3">Sample Location Filter</h6>
                        <select class="form-select mb-2" disabled>
                            <option>Choose CDO Barangay...</option>
                            <option selected>Cogon - Popular Residential</option>
                        </select>
                        <small class="text-muted d-block">
                            <i class="fi-map-pin me-1"></i>
                            Location-aware search with barangay-level filtering
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Property Features Demo -->
        <div class="demo-section">
            <div class="demo-label">🏡 CDO Property Showcase</div>
            <div class="row">
                <div class="col-lg-4">
                    <h3>Property Cards</h3>
                    <p class="text-muted mb-3">
                        Enhanced property cards with CDO-specific styling, peso pricing, 
                        and local context information.
                    </p>
                    <ul class="list-unstyled small">
                        <li>🏠 Property type badges</li>
                        <li>💰 Peso (₱) pricing</li>
                        <li>📍 CDO location display</li>
                        <li>❤️ Favorite functionality</li>
                        <li>📱 Mobile responsive</li>
                    </ul>
                </div>
                <div class="col-lg-8">
                    <!-- Sample Property Card -->
                    @include('properties.partials.card', [
                        'property' => (object)[
                            'id' => 1,
                            'title' => 'Modern Family Home in Cogon',
                            'price' => 2500000,
                            'bedrooms' => 3,
                            'bathrooms' => 2,
                            'area' => 120,
                            'location' => 'Cogon, CDO',
                            'property_type' => 'house',
                            'status' => 'for_sale',
                            'is_featured' => true,
                            'images' => []
                        ]
                    ])
                </div>
            </div>
        </div>

        <!-- 4. Advanced Features Demo -->
        <div class="demo-section">
            <div class="demo-label">⚡ Advanced Features</div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="bg-white rounded-3 p-3 shadow-sm h-100">
                        <h5 class="text-primary mb-3">
                            <i class="fi-heart me-2"></i>Favorites System
                        </h5>
                        <p class="text-muted small mb-3">
                            Users can save properties to their favorites list for easy access and comparison.
                        </p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fi-heart-filled me-1"></i>Saved
                            </button>
                            <span class="small text-muted align-self-center">3 properties saved</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="bg-white rounded-3 p-3 shadow-sm h-100">
                        <h5 class="text-success mb-3">
                            <i class="fi-share me-2"></i>Social Sharing
                        </h5>
                        <p class="text-muted small mb-3">
                            Share CDO properties across social media platforms and messaging apps.
                        </p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fi-facebook me-1"></i>Facebook
                            </button>
                            <button class="btn btn-sm btn-outline-info">
                                <i class="fi-twitter me-1"></i>Twitter
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="bg-white rounded-3 p-3 shadow-sm h-100">
                        <h5 class="text-warning mb-3">
                            <i class="fi-bell me-2"></i>Notifications
                        </h5>
                        <p class="text-muted small mb-3">
                            Real-time notifications for new CDO listings, price changes, and more.
                        </p>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning">3</span>
                            <span class="small text-muted">New properties in Cogon</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="bg-white rounded-3 p-3 shadow-sm h-100">
                        <h5 class="text-info mb-3">
                            <i class="fi-upload me-2"></i>Image Management
                        </h5>
                        <p class="text-muted small mb-3">
                            Advanced image upload and management for property listings.
                        </p>
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-dark">5 images</span>
                            <span class="small text-muted align-self-center">Max 10 per property</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Database & Seeder Demo -->
        <div class="demo-section">
            <div class="demo-label">💾 CDO Database System</div>
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h3>Cagayan de Oro Data Seeder</h3>
                    <p class="text-muted mb-3">
                        Comprehensive database seeder with realistic CDO property data, locations, 
                        and sample listings to populate the platform.
                    </p>
                    <div class="bg-dark rounded-3 p-3 text-light small mb-3">
                        <div class="text-success">$ php artisan db:seed --class=CagayanDeOroSeeder</div>
                        <div class="text-muted">Seeding CDO barangays...</div>
                        <div class="text-muted">Creating sample properties...</div>
                        <div class="text-muted">Setting up user accounts...</div>
                        <div class="text-success">✅ CDO database populated successfully!</div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 text-primary mb-0">40+</div>
                                <small class="text-muted">CDO Barangays</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 text-success mb-0">100+</div>
                                <small class="text-muted">Sample Properties</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bg-white rounded-3 p-3 shadow-sm">
                        <h6 class="mb-3">Sample CDO Property Data</h6>
                        <div class="small">
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Title:</div>
                                <div class="col-8">Modern House in Cogon</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Location:</div>
                                <div class="col-8">Cogon, Cagayan de Oro City</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Price:</div>
                                <div class="col-8 text-primary">₱2,500,000</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Type:</div>
                                <div class="col-8">House & Lot</div>
                            </div>
                            <div class="row">
                                <div class="col-4 text-muted">Specs:</div>
                                <div class="col-8">3BR, 2BA, 120m²</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Live Demo Links -->
    <section class="bg-body-tertiary py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="h3 mb-4">Try the Live Platform</h2>
                    <p class="text-muted mb-4">
                        Experience all the features we've built for the Cagayan de Oro real estate market.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                            <i class="fi-home me-2"></i>Homepage
                        </a>
                        <a href="{{ route('cdo.properties') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fi-search me-2"></i>Browse Properties
                        </a>
                        <a href="{{ route('cdo.property.show', 1) }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fi-eye me-2"></i>Property Details
                        </a>
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-success btn-lg">
                                <i class="fi-user-plus me-2"></i>Sign Up
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="btn btn-warning btn-lg">
                                <i class="fi-dashboard me-2"></i>Dashboard
                            </a>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Technical Implementation -->
    <section class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-5">
                    <h2 class="h3">Technical Implementation</h2>
                    <p class="text-muted">Built with modern Laravel architecture and best practices</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="bg-white rounded-3 p-4 shadow-sm">
                            <h5 class="text-primary mb-3">Backend Features</h5>
                            <ul class="list-unstyled small">
                                <li class="mb-2">✅ Laravel 12 Framework</li>
                                <li class="mb-2">✅ Advanced Property Controller</li>
                                <li class="mb-2">✅ Image Upload System</li>
                                <li class="mb-2">✅ API Endpoints</li>
                                <li class="mb-2">✅ User Authentication</li>
                                <li class="mb-2">✅ Database Seeders</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-white rounded-3 p-4 shadow-sm">
                            <h5 class="text-success mb-3">Frontend Features</h5>
                            <ul class="list-unstyled small">
                                <li class="mb-2">✅ Responsive Blade Templates</li>
                                <li class="mb-2">✅ CDO-Specific Branding</li>
                                <li class="mb-2">✅ Advanced Search Forms</li>
                                <li class="mb-2">✅ Interactive Property Cards</li>
                                <li class="mb-2">✅ Location-Aware Components</li>
                                <li class="mb-2">✅ Mobile-First Design</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add demo interaction effects
    document.querySelectorAll('.demo-section').forEach(section => {
        section.addEventListener('mouseenter', function() {
            this.style.borderColor = '#007bff';
            this.style.backgroundColor = '#f8f9fa';
        });
        
        section.addEventListener('mouseleave', function() {
            this.style.borderColor = '#e9ecef';
            this.style.backgroundColor = '#f8f9fa';
        });
    });
});
</script>
@endsection