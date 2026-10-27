@extends('admin.admin_layout')

@section('main_content')
    <div class="main-content">
        <div class="container-fluid p-4">
            <h1 class="page-title mb-2">Onboarding</h1>
            <p class="text-muted mb-4">
                Candidates appear here once their application is marked <strong>Hired</strong>. Tick each step as it's done.
            </p>

            @forelse($hires as $hire)
                @php
                    $done = $hire->onboardingCompletions->keyBy('task');
                    $percent = (int) round($done->count() / count($tasks) * 100);
                @endphp
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <div>
                                <a href="{{ route('admin.applicants.show', $hire) }}" class="fw-semibold text-decoration-none">{{ $hire->full_name }}</a>
                                <span class="text-muted">&middot; {{ $hire->jobPosting->title ?? 'Deleted job' }}</span>
                            </div>
                            <span class="small text-muted">{{ $done->count() }} of {{ count($tasks) }} done</span>
                        </div>
                        <div class="progress mb-3" role="progressbar" aria-label="Onboarding progress for {{ $hire->full_name }}"
                             aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $percent }}%"></div>
                        </div>
                        <div class="row g-2">
                            @foreach($tasks as $key => $label)
                                <div class="col-md-6 col-xl-4">
                                    <form method="POST" action="{{ route('admin.onboarding.toggle', [$hire, $key]) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="task-{{ $hire->id }}-{{ $key }}"
                                                   @checked($done->has($key)) onchange="this.form.submit()">
                                            <label class="form-check-label" for="task-{{ $hire->id }}-{{ $key }}">
                                                {{ $label }}
                                                @if($done->has($key))
                                                    <small class="text-muted d-block">{{ $done[$key]->created_at->format('M j, Y') }}</small>
                                                @endif
                                            </label>
                                        </div>
                                        <noscript><button type="submit" class="btn btn-sm btn-link p-0">Toggle</button></noscript>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        Nobody to onboard yet. Mark an application as <strong>Hired</strong> on the
                        <a href="{{ route('admin.applicants.index') }}">Applicants</a> page and they'll appear here.
                    </div>
                </div>
            @endforelse

            @if($hires->hasPages())
                <div class="mt-3">{{ $hires->links() }}</div>
            @endif
        </div>
    </div>
@endsection
