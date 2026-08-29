<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_posting_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'cover_letter',
        'resume_path',
        'portfolio_url',
        'linkedin_url',
        'github_url',
        'additional_information',
        'skills',
        'current_company',
        'current_position',
        'education',
        'highest_degree',
        'expected_salary',
        'years_of_experience',
        'referral_source',
        'status',
        'admin_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expected_salary' => 'decimal:2',
        'years_of_experience' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    /**
     * `reference` is intentionally absent from $fillable — it identifies the
     * application to the applicant and must never be settable from input.
     */
    protected static function booted(): void
    {
        static::creating(function (self $application) {
            if (empty($application->reference)) {
                $application->reference = static::generateReference();
            }
        });
    }

    /**
     * An opaque public reference. The auto-increment id is not used for this:
     * sequential numbers would leak total application volume to anyone who
     * applies twice.
     */
    public static function generateReference(): string
    {
        // Ambiguous characters (0/O, 1/I) excluded so a reference survives
        // being read over the phone or copied off a screen.
        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

        do {
            $token = '';
            for ($i = 0; $i < 8; $i++) {
                $token .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $reference = 'GST-' . substr($token, 0, 4) . '-' . substr($token, 4, 4);
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Look an application up by the reference an applicant quotes.
     */
    public function scopeReference($query, string $reference)
    {
        return $query->where('reference', strtoupper(trim($reference)));
    }

    /**
     * How each stored status is presented to the applicant.
     *
     * The column values are internal ("under_review"); these are the words the
     * applicant actually reads, plus the tone the badge is drawn in. `tone` is
     * a state, not a colour name, so the stylesheet owns the palette.
     */
    private const STATUS_PRESENTATION = [
        'submitted' => ['Submitted', 'neutral', 'We have your application.'],
        'under_review' => ['Under review', 'active', 'Our team is reading your application.'],
        'interview_scheduled' => ['Interview scheduled', 'active', 'Check your email for the details.'],
        'interviewed' => ['Interviewed', 'active', 'Thanks for meeting us — we are considering next steps.'],
        'shortlisted' => ['Shortlisted', 'positive', 'You are among the candidates we are taking forward.'],
        'offer_made' => ['Offer made', 'positive', 'Check your email for the offer.'],
        'offer_accepted' => ['Offer accepted', 'positive', 'Welcome aboard — we will be in touch about onboarding.'],
        'hired' => ['Hired', 'positive', 'Welcome to the team.'],
        'rejected' => ['Not progressing', 'closed', 'We are not moving forward with this application.'],
        'offer_declined' => ['Offer declined', 'closed', 'You declined this offer.'],
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_PRESENTATION[$this->status][0]
            ?? Str::headline($this->status ?? 'Submitted');
    }

    public function getStatusToneAttribute(): string
    {
        return self::STATUS_PRESENTATION[$this->status][1] ?? 'neutral';
    }

    public function getStatusDescriptionAttribute(): string
    {
        return self::STATUS_PRESENTATION[$this->status][2] ?? '';
    }

    /**
     * Get the job posting that owns the application.
     */
    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    /**
     * Get the user that owns the application.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who reviewed the application.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the applicant's full name.
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Scope a query to only include applications with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include applications that have been reviewed.
     */
    public function scopeReviewed($query)
    {
        return $query->whereNotNull('reviewed_at');
    }

    /**
     * Scope a query to only include applications that have not been reviewed.
     */
    public function scopePending($query)
    {
        return $query->whereNull('reviewed_at');
    }
}
