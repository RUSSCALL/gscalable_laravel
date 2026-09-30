<?php

namespace App\Http\Controllers;

use App\Actions\AssignRole;
use App\Models\Role;
use App\Models\RoleChange;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * SuperAdmin-only page for granting and removing admin access. SuperAdmin
 * itself is never granted here; that goes through `user:make-superadmin`.
 */
class UserRoleController extends Controller
{
    private const FILTERS = [
        'admin' => Role::ADMIN,
        'superadmin' => Role::SUPER_ADMIN,
        'applicant' => Role::APPLICANT,
    ];

    public function index(Request $request)
    {
        $query = User::with('role')->orderBy('name');

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $filter = $request->query('role');
        if (isset(self::FILTERS[$filter])) {
            $query->where('role_id', Role::idFor(self::FILTERS[$filter]));
        }

        return view('admin.roles', [
            'users' => $query->paginate(25)->withQueryString(),
            'changes' => RoleChange::latest('id')->limit(20)->get(),
            'search' => $search,
            'filter' => $filter,
        ]);
    }

    public function update(Request $request, User $user, AssignRole $assignRole)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in([Role::ADMIN, Role::APPLICANT])],
        ]);

        // The page never offers these; a request that tries them is refused outright.
        abort_if($user->is($request->user()), 403, 'You cannot change your own role.');
        abort_if($user->role?->role_name === Role::SUPER_ADMIN, 403, 'SuperAdmin accounts cannot be changed here.');

        if ($validated['role'] === Role::ADMIN && ! $user->hasVerifiedEmail()) {
            return back()->with('error', "{$user->email} must verify their email before they can be made an admin.");
        }

        $assignRole->handle($user, $validated['role'], $request->user(), 'web');

        return back()->with('success', $validated['role'] === Role::ADMIN
            ? "{$user->name} is now an admin."
            : "{$user->name} is no longer an admin.");
    }
}
