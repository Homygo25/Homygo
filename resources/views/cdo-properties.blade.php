@extends('partials.master2')

@section('title')
Properties in Cagayan de Oro City - Homygo CDO
@endsection

@section('css')
<!-- Vendor styles -->
<link rel="stylesheet" href="build/libs/choices.js/public/assets/styles/choices.min.css">
<link rel="stylesheet" href="build/libs/nouislider/dist/nouislider.min.css">
@endsection

@section('content')
<main class="content-wrapper">
    <!-- Breadcrumb -->
    <nav class="container pt-3 my-2 my-md-3" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Properties in CDO</li>
        </ol>
    </nav>

    <!-- Page header -->
    <section class="container mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="h2 mb-2">Properties in Cagayan de Oro City</h1>
                <p class="lead mb-0">Find your perfect home in the City of Golden Friendship</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                @auth
                    <a href="{{ route('properties.create') }}" class="btn btn-primary">
                        <i class="fi-plus me-2"></i>List Your Property
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-outline-primary me-2">Sign Up</a>
                    <a href="{{ route('login') }}" class="btn btn-primary">Sign In</a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Advanced Search -->
    <section class="container mb-4">
        @include('components.advanced-search')
    </section>

    <!-- Area Quick Links -->
    <section class="container mb-4">
        <div class="row">
            <div class="col-12">
                <div class="bg-body-tertiary rounded-3 p-3">
                    <h5 class="mb-3">Popular Areas in CDO</h5>
                    <div class="d-flex flex-wrap gap-2">
                        @php
                            $popularAreas = [
                                'cogon' => 'Cogon',
                                'carmen' => 'Carmen', 
                                'divisoria' => 'Divisoria',
                                'lapasan' => 'Lapasan',
                                'nazareth' => 'Nazareth',
                                'gusa' => 'Gusa',
                                'pueblo_de_oro' => 'Pueblo de Oro',
                                'lumbia' => 'Lumbia',
                                'macasandig' => 'Macasandig'
                            ];
                        @endphp
                        @foreach($popularAreas as $slug => $name)
                            <a href="{{ route('properties.index', ['location' => $slug]) }}" 
                               class="btn btn-sm btn-outline-secondary">
                                {{ $name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Properties Grid -->
    <section class="container mb-5">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="offcanvas-lg offcanvas-start" id="filtersOffcanvas">
                    <div class="offcanvas-header border-bottom">
                        <h5 class="offcanvas-title">Filters</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#filtersOffcanvas"></button>
                    </div>
                    <div class="offcanvas-body">
                        <!-- Property Type Filter -->
                        <div class="mb-4">
                            <h6 class="mb-3">Property Type</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="property_type[]" value="house" id="house">
                                <label class="form-check-label" for="house">Houses</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="property_type[]" value="apartment" id="apartment">
                                <label class="form-check-label" for="apartment">Apartments</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="property_type[]" value="condo" id="condo">
                                <label class="form-check-label" for="condo">Condominiums</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="property_type[]" value="commercial" id="commercial">
                                <label class="form-check-label" for="commercial">Commercial</label>
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-4">
                            <h6 class="mb-3">Price Range</h6>
                            <div class="range-slider" data-range-slider='{"min": 5000, "max": 10000000, "step": 5000}'>
                                <div class="range-slider-ui mb-3"></div>
                                <div class="d-flex gap-2">
                                    <input type="number" class="form-control form-control-sm" placeholder="Min price">
                                    <input type="number" class="form-control form-control-sm" placeholder="Max price">
                                </div>
                            </div>
                        </div>

                        <!-- Bedrooms -->
                        <div class="mb-4">
                            <h6 class="mb-3">Bedrooms</h6>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="bedrooms" id="bed-any" value="">
                                <label class="btn btn-outline-secondary btn-sm" for="bed-any">Any</label>
                                <input type="radio" class="btn-check" name="bedrooms" id="bed-1" value="1">
                                <label class="btn btn-outline-secondary btn-sm" for="bed-1">1+</label>
                                <input type="radio" class="btn-check" name="bedrooms" id="bed-2" value="2">
                                <label class="btn btn-outline-secondary btn-sm" for="bed-2">2+</label>
                                <input type="radio" class="btn-check" name="bedrooms" id="bed-3" value="3">
                                <label class="btn btn-outline-secondary btn-sm" for="bed-3">3+</label>
                                <input type="radio" class="btn-check" name="bedrooms" id="bed-4" value="4">
                                <label class="btn btn-outline-secondary btn-sm" for="bed-4">4+</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Properties List -->
            <div class="col-lg-9">
                <!-- Filters Toggle & Sort -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-outline-secondary d-lg-none" type="button" 
                                data-bs-toggle="offcanvas" data-bs-target="#filtersOffcanvas">
                            <i class="fi-filter me-2"></i>Filters
                        </button>
                        <span class="text-muted">{{ $properties->total() ?? 0 }} properties found</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0 me-2">Sort by:</label>
                        <select class="form-select form-select-sm" style="width: auto;">
                            <option>Newest First</option>
                            <option>Price: Low to High</option>
                            <option>Price: High to Low</option>
                            <option>Most Popular</option>
                        </select>
                    </div>
                </div>

                <!-- Properties Grid -->
                <div class="row g-4 mb-4">
                    @forelse($properties ?? [] as $property)
                        <div class="col-md-6 col-xl-4">
                            @include('properties.partials.card', ['property' => $property])
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fi-home display-1 text-muted mb-3"></i>
                                <h5>No Properties Found</h5>
                                <p class="text-muted mb-4">Try adjusting your search criteria or browse all available properties.</p>
                                <a href="{{ route('properties.index') }}" class="btn btn-primary">
                                    View All Properties
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if(isset($properties) && $properties->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $properties->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="bg-body-tertiary py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h3 class="h4 mb-3">Stay Updated with CDO Real Estate</h3>
                    <p class="mb-4">Get the latest property listings and market updates in Cagayan de Oro City delivered to your inbox.</p>
                    <form class="d-flex gap-2 max-w-md mx-auto" method="POST" action="{{ route('newsletter.subscribe') }}">
                        @csrf
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@section('js')
<!-- Vendor scripts -->
<script src="build/libs/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="build/libs/nouislider/dist/nouislider.min.js"></script>

<script>
    // Initialize choice selects
    document.querySelectorAll('[data-select]').forEach(element => {
        new Choices(element, JSON.parse(element.dataset.select));
    });

    // Initialize range sliders
    document.querySelectorAll('[data-range-slider]').forEach(element => {
        const options = JSON.parse(element.dataset.rangeSlider);
        const slider = element.querySelector('.range-slider-ui');
        noUiSlider.create(slider, {
            start: [options.min, options.max],
            connect: true,
            range: {
                'min': options.min,
                'max': options.max
            },
            step: options.step,
            format: {
                to: value => Math.round(value),
                from: value => Number(value)
            }
        });
    });
</script>
@endsection