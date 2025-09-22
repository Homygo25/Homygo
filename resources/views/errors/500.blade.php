@extends('layouts.app')

@section('title', '500 - Server Error')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-0 min-vh-100">
        <div class="col-12 d-flex align-items-center justify-content-center">
            <div class="text-center">
                <div class="mb-4">
                    <i class="fi-alert-triangle text-warning" style="font-size: 120px;"></i>
                </div>
                <h1 class="h2 mb-3">Server Error</h1>
                <p class="fs-lg text-muted mb-4">
                    Something went wrong on our end. We're working to fix this issue.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fi-home me-2"></i>
                        Go Home
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        <i class="fi-arrow-left me-2"></i>
                        Go Back
                    </a>
                </div>
                @if(config('app.debug'))
                <div class="mt-4">
                    <details class="text-start">
                        <summary class="btn btn-link">Show Error Details</summary>
                        <div class="mt-3 p-3 bg-light rounded">
                            <pre class="small">{{ $exception ?? 'No details available' }}</pre>
                        </div>
                    </details>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
