@extends('admin.admin_layout')

@php
    $roleLabels = [
        \App\Models\Role::SUPER_ADMIN => ['SuperAdmin', 'bg-dark'],
        \App\Models\Role::ADMIN => ['Admin', 'bg-primary'],
        \App\Models\Role::APPLICANT => ['Applicant', 'bg-secondary'],
    ];
    $label = fn (?string $role) => $roleLabels[$role][0] ?? ($role ?: 'No role');
@endphp

@section('main_content')

    <div class="main-content">
        <div class="container-fluid p-4">
            <div class="mb-4">
                <h1 class="page-title">Roles</h1>
                <p class="text-muted mb-0">
                    Give or remove admin access. SuperAdmin access is only granted from the server command line.
                </p>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('admin.roles') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="q" class="form-label">Search name or email</label>
                            <input type="search" class="form-control" id="q" name="q" value="{{ $search }}">
                        </div>
                        <div class="col-md-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role">
                                <option value="">All</option>
                                <option value="admin" @selected($filter === 'admin')>Admins</option>
                                <option value="superadmin" @selected($filter === 'superadmin')>SuperAdmins</option>
                                <option value="applicant" @selected($filter === 'applicant')>Applicants</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-gst me-2">
                                <i class="bi bi-search me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.roles') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Email verified</th>
                                    <th>Joined</th>
                                    <th class="text-end">Access</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    @php $role = $user->role?->role_name; @endphp
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge {{ $roleLabels[$role][1] ?? 'bg-warning text-dark' }}">{{ $label($role) }}</span>
                                        </td>
                                        <td>
                                            @if($user->hasVerifiedEmail())
                                                <i class="bi bi-check-circle text-success"></i> Yes
                                            @else
                                                <span class="text-muted">No</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->created_at?->format('M j, Y') }}</td>
                                        <td class="text-end">
                                            @if($user->is(auth()->user()))
                                                <span class="text-muted small">This is you</span>
                                            @elseif($role === \App\Models\Role::SUPER_ADMIN)
                                                <span class="text-muted small">Managed on the server</span>
                                            @elseif($role === \App\Models\Role::ADMIN)
                                                <form action="{{ route('admin.roles.update', $user) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm(@js('Remove admin access from ' . $user->name . ' (' . $user->email . ')?'))">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="role" value="{{ \App\Models\Role::APPLICANT }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-person-dash me-1"></i> Remove admin
                                                    </button>
                                                </form>
                                            @elseif($user->hasVerifiedEmail())
                                                <form action="{{ route('admin.roles.update', $user) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm(@js('Give ' . $user->name . ' (' . $user->email . ') admin access?'))">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="role" value="{{ \App\Models\Role::ADMIN }}">
                                                    <button type="submit" class="btn btn-sm btn-gst">
                                                        <i class="bi bi-person-check me-1"></i> Make admin
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                                        title="They must verify their email first">
                                                    Make admin
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No users match.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages())
                        <div class="mt-3">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- History -->
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Recent role changes</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>When</th>
                                    <th>User</th>
                                    <th>Change</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($changes as $change)
                                    <tr>
                                        <td>{{ $change->created_at->format('M j, Y g:i A') }}</td>
                                        <td>{{ $change->user_email }}</td>
                                        <td>{{ $label($change->from_role) }} &rarr; {{ $label($change->to_role) }}</td>
                                        <td>{{ $change->source === 'console' ? 'Server command' : ($change->changed_by_email ?? 'Deleted user') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No role changes yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="footer mt-auto">
                <p class="mb-0">©{{ date('Y') }} Global Scalable Technologies (GST). All rights reserved.</p>
            </div>
        </div>
    </div>

@endsection
