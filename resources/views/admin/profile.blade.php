@extends('admin.admin_layout')

@section('title', 'Profile')

@section('main_content')
    @php
        // Fortify's actions report errors in named bags, one per form.
        $profileErrors = $errors->getBag('updateProfileInformation');
        $passwordErrors = $errors->getBag('updatePassword');
    @endphp
    @component('admin._page_header', ['eyebrow' => 'Account', 'title' => 'Profile'])
    @endcomponent

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Your details</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.profile.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @if($profileErrors->has('name')) is-invalid @endif" id="name" name="name"
                                   value="{{ old('name', $user->name) }}" maxlength="255" required autocomplete="name">
                            @if($profileErrors->has('name'))<div class="invalid-feedback">{{ $profileErrors->first('name') }}</div>@endif
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control @if($profileErrors->has('email')) is-invalid @endif" id="email" name="email"
                                   value="{{ old('email', $user->email) }}" maxlength="255" required autocomplete="email">
                            @if($profileErrors->has('email'))<div class="invalid-feedback">{{ $profileErrors->first('email') }}</div>@endif
                            <div class="form-text">Changing it means verifying the new address before you can use the admin area again.</div>
                        </div>
                        <button type="submit" class="btn btn-gst">Save details</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Change password</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.profile.password') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current password</label>
                            <input type="password" class="form-control @if($passwordErrors->has('current_password')) is-invalid @endif" id="current_password"
                                   name="current_password" required autocomplete="current-password">
                            @if($passwordErrors->has('current_password'))<div class="invalid-feedback">{{ $passwordErrors->first('current_password') }}</div>@endif
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New password</label>
                            <input type="password" class="form-control @if($passwordErrors->has('password')) is-invalid @endif" id="password"
                                   name="password" required autocomplete="new-password">
                            @if($passwordErrors->has('password'))<div class="invalid-feedback">{{ $passwordErrors->first('password') }}</div>@endif
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm new password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-gst">Change password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
