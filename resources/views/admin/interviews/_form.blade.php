{{-- Pass $application to schedule for one candidate, or $candidates to pick from a list. --}}
<form method="POST" action="{{ route('admin.interviews.store') }}">
    @csrf
    @isset($application)
        <input type="hidden" name="job_application_id" value="{{ $application->id }}">
    @else
        <div class="mb-3">
            <label for="job_application_id" class="form-label">Candidate <span class="text-danger">*</span></label>
            <select class="form-select @error('job_application_id') is-invalid @enderror" id="job_application_id" name="job_application_id" required>
                <option value="">Select a candidate</option>
                @foreach($candidates as $candidate)
                    <option value="{{ $candidate->id }}" @selected(old('job_application_id') == $candidate->id)>
                        {{ $candidate->full_name }} &mdash; {{ $candidate->jobPosting->title ?? 'Deleted job' }}
                    </option>
                @endforeach
            </select>
            @error('job_application_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @endisset

    <div class="row g-2 mb-3">
        <div class="col-sm-7">
            <label for="scheduled_at" class="form-label">Date and time <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control @error('scheduled_at') is-invalid @enderror" id="scheduled_at" name="scheduled_at"
                   value="{{ old('scheduled_at') }}" required>
            @error('scheduled_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-sm-5">
            <label for="timezone" class="form-label">Time zone</label>
            <select class="form-select" id="timezone" name="timezone">
                @foreach(\App\Models\Interview::TIMEZONES as $value => $label)
                    <option value="{{ $value }}" @selected(old('timezone', 'ET') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-sm-6">
            <label for="type" class="form-label">Format</label>
            <select class="form-select" id="type" name="type">
                @foreach(\App\Models\Interview::TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(old('type', 'video') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6">
            <label for="duration_minutes" class="form-label">Length</label>
            <select class="form-select" id="duration_minutes" name="duration_minutes">
                @foreach([30, 45, 60, 90, 120] as $minutes)
                    <option value="{{ $minutes }}" @selected((int) old('duration_minutes', 60) === $minutes)>{{ $minutes }} minutes</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label for="location" class="form-label">Meeting link, phone number or address</label>
        <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location"
               value="{{ old('location') }}" maxlength="255">
        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="interviewers" class="form-label">Interviewers</label>
        <input type="text" class="form-control" id="interviewers" name="interviewers" value="{{ old('interviewers') }}" maxlength="255"
               placeholder="e.g. Jane Smith, Hiring Manager">
    </div>

    <div class="mb-3">
        <label for="notes" class="form-label">Internal notes</label>
        <textarea class="form-control" id="notes" name="notes" rows="2" maxlength="5000">{{ old('notes') }}</textarea>
        <div class="form-text">Not sent to the candidate.</div>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" name="notify_candidate" value="0">
        <input class="form-check-input" type="checkbox" id="notify_candidate" name="notify_candidate" value="1" @checked(old('notify_candidate', true))>
        <label class="form-check-label" for="notify_candidate">Email the candidate the interview details</label>
    </div>

    <button type="submit" class="btn btn-gst w-100"><i class="bi bi-calendar-plus me-2"></i>Schedule interview</button>
</form>
