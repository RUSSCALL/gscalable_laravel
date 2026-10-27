<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    public const TYPES = ['video' => 'Video call', 'phone' => 'Phone call', 'in_person' => 'In person'];

    public const STATUSES = ['scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

    public const TIMEZONES = ['ET' => 'Eastern (ET)', 'CT' => 'Central (CT)', 'MT' => 'Mountain (MT)', 'PT' => 'Pacific (PT)'];

    protected $fillable = [
        'job_application_id',
        'scheduled_at',
        'timezone',
        'duration_minutes',
        'type',
        'location',
        'interviewers',
        'notes',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUpcoming($query)
    {
        // From the start of today: times are stored in the interview's own zone, not UTC.
        return $query->where('status', 'scheduled')->where('scheduled_at', '>=', today());
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
