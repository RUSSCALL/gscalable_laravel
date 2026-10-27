<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplicationAttachment extends Model
{
    protected $fillable = ['path', 'original_name', 'size'];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }
}
