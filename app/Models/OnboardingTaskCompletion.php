<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnboardingTaskCompletion extends Model
{
    public const TASKS = [
        'offer_letter' => 'Offer letter signed',
        'background_check' => 'Background check complete',
        'work_authorization' => 'Work authorization (I-9) verified',
        'start_date' => 'Start date confirmed',
        'equipment' => 'Equipment and accounts ready',
        'orientation' => 'First-day orientation done',
    ];

    protected $fillable = ['task'];

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
