<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin OTP Verification - WRPlumb</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --wr-primary: #1d9bf0;
            --wr-primary-dark: #0f4c81;
            --wr-navy: #102a43;
            --wr-muted: #667085;
            --wr-border: #d8e5f2;
            --wr-bg: #eef8ff;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at 15% 20%, rgba(29, 155, 240, 0.18), transparent 28%),
                linear-gradient(135deg, #f8fcff 0%, var(--wr-bg) 100%);
            font-family: "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--wr-navy);
            padding: 20px;
        }

        .otp-card {
            width: min(100%, 430px);
            background: #ffffff;
            border: 1px solid var(--wr-border);
            border-radius: 26px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.15);
            overflow: hidden;
        }

        .otp-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 22px 24px;
            background: linear-gradient(135deg, #f8fcff, #eef8ff);
            border-bottom: 1px solid var(--wr-border);
        }

        .otp-logo {
            width: 54px;
            height: 54px;
            object-fit: cover;
            border-radius: 16px;
            box-shadow: 0 10px 22px rgba(15, 76, 129, 0.16);
        }

        .otp-header h1 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 900;
        }

        .otp-header p {
            margin: 2px 0 0;
            color: var(--wr-muted);
            font-size: 0.9rem;
        }

        .otp-body {
            padding: 28px 24px 24px;
        }

        .otp-icon {
            width: 58px;
            height: 58px;
            display: grid;
            place-items: center;
            margin: 0 auto 16px;
            border-radius: 20px;
            background: #e8f5ff;
            color: var(--wr-primary-dark);
            font-size: 1.5rem;
        }

        .otp-title {
            text-align: center;
            font-size: 1.35rem;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .otp-copy {
            text-align: center;
            color: var(--wr-muted);
            font-size: 0.92rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 800;
            color: #344054;
            font-size: 0.9rem;
        }

        .form-control {
            min-height: 50px;
            border-radius: 14px;
            border: 1px solid var(--wr-border);
            text-align: center;
            letter-spacing: 0.2em;
            font-weight: 900;
            font-size: 1.1rem;
        }

        .form-control:focus {
            border-color: var(--wr-primary);
            box-shadow: 0 0 0 0.2rem rgba(29, 155, 240, 0.14);
        }

        .otp-btn {
            min-height: 48px;
            border-radius: 14px;
            border: 0;
            background: linear-gradient(135deg, #1d9bf0, #0f4c81);
            color: #ffffff;
            font-weight: 900;
            box-shadow: 0 14px 28px rgba(15, 76, 129, 0.20);
        }

        .otp-btn:hover {
            color: #ffffff;
            filter: brightness(0.96);
        }

        .otp-secondary {
            border: 0;
            background: transparent;
            color: var(--wr-primary-dark);
            font-weight: 800;
            font-size: 0.9rem;
            padding: 0;
        }

        .otp-footer-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 18px;
        }
    </style>
</head>
<body>
    <main class="otp-card">
        <div class="otp-header">
            <img
                src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}"
                alt="WRPlumb Logo"
                class="otp-logo"
            >

            <div>
                <h1>WRPlumb Admin</h1>
                <p>Additional verification required</p>
            </div>
        </div>

        <div class="otp-body">
            <div class="otp-icon">
                <i class="fas fa-shield-halved"></i>
            </div>

            <div class="otp-title">Verify Admin Access</div>

            <p class="otp-copy">
                We sent a 6-digit OTP to your registered admin email.
                Enter the code below to continue to the admin dashboard.
            </p>

            @if (session('success'))
                <div class="alert alert-success rounded-3">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger rounded-3">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.otp.verify') }}">
                @csrf

                <div class="mb-3">
                    <label for="otp" class="form-label">Admin OTP Code</label>
                    <input
                        type="text"
                        id="otp"
                        name="otp"
                        class="form-control"
                        inputmode="numeric"
                        maxlength="6"
                        placeholder="000000"
                        required
                        autofocus
                    >
                </div>

                <button type="submit" class="btn otp-btn w-100">
                    <i class="fas fa-check-circle me-2"></i>Verify and Continue
                </button>
            </form>

            <div class="otp-footer-actions">
                <form method="POST" action="{{ route('admin.otp.resend') }}">
                    @csrf
                    <button type="submit" class="otp-secondary">
                        <i class="fas fa-paper-plane me-1"></i>Resend OTP
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="otp-secondary">
                        <i class="fas fa-right-from-bracket me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
