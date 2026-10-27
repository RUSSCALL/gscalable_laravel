<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantMessage extends Model
{
    protected $fillable = ['subject', 'body'];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
