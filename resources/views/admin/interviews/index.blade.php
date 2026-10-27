@extends('admin.admin_layout')

@section('main_content')
    <div class="main-content">
        <div class="container-fluid p-4">
            <h1 class="page-title mb-4">Interviews</h1>

            <div class="row g-4">
                <div class="col-lg-8">
                    <ul class="nav nav-pills mb-3">
                        <li class="nav-item">
                            <a class="nav-link {{ $status ? '' : 'active bg-dark' }}" href="{{ route('admin.interviews.index') }}">Upcoming</a>
                        </li>
                        @foreach(\App\Models\Interview::STATUSES as $value => $label)
                            @continue($value === 'scheduled')
                            <li class="nav-item">
                                <a class="nav-link {{ $status === $value ? 'active bg-dark' : '' }}" href="{{ route('admin.interviews.index', ['status' => $value]) }}">{{ $label }}</a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr><th>When</th><th>Candidate</th><th>Format</th><th>Interviewers</th><th></th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse($interviews as $interview)
                                            <tr>
                                                <td class="text-nowrap">
                                                    {{ $interview->scheduled_at->format('D, M j') }}<br>
                                                    <small class="text-muted">{{ $interview->scheduled_at->format('g:i A') }} {{ $interview->timezone }}, {{ $interview->duration_minutes }} min</small>
                                                </td>
                                                <td>
                                                    @if($interview->application)
                                                        <a href="{{ route('admin.applicants.show', $interview->application) }}">{{ $interview->application->full_name }}</a>
                                                        <div class="small text-muted">{{ $interview->application->jobPosting->title ?? '' }}</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $interview->type_label }}
                                                    @if($interview->location)<div class="small text-muted text-break">{{ $interview->location }}</div>@endif
                                                </td>
                                                <td>{{ $interview->interviewers ?: '—' }}</td>
                                                <td class="text-end">
                                                    @if($interview->status === 'scheduled')
                                                        <div class="d-flex gap-1 justify-content-end">
                                                            @foreach(['completed' => 'Done', 'cancelled' => 'Cancel'] as $newStatus => $label)
                                                                <form method="POST" action="{{ route('admin.interviews.update', $interview) }}">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="hidden" name="status" value="{{ $newStatus }}">
                                                                    <button type="submit" class="btn btn-sm {{ $newStatus === 'completed' ? 'btn-outline-success' : 'btn-outline-danger' }}">{{ $label }}</button>
                                                                </form>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    {{ $status ? 'No ' . strtolower(\App\Models\Interview::STATUSES[$status]) . ' interviews.' : 'No upcoming interviews.' }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($interviews->hasPages())
                                <div class="mt-3">{{ $interviews->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0">Schedule an interview</h5></div>
                        <div class="card-body">
                            @include('admin.interviews._form', ['candidates' => $candidates])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
