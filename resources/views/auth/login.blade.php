<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WRPlumb Portal Login</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/auth/login-auth.css') }}?v=20260517a">
</head>

<body>
    <main class="login-page">
        <section class="login-brand-panel">
            <div class="shape shape-one"></div>
            <div class="shape shape-two"></div>
            <div class="shape shape-three"></div>

            <div class="brand-content">
                <img
                    src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}"
                    alt="WRPlumb Logo"
                    class="brand-logo"
                >

                <h1>WRPlumb Service Portal</h1>

                <p>
                    Request services, monitor schedules, track quotations, and manage service records
                    through one secure online portal.
                </p>

                <div class="brand-points">
                    <div class="brand-point">
                        <i class="fas fa-file-signature"></i>
                        <strong>Submit Requests</strong>
                        <span>Send service details online</span>
                    </div>

                    <div class="brand-point">
                        <i class="fas fa-calendar-check"></i>
                        <strong>Track Schedules</strong>
                        <span>View service updates</span>
                    </div>

                    <div class="brand-point">
                        <i class="fas fa-receipt"></i>
                        <strong>Manage Records</strong>
                        <span>Access billing and receipts</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="login-form-panel">
            <div class="login-card">
                <div class="portal-heading">
                    <img
                        src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}"
                        alt="WRPlumb Logo"
                    >

                    <div>
                        <h2>Sign In</h2>
                        <p>Access your WRPlumb account.</p>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success rounded-3">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger rounded-3">
                        Please check your login details and try again.
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="login" class="form-label">
                            Username or Email <span class="text-danger">*</span>
                        </label>

                        <div class="input-wrap">
                            <input
                                type="text"
                                id="login"
                                name="login"
                                class="form-control @error('login') is-invalid @enderror"
                                value="{{ old('login') }}"
                                placeholder="Enter username or email"
                                required
                                autofocus
                            >
                            <i class="fas fa-user input-icon"></i>
                        </div>

                        <small class="text-muted">Use your registered username or email address.</small>

                        @error('login')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Password <span class="text-danger">*</span>
                        </label>

                        <div class="input-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Enter password"
                                required
                            >
                            <i class="fas fa-lock input-icon"></i>
                        </div>

                        @error('password')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="captcha" class="form-label">
                            Security Check <span class="text-danger">*</span>
                        </label>

                        <div class="captcha-box">
                            {{ session('login_captcha_question', $captchaQuestion ?? '1 + 1 = ?') }}
                        </div>

                        <input
                            type="number"
                            id="captcha"
                            name="captcha"
                            class="form-control mt-2 @error('captcha') is-invalid @enderror"
                            placeholder="Enter captcha answer"
                            required
                        >

                        @error('captcha')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-signin">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </form>

                <a href="{{ route('home') }}" class="back-link">
                    ← Back to Homepage
                </a>
            </div>
        </section>
    </main>
</body>
</html>