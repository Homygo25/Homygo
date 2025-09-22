@extends('layouts.app')

@section('title', '403 - Forbidden')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-0 min-vh-100">
        <div class="col-12 d-flex align-items-center justify-content-center">
            <div class="text-center">
                <div class="mb-4">
                    <i class="fi-lock text-danger" style="font-size: 120px;"></i>
                </div>
                <h1 class="h2 mb-3">Access Forbidden</h1>
                <p class="fs-lg text-muted mb-4">
                    You don't have permission to access this resource.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fi-home me-2"></i>
                        Go Home
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                        <i class="fi-user me-2"></i>
                        Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
