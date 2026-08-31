<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WRPlumb | Plumbing and Construction Services</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{asset('assets/css/home.css') }}?v=20260517a">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light navbar-home fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#home" aria-label="WRPlumb homepage">
            <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}" alt="WRPlumb Logo" class="logo-img">
            <strong>WRPlumb</strong>
        </a>

        <button id="landingNavbarToggle" class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#process">Process</a></li>
                <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                <li class="nav-item mt-2 mt-lg-0">
                    <button type="button" class="nav-link btn nav-auth-btn nav-signup ms-lg-2 border-0" data-bs-toggle="modal" data-bs-target="#registerModal">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </li>
                <li class="nav-item mt-2 mt-lg-0">
                    <a class="nav-link btn nav-auth-btn nav-login ms-lg-2" href="{{ route('login') }}">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main>
    <section id="home" class="wr-hero">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="wr-eyebrow"><i class="fas fa-shield-alt"></i> Plumbing and construction service portal</div>
                    <h1 class="wr-hero-title">Request, schedule, and track WRPlumb services in one place.</h1>
                    <p class="wr-hero-lead">A simple online system for clients to request plumbing or construction services, receive updates, and monitor quotation, scheduling, billing, payment, and receipt records.</p>

                    <div class="wr-hero-actions">
                        <a href="{{ route('login') }}" class="btn wr-btn-primary"><i class="fas fa-sign-in-alt me-2"></i>Login to Portal</a>
                        <button type="button" class="btn wr-btn-outline" data-bs-toggle="modal" data-bs-target="#registerModal"><i class="fas fa-user-plus me-2"></i>Create Client Account</button>
<button type="button"
    class="btn wr-btn-light request-cta"
    data-bs-toggle="modal"
    data-bs-target="#quoteModal">
    <i class="fas fa-file-signature me-2"></i>Request a Quote
