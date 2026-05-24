<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - WRPlumb</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --wr-primary: #1d9bf0;
            --wr-primary-dark: #0f4c81;
            --wr-navy: #102a43;
            --wr-border: #dbe8f5;
            --wr-muted: #5f7187;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at 15% 20%, rgba(29, 155, 240, 0.18), transparent 28%),
                linear-gradient(135deg, #f8fcff 0%, #e7f6ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--wr-navy);
            font-family: "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            padding: 24px;
        }

        .verify-card {
            width: min(100%, 520px);
            background: #ffffff;
            border: 1px solid var(--wr-border);
            border-radius: 26px;
            box-shadow: 0 26px 70px rgba(15, 42, 67, 0.16);
            overflow: hidden;
        }

        .verify-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 22px 24px;
            background: linear-gradient(135deg, #f8fcff 0%, #eef8ff 100%);
            border-bottom: 1px solid var(--wr-border);
        }

        .verify-logo {
            width: 54px;
            height: 54px;
            border-radius: 15px;
            object-fit: cover;
            background: #ffffff;
            border: 1px solid var(--wr-border);
            box-shadow: 0 8px 18px rgba(15, 76, 129, 0.10);
        }

        .verify-header h1 {
            font-size: 1.45rem;
            font-weight: 900;
            margin: 0;
            letter-spacing: -0.03em;
        }

        .verify-header p {
            margin: 3px 0 0;
            color: var(--wr-muted);
            font-size: 0.92rem;
        }

        .verify-body {
            padding: 28px 26px 26px;
            text-align: center;
        }

        .verify-icon {
            width: 74px;
            height: 74px;
            border-radius: 24px;
            margin: 0 auto 18px;
            display: grid;
            place-items: center;
            background: #e8f5ff;
            color: var(--wr-primary-dark);
            font-size: 1.9rem;
        }

        .verify-body h2 {
            font-size: 1.65rem;
            font-weight: 900;
            margin-bottom: 10px;
            letter-spacing: -0.03em;
        }

        .verify-body p {
            color: var(--wr-muted);
            line-height: 1.65;
            margin-bottom: 22px;
        }

        .alert {
            border-radius: 14px;
            font-size: 0.92rem;
            text-align: left;
        }

        .verify-btn {
            width: 100%;
            min-height: 50px;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--wr-primary), var(--wr-primary-dark));
            color: #ffffff;
            font-weight: 900;
            box-shadow: 0 14px 30px rgba(15, 76, 129, 0.22);
        }

        .verify-btn:hover {
            color: #ffffff;
            background: linear-gradient(135deg, #168fe0, #0a355e);
        }

        .logout-btn {
            border: 0;
            background: transparent;
            color: var(--wr-muted);
            font-weight: 700;
            margin-top: 16px;
        }

        .logout-btn:hover {
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="verify-header">
            <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}" alt="WRPlumb Logo" class="verify-logo">
            <div>
                <h1>WRPlumb Account</h1>
                <p>Email verification required</p>
            </div>
        </div>

        <div class="verify-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success">
                    A new verification link has been sent to your email address.
                </div>
            @endif

            <div class="verify-icon">
                <i class="fas fa-envelope-circle-check"></i>
            </div>

            <h2>Verify Your Email</h2>
            <p>
                We sent a verification link to your Gmail inbox. Please click the link to activate your WRPlumb client account.
            </p>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn verify-btn">
                    <i class="fas fa-paper-plane me-2"></i>Resend Verification Email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-right-from-bracket me-1"></i>Logout and verify later
                </button>
            </form>
        </div>
    </div>
</body>
</html>
