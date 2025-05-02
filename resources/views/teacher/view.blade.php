@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white">
                   
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div>
                            <h3 class="card-title text-white">
                                <i class="fas fa-users-cog text-white"></i> Teacher Details
                            </h3>
                        </div>
                        <div>
                            <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-list me-1"></i>View List
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            @if($teacher->profile_image)
                                <img src="{{ asset('storage/'.$teacher->profile_image) }}" 
                                     class="img-fluid rounded-circle shadow" 
                                     style="width: 200px; height: 200px; object-fit: cover; border: 5px solid #e9ecef;">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto" 
                                     style="width: 200px; height: 200px; border: 5px solid #e9ecef;">
                                    <i class="fas fa-user text-white" style="font-size: 80px;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h2 class="mb-3">{{ $teacher->name }}</h2>
                            <div class="d-flex mb-2">
                                <span class="badge bg-info text-dark me-2">
                                    <i class="fas fa-user-tag"></i> Teacher
                                </span>
                                @if($teacher->email_verified_at)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Verified
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-exclamation-circle"></i> Unverified
                                    </span>
                                @endif
                            </div>

                            <div class="mb-3">
                                @if($teacher->institute)
                                    <p class="mb-1">
                                        <i class="fas fa-university text-primary"></i> 
                                        <strong>Institute:</strong> {{ $teacher->institute->name }}
                                    </p>
                                @endif
                                
                                @if($teacher->admin)
                                    <p class="mb-1">
                                        <i class="fas fa-user-shield text-primary"></i> 
                                        <strong>Assigned Admin:</strong> {{ $teacher->admin->name }}
                                    </p>
                                @endif
                                
                                @if($teacher->designation)
                                    <p class="mb-1">
                                        <i class="fas fa-briefcase text-primary"></i> 
                                        <strong>Designation:</strong> {{ $teacher->designation }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4 border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-id-card text-primary"></i> Basic Information</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-envelope me-2 text-primary"></i> Email</span>
                                            <span>{{ $teacher->email }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-phone me-2 text-primary"></i> Phone</span>
                                            <span>{{ $teacher->phone ?? 'N/A' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-venus-mars me-2 text-primary"></i> Gender</span>
                                            <span>{{ ucfirst($teacher->gender) }}</span>
                                        </li>
                                        {{-- <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-birthday-cake me-2 text-primary"></i> Date of Birth</span>
                                            <span>{{ $teacher->dob->format('M d, Y') }}</span>
                                        </li> --}}
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-calendar-alt me-2 text-primary"></i> Created At</span>
                                            <span>{{ $teacher->created_at->format('M d, Y h:i A') }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4 border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-graduation-cap text-primary"></i> Professional Information</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-certificate me-2 text-primary"></i> Qualification</span>
                                            <span>{{ $teacher->qualification }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-clock me-2 text-primary"></i> Experience</span>
                                            <span>{{ $teacher->experience_years }} years</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-star me-2 text-primary"></i> Specialization</span>
                                            <span>{{ $teacher->specialization }}</span>
                                        </li>
                                        {{-- <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-calendar-check me-2 text-primary"></i> Joining Date</span>
                                            <span>{{ $teacher->joining_date->format('M d, Y') }}</span>
                                        </li> --}}
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-money-bill-wave me-2 text-primary"></i> Salary</span>
                                            <span>{{ number_format($teacher->salary, 2) }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4 border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-map-marker-alt text-primary"></i> Address</h5>
                                </div>
                                <div class="card-body">
                                    @if($teacher->address)
                                        <p class="card-text">{{ $teacher->address }}</p>
                                    @else
                                        <p class="card-text text-muted">No address provided</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4 border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-university text-primary"></i> Bank Details</h5>
                                </div>
                                <div class="card-body">
                                    @if($teacher->account_title || $teacher->account_number)
                                        <p class="mb-1"><strong>Account Title:</strong> {{ $teacher->account_title ?? 'N/A' }}</p>
                                        <p class="mb-0"><strong>Account Number:</strong> {{ $teacher->account_number ?? 'N/A' }}</p>
                                    @else
                                        <p class="card-text text-muted">No bank details provided</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn btn-primary me-2">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
    .card-header {
        border-bottom: none;
    }
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    .list-group-item:first-child {
        border-top: none;
    }
    .list-group-item:last-child {
        border-bottom: none;
    }
</style>
@endsection