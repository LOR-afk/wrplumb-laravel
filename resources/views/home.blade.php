<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>WRPlumb | Plumbing and Construction Services</title>

    <link         rel="icon"         type="image/jpeg"

        href="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}"    >

    <link         href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"         rel="stylesheet"

        >

    <link         rel="stylesheet"

        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"    >

    <link         rel="stylesheet"         href="{{ asset('assets/css/home.css') }}?v=20261003-2200"    >

    <link rel="stylesheet" href="{{ asset('assets/css/register-modal.css') }}?v=20261003-2145">

</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light navbar-home fixed-top">

        <div class="container">

            <a class="navbar-brand" href="#home" aria-label="WRPlumb homepage">

                <img

                    src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}"

                    alt="WRPlumb Logo"                 class="logo-img"            >

                <strong>WRPLUMB</strong>

                </a>

            <button             id="landingNavbarToggle"             class="navbar-toggler"

                type="button"             data-bs-toggle="collapse"             data-bs-target="#navbarNav"

                aria-controls="navbarNav"             aria-expanded="false"             aria-label="Toggle navigation"

                >

                <span class="navbar-toggler-icon"></span>

                </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav ms-auto align-items-lg-center">

                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                                        
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>

                    <li class="nav-item"><a class="nav-link" href="#process">Process</a></li>

                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>

                    <li class="nav-item mt-2 mt-lg-0">

                        <button type="button" class="nav-link btn nav-auth-btn nav-signup ms-lg-2"

                            data-bs-toggle="modal" data-bs-target="#registerModal">Sign Up</button>

                        </li>

                    <li class="nav-item mt-2 mt-lg-0">

                        <a class="nav-link btn nav-auth-btn nav-login ms-lg-2"

                            href="{{ route('login') }}">Sign In</a>

                        </li>

                    </ul>

                </div>

            </div>

    </nav>

    <main>

        <section id="home" class="wr-hero">

            <div class="wr-hero-bg" style="background-image:url('{{ asset('assets/images/wrplumb-hero.png') }}')"></div>

            <div class="wr-hero-overlay"></div>

            <div class="container wr-hero-inner">

                <div class="wr-hero-content">

                    <div class="wr-hero-kicker">WRPlumb · Cagayan de Oro</div>

                    <h1>Reliable plumbing and construction work for homes and businesses.</h1>

                    <p>Send your service request online, attach photos if needed, and let the WRPlumb team review the

                        job before scheduling.</p>

                    <div class="wr-hero-actions">

                        <button type="button" class="btn wr-btn-primary request-cta" data-bs-toggle="modal"

                            data-bs-target="#quoteModal">Request a Service</button>

                        <a href="{{ route('login') }}" class="btn wr-btn-ghost">Sign In</a>

                    </div>

                    <div class="wr-hero-details">

                        <span><i class="fas fa-location-dot"></i> Bulua, Cagayan de Oro</span>

                        <span><i class="fas fa-phone"></i> (088) 850 5197</span>

                    </div>

                </div>

                <div class="wr-hero-service-box">

                    <span class="wr-hero-service-label">Services</span>

                    <div><strong>Plumbing</strong><span>Repairs · Pipes · Fixtures · Water lines</span></div>

                    <div><strong>Construction</strong><span>Renovation · Masonry · Carpentry · Finishing</span></div>

                    <button type="button" class="wr-service-link" data-bs-toggle="modal"

                        data-bs-target="#quoteModal">Start a service request <i class="fas fa-arrow-right"></i></button>

                </div>

            </div>

        </section>

        <section class="wr-service-band">

            <div class="container">

                <div class="wr-service-band-grid">

                    <div><span>01</span><strong>Residential</strong><small>Home repair and improvement work</small>

                    </div>

                    <div><span>02</span><strong>Commercial</strong><small>Business and property service requests</small>

                    </div>

                    <div><span>03</span><strong>Plumbing</strong><small>Repair, replacement, and installation</small>

                    </div>

                    <div><span>04</span><strong>Construction</strong><small>Renovation and finishing services</small>

                    </div>

                </div>

            </div>

        </section>

        <section id="services" class="services-section">

            <div class="container">

                <div class="section-heading split-heading">

                    <div>

                        <span class="section-kicker">What we do</span>

                        <h2>Services for repair, renovation, and installation.</h2>

                    </div>

                    <p>WRPlumb handles plumbing concerns and selected construction work for residential and commercial

                        clients.</p>

                </div>

                <div class="wr-service-showcase">

                    <article class="wr-service-photo-card">

                        <img src="{{ asset('assets/images/wrplumb-hero.png') }}" alt="WR Plumbing and Construction Services">

                        <div class="wr-service-photo-overlay"></div>

                        <div class="wr-service-photo-copy">

                            <span>Plumbing Services</span>

                            <h3>Repair and installation work</h3>

                            <ul>

                                <li>Leak and pipe repair</li>

                                <li>Drain, toilet, and faucet concerns</li>

                                <li>Water and waste line work</li>

                                <li>Pumps, fixtures, and sprinkler systems</li>

                            </ul>

                        </div>

                    </article>

                    <article class="wr-service-photo-card">

                        <img src="https://images.pexels.com/photos/29181494/pexels-photo-29181494/free-photo-of-construction-worker-laying-tile-in-renovation-project.jpeg?auto=compress&dpr=1&h=900&w=1600"

                            alt="Construction and renovation work">

                        <div class="wr-service-photo-overlay"></div>

                        <div class="wr-service-photo-copy">

                            <span>Construction Services</span>

                            <h3>Renovation and finishing work</h3>

                            <ul>

                                <li>Masonry and carpentry</li>

                                <li>Tile installation</li>

                                <li>Steel works and welding</li>

                                <li>Painting and finishing work</li>

                            </ul>

                        </div>

                    </article>

                </div>

            </div>

        </section>

        <section id="process" class="process-section">

            <div class="container">

                <div class="section-heading">

                    <span class="section-kicker">How it works</span>

                    <h2>From request to completed service.</h2>

                    <p>The client portal keeps the important steps in one place without making the process complicated.

                    </p>

                </div>

                <div class="wr-process">

                    <div class="wr-process-line"></div>

                    <article><span>1</span>

                        <h3>Submit Request</h3>

                        <p>Send the service details, location, preferred date, and photos if available.</p>

                    </article>

                    <article><span>2</span>

                        <h3>Review &amp; Schedule</h3>

                        <p>The team reviews the concern and arranges the next service or inspection schedule.</p>

                    </article>

                    <article><span>3</span>

                        <h3>Quotation &amp; Work</h3>

                        <p>Review the quotation and follow the job as the assigned work progresses.</p>

                    </article>

                    <article><span>4</span>

                        <h3>Payment &amp; Records</h3>

                        <p>View invoices, payments, receipts, and completed service records from your account.</p>

                    </article>

                </div>

            </div>

        </section>

        <section id="contact" class="contact-section">

            <div class="container">

                <div class="wr-contact-banner">

                    <div>

                        <span class="section-kicker light">Need assistance?</span>

                        <h2>Tell us what needs to be repaired or built.</h2>

                        <p>Send a service request online or contact WRPlumb directly for assistance.</p>

                    </div>

                    <div class="wr-contact-info">

                        <span>139 Upper Zone 4 Bulua, Cagayan de Oro, Philippines</span>

                        <a href="tel:+63888505197">(088) 850 5197</a>

                        <a href="mailto:wrplumbing@gmail.com">wrplumbing@gmail.com</a>

                    </div>

                    <div class="wr-contact-actions">

                        <button type="button" class="btn wr-btn-accent" data-bs-toggle="modal"

                            data-bs-target="#quoteModal">Request Service</button>

                        <a href="{{ route('login') }}" class="btn wr-btn-contact-outline">Sign In</a>

                    </div>

                </div>

            </div>

        </section>

    </main>

    <footer class="footer-section">

        <div class="container">

            <div class="footer-main-row">

                <div class="footer-brand-copy">

                    <p class="mb-1">

                        <strong>WRPlumb</strong> — Plumbing and Construction Services

                        </p>

                    <p class="mb-0 footer-copyright">

                        &copy; <span id="current-year"></span> WRPlumb. All rights reserved.

                        </p>

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

                        <h4 class="modal-title mb-1" id="quoteModalLabel">Request a Service</h4>

                        <p class="mb-0 text-muted small">Tell us what you need so our team can

                            review and assist you.</p>

                        </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"

                        aria-label="Close"></button>

                    </div>

                <div class="modal-body p-4">

                    <div id="fq-error" class="alert alert-danger d-none" role="alert"></div>

                    <div id="fq-success" class="alert alert-success d-none" role="alert"></div>

                    @if (session('success'))

                        <div class="alert alert-success" role="alert">{{ session('success') }}</div>

                    @endif

                    @if ($errors->any() && old('form_type') !== 'register')

                        <div class="alert alert-danger" role="alert">Failed to submit request. Please check the

                            required fields.</div>

                    @endif

                    <form id="free-quotation-form" action="{{ route('free-quotation.store') }}"

                        method="POST" enctype="multipart/form-data">

                        @csrf

                        <div class="fq-grid">

                            <div class="fq-field fq-first"><label for="fq-first-name"

                                    class="form-label">First Name</label><input type="text" id="fq-first-name"

                                    name="first_name" class="form-control" placeholder="Your first name" required>

                            </div>

                            <div class="fq-field fq-mi fq-mi-col"><label

                                    for="fq-middle-initial" class="form-label">M.I. <small

                                        class="text-muted">(Optional)</small></label><input type="text"

                                    id="fq-middle-initial" name="middle_initial" class="form-control" maxlength="1"

                                    placeholder="M"><input type="hidden" id="fq-name" name="name"

                                    value=""></div>

                            <div class="fq-field fq-last"><label for="fq-last-name"

                                    class="form-label">Last Name</label><input type="text" id="fq-last-name"

                                    name="last_name" class="form-control" placeholder="Your last name" required>

                            </div>

                            <div class="fq-field fq-half"><label for="fq-email"

                                    class="form-label">Email</label><input type="email" id="fq-email"

                                    name="email" class="form-control" placeholder="you@example.com" required></div>

                            <div class="fq-field fq-half"><label for="fq-phone"

                                    class="form-label">Contact Number</label><input type="text" id="fq-phone"

                                    name="phone" class="form-control" placeholder="09XXXXXXXXX" required></div>

                            <div class="fq-field fq-half"><label for="fq-service-category"

                                    class="form-label">Service Category</label><select id="fq-service-category"

                                    name="service_category" class="form-select" required>

                                    <option value="" selected disabled>Select a category</option>

                                    <option value="plumbing">Plumbing</option>

                                    <option value="construction">Construction</option>

                                </select></div>

                            <div class="fq-field fq-half"><label for="fq-service-type"

                                    class="form-label">Service Needed</label><select id="fq-service-type"

                                    name="service_type" class="form-select" required>

                                    <option value="" selected disabled>Select a service</option>

                                </select></div>

                            <div class="fq-field fq-half"><label for="fq-project-type"

                                    class="form-label">Project Type</label><select id="fq-project-type"

                                    name="project_type" class="form-select" required>

                                    <option value="" selected disabled>Select project type</option>

                                    <option value="Residential">Residential</option>

                                    <option value="Commercial">Commercial</option>

                                </select></div>

                            <div class="fq-field fq-half"><label for="fq-preferred-date"

                                    class="form-label">Preferred Service Date</label><input type="date"

                                    id="fq-preferred-date" name="preferred_date" class="form-control"></div>

                            <div class="fq-field fq-full"><label for="fq-address"

                                    class="form-label">Service Address</label>

                                <div class="address-autocomplete">

                                    <div class="input-group"><input type="text" id="fq-address" name="address"

                                            class="form-control" placeholder="Enter address, e.g., Barra, Opol"

                                            autocomplete="street-address" required><a id="fq-address-map"

                                            class="btn btn-outline-secondary disabled" href="#" target="_blank"

                                            rel="noopener noreferrer" aria-disabled="true" tabindex="-1"

                                            title="Open the typed address in Google Maps"><i

                                                class="fas fa-map-marked-alt"></i></a></div>

                                    <div id="fq-address-suggestions" class="address-suggestions d-none"></div><input

                                        type="hidden" id="fq-address-lat" name="address_lat" value=""><input

                                        type="hidden" id="fq-address-lon" name="address_lon" value="">

                                </div>

                            </div>

                            <div class="fq-field fq-full"><label for="fq-details"

                                    class="form-label">Details of the Problem / Work Needed</label>

                                <textarea id="fq-details" name="details" rows="4" class="form-control"

                                    placeholder="Describe your plumbing or construction concern." required></textarea>

                            </div>

                            <div class="fq-field fq-full"><label for="fq-attachments"

                                    class="form-label">Attach Photos / Videos <span

                                        class="text-muted">(Optional)</span></label><input type="file"

                                    id="fq-attachments" name="attachments[]" class="form-control"

                                    accept="image/*,video/*" multiple>

                                <div class="form-text">Photos/videos help the team understand the concern before

                                    scheduling service or inspection. Max 25MB per file.</div>

                            </div>

                            <div class="fq-field fq-full">

                                <div class="form-check legal-consent-check">

                                    <input class="form-check-input" type="checkbox" value="1"

                                        id="fq-consent" name="consent" required>

                                    <label class="form-check-label" for="fq-consent">

                                        I agree to be contacted regarding this service request and

                                        acknowledge the

                                        <a href="{{ url('/terms') }}" target="_blank"

                                            rel="noopener">Service Terms</a>

                                        and

                                        <a href="{{ url('/privacy-policy') }}" target="_blank"

                                            rel="noopener">Privacy Policy</a>.

                                        </label>

                                    </div>

                            </div>

                            <div class="fq-field fq-full"><button type="submit"

                                    class="btn wr-submit-btn w-100"><i class="fas fa-paper-plane me-2"></i>Send

                                    Service Request</button></div>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

    </div>

    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered register-dialog">
        <div class="modal-content register-modal-content border-0">
            <div class="register-modal-top">
                <button type="button" class="register-back-btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <div class="register-logo-wrap">
                    <img
                        src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}"
                        alt="WRPlumb Logo"
                        class="register-modal-logo"
                    >
                    <span class="register-trademark">™</span>
                </div>

                <h4 class="modal-title" id="registerModalLabel">Create your account</h4>
                <p class="register-subtitle">Join WRPlumb to access your account and get started.</p>
            </div>

            <div class="modal-body register-modal-body">
                @if ($errors->any() && old('form_type') === 'register')
                    <div class="alert alert-danger register-error">
                        <strong>Please review the details below.</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="register-form" method="POST" action="{{ route('register.store') }}" class="register-form">
                    @csrf
                    <input type="hidden" name="form_type" value="register">

                    <div class="register-name-grid">
                        <div class="register-field">
                            <label for="first_name" class="form-label">First Name</label>
                            <input
                                type="text"
                                class="form-control @error('first_name') is-invalid @enderror"
                                id="first_name"
                                name="first_name"
                                value="{{ old('first_name') }}"
                                autocomplete="given-name"
                                required
                            >
                            @error('first_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="register-field register-mi-field">
                            <label for="middle_initial" class="form-label">M.I.</label>
                            <input
                                type="text"
                                class="form-control @error('middle_initial') is-invalid @enderror"
                                id="middle_initial"
                                name="middle_initial"
                                value="{{ old('middle_initial') }}"
                                maxlength="1"
                                autocomplete="additional-name"
                            >
                            @error('middle_initial')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="register-field">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input
                                type="text"
                                class="form-control @error('last_name') is-invalid @enderror"
                                id="last_name"
                                name="last_name"
                                value="{{ old('last_name') }}"
                                autocomplete="family-name"
                                required
                            >
                            @error('last_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="register-field">
                        <label for="email" class="form-label">Email</label>
                        <div class="register-input-wrap">
                            <span class="register-input-icon"><i class="far fa-envelope"></i></span>
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="you@yourname.com"
                                autocomplete="email"
                                required
                            >
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="register-field">
                        <label for="phone" class="form-label">Phone Number</label>
                        <div class="register-input-wrap">
                            <span class="register-input-icon"><i class="fas fa-phone"></i></span>
                            <input
                                type="tel"
                                class="form-control @error('phone') is-invalid @enderror"
                                id="phone"
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="09XXXXXXXXX"
                                autocomplete="tel"
                                required
                            >
                        </div>
                        @error('phone')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="register-field">
                        <label for="username" class="form-label">Username</label>
                        <div class="register-input-wrap">
                            <span class="register-input-icon"><i class="far fa-user"></i></span>
                            <input
                                type="text"
                                class="form-control @error('username') is-invalid @enderror"
                                id="username"
                                name="username"
                                value="{{ old('username') }}"
                                placeholder="e.g. lore123"
                                autocomplete="username"
                                required
                            >
                        </div>
                        @error('username')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="register-field">
                        <label for="address" class="form-label">Address <span class="register-optional">(Optional)</span></label>
                        <textarea
                            class="form-control @error('address') is-invalid @enderror"
                            id="address"
                            name="address"
                            rows="2"
                            placeholder="House/Unit, Street, Barangay, City"
                            autocomplete="street-address"
                        >{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="register-field">
                        <label for="password" class="form-label">Password</label>
                        <div class="register-input-wrap">
                            <span class="register-input-icon"><i class="fas fa-lock"></i></span>
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                placeholder="Create a password"
                                autocomplete="new-password"
                                required
                            >
                            <button
                                class="register-password-toggle"
                                type="button"
                                data-password-toggle="password"
                                aria-label="Show or hide password"
                            >
                                <i class="far fa-eye-slash"></i>
                            </button>
                        </div>
                        <div class="register-strength" id="registerStrength" aria-hidden="true">
                            <span></span><span></span><span></span><span></span><span></span>
                        </div>
                        <div class="register-strength-text" id="registerStrengthText"></div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="register-field">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <div class="register-input-wrap">
                            <span class="register-input-icon"><i class="fas fa-lock"></i></span>
                            <input
                                type="password"
                                class="form-control"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Confirm your password"
                                autocomplete="new-password"
                                required
                            >
                            <button
                                class="register-password-toggle"
                                type="button"
                                data-password-toggle="password_confirmation"
                                aria-label="Show or hide password confirmation"
                            >
                                <i class="far fa-eye-slash"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback d-block d-none" id="passwordMatchError">
                            Passwords do not match.
                        </div>
                    </div>

                    <div class="register-consent">
                        <div class="form-check">
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
                                <a href="{{ url('/terms') }}" target="_blank" rel="noopener">Terms of Service</a>
                                and
                                <a href="{{ url('/privacy-policy') }}" target="_blank" rel="noopener">Privacy Policy</a>.
                            </label>
                        </div>
                        @error('terms')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn register-primary-btn">
                        Create Account <span>→</span>
                    </button>

                    <p class="register-login-note">
                        Already have an account?
                        <a href="{{ route('login') }}">Sign in</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('register-form');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
        const matchError = document.getElementById('passwordMatchError');
        const strength = document.getElementById('registerStrength');
        const strengthText = document.getElementById('registerStrengthText');

        if (!form || !password || !confirmPassword) return;

        function updateStrength() {
            const value = password.value;
            let score = 0;

            if (value.length >= 8) score++;
            if (/[A-Z]/.test(value)) score++;
            if (/[a-z]/.test(value)) score++;
            if (/[0-9]/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value)) score++;

            strength.querySelectorAll('span').forEach(function (bar, index) {
                bar.classList.toggle('active', index < score);
            });

            strengthText.textContent = value ? (score >= 4 ? 'Strong password' : 'Keep strengthening your password') : '';
            strengthText.classList.toggle('is-strong', score >= 4);
        }

        function validatePasswords() {
            const matches = password.value === confirmPassword.value;

            confirmPassword.classList.toggle('is-invalid', !matches && confirmPassword.value !== '');
            matchError.classList.toggle('d-none', matches || confirmPassword.value === '');

            return matches;
        }

        password.addEventListener('input', function () {
            updateStrength();
            validatePasswords();
        });

        confirmPassword.addEventListener('input', validatePasswords);

        form.addEventListener('submit', function (event) {
            if (!validatePasswords()) {
                event.preventDefault();
                confirmPassword.focus();
            }
        });

        document.addEventListener('click', function (event) {
            const button = event.target.closest('[data-password-toggle]');
            if (!button) return;

            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;

            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';

            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', show);
                icon.classList.toggle('fa-eye-slash', !show);
            }
        });
    });
</script>

    @if ($errors->any() && old('form_type') === 'register')

        <script>

            document.addEventListener('DOMContentLoaded', function() {

                const registerModal = document.getElementById('registerModal');

                if (registerModal && typeof bootstrap !== 'undefined') {

                    new bootstrap.Modal(registerModal).show();

                }

            });

        </script>

    @endif

    @if ($errors->any() && old('form_type') !== 'register')

        <script>

            document.addEventListener('DOMContentLoaded', function() {

                const quoteModal = document.getElementById('quoteModal');

                if (quoteModal && typeof bootstrap !== 'undefined') {

                    new bootstrap.Modal(quoteModal).show();

                }

            });

        </script>

    @endif

    <script>

        document.addEventListener('DOMContentLoaded', function() {

            const category = document.getElementById("fq-service-category");

            const serviceType = document.getElementById("fq-service-type");

            const form = document.getElementById("free-quotation-form");

            const email = document.getElementById("fq-email");

            const phone = document.getElementById("fq-phone");

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

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

            form.addEventListener("submit", function(e) {

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('assets/js/home.js') }}?v=20260225b"></script>

</body>

</html>
