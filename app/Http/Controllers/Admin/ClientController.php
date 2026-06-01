<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    protected array $roles = [
        'admin',
        'hr',
        'client',
        'worker',
        'inspector',
    ];

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('verified')) {
            if ($request->verified === 'verified') {
                $query->whereNotNull('email_verified_at');
            }

            if ($request->verified === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
        ];

        return view('admin.clients.index', compact('users', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'size:1'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'role' => ['required', Rule::in($this->roles)],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[\W_]/',
            ],
            'is_active' => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
        ], [
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
        ]);

        $fullName = $this->buildFullName($validated);

        $user = User::create([
            'name' => $fullName,
            'username' => $validated['username'],
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'role' => $validated['role'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'email_verified_at' => !empty($validated['email_verified']) ? now() : null,
            'password' => $validated['password'],
        ]);

        AuditLogService::log(
            'Admin Created User',
            'User Management',
            $user,
            null,
            $this->userAuditSnapshot($user),
            "Admin created {$user->role} account for {$user->name}."
        );

        return back()->with('success', 'User account created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'size:1'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'role' => ['required', Rule::in($this->roles)],
        ]);

        $oldValues = $this->userAuditSnapshot($user);
        $oldRole = $user->role;

        $user->update([
            'name' => $this->buildFullName($validated),
            'username' => $validated['username'],
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'role' => $validated['role'],
        ]);

        $user->refresh();

        AuditLogService::log(
            $oldRole !== $user->role ? 'Admin Changed User Role' : 'Admin Updated User',
            'User Management',
            $user,
            $oldValues,
            $this->userAuditSnapshot($user),
            $oldRole !== $user->role
                ? "Admin changed {$user->name}'s role from {$oldRole} to {$user->role}."
                : "Admin updated user account details for {$user->name}."
        );

        return back()->with('success', 'User account updated successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id() && $user->is_active) {
            return back()->withErrors([
                'user' => 'You cannot deactivate your own account while logged in.',
            ]);
        }

        $oldValues = $this->userAuditSnapshot($user);

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $user->refresh();

        AuditLogService::log(
            $user->is_active ? 'Admin Activated User' : 'Admin Deactivated User',
            'User Management',
            $user,
            $oldValues,
            $this->userAuditSnapshot($user),
            $user->is_active
                ? "Admin activated {$user->name}'s account."
                : "Admin deactivated {$user->name}'s account."
        );

        return back()->with('success', 'User account status updated.');
    }

    public function verifyEmail(User $user)
    {
        if ($user->email_verified_at) {
            return back()->with('info', 'This user email is already verified.');
        }

        $oldValues = $this->userAuditSnapshot($user);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        $user->refresh();

        AuditLogService::log(
            'Admin Verified User Email',
            'User Management',
            $user,
            $oldValues,
            $this->userAuditSnapshot($user),
            "Admin manually verified the email address of {$user->name}."
        );

        return back()->with('success', 'User email marked as verified.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[\W_]/',
            ],
        ], [
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        AuditLogService::log(
            'Admin Reset User Password',
            'User Management',
            $user,
            null,
            [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'password_reset_at' => now()->toDateTimeString(),
            ],
            "Admin reset the password for {$user->name}."
        );

        return back()->with('success', 'Temporary password has been set successfully.');
    }

    protected function buildFullName(array $data): string
    {
        return trim(
            $data['first_name'] . ' ' .
            (!empty($data['middle_initial']) ? $data['middle_initial'] . '. ' : '') .
            $data['last_name']
        );
    }

    protected function userAuditSnapshot(User $user): array
    {
        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'first_name' => $user->first_name,
            'middle_initial' => $user->middle_initial,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'role' => $user->role,
            'is_active' => (bool) $user->is_active,
            'email_verified_at' => optional($user->email_verified_at)->toDateTimeString(),
        ];
    }
}
