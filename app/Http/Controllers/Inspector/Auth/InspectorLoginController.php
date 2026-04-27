<?php

namespace App\Http\Controllers\Inspector\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InspectorLoginController extends Controller
{
    public function show()
    {
        [$question, $answer] = $this->generateCaptcha();

        session([
            'inspector_login_captcha_question' => $question,
            'inspector_login_captcha_answer' => $answer,
        ]);

        return view('inspector.auth.login', [
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

        if ((int) $validated['captcha'] !== (int) session('inspector_login_captcha_answer')) {
            [$question, $answer] = $this->generateCaptcha();

            session([
                'inspector_login_captcha_question' => $question,
                'inspector_login_captcha_answer' => $answer,
            ]);

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
            [$question, $answer] = $this->generateCaptcha();

            session([
                'inspector_login_captcha_question' => $question,
                'inspector_login_captcha_answer' => $answer,
            ]);

            return back()
                ->withErrors([
                    'login' => 'Invalid username/email or password.',
                ])
                ->withInput($request->only('login'));
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // keep current internal role as worker
        if ($user->role !== 'worker') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'login' => 'This login page is for inspector accounts only.',
            ]);
        }

        return redirect()->route('inspector.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inspector.login');
    }

    private function generateCaptcha(): array
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        return ["{$a} + {$b} = ?", $a + $b];
    }
}