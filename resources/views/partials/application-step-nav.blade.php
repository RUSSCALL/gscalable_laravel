<div class="application-step-nav" data-js-only hidden>
    @if($n > 1)
        <button type="button" class="btn-gst-outline" data-step-prev>
            <i class="bi bi-arrow-left"></i> Previous
        </button>
    @endif
    <button type="button" class="btn-gst-primary" data-step-next>
        Next: {{ $steps[$n + 1]['title'] }} <i class="bi bi-arrow-right"></i>
    </button>
</div>
