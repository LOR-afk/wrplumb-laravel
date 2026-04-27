<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Professional Plumbing Services</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/custom.css?v=20260225b">
    <link rel="stylesheet" href="assets/css/home.css?v=20260225b">

</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-home fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <img src="image/294539416_407599744767669_1937739510480713048_n.jpg" alt="WRPlumb Logo" class="logo-img">
                <strong>WRPlumb</strong>
            </a>
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#process">Process</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#projects">Projects</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                
                    <li class="nav-item">
                        <button
                            type="button"
                            class="nav-link btn nav-auth-btn nav-signup ms-2 border-0"
                            data-bs-toggle="modal"
                            data-bs-target="#registerModal"
                        >
                            <i class="fas fa-user-plus"></i> Sign Up
                        </button>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link btn nav-auth-btn nav-login ms-2" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt"></i> Client Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Background Image -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Logo Section -->
                    <div class="hero-logo-section">
                        <img src="image/294539416_407599744767669_1937739510480713048_n.jpg" alt="WRPlumb Plumbing &amp; Construction" class="logo-large">
                    </div>
                    <h1>WRPlumb</h1>
                    <p class="lead">Professional Plumbing &amp; Construction Services You Can Trust</p>
                    <p>We are a local plumbing and construction service company in Cagayan de Oro City that constantly aims to deliver reliable &amp; cost effective service. Your trusted partner for all plumbing and construction needs.</p>
                    <div class="mt-4 mb-5">
                        <a href="client/register.html" class="btn btn-light btn-lg hero-btn me-3">
                            <i class="fas fa-user-plus"></i> Get Started - Book a Service
                        </a>
                        <a href="#free-quotation" class="btn btn-outline-warning btn-lg hero-btn me-3">
                            <i class="fas fa-file-invoice"></i> Free Quotation
                        </a>
                        <a href="#contact" class="btn btn-outline-light btn-lg hero-btn">
                            <i class="fas fa-phone"></i> Contact Us
                        </a>
                    </div>
                    
                    <!-- Services Display -->
                    <div class="services-overlay">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h3>PLUMBING</h3>
                                <h4>SERVICES</h4>
                                <ul>
                                    <li>Residential Plumbing &amp; Repair</li>
                                    <li>Waste Line Installation</li>
                                    <li>Water Line Installation</li>
                                    <li>Downspout &amp; Sewer Line Installation</li>
                                    <li>Transfer &amp; Jockey Pump Installation</li>
                                    <li>Plumbing Fixtures &amp; Accessories Installation</li>
                                    <li>Fire Sprinkler System Installation</li>
                                </ul>
                            </div>
                            <div class="col-md-6 mb-4">
                                <h3>CONSTRUCTION</h3>
                                <h4>SERVICES</h4>
                                <ul>
                                    <li>New Home &amp; Commercial Building &amp; Renovation</li>
                                    <li>Masonry Works</li>
                                    <li>Carpentry</li>
                                    <li>Finishing Works</li>
                                    <li>Tile Installation</li>
                                    <li>Steel Works</li>
                                    <li>New &amp; Renovation Paint Works</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Video Ad Section -->
    <section class="video-ad-section section-padding">
        <div class="container">
            <div class="text-center mb-4">
                <h2>WRPlumb In Action</h2>
                <p class="lead text-muted mb-0">Watch our featured service ad and see our quality work standards.</p>
            </div>
            <div class="video-ad-grid">
                <div class="video-ad-wrap">
                    <video
                        class="video-ad-media video-ad-media-plumbing video-ad-clip"
                        autoplay
                        muted
                        loop
                        playsinline
                        preload="metadata"
                        poster="image/294539416_407599744767669_1937739510480713048_n.jpg"
                        aria-label="Plumbing and construction promotional video"
                        data-clip-start="0"
                        data-clip-end="18"
                    >
                        <source src="image/Plumbing%20Video%20Template%20%28Editable%29.mp4" type="video/mp4">
                        <track kind="captions" srclang="en" label="English captions" src="data:text/vtt,WEBVTT%0A">
                        Your browser does not support the video tag.
                    </video>
                    <div class="video-ad-overlay">
                        <div class="video-ad-badge">
                            <img src="image/294539416_407599744767669_1937739510480713048_n.jpg" alt="WR Logo">
                        </div>
                        <div class="video-ad-copy">
                            <h4 class="mb-2">Reliable Plumbing &amp; Construction</h4>
                            <p class="mb-0">Professional team, quality output, and trusted service for every project.</p>
                        </div>
                    </div>
                </div>

                <div class="video-ad-wrap">
                    <video
                        class="video-ad-media video-ad-media-construction video-ad-clip"
                        autoplay
                        muted
                        loop
                        playsinline
                        preload="metadata"
                        poster="image/294539416_407599744767669_1937739510480713048_n.jpg"
                        aria-label="Construction works promotional video"
                        data-clip-start="6"
                        data-clip-end="28"
                    >
                        <source src="image/The%20Power%20and%20Beauty%20of%20Construction%20Sites%EF%BC%9A%20A%20Cinematic%20Reel.mp4" type="video/mp4">
                        <track kind="captions" srclang="en" label="English captions" src="data:text/vtt,WEBVTT%0A">
                        Your browser does not support the video tag.
                    </video>
                    <div class="video-ad-overlay">
                        <div class="video-ad-badge">
                            <img src="image/294539416_407599744767669_1937739510480713048_n.jpg" alt="WR Logo">
                        </div>
                        <div class="video-ad-copy">
                            <h4 class="mb-2">Built Strong, Delivered Right</h4>
                            <p class="mb-0">WRPlumb Construction Services with trusted workmanship, durable builds, and on-time project delivery.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <h2 id="stat-completed-projects">0</h2>
                        <p class="text-muted">Completed Projects</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <h2 id="stat-happy-clients">0</h2>
                        <p class="text-muted">Happy Clients</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <h2 id="stat-services-completed">0</h2>
                        <p class="text-muted">Services Completed</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <h2 id="stat-active-projects">0</h2>
                        <p class="text-muted">Active Projects</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Process Timeline Section -->
    <section id="process" class="process-section section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Application Process</h2>
                <p class="lead text-muted mb-0">How clients use the portal from login to payment</p>
            </div>

            <div class="process-timeline">
                <div class="process-item reveal">
                    <div class="process-marker">1</div>
                    <div class="process-content">
                        <h4>Login to Your Client Portal</h4>
                        <p>Create an account or sign in to access your dashboard and service features.</p>
                    </div>
                </div>
                <div class="process-item reveal">
                    <div class="process-marker">2</div>
                    <div class="process-content">
                        <h4>Submit a Service Request</h4>
                        <p>Choose service type, enter your concern, and provide your service address.</p>
                    </div>
                </div>
                <div class="process-item reveal">
                    <div class="process-marker">3</div>
                    <div class="process-content">
                        <h4>Upload Supporting Photos (Optional)</h4>
                        <p>Add pictures or files so your request can be reviewed more accurately.</p>
                    </div>
                </div>
                <div class="process-item reveal">
                    <div class="process-marker">4</div>
                    <div class="process-content">
                        <h4>Track Request Status in Real-Time</h4>
                        <p>Monitor updates directly from your portal without needing follow-up calls.</p>
                    </div>
                </div>
                <div class="process-item reveal">
                    <div class="process-marker">5</div>
                    <div class="process-content">
                        <h4>Receive Quotation in Portal</h4>
                        <p>View your quotation details, costs, and notes in one place.</p>
                    </div>
                </div>
                <div class="process-item reveal">
                    <div class="process-marker">6</div>
                    <div class="process-content">
                        <h4>View Job Order and Schedule</h4>
                        <p>Check approved job details and service schedule from your client account.</p>
                    </div>
                </div>
                <div class="process-item reveal">
                    <div class="process-marker">7</div>
                    <div class="process-content">
                        <h4>Receive Invoice and Billing Summary</h4>
                        <p>See your billing breakdown, due amounts, and payment status updates online.</p>
                    </div>
                </div>
                <div class="process-item reveal">
                    <div class="process-marker">8</div>
                    <div class="process-content">
                        <h4>Submit Payment and Confirm Completion</h4>
                        <p>Record your payment and track confirmation through the portal notifications.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
