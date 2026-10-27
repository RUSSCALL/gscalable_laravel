<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplicationScreenerAnswer extends Model
{
    protected $fillable = ['job_screener_question_id', 'question', 'importance', 'required_answer', 'answer'];

    protected $casts = [
        'required_answer' => 'boolean',
        'answer' => 'boolean',
    ];

    public function isMet(): bool
    {
        return $this->answer === $this->required_answer;
    }
}
