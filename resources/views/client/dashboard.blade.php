@extends('client.layouts.app')

@section('title', 'Client Dashboard - WRPlumb')
@section('topbar_title', 'Client Dashboard')
@section('topbar_subtitle', 'Access your service requests and customer support.')

@section('content')
<div class="page-header">
    <h1>Welcome, {{ auth()->user()->first_name ?? 'Client' }}</h1>
    <p>Use your dashboard to track requests, contact support, and manage your account activity.</p>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <a href="#" class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-user-cog"></i>
            </div>
            <div class="feature-title">My Profile</div>
            <p class="feature-text">View and update your client information.</p>
        </a>
    </div>

    <div class="col-md-4">
        <a href="{{ route('client.requests.create') }}" class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-tools"></i>
            </div>
            <div class="feature-title">Book a Service</div>
            <p class="feature-text">Submit a new plumbing or service request.</p>
        </a>
    </div>

    <div class="col-md-4">
        <a href="{{ route('client.requests.index') }}" class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-file-signature"></i>
            </div>
            <div class="feature-title">My Requests</div>
            <p class="feature-text">Track quotations, assigned inspector, and service progress.</p>
        </a>
    </div>

        <div class="col-md-4">
            <a href="{{ route('client.quotations.index') }}" class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="feature-title">Quotations</div>
                <p class="feature-text">View the quotations prepared for your service requests.</p>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('client.contracts.index') }}" class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="feature-title">Contracts</div>
                <p class="feature-text">View service contracts generated from your quotations.</p>
            </a>
        </div>

    <div class="col-md-4">
        <a href="{{ route('client.payments.index') }}" class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-credit-card"></i>
            </div>
            <div class="feature-title">Payments</div>
            <p class="feature-text">View recorded payments for your invoices.</p>
        </a>
    </div>

        <div class="col-md-4">
            <a href="{{ route('client.receipts.index') }}" class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="feature-title">Receipts</div>
                <p class="feature-text">View official receipts for your confirmed payments.</p>
            </a>
        </div>

    <div class="col-md-4">
        <a href="{{ route('client.invoices.index') }}" class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="feature-title">Invoices</div>
            <p class="feature-text">View your billing records and invoice details.</p>
        </a>
    </div>

        <div class="col-md-4">
        <a href="{{ route('client.job-orders.index') }}" class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="feature-title">Job Orders</div>
            <p class="feature-text">Track your scheduled and ongoing service work.</p>
        </a>
    </div>

    <div class="col-md-4">
        <a href="{{ route('client.support.index') }}" class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-headset"></i>
            </div>
            <div class="feature-title">Customer Support</div>
            <p class="feature-text">Chat with support if you have questions or concerns.</p>
        </a>
    </div>
</div>
@endsection