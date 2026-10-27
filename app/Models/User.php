<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * role_id is deliberately excluded so mass assignment cannot escalate a user's role.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'notification_preferences' => 'array',
    ];

    // Event => [channel => default]. The bell is on and email off until an admin changes it in Settings.
    public const NOTIFICATION_DEFAULTS = [
        'new_application' => ['database' => true, 'mail' => false],
        'job_expiring' => ['database' => true, 'mail' => false],
    ];

    public function role(){
        return $this->hasOne(Role::class , 'id' , 'role_id');
    }

    public function scopeAdmins($query)
    {
        return $query->whereHas('role', fn ($role) => $role->whereIn('role_name', [Role::ADMIN, Role::SUPER_ADMIN]));
    }

    public function wantsNotification(string $event, string $channel): bool
    {
        return (bool) ($this->notification_preferences[$event][$channel]
            ?? self::NOTIFICATION_DEFAULTS[$event][$channel] ?? false);
    }

    /** @return array<int, string> */
    public function notificationChannels(string $event): array
    {
        return array_values(array_filter(['database', 'mail'], fn ($channel) => $this->wantsNotification($event, $channel)));
    }

    /**
     * Applications submitted by this user, newest first. Guest applications
     * are linked here by email when an account is claimed.
     */
    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class)->latest();
    }
}
