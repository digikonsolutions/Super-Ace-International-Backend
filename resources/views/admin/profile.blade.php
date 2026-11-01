@extends('admin.layouts.app')

@section('title', 'Profile')
@section('crumbs') Profile @endsection

@section('content')
    <form method="POST" action="{{ route('admin.profile.update') }}">
        @csrf @method('PUT')

        <div class="page-head">
            <div>
                <div class="eyebrow">Account</div>
                <h1>Your profile</h1>
                <p>Update your login email and password.</p>
            </div>
            <div class="page-head__actions">
                <button class="btn btn-primary"><svg class="i i-sm"><use href="#i-check"/></svg> Save</button>
            </div>
        </div>

        <div class="grid-2">
            <div class="card">
                <div class="card__head"><div class="card__title">Details</div></div>
                <div class="card__body stack">
                    <div class="field @error('name') has-error @enderror">
                        <label class="label" for="name">Name <span class="req">*</span></label>
                        <input class="input" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="field @error('email') has-error @enderror">
                        <label class="label" for="email">Email <span class="req">*</span></label>
                        <input class="input" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card__head"><div class="card__title">Change password</div></div>
                <div class="card__body stack">
                    <div class="field @error('current_password') has-error @enderror">
                        <label class="label" for="current_password">Current password</label>
                        <input class="input" type="password" id="current_password" name="current_password" autocomplete="current-password">
                        @error('current_password') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field @error('password') has-error @enderror">
                        <label class="label" for="password">New password</label>
                        <input class="input" type="password" id="password" name="password" autocomplete="new-password">
                        <span class="hint">At least 8 characters. Leave empty to keep the current one.</span>
                        @error('password') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label class="label" for="password_confirmation">Confirm new password</label>
                        <input class="input" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
