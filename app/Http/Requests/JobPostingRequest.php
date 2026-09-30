<?php

namespace App\Http\Requests;

use App\Models\JobCategory;
use App\Models\JobLocation;
use App\Models\JobPosting;
use App\Support\RichText;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobPostingRequest extends FormRequest
{
    // Prefix the form's pickers put on a typed-in option that doesn't exist yet.
    public const NEW_PREFIX = 'new:';

    public const RICH_TEXT_FIELDS = ['description', 'responsibilities', 'requirements', 'benefits'];

    public function authorize(): bool
    {
        return $this->user()?->can('user-is-admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        // Unticked checkboxes are simply absent from the POST.
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
            'is_published' => $this->boolean('is_published'),
            'is_remote' => $this->boolean('is_remote'),
        ]);

        // Sanitize editor HTML before validating, so an editor left blank ("<div><br></div>") fails "required".
        foreach (self::RICH_TEXT_FIELDS as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => RichText::clean($this->input($field))]);
            }
        }
    }

    public function rules(): array
    {
        $deadline = ['required', 'date'];

        // Expired published jobs are hidden from /careers; only old drafts may keep a past date.
        if (! $this->route('id') || $this->boolean('is_published')) {
            $deadline[] = 'after_or_equal:today';
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', function ($attribute, $value, $fail) {
                $name = self::newName($value);
                if ($name !== null) {
                    if ($name === '' || mb_strlen($name) > 100) {
                        $fail('A new category name must be 1 to 100 characters.');
                    }
                } elseif (! ctype_digit((string) $value) || ! JobCategory::whereKey($value)->exists()) {
                    $fail('The selected category is invalid.');
                }
            }],
            'location_id' => ['nullable', function ($attribute, $value, $fail) {
                $label = self::newName($value);
                if ($label !== null) {
                    if (! JobLocation::parseLabel($label)) {
                        $fail('Enter a new location as "City, Country" or "City, State, Country".');
                    }
                } elseif (! ctype_digit((string) $value) || ! JobLocation::whereKey($value)->exists()) {
                    $fail('The selected location is invalid.');
                }
            }],
            'is_remote' => ['boolean'],
            'description' => ['required', 'string', 'max:20000'],
            'requirements' => ['required', 'string', 'max:20000'],
            'responsibilities' => ['required', 'string', 'max:20000'],
            'benefits' => ['nullable', 'string', 'max:20000'],
            'employment_type' => ['required', Rule::in($this->allowed('employment_type', JobPosting::EMPLOYMENT_TYPES))],
            'experience_level' => ['required', Rule::in($this->allowed('experience_level', JobPosting::EXPERIENCE_LEVELS))],
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            // gte fails outright against an empty min, so only compare when one was given.
            'salary_max' => array_filter(['nullable', 'numeric', 'min:0', 'max:99999999', $this->filled('salary_min') ? 'gte:salary_min' : null]),
            'salary_period' => ['nullable', 'required_with:salary_min,salary_max', Rule::in(JobPosting::SALARY_PERIODS)],
            'application_deadline' => $deadline,
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'application_deadline.after_or_equal' => 'The application deadline cannot be in the past for a published or new job.',
            'salary_max.gte' => 'The maximum salary must be at least the minimum salary.',
            'salary_period.required_with' => 'Choose a salary period when a salary is given.',
        ];
    }

    /** The typed-in text of a "new:" picker value, or null for an existing id. */
    public static function newName(mixed $value): ?string
    {
        return is_string($value) && str_starts_with($value, self::NEW_PREFIX)
            ? trim(substr($value, strlen(self::NEW_PREFIX)))
            : null;
    }

    // Jobs saved before the option lists existed keep their current value valid.
    private function allowed(string $field, array $options): array
    {
        $current = $this->route('id') ? JobPosting::whereKey($this->route('id'))->value($field) : null;

        return $current ? array_unique([...$options, $current]) : $options;
    }
}
