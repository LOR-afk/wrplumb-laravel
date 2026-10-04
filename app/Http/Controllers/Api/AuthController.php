<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function captcha(Request $request)
    {
        $question = $this->issueCaptcha($request);

        return response()->json([
            'question' => $question,
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'captcha' => ['required', 'numeric'],
        ]);

        $expectedCaptcha = $request->session()->get('api_login_captcha_answer');

        if (
            $expectedCaptcha === null ||
            (int) $validated['captcha'] !== (int) $expectedCaptcha
        ) {
            $question = $this->issueCaptcha($request);

            return response()->json([
                'message' => 'Incorrect captcha.',
                'errors' => [
                    'captcha' => ['Incorrect captcha.'],
                ],
                'captcha_question' => $question,
            ], 422);
        }

        $loginField = filter_var(
            $validated['login'],
            FILTER_VALIDATE_EMAIL
        ) ? 'email' : 'username';

        if (!Auth::attempt([
            $loginField => $validated['login'],
            'password' => $validated['password'],
        ])) {
            $question = $this->issueCaptcha($request);

            return response()->json([
                'message' => 'Invalid username/email or password.',
                'errors' => [
                    'login' => ['Invalid username/email or password.'],
                ],
                'captcha_question' => $question,
            ], 422);
        }

        $request->session()->regenerate();
        $request->session()->forget('api_login_captcha_answer');

        /** @var User $user */
        $user = Auth::user();

        if (isset($user->is_active) && (int) $user->is_active !== 1) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => 'Your account is currently inactive. Please contact the administrator.',
            ], 403);
        }

        if (!in_array($user->role, [
            'admin',
            'hr',
            'inspector',
            'client',
        ], true)) {
            Auth::logout();

            return response()->json([
                'message' => 'Your account role is not recognized.',
            ], 403);
        }

        if ($user->role === 'admin') {
            $request->session()->forget('admin_otp_verified');

            if (!$this->sendAdminOtp($request, $user)) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return response()->json([
                    'message' => 'Unable to send admin OTP. Please check the admin email configuration.',
                ], 500);
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

            return response()->json([
                'message' => 'OTP sent to your registered email.',
                'requires_otp' => true,
                'user' => $this->userPayload($user),
            ]);
        }

        return response()->json([
            'message' => 'Login successful.',
            'requires_otp' => false,
            'requires_email_verification' =>
                $user->role === 'client' &&
                $user->email_verified_at === null,
            'user' => $this->userPayload($user),
        ]);
    }

    public function user(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (
            $user->role === 'admin' &&
            $request->session()->get('admin_otp_verified') !== true
        ) {
            return response()->json([
                'message' => 'Admin OTP verification required.',
                'requires_otp' => true,
                'user' => $this->userPayload($user),
            ], 403);
        }

        return response()->json([
            'user' => $this->userPayload($user),
            'requires_email_verification' =>
                $user->role === 'client' &&
                $user->email_verified_at === null,
        ]);
    }

    public function verifyAdminOtp(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $expectedOtp = (string) $request->session()->get('admin_otp_code');
        $expiresAt = $request->session()->get('admin_otp_expires_at');

        if (!$expectedOtp || !$expiresAt) {
            return response()->json([
                'message' => 'Your OTP session is missing. Please request a new code.',
            ], 422);
        }

        if (Carbon::parse($expiresAt)->isPast()) {
            $this->sendAdminOtp($request, $user);

            return response()->json([
                'message' => 'Your OTP has expired. A new code was sent to your email.',
            ], 422);
        }

        if (!hash_equals($expectedOtp, (string) $validated['otp'])) {
            $attempts =
                (int) $request->session()->get('admin_otp_attempts', 0) + 1;

            $request->session()->put(
                'admin_otp_attempts',
                $attempts
            );

            if ($attempts >= 5) {
                $this->sendAdminOtp($request, $user);

                return response()->json([
                    'message' => 'Too many incorrect attempts. A new OTP was sent to your email.',
                ], 422);
            }

            return response()->json([
                'message' => 'Invalid OTP code.',
            ], 422);
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
            $user,
            null,
            [
                'verified_at' => now()->toDateTimeString(),
            ],
            'Admin successfully completed OTP verification.'
        );

        return response()->json([
            'message' => 'Admin verification successful.',
            'user' => $this->userPayload($user),
        ]);
    }

    public function resendAdminOtp(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        if (!$this->sendAdminOtp($request, $user)) {
            return response()->json([
                'message' => 'Unable to send OTP. Please check the admin email configuration.',
            ], 500);
        }

        return response()->json([
            'message' => 'A new OTP has been sent to your registered email.',
        ]);
    }

public function logout(Request $request)
{
    /** @var User|null $user */
    $user = $request->user();

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

    Auth::guard('web')->logout();

    $request->session()->forget([
        'admin_otp_verified',
        'admin_otp_code',
        'admin_otp_expires_at',
        'admin_otp_attempts',
        'api_login_captcha_answer',
    ]);

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json([
        'message' => 'Logged out successfully.',
    ]);
}

    protected function sendAdminOtp(Request $request, User $user): bool
    {
        if (empty($user->email)) {
            return false;
        }

        $otp = (string) random_int(100000, 999999);

        $request->session()->put([
            'admin_otp_code' => $otp,
            'admin_otp_expires_at' =>
                now()->addMinutes(10)->toDateTimeString(),
            'admin_otp_attempts' => 0,
        ]);

        try {
            Mail::raw(
                "Your WRPlumb admin verification code is {$otp}.\n\nThis code will expire in 10 minutes. If you did not attempt to sign in, please ignore this message.",
                function ($message) use ($user) {
                    $message
                        ->to($user->email)
                        ->subject('WRPlumb Admin OTP Verification');
                }
            );

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send WRPlumb admin OTP.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function issueCaptcha(Request $request): string
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        $request->session()->put(
            'api_login_captcha_answer',
            $a + $b
        );

        return "{$a} + {$b} = ?";
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'email_verified_at' => $user->email_verified_at,
        ];
    }
}