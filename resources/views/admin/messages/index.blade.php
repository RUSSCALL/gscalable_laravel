@extends('admin.admin_layout')

@section('title', 'Messages')

@section('main_content')
    @component('admin._page_header', ['eyebrow' => 'Hiring', 'title' => 'Messages'])
        @slot('subtitle')
            Every email sent to a candidate from their application page. To write a new one, open the candidate under
            <a href="{{ route('admin.applicants.index') }}">Applicants</a>.
        @endslot
    @endcomponent

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.messages.index') }}" method="GET" class="row g-3">
                <div class="col-md-6">
                    <label for="q" class="form-label">Search</label>
                    <input type="search" class="form-control" id="q" name="q" value="{{ $search }}" placeholder="Candidate name, email or subject">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-gst"><i class="bi bi-search me-1"></i> Search</button>
                    <a href="{{ route('admin.messages.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr><th>Sent</th><th>To</th><th>Job</th><th>Subject</th><th>From</th></tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $sent)
                            <tr>
                                <td class="text-nowrap">{{ $sent->created_at->format('M j, Y') }}</td>
                                <td>
                                    @if($sent->application)
                                        <a href="{{ route('admin.applicants.show', $sent->application) }}#messages" class="cell-title">{{ $sent->application->full_name }}</a>
                                    @endif
                                </td>
                                <td>{{ $sent->application?->jobPosting?->title ?? '—' }}</td>
                                <td>{{ $sent->subject }}</td>
                                <td>{{ $sent->sender->name ?? 'A former admin' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No messages sent yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($messages->hasPages())
            <div class="card-footer">{{ $messages->links() }}</div>
        @endif
    </div>
@endsection
