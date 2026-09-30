<div class="job-preview-bar" role="region" aria-label="Job preview">
    <div class="container job-preview-bar__inner">
        <p>
            <strong>Preview</strong> &mdash; this is how applicants will see this job.
            Status after saving: <strong>{{ $job->is_published ? 'Published' : 'Draft' }}</strong>.
        </p>
        <form method="POST" action="{{ $preview['action'] }}" class="job-preview-bar__actions" id="job-preview-form">
            @csrf
            @if($preview['method'] === 'PUT')
                @method('PUT')
            @endif
            @foreach($preview['fields'] as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <button type="submit" name="intent" value="edit" class="btn-gst-ghost">
                <i class="bi bi-pencil"></i> Keep editing
            </button>
            <button type="submit" class="btn-gst-primary">
                <i class="bi bi-check2"></i> I like how it looks &mdash; Save
            </button>
        </form>
    </div>
</div>
<script>
    document.querySelectorAll('body > :not(.job-preview-bar)').forEach(function (el) { el.inert = true; });

    // Guard against a double click creating the job twice.
    var previewForm = document.getElementById('job-preview-form');
    previewForm.addEventListener('submit', function (e) {
        if (previewForm.dataset.sent) {
            e.preventDefault();
        }
        previewForm.dataset.sent = '1';
    });
</script>
