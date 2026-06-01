@php
    $editing = !empty($userRecord);
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">First Name</label>
        <input
            type="text"
            name="first_name"
            class="form-control"
            value="{{ old('first_name', $userRecord->first_name ?? '') }}"
            required
        >
    </div>

    <div class="col-md-2">
        <label class="form-label">M.I.</label>
        <input
            type="text"
            name="middle_initial"
            class="form-control"
            maxlength="1"
            value="{{ old('middle_initial', $userRecord->middle_initial ?? '') }}"
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">Last Name</label>
        <input
            type="text"
            name="last_name"
            class="form-control"
            value="{{ old('last_name', $userRecord->last_name ?? '') }}"
            required
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">Username</label>
        <input
            type="text"
            name="username"
            class="form-control"
            value="{{ old('username', $userRecord->username ?? '') }}"
            required
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input
            type="email"
            name="email"
            class="form-control"
            value="{{ old('email', $userRecord->email ?? '') }}"
            required
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input
            type="text"
            name="phone"
            class="form-control"
            value="{{ old('phone', $userRecord->phone ?? '') }}"
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">Role</label>
        <select name="role" class="form-select" required>
            @foreach ($roleOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $userRecord->role ?? 'client') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="2">{{ old('address', $userRecord->address ?? '') }}</textarea>
    </div>

    @if ($includePassword)
        <div class="col-12">
            <label class="form-label">Temporary Password</label>
            <input
                type="text"
                name="password"
                class="form-control"
                placeholder="Example: TempPass.123"
                required
            >
            <small class="text-muted">
                Must contain uppercase, lowercase, number, and special character.
            </small>
        </div>
    @endif
</div>
