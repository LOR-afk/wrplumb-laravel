<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internal Portal - WRPlumb</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --wr-blue: #0284c7;
            --wr-dark: #0f172a;
            --wr-navy: #082f49;
            --wr-light: #e0f2fe;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            color: var(--wr-dark);
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.28), transparent 32%),
                radial-gradient(circle at bottom left, rgba(14, 165, 233, 0.20), transparent 28%),
                linear-gradient(135deg, #f8fafc 0%, #e0f2fe 48%, #dbeafe 100%);
        }

        .internal-navbar {
            height: 72px;
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            object-fit: cover;
            border-radius: 12px;
            margin-right: 12px;
        }

        .navbar-brand {
            font-weight: 900;
            color: var(--wr-dark) !important;
        }

        .nav-link {
            font-weight: 800;
            color: var(--wr-dark) !important;
            border-radius: 14px;
            padding: 9px 14px !important;
            transition: all 0.25s ease;
        }

        .nav-link:hover {
            color: #ffffff !important;
            background: var(--wr-blue);
            box-shadow: 0 0 20px rgba(2, 132, 199, 0.35);
            transform: translateY(-1px);
        }

        .portal-hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 110px 0 50px;
            position: relative;
            overflow: hidden;
        }

        .portal-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            color: #075985;
            padding: 9px 16px;
            border-radius: 999px;
            font-weight: 900;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.10);
            margin-bottom: 24px;
        }

        .hero-title {
            font-size: clamp(2.7rem, 6vw, 5rem);
            line-height: 0.96;
            font-weight: 950;
            letter-spacing: -3px;
            color: var(--wr-dark);
            margin-bottom: 24px;
        }

        .hero-title span {
            color: #075985;
        }

        .hero-text {
            max-width: 640px;
            font-size: 1.05rem;
            line-height: 1.8;
            color: #334155;
            margin-bottom: 28px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 26px;
        }

        .btn-main {
            border-radius: 16px;
            font-weight: 900;
            padding: 12px 20px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
        }

        .quick-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            max-width: 650px;
        }

        .metric-card {
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 20px;
            padding: 18px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        .metric-card i {
            color: var(--wr-blue);
            margin-bottom: 10px;
        }

        .metric-card h6 {
            font-weight: 900;
            margin-bottom: 4px;
        }

        .metric-card small {
            color: #64748b;
        }

        .access-panel {
            background: rgba(15, 23, 42, 0.96);
            border-radius: 34px;
            padding: 30px;
            color: #ffffff;
            box-shadow: 0 28px 70px rgba(15, 23, 42, 0.28);
            position: relative;
            overflow: hidden;
        }

        .access-panel::before {
            content: "";
            position: absolute;
            width: 280px;
            height: 280px;
            background: rgba(56, 189, 248, 0.18);
            border-radius: 50%;
            top: -120px;
            right: -90px;
        }

        .access-header {
            position: relative;
            z-index: 1;
            margin-bottom: 22px;
        }

        .access-header img {
            width: 84px;
            height: 84px;
            object-fit: cover;
            border-radius: 22px;
            background: #ffffff;
            padding: 6px;
            margin-bottom: 16px;
        }

        .access-header h3 {
            font-weight: 950;
            margin-bottom: 8px;
        }

        .access-header p {
            color: rgba(255, 255, 255, 0.70);
            margin-bottom: 0;
        }

        .role-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .role-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 22px;
            padding: 18px;
            transition: all 0.25s ease;
        }

        .role-card:hover {
            background: rgba(255, 255, 255, 0.14);
            transform: translateY(-4px);
            box-shadow: 0 18px 38px rgba(0, 0, 0, 0.20);
        }

        .role-icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            background: #38bdf8;
            color: #082f49;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            font-size: 1.2rem;
        }

        .role-card h5 {
            font-weight: 950;
            margin-bottom: 6px;
        }

        .role-card p {
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.88rem;
            min-height: 42px;
        }

        .role-card .btn {
            border-radius: 14px;
            font-weight: 900;
        }

        .security-note {
            position: relative;
            z-index: 1;
            margin-top: 18px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            padding: 14px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.9rem;
        }

        @media (max-width: 991.98px) {
            .quick-metrics {
                grid-template-columns: 1fr;
            }

            .access-panel {
                margin-top: 30px;
            }
        }

        @media (max-width: 575.98px) {
            .role-grid {
                grid-template-columns: 1fr;
            }

            .hero-title {
                letter-spacing: -1px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top internal-navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('landing.internal') }}">
                <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}" alt="WRPlumb Logo" class="brand-logo">
                WRPlumb Internal
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#internalNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="internalNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">
                            <i class="fas fa-globe me-1"></i> Client Site
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#access">
                            <i class="fas fa-lock me-1"></i> Login Access
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="portal-hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <div class="portal-badge">
                        <i class="fas fa-shield-halved"></i>
                        Authorized WRPlumb Personnel Only
                    </div>

                    <h1 class="hero-title">
                        Secure workspace for <span>WRPlumb operations.</span>
                    </h1>

                    <p class="hero-text">
                        Access role-based tools for managing service requests, quotations, job orders,
                        schedules, billing records, personnel, inspections, and operational reports.
                    </p>

                    <div class="hero-actions">
                        <a href="#access" class="btn btn-primary btn-main">
                            <i class="fas fa-right-to-bracket me-1"></i> Select Portal
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-light btn-main">
                            <i class="fas fa-arrow-left me-1"></i> Back to Client Site
                        </a>
                    </div>

                    <div class="quick-metrics">
                        <div class="metric-card">
                            <i class="fas fa-users-cog"></i>
                            <h6>Role-Based</h6>
                            <small>Access based on assigned user role</small>
                        </div>

                        <div class="metric-card">
                            <i class="fas fa-clipboard-list"></i>
                            <h6>Operations</h6>
                            <small>Manage requests, jobs, and schedules</small>
                        </div>

                        <div class="metric-card">
                            <i class="fas fa-chart-line"></i>
                            <h6>Monitoring</h6>
                            <small>Track service progress and records</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5" id="access">
                    <div class="access-panel">
                        <div class="access-header">
                            <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}" alt="WRPlumb Logo">
                            <h3>Internal Login Access</h3>
                            <p>Select your assigned portal to continue.</p>
                        </div>

                        <div class="role-grid">
                            <div class="role-card">
                                <div class="role-icon">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <h5>Admin</h5>
                                <p>Manage system records, users, requests, job orders, and reports.</p>
                                <a href="{{ url('/admin/login') }}" class="btn btn-light w-100">
                                    Admin Login
                                </a>
                            </div>

                            <div class="role-card">
                                <div class="role-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h5>HR</h5>
                                <p>Manage personnel, staff records, and company workforce details.</p>
                                <a href="{{ url('/hr/login') }}" class="btn btn-info text-white w-100">
                                    HR Login
                                </a>
                            </div>

                            <div class="role-card">
                                <div class="role-icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <h5>Inspector</h5>
                                <p>View assigned inspections, schedules, and quotation request details.</p>
                                <a href="{{ url('/inspector/login') }}" class="btn btn-warning w-100">
                                    Inspector Login
                                </a>
                            </div>

                            <div class="role-card">
                                <div class="role-icon">
                                    <i class="fas fa-hard-hat"></i>
                                </div>
                                <h5>Personnel</h5>
                                <p>Access assigned job orders, service schedules, and progress updates.</p>
                                <a href="{{ url('/staff/login') }}" class="btn btn-success w-100">
                                    Personnel Login
                                </a>
                            </div>
                        </div>

                        <div class="security-note">
                            <i class="fas fa-circle-info me-1"></i>
                            This page is for authorized WRPlumb internal users. Use only the account assigned by the system administrator.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>