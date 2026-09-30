@extends('admin.admin_layout')

@include('admin.jobs._form_assets')

@section('main_content')
    <div class="main-content">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title">Create Job Listing</h1>
                <a href="{{ route('jobListings') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Back to listings
                </a>
            </div>

            <form action="{{ route('jobs.preview') }}" method="POST">
                @include('admin.jobs._form')

                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="{{ route('jobListings') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-gst"><i class="bi bi-eye me-2"></i>Preview</button>
                </div>
            </form>
        </div>
    </div>
@endsection
