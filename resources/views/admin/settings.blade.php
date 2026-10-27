@extends('admin.admin_layout')

@php
    $events = [
        'new_application' => ['New application received', 'Someone applies for any job.'],
        'job_expiring' => ['Job posting expiring soon', 'A live job is three days or less from its application deadline.'],
    ];
@endphp

@section('main_content')
    <div class="main-content">
        <div class="container-fluid p-4">
            <h1 class="page-title mb-4">Settings</h1>

            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0">Notifications</h5></div>
                        <div class="card-body">
                            <p class="text-muted">Choose which alerts you get, on the bell in the top bar and by email to {{ $user->email }}.</p>
                            <form method="POST" action="{{ route('admin.settings.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead>
                                            <tr><th>Alert</th><th class="text-center">Bell</th><th class="text-center">Email</th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach($events as $event => [$label, $hint])
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ $label }}</div>
                                                        <div class="small text-muted">{{ $hint }}</div>
                                                    </td>
                                                    @foreach(['database' => 'Bell', 'mail' => 'Email'] as $channel => $channelLabel)
                                                        <td class="text-center">
                                                            <input class="form-check-input" type="checkbox" value="1"
                                                                   name="notifications[{{ $event }}][{{ $channel }}]"
                                                                   aria-label="{{ $label }}: {{ $channelLabel }}"
                                                                   @checked($user->wantsNotification($event, $channel))>
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" class="btn btn-gst">Save settings</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0">Your account</h5></div>
                        <div class="card-body">
                            <p class="mb-2">Change your name, email address or password on your profile.</p>
                            <a href="{{ route('admin.profile') }}" class="btn btn-outline-secondary">Go to profile</a>
                            @can('user-is-superadmin')
                                <hr>
                                <p class="mb-2">Grant or remove admin access for other users.</p>
                                <a href="{{ route('admin.roles') }}" class="btn btn-outline-secondary">Manage roles</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
