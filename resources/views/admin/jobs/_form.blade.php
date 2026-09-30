@php
    // Keep a legacy stored value selectable so editing never silently changes it.
    $withCurrent = fn (array $options, ?string $current) => $current && ! in_array($current, $options, true)
        ? [...$options, $current]
        : $options;
@endphp

@csrf

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Basics</h5></div>
    <div class="card-body">
        <div class="mb-3">
            <label for="title" class="form-label">Job title <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title"
                   value="{{ old('title', $job->title) }}" maxlength="255" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        @php
            // An inactive category is hidden from the list but must stay selectable on its own job.
            if ($job->category && ! $categories->contains('id', $job->category_id)) {
                $categories = $categories->push($job->category);
            }
            $newCategory = \App\Http\Requests\JobPostingRequest::newName(old('category_id'));
            $newLocation = \App\Http\Requests\JobPostingRequest::newName(old('location_id'));
        @endphp
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select js-creatable @error('category_id') is-invalid @enderror" id="category_id" name="category_id"
                        data-placeholder="Search or add a category">
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $job->category_id) == $category->id)>{{ $category->name }}@unless($category->is_active) (inactive)@endunless</option>
                    @endforeach
                    @if($newCategory)
                        <option value="{{ old('category_id') }}" selected>{{ $newCategory }} (new)</option>
                    @endif
                </select>
                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Pick one, or type a new category name to add it.</div>
            </div>
            <div class="col-md-6">
                <label for="location_id" class="form-label">Location</label>
                <select class="form-select js-creatable @error('location_id') is-invalid @enderror" id="location_id" name="location_id"
                        data-placeholder="Search or add a location" data-create-pattern="^[^,]+,[^,]+(,[^,]+)?$">
                    <option value="">Select location</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" @selected(old('location_id', $job->location_id) == $location->id)>
                            {{ $location->city }}, {{ $location->country }}@if($location->is_remote) (Remote)@endif
                        </option>
                    @endforeach
                    @if($newLocation)
                        <option value="{{ old('location_id') }}" selected>{{ $newLocation }} (new)</option>
                    @endif
                </select>
                @error('location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Pick one, or type a new one as "City, Country".</div>
                <div class="form-check mt-2">
                    <input type="hidden" name="is_remote" value="0">
                    <input class="form-check-input" type="checkbox" id="is_remote" name="is_remote" value="1"
                           @checked(old('is_remote', $job->location?->is_remote))>
                    <label class="form-check-label" for="is_remote">Remote position (used when no location is selected)</label>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="employment_type" class="form-label">Employment type <span class="text-danger">*</span></label>
                <select class="form-select @error('employment_type') is-invalid @enderror" id="employment_type" name="employment_type" required>
                    <option value="">Select type</option>
                    @foreach($withCurrent(\App\Models\JobPosting::EMPLOYMENT_TYPES, $job->employment_type) as $type)
                        <option value="{{ $type }}" @selected(old('employment_type', $job->employment_type) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                @error('employment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="experience_level" class="form-label">Experience level <span class="text-danger">*</span></label>
                <select class="form-select @error('experience_level') is-invalid @enderror" id="experience_level" name="experience_level" required>
                    <option value="">Select level</option>
                    @foreach($withCurrent(\App\Models\JobPosting::EXPERIENCE_LEVELS, $job->experience_level) as $level)
                        <option value="{{ $level }}" @selected(old('experience_level', $job->experience_level) === $level)>{{ $level }}</option>
                    @endforeach
                </select>
                @error('experience_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Compensation &amp; deadline</h5></div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label for="salary_min" class="form-label">Minimum salary ({{ $job->salary_currency ?: 'USD' }})</label>
                <input type="number" class="form-control @error('salary_min') is-invalid @enderror" id="salary_min" name="salary_min"
                       value="{{ old('salary_min', $job->salary_min) }}" min="0" step="0.01">
                @error('salary_min')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="salary_max" class="form-label">Maximum salary ({{ $job->salary_currency ?: 'USD' }})</label>
                <input type="number" class="form-control @error('salary_max') is-invalid @enderror" id="salary_max" name="salary_max"
                       value="{{ old('salary_max', $job->salary_max) }}" min="0" step="0.01">
                @error('salary_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="salary_period" class="form-label">Salary period</label>
                <select class="form-select @error('salary_period') is-invalid @enderror" id="salary_period" name="salary_period">
                    <option value="">Select period</option>
                    @foreach(\App\Models\JobPosting::SALARY_PERIODS as $period)
                        <option value="{{ $period }}" @selected(old('salary_period', $job->salary_period) === $period)>{{ $period }}</option>
                    @endforeach
                </select>
                @error('salary_period')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-text mb-3">Leave both salary fields empty to show "Competitive".</div>

        <div class="col-md-4">
            <label for="application_deadline" class="form-label">Application deadline <span class="text-danger">*</span></label>
            <input type="date" class="form-control @error('application_deadline') is-invalid @enderror" id="application_deadline" name="application_deadline"
                   value="{{ old('application_deadline', $job->application_deadline?->format('Y-m-d')) }}" required>
            @error('application_deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Applications close at the end of this day.</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Description</h5></div>
    <div class="card-body">
        @foreach([
            'description' => ['Job description', true],
            'responsibilities' => ['Responsibilities', true],
            'requirements' => ['Requirements', true],
            'benefits' => ['Benefits', false],
        ] as $field => [$label, $required])
            <div class="mb-3">
                <label for="{{ $field }}" class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
                {{-- Holds the editor's HTML; replaced by a Trix editor once the script loads. --}}
                <textarea class="form-control js-rich-text @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}"
                          rows="6" aria-label="{{ $label }}" @required($required)>{{ old($field, \App\Support\RichText::toEditor($job->$field)) }}</textarea>
                @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        @endforeach
        <div class="form-text">Use the toolbar for bold, italics, headings, lists and links. Pasted formatting is cleaned up on save.</div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Visibility</h5></div>
    <div class="card-body">
        <div class="form-check form-switch mb-2">
            <input type="hidden" name="is_published" value="0">
            <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1"
                   @checked(old('is_published', $job->is_published))>
            <label class="form-check-label" for="is_published">Published (visible on the careers page)</label>
        </div>
        <div class="form-check form-switch">
            <input type="hidden" name="is_featured" value="0">
            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1"
                   @checked(old('is_featured', $job->is_featured))>
            <label class="form-check-label" for="is_featured">Featured (pinned to the top of the board)</label>
        </div>
    </div>
</div>