</button>                    </div>

                    <div class="wr-trust-row">
                        <div class="wr-trust-card"><i class="fas fa-calendar-check"></i><strong>Easy Request</strong><span>Submit service details online</span></div>
                        <div class="wr-trust-card"><i class="fas fa-user-check"></i><strong>Team Review</strong><span>Admin/HR handles scheduling</span></div>
                        <div class="wr-trust-card"><i class="fas fa-receipt"></i><strong>Clear Records</strong><span>Track quotations and payments</span></div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="wr-hero-card">
                        <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}" alt="WRPlumb Plumbing and Construction">
                        <div class="wr-hero-card-body">
                            <span class="status-pill"><i class="fas fa-circle"></i> Ready to serve</span>
                            <h5>For clients, admin, HR, and inspectors</h5>
                            <p>One login page. The system opens the correct dashboard based on the user role.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="process" class="process-section">
        <div class="container">
            <div class="section-heading text-center">
                <span class="section-kicker">How it works</span>
                <h2>Simple client journey</h2>
                <p>Only the essential steps are shown so users understand the system quickly.</p>
            </div>
            <div class="process-grid">
                <div class="process-card"><div class="process-icon">1</div><h4>Submit Request</h4><p>Client provides service details, address, preferred date, and optional attachments.</p></div>
                <div class="process-card"><div class="process-icon">2</div><h4>Review and Schedule</h4><p>The system identifies whether the request is ready for service or needs inspection first.</p></div>
                <div class="process-card"><div class="process-icon">3</div><h4>Track Updates</h4><p>Client can view request status, quotation, contract, job order, invoice, and payment updates.</p></div>
                <div class="process-card"><div class="process-icon">4</div><h4>Confirm Records</h4><p>Receipts and completed service records remain organized for client and staff reference.</p></div>
            </div>
        </div>
    </section>

    <section id="services" class="services-section">
        <div class="container">
            <div class="section-heading text-center">
                <span class="section-kicker">Services</span>
                <h2>What clients can request</h2>
                <p>WRPlumb supports common plumbing concerns and construction-related work requests.</p>
            </div>
            <div class="service-summary-grid">
                <div class="service-summary-card">
                    <div class="service-summary-icon"><i class="fas fa-faucet"></i></div>
                    <h4>Plumbing Services</h4>
                    <p>Leak repair, clogged drain, toilet and faucet repair, pipe replacement, water line, waste line, pump, fixtures, and sprinkler system requests.</p>
                </div>
                <div class="service-summary-card">
                    <div class="service-summary-icon"><i class="fas fa-hard-hat"></i></div>
                    <h4>Construction Services</h4>
                    <p>Renovation, masonry, carpentry, finishing works, tile installation, steel works, welding, and painting work requests.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="contact-section">
        <div class="container">
            <div class="contact-card">
                <div>
                    <span class="section-kicker">Contact</span>
                    <h2>Need help with a request?</h2>
                    <p>Contact WRPlumb or login to the portal to manage your request records.</p>
                </div>
                <div class="contact-details">
                    <div><i class="fas fa-map-marker-alt"></i><span>139 Upper Zone 4 Bulua, Cagayan de Oro, Philippines</span></div>
                    <div><i class="fas fa-phone"></i><a href="tel:+63888505197">(088) 850 5197</a></div>
                    <div><i class="fas fa-envelope"></i><a href="mailto:wrplumbing@gmail.com">wrplumbing@gmail.com</a></div>
                </div>
                <div class="contact-actions">
                    <button type="button" class="btn wr-btn-primary" data-bs-toggle="modal" data-bs-target="#quoteModal"><i class="fas fa-file-signature me-2"></i>Request a Quote</button>
                    <a href="{{ route('login') }}" class="btn wr-btn-outline"><i class="fas fa-sign-in-alt me-2"></i>Login</a>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="footer-section">
    <div class="container">
        <div class="footer-main-row">
            <div class="footer-brand-copy">
                <p class="mb-1"><strong>WRPlumb</strong> — Plumbing and Construction Services</p>
                <p class="mb-0 footer-copyright">&copy; <span id="current-year"></span> WRPlumb. All rights reserved.</p>
            </div>

            <nav class="footer-legal-links" aria-label="Legal links">
                <a href="{{ route('terms') }}">Terms &amp; Conditions</a>
                <span class="footer-divider" aria-hidden="true">•</span>
                <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
            </nav>
        </div>
    </div>
</footer>

