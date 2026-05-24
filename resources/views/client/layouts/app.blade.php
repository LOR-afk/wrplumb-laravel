<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $isSupportWidget = request()->boolean('support_widget');
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WRPlumb Client Panel')</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/client/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/client/support-widget.css') }}">
    @stack('styles')
</head>

<body class="{{ $isSupportWidget ? 'support-widget-mode' : '' }}">
<div class="app-shell">
    @unless($isSupportWidget)
        <aside class="sidebar" id="clientSidebar">
            <div class="brand-wrap">
                <img
                    src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}"
                    alt="WRPlumb Logo"
                    class="brand-logo"
                >
                <div>
                    <div class="brand-title">WRPlumb</div>
                    <div class="brand-subtitle">Client Panel</div>
                </div>
            </div>

            <div class="sidebar-label">Navigation</div>

            <a href="{{ route('client.dashboard') }}" class="sidebar-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                <i class="fas fa-house"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('client.requests.create') }}"
               class="sidebar-link {{ request()->routeIs('client.requests.create') ? 'active' : '' }}">
                <i class="fas fa-tools"></i>
                <span>Book a Service</span>
            </a>

            <a href="{{ route('client.requests.index') }}"
               class="sidebar-link {{ request()->routeIs('client.requests.index', 'client.requests.show') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>My Requests</span>
            </a>

            <a href="{{ route('client.quotations.index') }}"
               class="sidebar-link {{ request()->routeIs('client.quotations.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Quotations</span>
            </a>

            <a href="{{ route('client.contracts.index') }}"
               class="sidebar-link {{ request()->routeIs('client.contracts.*') ? 'active' : '' }}">
                <i class="fas fa-file-contract"></i>
                <span>Contracts</span>
            </a>

            <a href="{{ route('client.job-orders.index') }}"
               class="sidebar-link {{ request()->routeIs('client.job-orders.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-check"></i>
                <span>Job Orders</span>
            </a>

            <a href="{{ route('client.invoices.index') }}"
               class="sidebar-link {{ request()->routeIs('client.invoices.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice"></i>
                <span>Invoices</span>
            </a>

            <a href="{{ route('client.payments.index') }}"
               class="sidebar-link {{ request()->routeIs('client.payments.*') ? 'active' : '' }}">
                <i class="fas fa-credit-card"></i>
                <span>Payments</span>
            </a>

            <a href="{{ route('client.receipts.index') }}"
               class="sidebar-link {{ request()->routeIs('client.receipts.*') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i>
                <span>Receipts</span>
            </a>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-right-from-bracket me-2"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="client-sidebar-overlay" id="clientSidebarOverlay"></div>
    @endunless

    <main class="main">
        @unless($isSupportWidget)
            <div class="topbar">
                <div class="topbar-left">
                    <button type="button" class="mobile-menu-btn" id="clientSidebarToggle" aria-label="Open navigation menu">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="topbar-heading">
                        <h1 class="topbar-title">@yield('topbar_title', 'Client Dashboard')</h1>
                        <p class="topbar-subtitle">@yield('topbar_subtitle', 'Track your requests and communicate with support.')</p>
                    </div>
                </div>

                @php
                    /** @var \App\Models\User $currentUser */
                    $currentUser = auth()->user();
                    $clientUnreadAlerts = $currentUser ? $currentUser->alerts()->where('is_read', false)->count() : 0;
                @endphp

                <div class="topbar-actions d-flex align-items-center gap-2">
                    <a href="{{ route('client.alerts.index') }}" class="topbar-user text-decoration-none">
                        <i class="fas fa-bell"></i>
                        <span>Alerts</span>
                        @if ($clientUnreadAlerts > 0)
                            <span class="badge bg-danger rounded-pill">{{ $clientUnreadAlerts }}</span>
                        @endif
                    </a>

                    <div class="topbar-user">
                        <i class="fas fa-user"></i>
                        <span>{{ auth()->user()->first_name ?? 'Client' }}</span>
                    </div>
                </div>
            </div>
        @endunless

        <div class="content">
            @if (session('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    Please check the form and try again.
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

@unless($isSupportWidget)
    <div class="client-support-widget">
        <div class="client-support-panel" id="clientSupportPanel" aria-hidden="true">
            <iframe
                id="clientSupportFrame"
                src="{{ route('client.support.index', ['support_widget' => 1]) }}"
                title="Customer Support"
                loading="lazy"
            ></iframe>
        </div>

        <button type="button" class="client-support-toggle" id="clientSupportToggle" aria-label="Open customer support">
            <span class="client-support-toggle-inner">
                <i class="fas fa-headset"></i>
            </span>
        </button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const supportToggle = document.getElementById('clientSupportToggle');
            const supportPanel = document.getElementById('clientSupportPanel');

            if (!supportToggle || !supportPanel) return;

            function closeSupportPanel() {
                supportPanel.classList.remove('open');
                supportToggle.classList.remove('open');
                supportPanel.setAttribute('aria-hidden', 'true');
                supportToggle.setAttribute('aria-label', 'Open customer support');
            }

            supportToggle.addEventListener('click', function () {
                const isOpen = supportPanel.classList.toggle('open');

                supportToggle.classList.toggle('open', isOpen);
                supportPanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                supportToggle.setAttribute('aria-label', isOpen ? 'Close customer support' : 'Open customer support');
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeSupportPanel();
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarToggle = document.getElementById('clientSidebarToggle');
            const sidebarOverlay = document.getElementById('clientSidebarOverlay');

            function closeClientSidebar() {
                document.body.classList.remove('client-sidebar-open');
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function () {
                    document.body.classList.toggle('client-sidebar-open');
                });
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeClientSidebar);
            }

            document.querySelectorAll('#clientSidebar .sidebar-link').forEach(function (link) {
                link.addEventListener('click', closeClientSidebar);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeClientSidebar();
                }
            });
        });
    </script>
@endunless
</body>
</html>
