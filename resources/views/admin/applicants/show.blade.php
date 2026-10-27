@extends('admin.admin_layout')

@php
    use Illuminate\Support\Str;

    $job = $application->jobPosting;
    $details = [
        'Email' => $application->email,
        'Phone' => $application->phone,
        'Current location' => $application->current_location,
        'Current company' => $application->current_company,
        'Current position' => $application->current_position,
        'Years of experience' => $application->years_of_experience,
        'Expected salary' => $application->expected_salary ? number_format((float) $application->expected_salary) . ' ' . ($job->salary_currency ?? 'USD') : null,
        'Highest degree' => $application->highest_degree,
        'Institution' => $application->education,
        'Availability to start' => $application->availability_label,
        'Work authorization' => $application->work_authorization_label,
        'Willing to relocate' => $application->willing_to_relocate === null ? null : ($application->willing_to_relocate ? 'Yes' : 'No'),
        'Heard about us via' => $application->referral_source,
    ];
@endphp

@section('main_content')
    <div class="main-content">
        <div class="container-fluid p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
                <div>
                    <h1 class="page-title mb-1">{{ $application->full_name }}</h1>
                    <div class="text-muted">
                        Applied for
                        @if($job)
                            <a href="{{ route('jobs.show', $job->id) }}">{{ $job->title }}</a>
                        @else
                            a deleted job
                        @endif
                        on {{ $application->created_at->format('M j, Y') }} &middot; Ref {{ $application->reference }}
                    </div>
                    <div class="mt-2 d-flex gap-2 align-items-center">
                        @include('admin.applicants._rank')
                        <span class="badge bg-light text-dark border">{{ Str::headline($application->status) }}</span>
                    </div>
                </div>
                <a href="{{ route('admin.applicants.index', $job ? ['job' => $job->id] : []) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Back to applicants
                </a>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Candidate details</h5></div>
                        <div class="card-body">
                            <dl class="row detail-list mb-0">
                                @foreach($details as $label => $value)
                                    <div class="col-sm-6">
                                        <dt>{{ $label }}</dt>
                                        <dd>{{ ($value === null || $value === '') ? '—' : $value }}</dd>
                                    </div>
                                @endforeach
                                @foreach(['LinkedIn' => $application->linkedin_url, 'GitHub' => $application->github_url, 'Portfolio' => $application->portfolio_url] as $label => $url)
                                    @if($url)
                                        <div class="col-sm-6">
                                            <dt>{{ $label }}</dt>
                                            <dd><a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $url }}</a></dd>
                                        </div>
                                    @endif
                                @endforeach
                                @foreach(['Skills' => $application->skills, 'Reason for leaving previous job' => $application->reason_for_leaving, 'Anything else' => $application->additional_information] as $label => $text)
                                    @if($text)
                                        <div class="col-12">
                                            <dt>{{ $label }}</dt>
                                            <dd>{!! nl2br(e($text)) !!}</dd>
                                        </div>
                                    @endif
                                @endforeach
                            </dl>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Documents</h5></div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <a href="{{ route('admin.applicants.download', [$application, 'resume']) }}">
                                        <i class="bi bi-file-earmark-person me-2"></i>Resume
                                    </a>
                                </li>
                                @if($application->cover_letter_path)
                                    <li class="mb-2">
                                        <a href="{{ route('admin.applicants.download', [$application, 'cover-letter']) }}">
                                            <i class="bi bi-file-earmark-text me-2"></i>Cover letter (file)
                                        </a>
                                    </li>
                                @endif
                                @foreach($application->attachments as $attachment)
                                    <li class="mb-2">
                                        <a href="{{ route('admin.applicants.download', [$application, $attachment->id]) }}">
                                            <i class="bi bi-paperclip me-2"></i>{{ $attachment->original_name }}
                                        </a>
                                        <small class="text-muted">({{ number_format($attachment->size / 1024) }} KB)</small>
                                    </li>
                                @endforeach
                            </ul>

                            @if($application->cover_letter)
                                <hr>
                                <h6>Cover letter</h6>
                                <div>{!! nl2br(e($application->cover_letter)) !!}</div>
                            @endif
                        </div>
                    </div>

                    @if($application->screenerAnswers->isNotEmpty())
                        <div class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">Screener answers</h5></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr><th>Question</th><th>Importance</th><th>Needed</th><th>Answer</th><th>Met</th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach($application->screenerAnswers as $answer)
                                                <tr>
                                                    <td>{{ $answer->question }}</td>
                                                    <td>{{ \App\Models\JobScreenerQuestion::IMPORTANCE[$answer->importance] ?? $answer->importance }}</td>
                                                    <td>{{ $answer->required_answer ? 'Yes' : 'No' }}</td>
                                                    <td>{{ $answer->answer ? 'Yes' : 'No' }}</td>
                                                    <td>
                                                        @if($answer->isMet())
                                                            <i class="bi bi-check-circle-fill text-success" aria-label="Met"></i>
                                                        @else
                                                            <i class="bi bi-x-circle-fill text-danger" aria-label="Not met"></i>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="card mb-4" id="messages">
                        <div class="card-header"><h5 class="mb-0">Messages</h5></div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.messages.store', $application) }}" class="mb-4">
                                @csrf
                                <div class="mb-2">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" maxlength="191" required
                                           value="{{ old('subject', 'Your application for ' . ($job->title ?? 'Global Scalable Technologies')) }}">
                                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-2">
                                    <label for="body" class="form-label">Message</label>
                                    <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="5" maxlength="10000" required>{{ old('body') }}</textarea>
                                    @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-text">Sent to {{ $application->email }}. Replies come to your own inbox ({{ auth()->user()->email }}).</div>
                                </div>
                                <button type="submit" class="btn btn-gst"><i class="bi bi-send me-2"></i>Send email</button>
                            </form>

                            @forelse($application->messages as $sent)
                                <div class="border-top pt-3 mt-3">
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <strong>{{ $sent->subject }}</strong>
                                        <small class="text-muted">{{ $sent->sender->name ?? 'A former admin' }} &middot; {{ $sent->created_at->format('M j, Y g:i A') }} UTC</small>
                                    </div>
                                    <div class="mt-1">{!! nl2br(e($sent->body)) !!}</div>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No messages sent yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Status</h5></div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.applicants.status', $application) }}">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label for="status" class="form-label">Application status</label>
                                    <select class="form-select" id="status" name="status">
                                        @foreach(\App\Models\JobApplication::STATUSES as $status)
                                            <option value="{{ $status }}" @selected($application->status === $status)>{{ Str::headline($status) }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Applicants see this status on their dashboard.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="admin_notes" class="form-label">Internal notes</label>
                                    <textarea class="form-control" id="admin_notes" name="admin_notes" rows="4" maxlength="5000">{{ old('admin_notes', $application->admin_notes) }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-gst w-100">Save</button>
                            </form>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Interviews</h5></div>
                        <div class="card-body">
                            @foreach($application->interviews as $interview)
                                <div class="border rounded p-2 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $interview->scheduled_at->format('M j, Y g:i A') }} {{ $interview->timezone }}</strong>
                                        <span class="badge bg-light text-dark border">{{ \App\Models\Interview::STATUSES[$interview->status] ?? $interview->status }}</span>
                                    </div>
                                    <div class="small text-muted">{{ $interview->type_label }}, {{ $interview->duration_minutes }} min</div>
                                    @if($interview->location)<div class="small text-break">{{ $interview->location }}</div>@endif
                                    @if($interview->interviewers)<div class="small">With {{ $interview->interviewers }}</div>@endif
                                    @if($interview->status === 'scheduled')
                                        <div class="d-flex gap-2 mt-2">
                                            @foreach(['completed' => 'Mark completed', 'cancelled' => 'Cancel'] as $newStatus => $label)
                                                <form method="POST" action="{{ route('admin.interviews.update', $interview) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="{{ $newStatus }}">
                                                    <button type="submit" class="btn btn-sm {{ $newStatus === 'completed' ? 'btn-outline-success' : 'btn-outline-danger' }}">{{ $label }}</button>
                                                </form>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            <h6 class="mt-2">Schedule an interview</h6>
                            @include('admin.interviews._form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
