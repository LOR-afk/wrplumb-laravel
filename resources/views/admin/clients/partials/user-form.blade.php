@php
    $editing = !empty($userRecord);
@endphp

<div class="user-form-grid">
    <div class="user-form-field user-form-first">
        <label class="form-label">First Name</label>
        <input
            type="text"
            name="first_name"
            class="form-control"
            value="{{ old('first_name', $userRecord->first_name ?? '') }}"
            required
        >
    </div>

    <div class="user-form-field user-form-mi">
        <label class="form-label">M.I.</label>
        <input
            type="text"
            name="middle_initial"
            class="form-control"
            maxlength="1"
            value="{{ old('middle_initial', $userRecord->middle_initial ?? '') }}"
        >
    </div>

    <div class="user-form-field user-form-last">
        <label class="form-label">Last Name</label>
        <input
            type="text"
            name="last_name"
            class="form-control"
            value="{{ old('last_name', $userRecord->last_name ?? '') }}"
            required
        >
    </div>

    <div class="user-form-field">
        <label class="form-label">Username</label>
        <input
            type="text"
            name="username"
            class="form-control"
            value="{{ old('username', $userRecord->username ?? '') }}"
            required
        >
    </div>

    <div class="user-form-field">
        <label class="form-label">Email</label>
        <input
            type="email"
            name="email"
            class="form-control"
            value="{{ old('email', $userRecord->email ?? '') }}"
            required
        >
    </div>

    <div class="user-form-field">
        <label class="form-label">Phone</label>
        <input
            type="text"
            name="phone"
            class="form-control"
            value="{{ old('phone', $userRecord->phone ?? '') }}"
        >
    </div>

    <div class="user-form-field">
        <label class="form-label">Role</label>
        <select name="role" class="form-select" required>
            @foreach ($roleOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $userRecord->role ?? 'client') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="user-form-field user-form-full">
        <label class="form-label">Address</label>
        <textarea
            name="address"
            class="form-control user-address"
            rows="2"
            placeholder="House no., street, barangay, city"
        >{{ old('address', $userRecord->address ?? '') }}</textarea>
    </div>

    @if ($includePassword)
        <div class="user-form-field user-form-full">
            <div class="password-label-row">
                <label class="form-label mb-0">Temporary Password</label>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary generate-password-btn"
                    data-password-target="createTemporaryPassword"
                >
                    <i class="fas fa-wand-magic-sparkles me-1"></i>
                    Generate Password
                </button>
            </div>

            <div class="password-field-wrap">
                <input
                    type="password"
                    id="createTemporaryPassword"
                    name="password"
                    class="form-control password-input"
                    minlength="16"
                    autocomplete="new-password"
                    placeholder="Generate or enter a strong password"
                    required
                >

                <button
                    type="button"
                    class="password-toggle"
                    data-password-target="createTemporaryPassword"
                    aria-label="Show password"
                    title="Show password"
                >
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <div class="password-policy">
                <span><i class="fas fa-shield-halved me-1"></i>At least 16 characters</span>
                <span>Uppercase & lowercase</span>
                <span>Number</span>
                <span>Symbol</span>
            </div>
        </div>
    @endif
</div>