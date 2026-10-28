@extends('layouts.app')

@section('title', 'Edit profile')

@section('content')
    <h1 class="mb-4">Edit profile</h1>

    <form method="POST" action="{{ route('profile.update') }}" class="vstack gap-3">
        @csrf
        @method('PUT')

        <section class="profile-avatar-section" aria-labelledby="profile-avatar-heading">
            <h2 class="h5" id="profile-avatar-heading">Profile picture</h2>
            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-4">
                <img
                    class="profile-avatar"
                    src="{{ route('profile.avatar') }}"
                    data-fallback-src="{{ asset('images/default-avatar.svg') }}"
                    alt="{{ $user->user_name }}'s profile picture"
                    width="160"
                    height="160"
                >
                <div class="profile-avatar-uploader">
                    <label class="form-label" for="avatar">Choose a JPEG or PNG image (maximum 2 MB)</label>
                    <input
                        id="avatar"
                        name="avatar"
                        type="file"
                        accept="image/jpeg,image/png"
                        data-upload-url="{{ route('profile.avatar.uploads.store') }}"
                        data-revert-url="{{ route('profile.avatar.uploads.destroy') }}"
                        aria-describedby="avatar-help"
                    >
                    <div class="form-text" id="avatar-help">
                        Your picture is uploaded temporarily. Save your profile to apply it.
                    </div>
                    @error('avatar')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>
            <input
                id="avatar_upload_id"
                name="avatar_upload_id"
                type="hidden"
                value="{{ old('avatar_upload_id', 'false') }}"
            >
        </section>

        <div>
            <label class="form-label" for="user_name">Name</label>
            <input class="form-control" id="user_name" name="user_name" value="{{ old('user_name', $user->user_name) }}" maxlength="30" required>
            @error('user_name')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div>
            <label class="form-label" for="user_email">Email</label>
            <input class="form-control" id="user_email" name="user_email" type="email" value="{{ old('user_email', $user->user_email) }}" required>
            @error('user_email')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div>
            <label class="form-label" for="password">New password (optional)</label>
            <input class="form-control" id="password" name="password" type="password" autocomplete="new-password">
            @error('password')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div>
            <label class="form-label" for="password_confirmation">Confirm new password</label>
            <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
        </div>

        <button class="btn btn-primary align-self-start" type="submit">Save profile</button>
    </form>
@endsection