<section id="about" class="section-padding about-clean-section">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            
            <!-- LEFT: About -->
            <div class="col-lg-6">
                <div class="card about-clean-card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <span class="about-kicker">About WRPlumb</span>
                        <h2 class="about-title mt-2 mb-3">Your Trusted Local Plumbing & Construction Partner</h2>

                        <div class="about-badges mb-4">
                            <span class="badge-chip">
                                <i class="fas fa-check-circle"></i> Licensed & Insured
                            </span>
                            <span class="badge-chip">
                                <i class="fas fa-bolt"></i> Fast Response
                            </span>
                            <span class="badge-chip">
                                <i class="fas fa-tags"></i> Transparent Pricing
                            </span>
                        </div>

                        <p class="about-lead">
                            We are a local plumbing and construction service company in Cagayan de Oro City
                            committed to delivering reliable and cost-effective service.
                        </p>

                        <p class="about-text">
                            WRPlumb provides residential and commercial plumbing and construction solutions
                            with a strong focus on workmanship, professionalism, and customer satisfaction.
                        </p>

                        <p class="about-text mb-4">
                            Our experienced and certified team works to ensure every project is completed
                            efficiently, safely, and to a high standard.
                        </p>

                        <div class="row g-3 about-info-grid">
                            <div class="col-sm-6">
                                <div class="info-box h-100">
                                    <div class="info-icon blue"><i class="fas fa-map-marker-alt"></i></div>
                                    <h6>Our Location</h6>
                                    <p>139 Upper Zone 4 Bulua<br>Cagayan de Oro, Philippines</p>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="info-box h-100">
                                    <div class="info-icon green"><i class="fas fa-clock"></i></div>
                                    <h6>Open Now</h6>
                                    <p>We're currently open and ready to serve you.</p>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="info-box h-100">
                                    <div class="info-icon gold"><i class="fas fa-tag"></i></div>
                                    <h6>Price Range</h6>
                                    <p>Moderate pricing with quality service at fair rates.</p>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="info-box h-100">
                                    <div class="info-icon gray"><i class="fas fa-building"></i></div>
                                    <h6>Service Coverage</h6>
                                    <p>Residential and commercial plumbing & construction.</p>
                                </div>
                            </div>
                        </div>

                        <div class="about-actions mt-4">
                            <a href="#free-quotation" class="btn btn-warning me-2 mb-2">
                                <i class="fas fa-file-invoice"></i> Free Quotation
                            </a>
                            <a href="#services" class="btn btn-outline-primary mb-2">
                                <i class="fas fa-tools"></i> View Services
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Why Choose Us -->
            <div class="col-lg-6">
                <div class="card about-clean-card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
                            <h3 class="mb-0 why-title">Why Choose Us?</h3>
                            <span class="why-badge-clean">
                                <i class="fas fa-star"></i> Trusted Local Team
                            </span>
                        </div>

                        <div class="why-list-clean">
                            <div class="why-item-clean">
                                <div class="why-icon-clean"><i class="fas fa-user-check"></i></div>
                                <div>
                                    <h6>Experienced Team</h6>
                                    <p>Years of expertise in plumbing solutions and construction work.</p>
                                </div>
                            </div>

                            <div class="why-item-clean">
                                <div class="why-icon-clean"><i class="fas fa-award"></i></div>
                                <div>
                                    <h6>Quality Service</h6>
                                    <p>We guarantee satisfaction and maintain high work standards.</p>
                                </div>
                            </div>

                            <div class="why-item-clean">
                                <div class="why-icon-clean"><i class="fas fa-bolt"></i></div>
                                <div>
                                    <h6>24/7 Availability</h6>
                                    <p>Emergency services are available when you need immediate help.</p>
                                </div>
                            </div>

                            <div class="why-item-clean">
                                <div class="why-icon-clean"><i class="fas fa-tags"></i></div>
                                <div>
                                    <h6>Affordable Pricing</h6>
                                    <p>Competitive rates without compromising quality.</p>
                                </div>
                            </div>

                            <div class="why-item-clean">
                                <div class="why-icon-clean"><i class="fas fa-shield-alt"></i></div>
                                <div>
                                    <h6>Licensed & Insured</h6>
                                    <p>Fully certified and protected for your peace of mind.</p>
                                </div>
                            </div>

                            <div class="why-item-clean">
                                <div class="why-icon-clean"><i class="fas fa-hand-holding-heart"></i></div>
                                <div>
                                    <h6>Work Warranty</h6>
                                    <p>We stand behind our work with dependable service support.</p>
                                </div>
                            </div>
                        </div>

                        <div class="why-footer-clean mt-4">
                            <p class="mb-0">
                                Have questions?
                                <a href="#contact">Contact us</a> and we will respond quickly.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

    <!-- Services Section -->
    <section id="services" class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Our Services</h2>
                <p class="lead text-muted">Comprehensive plumbing &amp; construction solutions for your needs</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card service-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-tools fa-3x text-primary mb-3"></i>
                            <h5>Emergency Repairs</h5>
                            <p class="text-muted">24/7 emergency plumbing services for urgent issues like leaks, burst pipes, and clogs.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-faucet fa-3x text-primary mb-3"></i>
                            <h5>Installation Services</h5>
                            <p class="text-muted">Professional installation of fixtures, water heaters, pipes, and plumbing systems.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-wrench fa-3x text-primary mb-3"></i>
                            <h5>Maintenance &amp; Inspection</h5>
                            <p class="text-muted">Regular maintenance and inspections to keep your plumbing system in top condition.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-shower fa-3x text-primary mb-3"></i>
                            <h5>Bathroom Remodeling</h5>
                            <p class="text-muted">Complete bathroom plumbing solutions for renovations and upgrades.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-sink fa-3x text-primary mb-3"></i>
                            <h5>Kitchen Plumbing</h5>
                            <p class="text-muted">Expert kitchen plumbing services including sink installation and garbage disposal.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card service-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-water fa-3x text-primary mb-3"></i>
                            <h5>Water System Services</h5>
                            <p class="text-muted">Water heater installation, repair, and water quality solutions.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plumbing / Construction: what we do -->
            <div class="row mt-4">
                <div class="col-lg-10 mx-auto">
                    <div class="card shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                <div>
                                    <h3 class="mb-1">What We Do</h3>
                                    <div class="text-muted">Choose a category to see the specific works we offer.</div>
                                </div>
                                <ul class="nav nav-pills services-pills" id="serviceCategoryTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="plumbing-tab" data-bs-toggle="pill" data-bs-target="#plumbing-pane" type="button" role="tab" aria-controls="plumbing-pane" aria-selected="true">
                                            <i class="fas fa-faucet"></i> Plumbing
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="construction-tab" data-bs-toggle="pill" data-bs-target="#construction-pane" type="button" role="tab" aria-controls="construction-pane" aria-selected="false">
                                            <i class="fas fa-hard-hat"></i> Construction
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <div class="tab-content pt-2" id="serviceCategoryTabsContent">
                                <div class="tab-pane fade show active" id="plumbing-pane" role="tabpanel" aria-labelledby="plumbing-tab" tabindex="0">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="services-list-title">Plumbing Services</div>
                                            <ul class="list-group list-group-flush services-list">
                                                <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Residential Plumbing &amp; Repair</li>
                                                <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Waste Line Installation</li>
                                                <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Water Line Installation</li>
                                                <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Downspout &amp; Sewer Line Installation</li>
                                                <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Transfer &amp; Jockey Pump Installation</li>
                                                <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Plumbing Fixtures &amp; Accessories Installation</li>
                                                <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Fire Sprinkler System Installation</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="services-callout">
                                                <div class="d-flex align-items-start gap-3">
                                                    <div class="services-callout-icon">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">Need a quote fast?</div>
                                                        <div class="text-muted small">Submit a free quotation request and our team will contact you.</div>
                                                        <div class="mt-3">
                                                            <a href="#free-quotation" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-file-invoice"></i> Free Quotation
                                                            </a>
                                                            <a href="#contact" class="btn btn-outline-primary btn-sm ms-2">
                                                                <i class="fas fa-phone"></i> Contact
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="construction-pane" role="tabpanel" aria-labelledby="construction-tab" tabindex="0">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="services-list-title">Construction Services</div>
                                            <ul class="list-group list-group-flush services-list">
                                                <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>New Home &amp; Commercial Building &amp; Renovation</li>
                                                <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Masonry Works</li>
                                                <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Carpentry</li>
                                                <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Finishing Works</li>
                                                <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Tile Installation</li>
                                                <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Steel Works</li>
                                                <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>New &amp; Renovation Paint Works</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="services-callout">
                                                <div class="d-flex align-items-start gap-3">
                                                    <div class="services-callout-icon">
                                                        <i class="fas fa-clipboard-list"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">Planning a project?</div>
                                                        <div class="text-muted small">Tell us the scope and preferred date, and we will prepare an estimate.</div>
                                                        <div class="mt-3">
                                                            <a href="#free-quotation" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-file-invoice"></i> Free Quotation
                                                            </a>
                                                            <a href="#projects" class="btn btn-outline-primary btn-sm ms-2">
                                                                <i class="fas fa-project-diagram"></i> View Projects
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Service Types with Images -->
            <div class="mt-5">
                <div class="text-center mb-4">
                    <h3>Service Types You Can Request</h3>
                    <p class="text-muted">See what each service covers before you request a free quotation.</p>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm service-type-card">
                            <img src="image/technician.webp" class="card-img-top" alt="Maintenance Technician">
                            <div class="card-body">
                                <h5 class="card-title">Maintenance Technician</h5>
                                <p class="card-text text-muted">
                                    General maintenance for your home or building including minor repairs, inspections, and preventive checks to avoid bigger problems.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm service-type-card">
                            <img src="image/plumber.webp" class="card-img-top" alt="Plumber">
                            <div class="card-body">
                                <h5 class="card-title">Plumber</h5>
                                <p class="card-text text-muted">
                                    Plumbing repair and installation for leaks, clogged drains, water lines, waste lines, and bathroom/kitchen fixtures.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm service-type-card">
                            <img src="image/construction.webp" class="card-img-top" alt="Construction Worker">
                            <div class="card-body">
                                <h5 class="card-title">Construction Worker</h5>
                                <p class="card-text text-muted">
                                    Support for house and building construction, structural works, renovation, and general construction labor.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm service-type-card">
                            <img src="image/painter.webp" class="card-img-top" alt="Painter">
                            <div class="card-body">
                                <h5 class="card-title">Painter</h5>
                                <p class="card-text text-muted">
                                    Interior and exterior painting, repainting, and finishing works to refresh and protect your property.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm service-type-card">
                            <img src="image/weilder.webp" class="card-img-top" alt="Welder">
                            <div class="card-body">
                                <h5 class="card-title">Welder</h5>
                                <p class="card-text text-muted">
                                    Steel works for gates, railings, frames, and other metal fabrication and repair needs.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm service-type-card">
                            <img src="image/mason.webp" class="card-img-top" alt="Mason">
                            <div class="card-body">
                                <h5 class="card-title">Mason</h5>
                                <p class="card-text text-muted">
                                    Masonry works such as hollow block laying, plastering, concrete works, and tiling for floors and walls.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm service-type-card">
                            <img src="image/handy.webp" class="card-img-top" alt="Repair Man">
                            <div class="card-body">
                                <h5 class="card-title">Repair Man (Construction &amp; Plumbing)</h5>
                                <p class="card-text text-muted">
                                    Small repair jobs for both plumbing and construction such as fixture replacement, minor leaks, and small structural fixes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Free Quotation Section -->
    <section id="free-quotation" class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Request a Free Quotation</h2>
                <p class="lead text-muted">
                    Fill out this form and our team will contact you with a free quotation based on the service type you choose.
                </p>
            </div>
            <div class="row g-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h4 class="mb-3"><i class="fas fa-file-invoice-dollar text-warning"></i> Free Quotation Form</h4>
                            <div id="fq-error" class="alert alert-danger d-none" role="alert"></div>
                            <div id="fq-success" class="alert alert-success d-none" role="alert"></div>
                            @if (session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        Failed to submit request. Please check the required fields.
    </div>
@endif
                            <form id="free-quotation-form" action="{{ route('free-quotation.store') }}" method="POST" enctype="multipart/form-data">
                                     @csrf
                                <div class="row g-3">
                                    <div class="col-lg-4 col-md-5">
                                        <label for="fq-first-name" class="form-label">First Name</label>
                                        <input type="text" id="fq-first-name" name="first_name" class="form-control" placeholder="Your first name" required>
                                    </div>
                                    <div class="col-lg-2 col-md-2 fq-mi-col">
                                        <label for="fq-middle-initial" class="form-label">M.I. <small class="text-muted">(Optional)</small></label>
                                        <input type="text" id="fq-middle-initial" name="middle_initial" class="form-control" maxlength="1" placeholder="M">
                                        <input type="hidden" id="fq-name" name="name" value="">
                                    </div>
                                    <div class="col-lg-6 col-md-5">
                                        <label for="fq-last-name" class="form-label">Last Name</label>
                                        <input type="text" id="fq-last-name" name="last_name" class="form-control" placeholder="Your last name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fq-email" class="form-label">Email</label>
                                        <input type="email" id="fq-email" name="email" class="form-control" placeholder="you@example.com" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fq-phone" class="form-label">Contact Number</label>
                                        <input type="text" id="fq-phone" name="phone" class="form-control" placeholder="09XXXXXXXXX" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fq-service-category" class="form-label">Service Category</label>
                                        <select id="fq-service-category" name="service_category" class="form-select" required>
                                            <option value="" selected disabled>Select a category</option>
                                            <option value="plumbing">Plumbing</option>
                                            <option value="construction">Construction</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fq-service-type" class="form-label">Service Type</label>
                                        <select id="fq-service-type" name="service_type" class="form-select" required>
                                            <option value="" selected disabled>Select a service</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fq-project-type" class="form-label">Project Type</label>
                                        <select id="fq-project-type" name="project_type" class="form-select" required>
                                            <option value="" selected disabled>Select project type</option>
                                            <option value="Residential">Residential</option>
                                            <option value="Commercial">Commercial</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fq-preferred-date" class="form-label">Preferred Service Date</label>
                                        <input type="date" id="fq-preferred-date" name="preferred_date" class="form-control">
                                    </div>
                                    <div class="col-md-12">
                                        <label for="fq-address" class="form-label">Service Address</label>
                                        <div class="address-autocomplete">
                                        <div class="input-group">
                                            <input
                                                type="text"
                                                id="fq-address"
                                                name="address"
                                                class="form-control"
                                                placeholder="Enter address (e.g., Barra, Opol)"
                                                autocomplete="street-address"
                                                required
                                            >
                                            <a
                                                id="fq-address-map"
                                                class="btn btn-outline-secondary disabled"
                                                href="#"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                aria-disabled="true"
                                                tabindex="-1"
                                                title="Open the typed address in Google Maps"
                                            >
                                                <i class="fas fa-map-marked-alt"></i>
                                            </a>
                                        </div>
                                        <div id="fq-address-suggestions" class="address-suggestions d-none"></div>
                                        <input type="hidden" id="fq-address-lat" name="address_lat" value="">
                                        <input type="hidden" id="fq-address-lon" name="address_lon" value="">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="fq-details" class="form-label">Details of the Problem / Work Needed</label>
                                        <textarea id="fq-details" name="details" rows="4" class="form-control" placeholder="Describe your plumbing or construction concern so we can estimate properly." required></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="fq-attachments" class="form-label">Attach Photos / Videos (Optional)</label>
                                        <input
                                            type="file"
                                            id="fq-attachments"
                                            name="attachments[]"
                                            class="form-control"
                                            accept="image/*,video/*"
                                            multiple
                                        >
                                        <div class="form-text">
                                            Upload photos/videos so we can confirm the problem and prepare the right tools/materials before dispatch.
                                            Include a close-up of the issue and a wider view of the area if possible.
                                            After review, we may contact you to schedule an inspector visit.
                                            Max 25MB per file.
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="fq-consent" required>
                                            <label class="form-check-label" for="fq-consent">
                                                I agree that WRPlumb may contact me via phone or email regarding this free quotation request.
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn-warning w-100">
                                            <i class="fas fa-paper-plane"></i> Submit Free Quotation Request
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Projects Section -->
    <section id="projects" class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Featured Projects</h2>
                <p class="lead text-muted">Some of our best completed plumbing and construction services</p>
            </div>
            <div id="projects-container">
                <div class="row">
                    <!-- Sample Project 1 -->
                    <div class="col-md-4 mb-4">
                        <div class="card project-card h-100 shadow-sm">
                            <img src="image/projects/plumbing-highrise.webp" class="card-img-top" alt="High-Rise Building Plumbing Installation">
                            <div class="card-body">
                                <h5 class="card-title">High-Rise Plumbing Installation</h5>
                                <p class="card-text text-muted">
                                    Complete water and waste line installation for a multi-storey residential building in Cagayan de Oro,
                                    including fire sprinkler and booster pump systems.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Project 2 -->
                    <div class="col-md-4 mb-4">
                        <div class="card project-card h-100 shadow-sm">
                            <img src="image/projects/house-renovation.webp" class="card-img-top" alt="Residential House Renovation">
                            <div class="card-body">
                                <h5 class="card-title">Residential House Renovation</h5>
                                <p class="card-text text-muted">
                                    Full renovation of a 2-storey house including new plumbing layout, tile installation,
                                    masonry works, and interior/exterior repainting.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Project 3 -->
                    <div class="col-md-4 mb-4">
                        <div class="card project-card h-100 shadow-sm">
                            <img src="image/projects/commercial-kitchen.webp" class="card-img-top" alt="Commercial Kitchen Re-Piping">
                            <div class="card-body">
                                <h5 class="card-title">Commercial Kitchen Re-Piping</h5>
                                <p class="card-text text-muted">
                                    Upgrade of an industrial kitchen plumbing system with new stainless supply lines,
                                    grease trap installation, and drainage improvements to meet safety standards.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <p class="text-muted small mb-0">
                        These are sample projects. Your actual completed projects from the system can be displayed here later.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Get In Touch</h2>
                <p class="lead text-muted">We're here to help with all your plumbing needs</p>
            </div>
            <div class="row contact-grid">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 equal-card contact-card">
                        <div class="card-body p-4 d-flex flex-column">
                            <h4 class="mb-4">Contact Information</h4>
                            <div class="contact-list flex-grow-1">
                                <div class="mb-3">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    <strong>Address:</strong><br>
                                    139 Upper Zone 4 Bulua<br>
                                    Cagayan de Oro, Philippines
                                </div>
                                <div class="mb-3">
                                    <i class="fas fa-phone text-primary me-2"></i>
                                    <strong>Phone:</strong><br>
                                    <a href="tel:+63888505197" class="text-decoration-none">(088) 850 5197</a>
                                </div>
                                <div class="mb-3">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    <strong>Email:</strong><br>
                                    <a href="mailto:wrplumbingcon@gmail.com" class="text-decoration-none">wrplumbingcon@gmail.com</a>
                                </div>
                                <div class="mb-3">
                                    <i class="fas fa-clock text-success me-2"></i>
                                    <strong>Status:</strong><br>
                                    <span class="badge bg-success">Open Now</span> - We're ready to serve you!
                                </div>
                                <div class="mb-3">
                                    <i class="fas fa-tag text-warning me-2"></i>
                                    <strong>Price Range:</strong><br>
                                    <span class="badge bg-warning text-dark">Moderate Pricing</span> - Quality service at fair rates
                                </div>
                                <div class="mb-3">
                                    <i class="fas fa-info-circle text-primary me-2"></i>
                                    <strong>About Us:</strong><br>
                                    Local plumbing and construction service company in Cagayan de Oro City that constantly aims to deliver reliable &amp; cost effective service.
                                </div>
                            </div>

                            <div class="contact-actions pt-3 border-top">
                                <a
                                    class="btn btn-outline-primary btn-sm"
                                    href="https://www.google.com/maps?q=139%20Upper%20Zone%204%20Bulua%2C%20Cagayan%20de%20Oro%2C%20Philippines"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <i class="fas fa-location-arrow"></i> Get Directions
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 equal-card reviews-card">
                        <div class="card-body p-4 d-flex flex-column">
                            <h4 class="mb-4"><i class="fas fa-star text-warning"></i> Customer Reviews</h4>

                            <div class="reviews-list flex-grow-1">
                            
                            <!-- Review 1 -->
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-2">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                    </div>
                                    <strong>Lore</strong>
                                </div>
                                <p class="text-muted small mb-0">"Excellent service! WRPlumb fixed our plumbing issues quickly and professionally. Highly recommended!"</p>
                                <small class="text-muted"><i class="fas fa-calendar"></i> 2 weeks ago</small>
                            </div>
                            
                            <!-- Review 2 -->
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-2">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star-half-alt text-warning"></i>
                                    </div>
                                    <strong>Tamayo</strong>
                                </div>
                                <p class="text-muted small mb-0">"Great construction work on our renovation project. Quality materials and on-time completion. Very satisfied!"</p>
                                <small class="text-muted"><i class="fas fa-calendar"></i> 1 month ago</small>
                            </div>
                            
                            <!-- Review 3 -->
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-2">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                    </div>
                                    <strong>Noel Amber</strong>
                                </div>
                                <p class="text-muted small mb-0">"Reliable and cost-effective service. The team was professional and cleaned up after the work. Will definitely hire again!"</p>
                                <small class="text-muted"><i class="fas fa-calendar"></i> 3 weeks ago</small>
                            </div>
                            
                            <!-- Review 4 -->
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-2">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                    </div>
                                    <strong>Clint Denzel</strong>
                                </div>
                                <p class="text-muted small mb-0">"Professional and reliable service. They installed our new water heater perfectly. Will definitely call them again for future needs."</p>
                                <small class="text-muted"><i class="fas fa-calendar"></i> 2 months ago</small>
                            </div>
                            
                            <!-- Review 5 -->
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-2">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star-half-alt text-warning"></i>
                                    </div>
                                    <strong>Clark</strong>
                                </div>
                                <p class="text-muted small mb-0">"Great experience with WRPlumb. They were prompt, courteous, and fixed our drainage issue efficiently. Highly recommend their services."</p>
                                <small class="text-muted"><i class="fas fa-calendar"></i> 1 month ago</small>
                            </div>
                            
                            <!-- Review 6 -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-2">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                    </div>
                                    <strong>Jehv</strong>
                                </div>
                                <p class="text-muted small mb-0">"The best plumbing service in Cagayan de Oro! They handled a complex pipe replacement with ease and professionalism. Very satisfied!"</p>
                                <small class="text-muted"><i class="fas fa-calendar"></i> 1 month ago</small>
                            </div>
                            
                            <hr>
                            
                            <!-- Additional Information -->
                            <div class="mt-3">
                                <h6 class="mb-3"><i class="fas fa-info-circle text-primary"></i> Why Choose WRPlumb?</h6>
                                <ul class="list-unstyled small">
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Licensed &amp; Insured Professionals</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> 24/7 Emergency Service Available</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Free Estimates &amp; Consultations</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Warranty on All Work</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Competitive &amp; Transparent Pricing</li>
                                </ul>
                            </div>
                            
                            <hr>
                            </div>

                            <!-- Quick Links (fixed at bottom for consistent card height) -->
                            <div class="reviews-actions pt-3 border-top text-center">
                                <a href="client/register.html" class="btn btn-primary btn-sm me-2">
                                    <i class="fas fa-user-plus"></i> Register Now
                                </a>
                                <a href="client/index.html" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-sign-in-alt"></i> Client Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="row mt-2">
                <div class="col-12">
                    <div class="card map-card">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <h4 class="mb-0"><i class="fas fa-map-marked-alt text-primary"></i> Find Us</h4>
                                <div class="small text-muted">
                                    139 Upper Zone 4 Bulua, Cagayan de Oro, Philippines
                                </div>
                            </div>

                            <div class="map-embed">
                                <iframe
                                    title="WRPlumb Location Map"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    allow="fullscreen"
                                    sandbox="allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox"
                                    src="https://www.google.com/maps?q=139%20Upper%20Zone%204%20Bulua%2C%20Cagayan%20de%20Oro%2C%20Philippines&amp;output=embed"
                                ></iframe>
                            </div>
                            <div class="mt-3 d-flex flex-wrap gap-2">
                                <a
                                    class="btn btn-primary btn-sm"
                                    href="https://www.google.com/maps?q=139%20Upper%20Zone%204%20Bulua%2C%20Cagayan%20de%20Oro%2C%20Philippines"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <i class="fas fa-directions"></i> Open in Google Maps
                                </a>
                                <span class="small text-muted align-self-center">
                                    Tip: You can zoom and switch to satellite view.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-wrench"></i> WRPlumb</h5>
                    <p class="mb-0">Professional Plumbing Services</p>
                    <p class="text-muted small">&copy; <span id="current-year"></span> WRPlumb. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-1">139 Upper Zone 4 Bulua, Cagayan de Oro, Philippines</p>
                    <p class="mb-1">Phone: <a href="tel:+63888505197" class="text-white text-decoration-none">(088) 850 5197</a> | Email: <a href="mailto:wrplumbingcon@gmail.com" class="text-white text-decoration-none">wrplumbingcon@gmail.com</a></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content register-modal-content border-0">
            <div class="modal-header register-modal-header border-0">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}" alt="WRPlumb Logo" class="register-modal-logo">
                    <div>
                        <h4 class="modal-title mb-1" id="registerModalLabel">Create Your Account</h4>
                        <p class="mb-0 text-muted small">Join WRPlumb and start booking services today</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 p-lg-5">
                <form id="register-form" method="POST" action="{{ route('register.store') }}">
                    @csrf
                    <input type="hidden" name="form_type" value="register">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="middle_initial" class="form-label">M.I.</label>
                            <input type="text" class="form-control" id="middle_initial" name="middle_initial" maxlength="1">
                        </div>

                        <div class="col-md-5 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>

                    <div class="mb-4">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 register-submit-btn">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
    
    <!-- Custom JavaScript -->
    <script src="assets/js/home.js?v=20260225b"></script>
</body>
</html>
