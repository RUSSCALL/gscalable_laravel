<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobScreenerQuestion extends Model
{
    public const IMPORTANCE = ['essential' => 'Essential', 'preferred' => 'Preferred'];

    protected $fillable = ['question', 'importance', 'required_answer', 'position'];

    protected $casts = [
        'required_answer' => 'boolean',
    ];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }
}
