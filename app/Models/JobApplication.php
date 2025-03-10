<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
