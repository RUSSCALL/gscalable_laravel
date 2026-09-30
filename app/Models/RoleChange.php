<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleChange extends Model
{
    protected $fillable = [
        'user_id',
        'user_email',
        'changed_by',
        'changed_by_email',
        'from_role',
        'to_role',
        'source',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
