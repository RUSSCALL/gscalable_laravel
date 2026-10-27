<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\DetectsAutomatedSubmissions;
use App\Models\DisabilitySelfIdentification;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class StoreJobApplicationRequest extends FormRequest
{
    use DetectsAutomatedSubmissions;

    public const MAX_ATTACHMENTS = 5;

    private ?Collection $screenerQuestions = null;

    public function authorize(): bool
    {
        // Applying is open to everyone, including guests.
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:191',
            'phone' => 'required|string|max:30',
            'current_location' => 'required|string|max:191',
            'cover_letter' => 'nullable|string|max:10000',
            'cover_letter_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'resume_path' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB
            'attachments' => 'nullable|array|max:' . self::MAX_ATTACHMENTS,
            'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'portfolio_url' => 'nullable|url|max:191',
            'linkedin_url' => 'nullable|url|max:191',
            'github_url' => 'nullable|url|max:191',
            'additional_information' => 'nullable|string',
            'skills' => 'nullable|string',
            'current_company' => 'nullable|string|max:191',
            'current_position' => 'nullable|string|max:191',
            'reason_for_leaving' => 'nullable|string|max:2000',
            'availability' => ['required', Rule::in(array_keys(JobApplication::AVAILABILITY))],
            'availability_other' => 'nullable|required_if:availability,other|string|max:191',
            'work_authorization' => ['required', Rule::in(array_keys(JobApplication::WORK_AUTHORIZATION))],
            'willing_to_relocate' => 'required|boolean',
            'education' => 'required|string|max:191',
            'highest_degree' => 'required|string|max:191',
            'expected_salary' => 'nullable|numeric|min:0',
            'years_of_experience' => 'required|integer|min:0',
            'referral_source' => 'nullable|string|max:191',
            'disability_status' => ['nullable', Rule::in(array_keys(DisabilitySelfIdentification::OPTIONS))],
            'terms_agree' => 'required|accepted',
        ];

        foreach ($this->screenerQuestions() as $question) {
            $rules['screener.' . $question->id] = 'required|boolean';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'terms_agree.required' => 'Please confirm your information is accurate before submitting.',
            'terms_agree.accepted' => 'Please confirm your information is accurate before submitting.',
            'resume_path.required' => 'Please attach your resume.',
            'resume_path.mimes' => 'Your resume must be a PDF, DOC or DOCX file.',
            'resume_path.max' => 'Your resume must be smaller than 5MB.',
            'cover_letter_file.mimes' => 'Your cover letter must be a PDF, DOC or DOCX file.',
            'cover_letter_file.max' => 'Your cover letter must be smaller than 5MB.',
            'attachments.max' => 'You can attach up to ' . self::MAX_ATTACHMENTS . ' documents.',
            'attachments.*.mimes' => 'Attachments must be PDF, DOC, DOCX, JPG or PNG files.',
            'attachments.*.max' => 'Each attachment must be smaller than 5MB.',
            'availability_other.required_if' => 'Tell us when you could start.',
            'willing_to_relocate.required' => 'Please tell us whether you are willing to relocate.',
            'screener.*.required' => 'Please answer every screening question.',
        ];
    }

    /** The job's screener questions, looked up from the slug in the URL. */
    public function screenerQuestions(): Collection
    {
        return $this->screenerQuestions ??= JobPosting::where('slug', $this->route('slug'))
            ->first()?->screenerQuestions ?? collect();
    }

    /**
     * Only the fields that belong on the model — files, screener answers, the
     * disability answer (stored apart) and the transport fields never reach it.
     */
    public function applicationData(): array
    {
        return $this->safe()->except([
            'terms_agree', 'cover_letter_file', 'attachments', 'screener', 'disability_status',
        ]);
    }
}
