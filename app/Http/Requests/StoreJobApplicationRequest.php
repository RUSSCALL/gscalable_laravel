<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class StoreJobApplicationRequest extends FormRequest
{
    /**
     * Minimum seconds between the form rendering and being submitted. A human
     * filling in this many fields takes far longer; a script does not.
     */
    private const MIN_FILL_SECONDS = 3;

    public function authorize(): bool
    {
        // Applying is open to everyone, including guests.
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:191',
            'phone' => 'required|string|max:30',
            'cover_letter' => 'nullable|string',
            'resume_path' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB
            'portfolio_url' => 'nullable|url|max:191',
            'linkedin_url' => 'nullable|url|max:191',
            'github_url' => 'nullable|url|max:191',
            'additional_information' => 'nullable|string',
            'skills' => 'nullable|string',
            'current_company' => 'nullable|string|max:191',
            'current_position' => 'nullable|string|max:191',
            'education' => 'required|string|max:191',
            'highest_degree' => 'required|string|max:191',
            'expected_salary' => 'nullable|numeric|min:0',
            'years_of_experience' => 'required|integer|min:0',
            'referral_source' => 'nullable|string|max:191',
            'terms_agree' => 'required|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'terms_agree.required' => 'Please confirm your information is accurate before submitting.',
            'terms_agree.accepted' => 'Please confirm your information is accurate before submitting.',
            'resume_path.required' => 'Please attach your resume.',
            'resume_path.mimes' => 'Your resume must be a PDF, DOC or DOCX file.',
            'resume_path.max' => 'Your resume must be smaller than 5MB.',
        ];
    }

    /**
     * Only the fields that belong on the model — the honeypot and timestamp
     * are transport concerns and must never reach the database.
     */
    public function applicationData(): array
    {
        return $this->safe()->except(['terms_agree']);
    }

    /**
     * True when this submission looks automated.
     *
     * Deliberately not expressed as a validation rule: a bot must not be told
     * which check it failed, so the controller handles this with a single
     * neutral response rather than a field-specific error.
     */
    public function looksAutomated(): bool
    {
        // The honeypot is visually hidden and hidden from assistive tech, so
        // only a script fills it in.
        if (filled($this->input('website'))) {
            return true;
        }

        $stamp = $this->input('_ts');

        // A missing or unreadable timestamp means the form was not rendered
        // by us in this session.
        if (! is_string($stamp) || $stamp === '') {
            return true;
        }

        try {
            $renderedAt = (int) Crypt::decryptString($stamp);
        } catch (DecryptException $e) {
            // Encrypted server-side, so this cannot be forged or back-dated.
            return true;
        }

        return (time() - $renderedAt) < self::MIN_FILL_SECONDS;
    }
}
