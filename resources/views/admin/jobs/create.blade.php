@extends('admin.admin_layout')

@include('admin.jobs._form_assets')

@section('title', 'Create Job Listing')

@section('crumb_parent')
    <a href="{{ route('jobListings') }}">Job Listings</a>
@endsection

@section('main_content')
    @component('admin._page_header', ['eyebrow' => 'Job listing', 'title' => 'Create Job Listing'])
        <a href="{{ route('jobListings') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to listings
        </a>
    @endcomponent

    <form action="{{ route('jobs.preview') }}" method="POST">
        @include('admin.jobs._form')

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('jobListings') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-gst"><i class="bi bi-eye me-2"></i>Preview</button>
        </div>
    </form>
@endsection
