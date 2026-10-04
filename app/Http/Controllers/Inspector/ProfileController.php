<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'max:10'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'remove_profile_photo' => ['nullable', 'boolean'],
            'current_password' => [
                'nullable',
                'required_with:password',
                'current_password',
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $firstName = trim((string) ($validated['first_name'] ?? ''));
        $middleInitial = trim((string) ($validated['middle_initial'] ?? ''));
        $lastName = trim((string) ($validated['last_name'] ?? ''));

        $fullName = trim(
            $firstName . ' ' .
            ($middleInitial !== '' ? $middleInitial . ' ' : '') .
            $lastName
        );

        $user->first_name = $firstName !== '' ? $firstName : null;
        $user->middle_initial = $middleInitial !== '' ? $middleInitial : null;
        $user->last_name = $lastName !== '' ? $lastName : null;
        $user->name = trim((string) ($validated['name'] ?? '')) ?: ($fullName ?: $user->name);
        $user->email = $validated['email'];
        $user->phone = !empty($validated['phone']) ? $validated['phone'] : null;

        if ($request->boolean('remove_profile_photo')) {
            if (!empty($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $user->profile_photo_path = null;
        }

        if ($request->hasFile('profile_photo')) {
            if (!empty($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $user->profile_photo_path = $request
                ->file('profile_photo')
                ->store('profile-photos', 'public');
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with(
            'success',
            'Inspector account settings updated successfully.'
        );
    }
}
