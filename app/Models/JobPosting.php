<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'location_id',
        'created_by',
        'description',
        'requirements',
        'benefits',
        'responsibilities',
        'employment_type',
        'experience_level',
        'salary_min',
        'salary_max',
        'salary_currency',
        'salary_period',
        'application_deadline',
        'is_featured',
        'is_published',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'application_deadline' => 'date',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
    ];

    /**
     * Get the category that owns the job posting.
     */
    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    /**
     * Get the location that owns the job posting.
     */
    public function location()
    {
        return $this->belongsTo(JobLocation::class, 'location_id');
    }

    /**
     * Get the user who created the job posting.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the skills required for this job posting.
     */
    public function skills()
    {
        return $this->belongsToMany(JobSkill::class, 'job_posting_skill')
            ->withPivot('is_required')
            ->withTimestamps();
    }

    /**
     * Get the applications for this job posting.
     */
    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Scope a query to only include published job postings.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include featured job postings.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include active job postings (published and not past deadline).
     */
    public function scopeActive($query)
    {
        return $query->where('is_published', true)
            ->where('application_deadline', '>=', now());
    }
    
    /**
     * Get a formatted salary range for display.
     */
    public function getSalaryRangeAttribute()
    {
        if (!$this->salary_min && !$this->salary_max) {
            return 'Competitive';
        }

        $currency = $this->salary_currency ?: 'USD';

        if ($this->salary_min && $this->salary_max) {
            $amount = $currency . ' ' . number_format($this->salary_min, 0)
                . ' - ' . number_format($this->salary_max, 0);
        } elseif ($this->salary_min) {
            $amount = 'From ' . $currency . ' ' . number_format($this->salary_min, 0);
        } else {
            $amount = 'Up to ' . $currency . ' ' . number_format($this->salary_max, 0);
        }

        // Currency and period are stated once for the whole range, not per bound.
        return $this->salary_period
            ? $amount . ' per ' . strtolower($this->salary_period)
            : $amount;
    }
}
