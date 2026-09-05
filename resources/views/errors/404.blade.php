@extends('main_layout')

@section('title', 'Page not found | Global Scalable Technologies')
@section('meta_description', 'The page you were looking for could not be found.')

@section('main_content')

<section class="error-page">
    <div class="container">
        <div class="error-card gst-card-base">
            <p class="error-code">404</p>
            <h1>We can&rsquo;t find that page</h1>
            <p class="error-message">
                The link may be out of date, or the page may have moved. If you were
                looking for a job posting, it may have closed since you saved the link.
            </p>
            <div class="error-actions">
                <a href="{{ route('careers') }}" class="btn-gst-primary">
                    <i class="bi bi-briefcase"></i> Browse open roles
                </a>
                <a href="{{ url('/') }}" class="btn-gst-ghost">
                    Back to homepage
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
