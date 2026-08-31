<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\DetectsAutomatedSubmissions;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobAlertRequest extends FormRequest
{
    use DetectsAutomatedSubmissions;

    /**
     * Three fields, not twenty -- a genuine subscriber can finish this in
     * about a second, so the application form's threshold would reject them.
     */
    protected function minimumFillSeconds(): int
    {
        return 1;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email:rfc|max:191',
            'category_id' => 'nullable|integer|exists:job_categories,id',
            'location_id' => 'nullable|integer|exists:job_locations,id',
            'keywords' => 'nullable|string|max:120',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Please enter an email address.',
            'email.email' => 'Please enter a valid email address.',
        ];
    }

    /** Normalised so the unique index matches regardless of how it was typed. */
    public function subscriptionData(): array
    {
        return [
            'email' => strtolower(trim($this->input('email'))),
            'category_id' => $this->filled('category_id') ? (int) $this->input('category_id') : null,
            'location_id' => $this->filled('location_id') ? (int) $this->input('location_id') : null,
            'keywords' => $this->filled('keywords') ? trim($this->input('keywords')) : null,
        ];
    }
}
