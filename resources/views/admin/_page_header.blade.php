{{-- Pass eyebrow and title; optional subtitle and meta slots; the default slot holds the page's buttons. --}}
<div class="page-header">
    <div>
        <p class="page-eyebrow">{{ $eyebrow }}</p>
        <h1 class="page-title">{{ $title }}</h1>
        @isset($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endisset
        @isset($meta)
            <div class="page-meta">{{ $meta }}</div>
        @endisset
    </div>
    @if(trim($slot))
        <div class="page-actions">{{ $slot }}</div>
    @endif
</div>
