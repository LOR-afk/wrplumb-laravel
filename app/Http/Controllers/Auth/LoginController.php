<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class LoginController extends Controller
{
    public function show()
    {
        [$question, $answer] = $this->generateCaptcha();

        session([
            'login_captcha_question' => $question,
            'login_captcha_answer' => $answer,
        ]);

        return view('auth.login', [
            'captchaQuestion' => $question,
        ]);
    }

    public function authenticate(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'captcha' => ['required', 'numeric'],
        ]);

        if ((int) $validated['captcha'] !== (int) session('login_captcha_answer')) {
            $this->refreshCaptcha();

            return back()
                ->withErrors(['captcha' => 'Incorrect captcha.'])
                ->withInput($request->only('login'));
        }

        $loginField = filter_var($validated['login'], FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        if (!Auth::attempt([
            $loginField => $validated['login'],
            'password' => $validated['password'],
        ])) {
            $this->refreshCaptcha();

            return back()
                ->withErrors([
                    'login' => 'Invalid username/email or password.',
                ])
                ->withInput($request->only('login'));
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (isset($user->is_active) && (int) $user->is_active !== 1) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'login' => 'Your account is currently inactive. Please contact the administrator.',
                ]);
        }

        return $this->redirectByRole($request, $user);
    }

    protected function redirectByRole(Request $request, $user)
    {
        return match ($user->role) {
            'admin' => $this->startAdminOtpChallenge($request, $user),
            'hr' => redirect()->route('hr.dashboard'),
            'inspector' => redirect()->route('inspector.dashboard'),
            'client' => $user->email_verified_at === null
                ? redirect()->route('verification.notice')
                : redirect()->route('client.dashboard'),
            default => redirect()
                ->route('login')
                ->withErrors([
                    'login' => 'Your account role is not recognized.',
                ]),
        };
    }

    protected function startAdminOtpChallenge(Request $request, $user)
    {
        $request->session()->forget('admin_otp_verified');

        if (!$this->sendAdminOtp($request, $user)) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'login' => 'Unable to send admin OTP. Please check the admin email configuration.',
                ]);
        }

        AuditLogService::log(
            'Admin Logged In',
            'Authentication',
            $user,
            null,
            [
                'email' => $user->email,
                'role' => $user->role,
                'otp_challenge' => 'sent',
            ],
            'Admin credentials were accepted and an OTP challenge was sent.'
        );

        return redirect()
            ->route('admin.otp.form')
            ->with('success', 'Admin OTP has been sent to your registered email.');
    }

    public function showAdminOtpForm(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->role !== 'admin') {
            return $this->redirectByRole($request, $user);
        }

        if ($request->session()->get('admin_otp_verified') === true) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-otp');
    }

    public function verifyAdminOtp(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $expectedOtp = (string) $request->session()->get('admin_otp_code');
        $expiresAt = $request->session()->get('admin_otp_expires_at');

        if (!$expectedOtp || !$expiresAt) {
            return back()->withErrors([
                'otp' => 'Your OTP session is missing. Please request a new code.',
            ]);
        }

        if (Carbon::parse($expiresAt)->isPast()) {
            $this->sendAdminOtp($request, Auth::user());

            return back()->withErrors([
                'otp' => 'Your OTP has expired. A new code was sent to your email.',
            ]);
        }

        if (!hash_equals($expectedOtp, (string) $validated['otp'])) {
            $attempts = (int) $request->session()->get('admin_otp_attempts', 0) + 1;
            $request->session()->put('admin_otp_attempts', $attempts);

            if ($attempts >= 5) {
                $this->sendAdminOtp($request, Auth::user());

                return back()->withErrors([
                    'otp' => 'Too many incorrect attempts. A new OTP was sent to your email.',
                ]);
            }

            return back()->withErrors([
                'otp' => 'Invalid OTP code.',
            ]);
        }

        $request->session()->forget([
            'admin_otp_code',
            'admin_otp_expires_at',
            'admin_otp_attempts',
        ]);

        $request->session()->put('admin_otp_verified', true);

        AuditLogService::log(
            'Admin OTP Verified',
            'Authentication',
            Auth::user(),
            null,
            [
                'verified_at' => now()->toDateTimeString(),
            ],
            'Admin successfully completed OTP verification.'
        );

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Admin verification successful.');
    }

    public function resendAdminOtp(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('login');
        }

        if (!$this->sendAdminOtp($request, Auth::user())) {
            return back()->withErrors([
                'otp' => 'Unable to send OTP. Please check the admin email configuration.',
            ]);
        }

        return back()->with('success', 'A new OTP has been sent to your registered email.');
    }

    protected function sendAdminOtp(Request $request, $user): bool
    {
        if (empty($user->email)) {
            return false;
        }

        $otp = (string) random_int(100000, 999999);

        $request->session()->put([
            'admin_otp_code' => $otp,
            'admin_otp_expires_at' => now()->addMinutes(10)->toDateTimeString(),
            'admin_otp_attempts' => 0,
        ]);

        try {
            Mail::raw(
                "Your WRPlumb admin verification code is {$otp}.\n\nThis code will expire in 10 minutes. If you did not attempt to sign in, please ignore this message.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('WRPlumb Admin OTP Verification');
                }
            );

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send WRPlumb admin OTP.', [
                'user_id' => $user->id ?? null,
                'email' => $user->email ?? null,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

public function logout(Request $request)
{
    /** @var User|null $user */
    $user = Auth::user();

    if ($user) {
        AuditLogService::log(
            ucfirst($user->role) . ' Logged Out',
            'Authentication',
            $user,
            null,
            [
                'role' => $user->role,
                'logged_out_at' => now()->toDateTimeString(),
            ],
            ucfirst($user->role) . ' user logged out of the system.'
        );
    }

    Auth::logout();

    $request->session()->forget([
        'admin_otp_verified',
        'admin_otp_code',
        'admin_otp_expires_at',
        'admin_otp_attempts',
    ]);

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
}

    private function refreshCaptcha(): void
    {
        [$question, $answer] = $this->generateCaptcha();

        session([
            'login_captcha_question' => $question,
            'login_captcha_answer' => $answer,
        ]);
    }

    private function generateCaptcha(): array
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        return ["{$a} + {$b} = ?", $a + $b];
    }
}
