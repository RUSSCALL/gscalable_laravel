<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    public const SUPER_ADMIN = 'SuperAdmin';

    public const ADMIN = 'Admin';

    public const APPLICANT = 'job_applicant';

    protected $fillable = [
        'role_name',
    ];

    // Looked up by name: ids differ between environments.
    public static function idFor(string $name): ?int
    {
        return static::where('role_name', $name)->orderBy('id')->value('id');
    }

    public function user() {
        return $this->belongsTo(user::class);
    }
}