<div class="modal fade" id="quoteModal" tabindex="-1" aria-labelledby="quoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content wr-modal-content border-0">
            <div class="modal-header wr-modal-header">
                <div>
                    <h4 class="modal-title mb-1" id="quoteModalLabel">Service Request Form</h4>
                    <p class="mb-0 text-muted small">Provide accurate details so WRPlumb can review your request properly.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="fq-error" class="alert alert-danger d-none" role="alert"></div>
                <div id="fq-success" class="alert alert-success d-none" role="alert"></div>

                @if (session('success'))
                    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                @endif

                @if ($errors->any() && old('form_type') !== 'register')
                    <div class="alert alert-danger" role="alert">Failed to submit request. Please check the required fields.</div>
                @endif

                <form id="free-quotation-form" action="{{ route('free-quotation.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-5"><label for="fq-first-name" class="form-label">First Name</label><input type="text" id="fq-first-name" name="first_name" class="form-control" placeholder="Your first name" required></div>
                        <div class="col-lg-2 col-md-2 fq-mi-col"><label for="fq-middle-initial" class="form-label">M.I. <small class="text-muted">(Optional)</small></label><input type="text" id="fq-middle-initial" name="middle_initial" class="form-control" maxlength="1" placeholder="M"><input type="hidden" id="fq-name" name="name" value=""></div>
                        <div class="col-lg-6 col-md-5"><label for="fq-last-name" class="form-label">Last Name</label><input type="text" id="fq-last-name" name="last_name" class="form-control" placeholder="Your last name" required></div>
                        <div class="col-md-6"><label for="fq-email" class="form-label">Email</label><input type="email" id="fq-email" name="email" class="form-control" placeholder="you@example.com" required></div>
                        <div class="col-md-6"><label for="fq-phone" class="form-label">Contact Number</label><input type="text" id="fq-phone" name="phone" class="form-control" placeholder="09XXXXXXXXX" required></div>
                        <div class="col-md-6"><label for="fq-service-category" class="form-label">Service Category</label><select id="fq-service-category" name="service_category" class="form-select" required><option value="" selected disabled>Select a category</option><option value="plumbing">Plumbing</option><option value="construction">Construction</option></select></div>
                        <div class="col-md-6"><label for="fq-service-type" class="form-label">Service Needed</label><select id="fq-service-type" name="service_type" class="form-select" required><option value="" selected disabled>Select a service</option></select></div>
                        <div class="col-md-6"><label for="fq-project-type" class="form-label">Project Type</label><select id="fq-project-type" name="project_type" class="form-select" required><option value="" selected disabled>Select project type</option><option value="Residential">Residential</option><option value="Commercial">Commercial</option></select></div>
                        <div class="col-md-6"><label for="fq-preferred-date" class="form-label">Preferred Service Date</label><input type="date" id="fq-preferred-date" name="preferred_date" class="form-control"></div>
                        <div class="col-12"><label for="fq-address" class="form-label">Service Address</label><div class="address-autocomplete"><div class="input-group"><input type="text" id="fq-address" name="address" class="form-control" placeholder="Enter address, e.g., Barra, Opol" autocomplete="street-address" required><a id="fq-address-map" class="btn btn-outline-secondary disabled" href="#" target="_blank" rel="noopener noreferrer" aria-disabled="true" tabindex="-1" title="Open the typed address in Google Maps"><i class="fas fa-map-marked-alt"></i></a></div><div id="fq-address-suggestions" class="address-suggestions d-none"></div><input type="hidden" id="fq-address-lat" name="address_lat" value=""><input type="hidden" id="fq-address-lon" name="address_lon" value=""></div></div>
                        <div class="col-12"><label for="fq-details" class="form-label">Details of the Problem / Work Needed</label><textarea id="fq-details" name="details" rows="4" class="form-control" placeholder="Describe your plumbing or construction concern." required></textarea></div>
                        <div class="col-12"><label for="fq-attachments" class="form-label">Attach Photos / Videos <span class="text-muted">(Optional)</span></label><input type="file" id="fq-attachments" name="attachments[]" class="form-control" accept="image/*,video/*" multiple><div class="form-text">Photos/videos help the team understand the concern before scheduling service or inspection. Max 25MB per file.</div></div>
                        <div class="col-12">
    <div class="form-check legal-consent-check">
        <input class="form-check-input" type="checkbox" value="1" id="fq-consent" name="consent" required>
        <label class="form-check-label" for="fq-consent">
            I agree to be contacted regarding this service request and acknowledge the
            <a href="{{ url('/terms') }}" target="_blank" rel="noopener">Service Terms</a>
            and
            <a href="{{ url('/privacy-policy') }}" target="_blank" rel="noopener">Privacy Policy</a>.
        </label>
    </div>
