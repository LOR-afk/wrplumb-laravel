@extends('client.layouts.app')

@section('title', 'Book a Service')
@section('topbar_title', 'Book a Service')
@section('topbar_subtitle', 'Submit a new service request for review and scheduling.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/client-req-form.css') }}?v=20260904-final">
@endpush

@section('content')
<div class="client-request-page">
    <section class="client-request-intro">
        <div>
            <span class="client-request-kicker">New Service Request</span>
            <h2>Tell us what you need help with.</h2>
            <p>Provide the service, preferred schedule, location, and problem details before submitting your request.</p>
        </div>

        <div class="client-request-intro-badge">
            <i class="fas fa-clock"></i>
            <div>
                <strong>Admin review</strong>
                <span>Scheduling follows after review</span>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('client.requests.store') }}" class="client-request-form" enctype="multipart/form-data">
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

            <div class="request-field service-address-field">
                <div class="service-address-label-row">
                    <label for="address">Service Address</label>

                    <button
                        type="button"
                        class="location-icon-button"
                        id="captureLocationButton"
                        title="Use current location"
                        aria-label="Use current location"
                    >
                        <i class="fas fa-location-crosshairs"></i>
                    </button>
                </div>

                <div class="service-address-input-wrap">
                    <input
                        type="text"
                        id="address"
                        name="address"
                        class="form-control"
                        value="{{ old('address') }}"
                        placeholder="House no., street, barangay, city"
                        autocomplete="off"
                        required
                    >

                    <div id="addressSuggestions" class="address-suggestions" role="listbox"></div>
                </div>

                <span id="locationStatus" class="request-location-status compact">
                    No exact location captured yet.
                </span>

                @error('address')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

            @error('latitude')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            @error('longitude')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
        </section>

        <section class="request-form-card">
            <div class="request-form-section-head problem-details-head">
                <span class="request-section-icon orange"><i class="fas fa-message"></i></span>
                <div>
                    <h3>Problem Details</h3>
                    <p>Describe the issue clearly so the team can prepare before reviewing your request.</p>
                </div>

                <button type="button" class="problem-image-button" id="addProblemImageButton" title="Add problem images">
                    <i class="fas fa-plus"></i>
                    Add Image
                </button>
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

            <input
                type="file"
                id="problemImages"
                name="problem_images[]"
                accept="image/jpeg,image/png,image/webp"
                multiple
                hidden
            >

            <div id="problemImagePreview" class="problem-image-preview"></div>

            <div class="problem-image-meta" id="problemImageMeta">
                <span><i class="fas fa-image"></i> Optional</span>
                <span id="problemImageCount">0/5 images</span>
            </div>

            @error('problem_images')
                <small class="text-danger problem-image-error">{{ $message }}</small>
            @enderror

            @error('problem_images.*')
                <small class="text-danger problem-image-error">{{ $message }}</small>
            @enderror
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


    const addProblemImageButton = document.getElementById('addProblemImageButton');
    const problemImagesInput = document.getElementById('problemImages');
    const problemImagePreview = document.getElementById('problemImagePreview');
    const problemImageCount = document.getElementById('problemImageCount');
    const problemImageMeta = document.getElementById('problemImageMeta');

    let selectedProblemFiles = [];

    function syncProblemImageInput() {
        if (!problemImagesInput) return;

        const transfer = new DataTransfer();

        selectedProblemFiles.forEach(function (file) {
            transfer.items.add(file);
        });

        problemImagesInput.files = transfer.files;
    }

    function renderProblemImagePreview() {
        if (!problemImagePreview) return;

        problemImagePreview.innerHTML = '';

        if (problemImageCount) {
            problemImageCount.textContent = selectedProblemFiles.length + '/5 images';
        }

        if (problemImageMeta) {
            problemImageMeta.classList.toggle('has-images', selectedProblemFiles.length > 0);
        }

        selectedProblemFiles.forEach(function (file, index) {
            const item = document.createElement('div');
            item.className = 'problem-image-preview-item';

            const image = document.createElement('img');
            image.alt = file.name;

            const reader = new FileReader();

            reader.onload = function (event) {
                image.src = event.target.result;
            };

            reader.readAsDataURL(file);

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'problem-image-remove';
            removeButton.setAttribute('aria-label', 'Remove image');
            removeButton.innerHTML = '<i class="fas fa-xmark"></i>';

            removeButton.addEventListener('click', function () {
                selectedProblemFiles.splice(index, 1);
                syncProblemImageInput();
                renderProblemImagePreview();
            });

            item.appendChild(image);
            item.appendChild(removeButton);
            problemImagePreview.appendChild(item);
        });
    }

    if (addProblemImageButton && problemImagesInput) {
        addProblemImageButton.addEventListener('click', function () {
            problemImagesInput.click();
        });

        problemImagesInput.addEventListener('change', function () {
            const files = Array.from(problemImagesInput.files || []);
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            const maxSize = 5 * 1024 * 1024;

            files.forEach(function (file) {
                if (!allowedTypes.includes(file.type)) {
                    return;
                }

                if (file.size > maxSize) {
                    return;
                }

                if (selectedProblemFiles.length >= 5) {
                    return;
                }

                const duplicate = selectedProblemFiles.some(function (selectedFile) {
                    return selectedFile.name === file.name &&
                        selectedFile.size === file.size &&
                        selectedFile.lastModified === file.lastModified;
                });

                if (!duplicate) {
                    selectedProblemFiles.push(file);
                }
            });

            syncProblemImageInput();
            renderProblemImagePreview();
        });
    }

    const captureButton = document.getElementById('captureLocationButton');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const locationStatus = document.getElementById('locationStatus');
    const addressInput = document.getElementById('address');
    const addressSuggestions = document.getElementById('addressSuggestions');

    let addressSearchTimer = null;
    let addressSearchController = null;

    function hideAddressSuggestions() {
        if (!addressSuggestions) return;
        addressSuggestions.classList.remove('show');
        addressSuggestions.innerHTML = '';
    }

    function renderAddressSuggestions(items) {
        if (!addressSuggestions) return;

        addressSuggestions.innerHTML = '';

        if (!items.length) {
            hideAddressSuggestions();
            return;
        }

        items.forEach(function (item) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'address-suggestion-item';
            button.setAttribute('role', 'option');

            const icon = document.createElement('span');
            icon.className = 'address-suggestion-icon';
            icon.innerHTML = '<i class="fas fa-location-dot"></i>';

            const copy = document.createElement('span');
            copy.className = 'address-suggestion-copy';

            const title = document.createElement('strong');
            const detail = document.createElement('small');

            const parts = item.display_name.split(',').map(function (part) {
                return part.trim();
            }).filter(Boolean);

            title.textContent = parts.slice(0, 2).join(', ');
            detail.textContent = parts.slice(2).join(', ');

            copy.appendChild(title);

            if (detail.textContent) {
                copy.appendChild(detail);
            }

            button.appendChild(icon);
            button.appendChild(copy);

            button.addEventListener('click', function () {
                addressInput.value = item.display_name;
                latitudeInput.value = item.lat;
                longitudeInput.value = item.lon;
                addressInput.classList.add('location-filled');

                locationStatus.innerHTML =
                    '<i class="fas fa-circle-check"></i> ' +
                    'Service Address selected and exact coordinates saved.';

                locationStatus.className =
                    'request-location-status compact success';

                captureButton.innerHTML = '<i class="fas fa-check"></i>';
                captureButton.classList.add('location-captured');
                captureButton.title = 'Location selected';

                hideAddressSuggestions();
            });

            addressSuggestions.appendChild(button);
        });

        addressSuggestions.classList.add('show');
    }

    async function searchAddresses(query) {
        if (!addressSuggestions) return;

        if (addressSearchController) {
            addressSearchController.abort();
        }

        addressSearchController = new AbortController();

        const endpoint =
            'https://nominatim.openstreetmap.org/search' +
            '?format=jsonv2' +
            '&addressdetails=1' +
            '&limit=5' +
            '&countrycodes=ph' +
            '&q=' + encodeURIComponent(query);

        addressSuggestions.innerHTML =
            '<div class="address-suggestions-loading">' +
            '<i class="fas fa-spinner fa-spin"></i>' +
            '<span>Searching locations...</span>' +
            '</div>';

        addressSuggestions.classList.add('show');

        try {
            const response = await fetch(endpoint, {
                headers: {
                    'Accept': 'application/json'
                },
                signal: addressSearchController.signal
            });

            if (!response.ok) {
                throw new Error('Unable to search addresses.');
            }

            const results = await response.json();

            renderAddressSuggestions(
                Array.isArray(results) ? results.slice(0, 5) : []
            );
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            hideAddressSuggestions();
        }
    }

    if (addressInput && addressSuggestions) {
        addressInput.addEventListener('input', function () {
            const query = addressInput.value.trim();

            clearTimeout(addressSearchTimer);

            if (query.length < 3) {
                hideAddressSuggestions();
                return;
            }

            addressSearchTimer = setTimeout(function () {
                searchAddresses(query);
            }, 450);
        });

        addressInput.addEventListener('focus', function () {
            if (addressSuggestions.children.length > 0) {
                addressSuggestions.classList.add('show');
            }
        });

        document.addEventListener('click', function (event) {
            if (
                !event.target.closest('.service-address-input-wrap') &&
                !event.target.closest('.location-icon-button')
            ) {
                hideAddressSuggestions();
            }
        });

        addressInput.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                hideAddressSuggestions();
            }
        });
    }

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
            locationStatus.className = 'request-location-status compact error';
            return;
        }

        captureButton.disabled = true;
        captureButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        captureButton.classList.remove('location-captured');
        captureButton.title = 'Getting current location';
        locationStatus.textContent = 'Finding your current location...';
        locationStatus.className = 'request-location-status compact loading';

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

                    locationStatus.className = 'request-location-status compact success';
                    captureButton.innerHTML = '<i class="fas fa-check"></i>';
                    captureButton.classList.add('location-captured');
                    captureButton.title = 'Location captured';
                } catch (error) {
                    locationStatus.innerHTML =
                        '<i class="fas fa-circle-check"></i> ' +
                        'Exact location captured, but the street address could not be detected. ' +
                        'Please enter or confirm the Service Address manually.';

                    locationStatus.className = 'request-location-status compact success';
                    captureButton.innerHTML = '<i class="fas fa-check"></i>';
                    captureButton.classList.add('location-captured');
                    captureButton.title = 'Location captured';
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
                locationStatus.className = 'request-location-status compact error';

                captureButton.disabled = false;
                captureButton.innerHTML = '<i class="fas fa-location-crosshairs"></i>';
                captureButton.classList.remove('location-captured');
                captureButton.title = 'Try current location again';
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