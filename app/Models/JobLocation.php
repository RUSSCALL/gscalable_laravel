<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'city',
        'state',
        'country',
        'address',
        'postal_code',
        'is_remote',
    ];

    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class, 'location_id');
    }

}
