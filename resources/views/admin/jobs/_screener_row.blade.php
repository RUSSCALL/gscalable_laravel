<div class="row g-2 align-items-start mb-2 js-screener-row">
    <div class="col-md-6">
        <input type="text" class="form-control @error("screener_questions.$index.question") is-invalid @enderror"
               name="screener_questions[{{ $index }}][question]" value="{{ $row['question'] ?? '' }}" maxlength="255"
               placeholder="e.g. Do you hold an active Secret clearance?" aria-label="Question">
        @error("screener_questions.$index.question")<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2">
        <select class="form-select" name="screener_questions[{{ $index }}][importance]" aria-label="Importance">
            @foreach(\App\Models\JobScreenerQuestion::IMPORTANCE as $value => $label)
                <option value="{{ $value }}" @selected(($row['importance'] ?? 'essential') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" name="screener_questions[{{ $index }}][required_answer]" aria-label="Answer needed">
            <option value="1" @selected((string) ($row['required_answer'] ?? '1') === '1')>Needs "Yes"</option>
            <option value="0" @selected((string) ($row['required_answer'] ?? '1') === '0')>Needs "No"</option>
        </select>
    </div>
    <div class="col-md-1 text-end">
        <button type="button" class="btn btn-outline-danger js-remove-screener" aria-label="Remove question">
            <i class="bi bi-trash"></i>
        </button>
    </div>
</div>
