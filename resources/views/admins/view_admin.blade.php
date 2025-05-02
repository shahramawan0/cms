@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div>
                            <h3 class="card-title text-white">
                                <i class="fas fa-users-cog text-white"></i> Admin Users Management
                            </h3>
                        </div>
                        <div>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-list me-1"></i>View List
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            @if($user->profile_image)
                                <img src="{{ asset('storage/'.$user->profile_image) }}" 
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
                            <h2 class="mb-3">{{ $user->name }}</h2>
                            <div class="d-flex mb-2">
                                <span class="badge bg-info text-dark me-2">
                                    <i class="fas fa-user-tag"></i> {{ $user->roles->first()->name ?? 'N/A' }}
                                </span>
                                @if($user->email_verified_at)
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
                                @if($user->institute)
                                    <p class="mb-1">
                                        <i class="fas fa-university text-primary"></i> 
                                        <strong>Institute:</strong> {{ $user->institute->name }}
                                    </p>
                                @endif
                                
                                @if($user->designation)
                                    <p class="mb-1">
                                        <i class="fas fa-briefcase text-primary"></i> 
                                        <strong>Designation:</strong> {{ $user->designation }}
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
                                            <span>{{ $user->email }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-phone me-2 text-primary"></i> Phone</span>
                                            <span>{{ $user->phone ?? 'N/A' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-calendar-alt me-2 text-primary"></i> Created At</span>
                                            <span>{{ $user->created_at->format('M d, Y h:i A') }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4 border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-map-marker-alt text-primary"></i> Address</h5>
                                </div>
                                <div class="card-body">
                                    @if($user->address)
                                        <p class="card-text">{{ $user->address }}</p>
                                    @else
                                        <p class="card-text text-muted">No address provided</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary me-2">
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