@extends('client.layouts.app')

@section('title', 'Book a Service')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Book a Service</h2>
    <p class="text-muted mb-0">Submit a new service request for admin review and scheduling.</p>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('client.requests.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Service Category</label>
                    <select name="service_category" id="service_category" class="form-select" required>                        
                        <option value="">Select category</option>
                        <option value="Plumbing" @selected(old('service_category') === 'Plumbing')>Plumbing</option>
                        <option value="Construction" @selected(old('service_category') === 'Construction')>Construction</option>
                    </select>
                    @error('service_category')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Service Type</label>
                    <select name="service_type" id="service_type" class="form-select" required>
                        <option value="">Select a service</option>
                    </select>
                    @error('service_type')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Project Type</label>
                    <select name="project_type" class="form-select" required>
                        <option value="">Select project type</option>
                        <option value="Residential" @selected(old('project_type') === 'Residential')>Residential</option>
                        <option value="Commercial" @selected(old('project_type') === 'Commercial')>Commercial</option>
                    </select>
                    @error('project_type')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Preferred Date</label>
                    <input
                        type="date"
                        name="preferred_date"
                        class="form-control"
                        value="{{ old('preferred_date') }}"
                    >
                    @error('preferred_date')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Preferred Time</label>
                    <input
                        type="time"
                        name="preferred_time"
                        class="form-control"
                        value="{{ old('preferred_time') }}"
                    >
                    @error('preferred_time')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Service Address</label>
                    <input
                        type="text"
                        name="address"
                        class="form-control"
                        value="{{ old('address') }}"
                        placeholder="Enter full service address"
                        required
                    >
                    @error('address')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Problem Details</label>
                    <textarea
                        name="details"
                        class="form-control"
                        rows="5"
                        placeholder="Describe the issue or service you need..."
                        required
                    >{{ old('details') }}</textarea>
                    @error('details')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <a href="{{ route('client.requests.index') }}" class="btn btn-outline-secondary">
                    Back to My Requests
                </a>
                <button type="submit" class="btn btn-primary">
                    Submit Service Request
                </button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const serviceCategory = document.getElementById('service_category');
    const serviceType = document.getElementById('service_type');

    const oldServiceType = @json(old('service_type'));

    const serviceOptions = {
        Plumbing: [
            'Residential Plumbing & Repair',
            'Waste Line Installation',
            'Water Line Installation',
            'Downspout & Sewer Line Installation',
            'Transfer & Jockey Pump Installation',
            'Plumbing Fixtures & Accessories Installation',
            'Fire Sprinkler System Installation'
        ],
        Construction: [
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
        const selectedCategory = serviceCategory.value;
        const options = serviceOptions[selectedCategory] || [];

        serviceType.innerHTML = '<option value="">Select a service</option>';

        options.forEach(function (item) {
            const option = document.createElement('option');
            option.value = item;
            option.textContent = item;

            if (oldServiceType && oldServiceType === item) {
                option.selected = true;
            }

            serviceType.appendChild(option);
        });
    }

    serviceCategory.addEventListener('change', function () {
        populateServiceTypes();
    });

    populateServiceTypes();
});
</script>
@endsection