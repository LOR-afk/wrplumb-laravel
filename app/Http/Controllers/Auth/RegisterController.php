<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'size:1'],
            'last_name' => ['required', 'string', 'max:100'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[\W_]/',
            ],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
        ], [
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
            'email.required' => 'Email is required so we can verify your account.',
            'email.unique' => 'This email is already registered. Please login instead.',
            'username.unique' => 'This username is already taken. Please choose another one.',
        ]);

        $fullName = trim(
            $validated['first_name'] . ' ' .
            (!empty($validated['middle_initial']) ? $validated['middle_initial'] . '. ' : '') .
            $validated['last_name']
        );

        $user = User::create([
            'name' => $fullName,
            'username' => $validated['username'],
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'role' => 'client',
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()
            ->route('verification.notice')
            ->with('success', 'Account created successfully. Please verify your email address.');
    }
}
