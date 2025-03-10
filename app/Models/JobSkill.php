<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobSkill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function jobPostings()
    {
        return $this->belongsToMany(JobPosting::class, 'job_posting_skill')
            ->withPivot('is_required')
            ->withTimestamps();
    }

    
}
