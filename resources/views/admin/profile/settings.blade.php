@extends('admin.layouts.app')

@section('title', 'Account Settings - WRPlumb')
@section('topbar_title', 'Account Settings')
@section('topbar_subtitle', 'Update your profile information, photo, and password.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/profile.css') }}">
@endpush

@section('content')
@php
    $displayName = $user->name
        ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
        ?: 'Admin User';
    $initials = collect(explode(' ', $displayName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('') ?: 'A';
    $photoUrl = $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null;
@endphp

<div class="profile-settings-page">
    <section class="profile-settings-card">
        <div class="profile-settings-head">
            <div>
                <div class="profile-settings-kicker"><i class="fas fa-user-shield me-2"></i>Administrator Account</div>
                <h2>Profile and Account Settings</h2>
                <p>Manage your personal details and profile picture used across the admin panel.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="profile-settings-form">
            @csrf
            @method('PUT')

            <input type="hidden" name="remove_profile_photo" id="removeProfilePhoto" value="0">

            <div class="profile-photo-panel">
                <div class="profile-photo-preview" id="profilePhotoPreview" data-initials="{{ $initials }}">
                    @if ($photoUrl)
                        <img src="{{ $photoUrl }}" alt="{{ $displayName }}" id="profilePhotoPreviewImage">
                    @else
                        <span id="profilePhotoPreviewInitials">{{ $initials }}</span>
                    @endif
                </div>

                <div>
                    <h5>{{ $displayName }}</h5>
                    <p>{{ $user->email }}</p>

                    <div class="profile-photo-actions">
                        <label class="btn btn-outline-primary profile-upload-btn mb-0">
                            <i class="fas fa-camera me-1"></i> Change Profile Picture
                            <input type="file"
                                   name="profile_photo"
                                   id="profilePhotoInput"
                                   accept="image/png,image/jpeg,image/webp"
                                   hidden>
                        </label>

                        @if ($photoUrl)
                            <button type="button" class="btn btn-outline-danger profile-remove-photo-btn" id="removeProfilePhotoButton">
                                <i class="fas fa-trash me-1"></i> Remove Photo
                            </button>
                        @endif
                    </div>

                    <div class="profile-photo-status" id="profilePhotoStatus">
                        @if ($photoUrl)
                            Current profile photo is active.
                        @else
                            No uploaded photo yet. Initials are currently used.
                        @endif
                    </div>

                    @error('profile_photo')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                    <div class="profile-help-text">Accepted formats: JPG, PNG, or WEBP. Maximum size: 2 MB.</div>
                </div>
            </div>

            <div class="profile-form-grid">
                <div>
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}">
                    @error('first_name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="form-label">Middle Initial</label>
                    <input type="text" name="middle_initial" class="form-control" value="{{ old('middle_initial', $user->middle_initial) }}">
                    @error('middle_initial') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}">
                    @error('last_name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="form-label">Display Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" placeholder="Admin User">
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="09XXXXXXXXX">
                    @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="profile-password-panel">
                <h5><i class="fas fa-lock me-2 text-primary"></i>Change Password</h5>
                <p>Leave these fields blank if you do not want to change your password.</p>

                <div class="profile-form-grid">
                    <div>
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                        @error('current_password') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div>
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" autocomplete="new-password">
                        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div>
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('profilePhotoInput');
    const preview = document.getElementById('profilePhotoPreview');
    const removeInput = document.getElementById('removeProfilePhoto');
    const removeButton = document.getElementById('removeProfilePhotoButton');
    const status = document.getElementById('profilePhotoStatus');

    if (!preview) return;

    const initials = preview.dataset.initials || 'A';

    function showInitials(message) {
        preview.innerHTML = `<span id="profilePhotoPreviewInitials">${initials}</span>`;
        if (status && message) status.textContent = message;
    }

    function showImage(src, message) {
        preview.innerHTML = `<img src="${src}" alt="Profile photo preview" id="profilePhotoPreviewImage">`;
        if (status && message) status.textContent = message;
    }

    if (input) {
        input.addEventListener('change', function () {
            const file = input.files && input.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                input.value = '';
                if (status) status.textContent = 'Please choose a valid image file.';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                showImage(event.target.result, 'New photo selected. Click Save Changes to apply it.');
                if (removeInput) removeInput.value = '0';
            };
            reader.readAsDataURL(file);
        });
    }

    if (removeButton) {
        removeButton.addEventListener('click', function () {
            if (input) input.value = '';
            if (removeInput) removeInput.value = '1';
            showInitials('Photo marked for removal. Click Save Changes to apply it.');
        });
    }
});
</script>
@endpush
