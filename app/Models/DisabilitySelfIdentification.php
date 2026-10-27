<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisabilitySelfIdentification extends Model
{
    // Wording from the federal voluntary self-identification form (OFCCP CC-305).
    public const OPTIONS = [
        'yes' => 'Yes, I have a disability (or previously had a disability)',
        'no' => 'No, I do not have a disability and have not had one in the past',
        'declined' => 'I do not want to answer',
    ];

    protected $fillable = ['job_application_id', 'status'];
}
