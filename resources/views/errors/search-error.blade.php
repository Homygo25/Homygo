@extends('layouts.app')

@section('title', 'Search Error')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-0 min-vh-100">
        <div class="col-12 d-flex align-items-center justify-content-center">
            <div class="text-center">
                <div class="mb-4">
                    <i class="fi-search text-warning" style="font-size: 120px;"></i>
                </div>
                <h1 class="h2 mb-3">Search Error</h1>
                <p class="fs-lg text-muted mb-4">
                    There was an error processing your search. Please try again.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fi-home me-2"></i>
                        Go Home
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        <i class="fi-arrow-left me-2"></i>
                        Try Again
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
