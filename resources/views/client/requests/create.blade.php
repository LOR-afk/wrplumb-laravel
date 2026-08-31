@extends('client.layouts.app')

@section('title', 'Book a Service')
@section('topbar_title', 'Book a Service')
@section('topbar_subtitle', 'Submit a new service request for review and scheduling.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/client-req-form.css') }}?v=20260818a">
@endpush

@section('content')
<div class="client-request-page">
    <section class="client-request-intro">
        <div>
            <span class="client-request-kicker">New Service Request</span>
            <h2>Tell us what you need help with.</h2>
            <p>Provide the service, preferred schedule, location, and problem details. You can review everything before submitting.</p>
        </div>

        <div class="client-request-intro-badge">
            <i class="fas fa-clock"></i>
            <div>
                <strong>Admin review</strong>
                <span>Scheduling follows after review</span>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('client.requests.store') }}" class="client-request-form">
        @csrf

        <section class="request-form-card">
            <div class="request-form-section-head">
                <span class="request-section-icon blue"><i class="fas fa-screwdriver-wrench"></i></span>
                <div>
                    <h3>Service Details</h3>
                    <p>Choose the service category and project type.</p>
                </div>
            </div>

            <div class="request-form-grid three">
                <div class="request-field">
                    <label for="service_category">Service Category</label>
                    <select name="service_category" id="service_category" class="form-select" required>
                        <option value="">Select category</option>
                        <option value="Plumbing" @selected(old('service_category') === 'Plumbing')>Plumbing</option>
                        <option value="Construction" @selected(old('service_category') === 'Construction')>Construction</option>
                    </select>
                    @error('service_category')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="request-field">
                    <label for="service_type">Service Type</label>
                    <select name="service_type" id="service_type" class="form-select" required>
                        <option value="">Select a service</option>
                    </select>
                    @error('service_type')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="request-field">
                    <label for="project_type">Project Type</label>
                    <select name="project_type" id="project_type" class="form-select" required>
                        <option value="">Select project type</option>
                        <option value="Residential" @selected(old('project_type') === 'Residential')>Residential</option>
                        <option value="Commercial" @selected(old('project_type') === 'Commercial')>Commercial</option>
                    </select>
                    @error('project_type')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
        </section>

        <section class="request-form-card">
            <div class="request-form-section-head">
                <span class="request-section-icon violet"><i class="fas fa-calendar-days"></i></span>
                <div>
                    <h3>Preferred Schedule</h3>
                    <p>Choose your preferred date and time. Final availability is confirmed after review.</p>
                </div>
            </div>

            <div class="request-form-grid two">
                <div class="request-field">
                    <label for="preferred_date">Preferred Date</label>
                    <input
                        type="date"
                        id="preferred_date"
                        name="preferred_date"
                        class="form-control"
                        value="{{ old('preferred_date') }}"
                    >
                    @error('preferred_date')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="request-field">
                    <label for="preferred_time">Preferred Time</label>
                    <input
                        type="time"
                        id="preferred_time"
                        name="preferred_time"
                        class="form-control"
                        value="{{ old('preferred_time') }}"
                    >
                    @error('preferred_time')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
        </section>

        <section class="request-form-card">
            <div class="request-form-section-head">
                <span class="request-section-icon green"><i class="fas fa-location-dot"></i></span>
                <div>
                    <h3>Service Location</h3>
                    <p>Enter the service address and optionally pin your exact current location.</p>
                </div>
            </div>

            <div class="request-field">
                <label for="address">Service Address</label>
                <input
                    type="text"
                    id="address"
                    name="address"
                    class="form-control"
                    value="{{ old('address') }}"
                    placeholder="House no., street, barangay, city"
                    required
                >
                @error('address')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="request-location-box">
                <div class="request-location-icon">
                    <i class="fas fa-location-crosshairs"></i>
                </div>

                <div class="request-location-copy">
                    <strong>Pin your exact service location</strong>
                    <p>Use this while you are physically at the service address. This helps the team locate the site accurately.</p>
                    <span id="locationStatus" class="request-location-status">
                        No exact location captured yet.
                    </span>
                </div>

                <button type="button" class="btn request-location-btn" id="captureLocationButton">
                    <i class="fas fa-crosshairs"></i>
                    Use Current Location
                </button>
            </div>

            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

            @error('latitude')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            @error('longitude')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
        </section>

        <section class="request-form-card">
            <div class="request-form-section-head">
                <span class="request-section-icon orange"><i class="fas fa-message"></i></span>
                <div>
                    <h3>Problem Details</h3>
                    <p>Describe the issue clearly so the team can prepare before reviewing your request.</p>
                </div>
            </div>

            <div class="request-field">
                <label for="details">What needs to be done?</label>
                <textarea
                    id="details"
                    name="details"
                    class="form-control request-details"
                    rows="6"
                    placeholder="Example: There is a leaking pipe under the kitchen sink. Water starts dripping when the faucet is used..."
                    required
                >{{ old('details') }}</textarea>
                @error('details')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </section>

        <section class="request-form-actions">
            <a href="{{ route('client.requests.index') }}" class="btn request-action-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to My Requests
            </a>

            <button type="submit" class="btn request-action-primary">
                <i class="fas fa-paper-plane"></i>
                Submit Service Request
            </button>
        </section>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const serviceCategory = document.getElementById('service_category');
    const serviceType = document.getElementById('service_type');
    const oldServiceType = @json(old('service_type'));

    const serviceOptions = {
        plumbing: [
            'Leak Repair',
            'Clogged Drain',
            'Toilet Repair',
            'Sink/Faucet Repair',
            'Pipe Replacement',
            'Low Water Pressure',
            'Waste Line Installation',
            'Water Line Installation',
            'Downspout & Sewer Line Installation',
            'Transfer & Jockey Pump Installation',
            'Plumbing Fixtures & Accessories Installation',
            'Fire Sprinkler System Installation'
        ],
        construction: [
            'New Home & Commercial Building & Renovation',
            'Masonry Works',
            'Carpentry',
            'Finishing Works',
            'Tile Installation',
            'Steel Works',
            'New & Renovation Paint Works'
        ]
    };

    function populateServiceTypes() {
        if (!serviceCategory || !serviceType) return;

        const selectedCategory = serviceCategory.value.trim().toLowerCase();
        const options = serviceOptions[selectedCategory] || [];

        serviceType.innerHTML = '<option value="" selected disabled>Select a service</option>';

        options.forEach(function (service) {
            const option = document.createElement('option');
            option.value = service;
            option.textContent = service;

            if (oldServiceType === service) {
                option.selected = true;
            }

            serviceType.appendChild(option);
        });
    }

    populateServiceTypes();

    if (serviceCategory) {
        serviceCategory.addEventListener('change', populateServiceTypes);
    }

    const captureButton = document.getElementById('captureLocationButton');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const locationStatus = document.getElementById('locationStatus');
    const addressInput = document.getElementById('address');

    if (!captureButton) return;

    async function reverseGeocode(latitude, longitude) {
        const endpoint =
            'https://nominatim.openstreetmap.org/reverse' +
            '?format=jsonv2' +
            '&lat=' + encodeURIComponent(latitude) +
            '&lon=' + encodeURIComponent(longitude) +
            '&zoom=18' +
            '&addressdetails=1';

        const response = await fetch(endpoint, {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Unable to resolve address.');
        }

        return response.json();
    }

    captureButton.addEventListener('click', function () {
        if (!navigator.geolocation) {
            locationStatus.textContent = 'Location services are not supported by this browser.';
            locationStatus.className = 'request-location-status error';
            return;
        }

        captureButton.disabled = true;
        captureButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Getting Location';
        locationStatus.textContent = 'Finding your current location...';
        locationStatus.className = 'request-location-status';

        navigator.geolocation.getCurrentPosition(
            async function (position) {
                const latitude = position.coords.latitude.toFixed(7);
                const longitude = position.coords.longitude.toFixed(7);

                latitudeInput.value = latitude;
                longitudeInput.value = longitude;

                locationStatus.textContent = 'Location captured. Finding the service address...';

                try {
                    const result = await reverseGeocode(latitude, longitude);

                    if (result && result.display_name && addressInput) {
                        addressInput.value = result.display_name;
                        addressInput.classList.add('location-filled');

                        locationStatus.innerHTML =
                            '<i class="fas fa-circle-check"></i> ' +
                            'Location captured and Service Address updated.';
                    } else {
                        locationStatus.innerHTML =
                            '<i class="fas fa-circle-check"></i> ' +
                            'Exact location captured. Please confirm the Service Address.';
                    }

                    locationStatus.className = 'request-location-status success';
                    captureButton.innerHTML = '<i class="fas fa-check"></i> Location Captured';
                } catch (error) {
                    locationStatus.innerHTML =
                        '<i class="fas fa-circle-check"></i> ' +
                        'Exact location captured, but the street address could not be detected. ' +
                        'Please enter or confirm the Service Address manually.';

                    locationStatus.className = 'request-location-status success';
                    captureButton.innerHTML = '<i class="fas fa-check"></i> Location Captured';
                } finally {
                    captureButton.disabled = false;
                }
            },
            function (error) {
                let message = 'Unable to capture your current location.';

                if (error.code === error.PERMISSION_DENIED) {
                    message = 'Location permission was denied. Please allow location access.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    message = 'Your current location is unavailable.';
                } else if (error.code === error.TIMEOUT) {
                    message = 'Location request timed out. Please try again.';
                }

                locationStatus.textContent = message;
                locationStatus.className = 'request-location-status error';

                captureButton.disabled = false;
                captureButton.innerHTML = '<i class="fas fa-crosshairs"></i> Try Again';
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    });
});
</script>
@endsection