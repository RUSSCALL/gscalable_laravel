<?php

namespace App\Actions;

use App\Models\Role;
use App\Models\RoleChange;
use App\Models\User;
use InvalidArgumentException;

/**
 * The one place a user's role is changed, so every change is recorded in
 * role_changes whether it came from the roles page or the console.
 */
class AssignRole
{
    public function handle(User $user, string $roleName, ?User $changedBy, string $source): void
    {
        $roleId = Role::idFor($roleName);

        if (! $roleId) {
            throw new InvalidArgumentException("Role [{$roleName}] does not exist.");
        }

        $from = $user->role?->role_name;

        if ((int) $user->role_id === $roleId) {
            return;
        }

        // role_id is not fillable, so it is set directly.
        $user->role_id = $roleId;
        $user->save();
        $user->unsetRelation('role');

        RoleChange::create([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'changed_by' => $changedBy?->id,
            'changed_by_email' => $changedBy?->email,
            'from_role' => $from,
            'to_role' => $roleName,
            'source' => $source,
        ]);
    }
}