</div>
                        <div class="col-12"><button type="submit" class="btn wr-submit-btn w-100"><i class="fas fa-paper-plane me-2"></i>Submit Service Request</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content register-modal-content border-0">
            <div class="modal-header register-modal-header border-0">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}" alt="WRPlumb Logo" class="register-modal-logo">
                    <div>
                        <h4 class="modal-title mb-1" id="registerModalLabel">Create Your Account</h4>
                        <p class="mb-0 text-muted small">Create a client account and verify your email before using the portal.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-lg-5">
                @if ($errors->any() && old('form_type') === 'register')
                    <div class="alert alert-danger rounded-3"><strong>Account registration failed.</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <form id="register-form" method="POST" action="{{ route('register.store') }}">
                    @csrf
                    <input type="hidden" name="form_type" value="register">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="username" class="form-label">Username <span class="text-danger">*</span></label><input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" required>@error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    </div>
                    <div class="row">
                        <div class="col-md-5 mb-3"><label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label><input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" required>@error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-2 mb-3"><label for="middle_initial" class="form-label">M.I.</label><input type="text" class="form-control @error('middle_initial') is-invalid @enderror" id="middle_initial" name="middle_initial" value="{{ old('middle_initial') }}" maxlength="1">@error('middle_initial')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-5 mb-3"><label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label><input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" required>@error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    </div>
                    <div class="mb-3"><label for="password" class="form-label">Password <span class="text-danger">*</span></label><input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required><div class="form-text">Use at least 8 characters with uppercase, lowercase, number, and special character.</div>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="mb-3"><label for="email" class="form-label">Email <span class="text-danger">*</span></label><input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="mb-3"><label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label><input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required>@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="mb-4"><label for="address" class="form-label">Address</label><textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address') }}</textarea>@error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="form-check legal-consent-check mb-3">
    <input
        class="form-check-input @error('terms') is-invalid @enderror"
        type="checkbox"
        value="1"
        id="register-terms"
        name="terms"
        required
        @checked(old('terms'))
    >
    <label class="form-check-label" for="register-terms">
        I agree to the
        <a href="{{ url('/terms') }}" target="_blank" rel="noopener">Terms &amp; Conditions</a>
        and
        <a href="{{ url('/privacy-policy') }}" target="_blank" rel="noopener">Privacy Policy</a>.
    </label>
    @error('terms')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<button type="submit" class="btn wr-submit-btn w-100"><i class="fas fa-user-plus me-2"></i>Create Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@if ($errors->any() && old('form_type') === 'register')
<script>document.addEventListener('DOMContentLoaded', function () { const registerModal = document.getElementById('registerModal'); if (registerModal && typeof bootstrap !== 'undefined') { new bootstrap.Modal(registerModal).show(); } });</script>
@endif

@if ($errors->any() && old('form_type') !== 'register')
<script>document.addEventListener('DOMContentLoaded', function () { const quoteModal = document.getElementById('quoteModal'); if (quoteModal && typeof bootstrap !== 'undefined') { new bootstrap.Modal(quoteModal).show(); } });</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {

    const category = document.getElementById("fq-service-category");
    const serviceType = document.getElementById("fq-service-type");

    const form = document.getElementById("free-quotation-form");
    const email = document.getElementById("fq-email");
    const phone = document.getElementById("fq-phone");

    const emailRegex = /^[^\s@]+@[^\s@]+****\\.****[^\s@]+$/;
    const phoneRegex = /^09[0-9]{9}$/;

    function loadServices() {
        const selected = category.value;

        serviceType.innerHTML = '<option disabled selected>Select a service</option>';

        if (!serviceMap[selected]) {
            serviceType.disabled = true;
            return;
        }

        serviceMap[selected].forEach(item => {
            const option = document.createElement("option");
            option.value = item;
            option.textContent = item;
            serviceType.appendChild(option);
        });

        serviceType.disabled = false;
    }

    category.addEventListener("change", loadServices);

    function validateEmail() {
        return emailRegex.test(email.value.trim());
    }

    function validatePhone() {
        return phoneRegex.test(phone.value.trim());
    }

    form.addEventListener("submit", function (e) {

        let valid = true;

        if (!validateEmail()) {
            email.classList.add("is-invalid");
            valid = false;
        } else {
            email.classList.remove("is-invalid");
            email.classList.add("is-valid");
        }

        if (!validatePhone()) {
            phone.classList.add("is-invalid");
            valid = false;
        } else {
            phone.classList.remove("is-invalid");
            phone.classList.add("is-valid");
        }

        if (serviceType.disabled || !serviceType.value) {
            serviceType.classList.add("is-invalid");
            valid = false;
        } else {
            serviceType.classList.remove("is-invalid");
        }

        if (!valid) {
            e.preventDefault();

            const errorBox = document.getElementById("fq-error");
            errorBox.classList.remove("d-none");
            errorBox.innerText = "Please complete all required fields correctly before submitting.";
        }
    });

});
</script>
<script src="{{ asset('assets/js/home.js') }}?v=20260225b"></script>
</body>
</html>