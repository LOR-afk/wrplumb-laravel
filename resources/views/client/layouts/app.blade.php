<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $isSupportWidget = request()->boolean('support_widget');
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WRPlumb Client Panel')</title>

    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
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
        <aside class="sidebar">
            <div class="brand-wrap">
                <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}" alt="WRPlumb Logo" class="brand-logo">
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
                <i class="fas fa-tools me-2"></i> Book a Service
            </a>

            <a href="{{ route('client.requests.index') }}"
               class="sidebar-link {{ request()->routeIs('client.requests.index', 'client.requests.show') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list me-2"></i> My Requests
            </a>

            <a href="{{ route('client.quotations.index') }}"
               class="sidebar-link {{ request()->routeIs('client.quotations.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar me-2"></i> Quotations
            </a>

            <a href="{{ route('client.contracts.index') }}"
               class="sidebar-link {{ request()->routeIs('client.contracts.*') ? 'active' : '' }}">
                <i class="fas fa-file-contract me-2"></i> Contracts
            </a>

            <a href="{{ route('client.job-orders.index') }}"
               class="sidebar-link {{ request()->routeIs('client.job-orders.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-check me-2"></i> Job Orders
            </a>

            <a href="{{ route('client.invoices.index') }}"
               class="sidebar-link {{ request()->routeIs('client.invoices.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice me-2"></i> Invoices
            </a>

            <a href="{{ route('client.payments.index') }}"
               class="sidebar-link {{ request()->routeIs('client.payments.*') ? 'active' : '' }}">
                <i class="fas fa-credit-card me-2"></i> Payments
            </a>

            <a href="{{ route('client.receipts.index') }}"
               class="sidebar-link {{ request()->routeIs('client.receipts.*') ? 'active' : '' }}">
                <i class="fas fa-receipt me-2"></i> Receipts
            </a>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-right-from-bracket me-2"></i>Logout
                    </button>
                </form>
            </div>
        </aside>
    @endunless

    <main class="main">
        @unless($isSupportWidget)
            <div class="topbar">
                <div>
                    <h1 class="topbar-title">@yield('topbar_title', 'Client Dashboard')</h1>
                    <p class="topbar-subtitle">@yield('topbar_subtitle', 'Track your requests and communicate with support.')</p>
                </div>

                @php
                    /** @var \App\Models\User $currentUser */
                    $currentUser = auth()->user();
                    $clientUnreadAlerts = $currentUser ? $currentUser->alerts()->where('is_read', false)->count() : 0;
                @endphp

                <div class="d-flex align-items-center gap-2">
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
            <img src="{{ asset('image/customer-support.png') }}" alt="Customer Support" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
            <i class="fas fa-headset support-icon-fallback" style="display:none;"></i>
        </button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('clientSupportToggle');
            const panel = document.getElementById('clientSupportPanel');

            if (!toggle || !panel) return;

            toggle.addEventListener('click', function () {
                const isOpen = panel.classList.toggle('open');
                toggle.classList.toggle('open', isOpen);
                panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                toggle.setAttribute('aria-label', isOpen ? 'Close customer support' : 'Open customer support');
            });
        });
    </script>
@endunless

</body>
</html>
