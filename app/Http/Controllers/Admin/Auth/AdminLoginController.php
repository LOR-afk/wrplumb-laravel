<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AdminOtpMail;
use App\Models\AdminLoginOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminLoginController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = User::where('email', $validated['email'])
            ->where('role', 'admin')
            ->where('is_active', true)
            ->first();

        if (!$admin || !Hash::check($validated['password'], $admin->password)) {
            return back()->withErrors([
                'email' => 'Invalid admin credentials.'
            ])->withInput($request->only('email'));
        }

        $this->createAndSendOtp($admin);

        session([
            'admin_otp_user_id' => $admin->id,
        ]);

        return redirect()->route('admin.otp.form')
            ->with('success', 'OTP sent to admin email.');
    }

    public function showOtpForm()
    {
        if (!session('admin_otp_user_id')) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $userId = session('admin_otp_user_id');

        if (!$userId) {
            return redirect()->route('admin.login');
        }

        $otpRecord = AdminLoginOtp::where('user_id', $userId)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (!$otpRecord || now()->gt($otpRecord->expires_at)) {
            return back()->withErrors([
                'otp' => 'OTP expired. You may request a new OTP or login again.'
            ]);
        }

        if (!Hash::check($request->otp, $otpRecord->otp_code)) {
            return back()->withErrors([
                'otp' => 'Invalid OTP.'
            ]);
        }

        $otpRecord->update([
            'used_at' => now(),
        ]);

        $admin = User::findOrFail($userId);

        Auth::login($admin);
        session()->forget('admin_otp_user_id');
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function resendOtp(Request $request)
    {
        $userId = session('admin_otp_user_id');

        if (!$userId) {
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Session expired. Please login again.']);
        }

        $admin = User::where('id', $userId)
            ->where('role', 'admin')
            ->where('is_active', true)
            ->first();

        if (!$admin) {
            session()->forget('admin_otp_user_id');

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Admin account not found or inactive. Please login again.']);
        }

        $latestOtp = AdminLoginOtp::where('user_id', $admin->id)
            ->latest()
            ->first();

        if ($latestOtp && $latestOtp->created_at && $latestOtp->created_at->gt(now()->subSeconds(60))) {
            $secondsLeft = max(1, 60 - (int) $latestOtp->created_at->diffInSeconds(now()));

            return back()->withErrors([
                'otp' => "Please wait {$secondsLeft} seconds before requesting another OTP."
            ]);
        }

        $this->createAndSendOtp($admin);

        return redirect()->route('admin.otp.form')
            ->with('success', 'A new OTP has been sent to admin email.');
    }

    protected function createAndSendOtp(User $admin): void
    {
        $otp = (string) random_int(100000, 999999);

        AdminLoginOtp::where('user_id', $admin->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        AdminLoginOtp::create([
            'user_id' => $admin->id,
            'otp_code' => Hash::make($otp),
            'expires_at' => now()->addMinutes(5),
        ]);

        Mail::to($admin->email)->send(new AdminOtpMail($otp));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
