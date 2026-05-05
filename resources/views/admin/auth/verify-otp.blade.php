<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - WRPlumb</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/verify-otp.css') }}">

</head>
<body>
    <div class="otp-page">
        <div class="otp-card">
            <div class="otp-card-top"></div>

            <div class="otp-card-body">
                <div class="otp-icon">
                    <i class="fas fa-lock"></i>
                </div>

                <h2 class="otp-title">Verify OTP</h2>
                <p class="otp-subtitle">
                    Enter the verification code sent to the authorized email.
                </p>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.otp.verify') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="otp">OTP Code</label>
                        <input
                            type="text"
                            name="otp"
                            id="otp"
                            class="form-control"
                            maxlength="6"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            required
                            autofocus
                        >
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Verify OTP
                    </button>
                </form>

                <div class="otp-resend-box">
                    <div>Didn’t receive the code or did it expire?</div>

                    <form method="POST" action="{{ route('admin.otp.resend') }}">
                        @csrf
                        <button type="submit" class="btn-resend-otp">
                            <i class="fas fa-rotate-right me-1"></i> Resend OTP
                        </button>
                    </form>

                    <a href="{{ route('admin.login') }}" class="back-login-link">
                        Back to Admin Login
                    </a>
                </div>

                <div class="otp-note">
                    For security, this code is required before accessing the internal portal.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
