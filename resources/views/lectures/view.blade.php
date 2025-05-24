@extends('layouts.app')

@section('content')
<div class="container py-3">
    <!-- Lecture Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-2">{{ $lecture->title }}</h1>
            <div class="d-flex align-items-center text-muted flex-wrap gap-2">
                <span class="badge bg-{{ $lecture->status == 'active' ? 'success' : 'secondary' }}">
                    {{ ucfirst($lecture->status) }}
                </span>
                <span><i class="far fa-calendar me-1"></i> {{ \Carbon\Carbon::parse($lecture->lecture_date)->format('M j, Y') }}</span>
                <span><i class="fas fa-chalkboard-teacher me-1"></i> {{ $lecture->teacher->name }}</span>
            </div>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('lectures.index') }}">Lectures</a></li>
                <li class="breadcrumb-item active" aria-current="page">View</li>
            </ol>
        </nav>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Combined Description and Media Actions Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <!-- Description Column -->
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h5 class="card-title mb-3 d-flex align-items-center">
                                <i class="fas fa-align-left text-primary me-2"></i> Lecture Description
                            </h5>
                            <p class="card-text">{{ $lecture->description ?? 'No description available' }}</p>
                        </div>
                        
                        <!-- Media Actions Column -->
                        <div class="col-md-6">
                            <h5 class="card-title mb-3 d-flex align-items-center">
                                <i class="fas fa-play-circle text-primary me-2"></i> Lecture Materials
                            </h5>
                            
                            <div class="d-flex flex-column gap-3">
                                @if(isset($fileInfo['video']))
                                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-film fs-4 text-danger me-3"></i>
                                        <div>
                                            <h6 class="mb-0">Lecture Video</h6>
                                            <small class="text-muted">{{ $fileInfo['video']['size'] }}</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#videoModal">
                                            <i class="fas fa-play me-1"></i> Watch
                                        </button>
                                        <a href="{{ $fileInfo['video']['download_url'] }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-download me-1"></i> Download
                                        </a>
                                    </div>
                                </div>
                                @endif

                                @if(isset($fileInfo['pdf']))
                                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-pdf fs-4 text-danger me-3"></i>
                                        <div>
                                            <h6 class="mb-0">Lecture Document</h6>
                                            <small class="text-muted">{{ $fileInfo['pdf']['size'] }}</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pdfModal">
                                            <i class="fas fa-eye me-1"></i> Preview
                                        </button>
                                        <a href="{{ $fileInfo['pdf']['download_url'] }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-download me-1"></i> Download
                                        </a>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Course Details Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3 d-flex align-items-center">
                        <i class="fas fa-info-circle text-primary me-2"></i> Course Details
                    </h5>
                    
                    <div class="lecture-details">
                        <div class="detail-item mb-3">
                            <div class="detail-icon bg-primary-light">
                                <i class="fas fa-university text-primary"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Institute</h6>
                                <p>{{ $lecture->institute->name }}</p>
                            </div>
                        </div>
                        
                        <div class="detail-item mb-3">
                            <div class="detail-icon bg-info-light">
                                <i class="fas fa-calendar-alt text-info"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Academic Session</h6>
                                <p>{{ $lecture->session->session_name }}</p>
                            </div>
                        </div>
                        
                        <div class="detail-item mb-3">
                            <div class="detail-icon bg-success-light">
                                <i class="fas fa-graduation-cap text-success"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Class</h6>
                                <p>{{ $lecture->class->name }}</p>
                            </div>
                        </div>
                        
                        <div class="detail-item mb-3">
                            <div class="detail-icon bg-warning-light">
                                <i class="fas fa-users text-warning"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Section</h6>
                                <p>{{ $lecture->section->section_name }}</p>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-icon bg-danger-light">
                                <i class="fas fa-book text-danger"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Course</h6>
                                <p>{{ $lecture->course->course_name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Lectures -->
            @if($relatedLectures->count() > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3 d-flex align-items-center">
                        <i class="fas fa-link text-primary me-2"></i> Related Lectures
                    </h5>
                    
                    <div class="list-group list-group-flush">
                        @foreach($relatedLectures as $related)
                        <a href="{{ route('lectures.view', $related->id) }}" class="list-group-item list-group-item-action px-0 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">{{ Str::limit($related->title, 30) }}</h6>
                                    <small class="text-muted">{{ $related->teacher->name }}</small>
                                </div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($related->lecture_date)->format('M j') }}</small>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Video Modal -->
@if(isset($fileInfo['video']))
<div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lecture Video</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <video controls class="w-100" style="background-color: #000;">
                        <source src="{{ $fileInfo['video']['url'] }}" type="video/mp4">
                    </video>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <small class="text-muted">{{ $fileInfo['video']['size'] }}</small>
                <a href="{{ $fileInfo['video']['download_url'] }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-download me-1"></i> Download Video
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<!-- PDF Modal -->
@if(isset($fileInfo['pdf']))
<div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lecture Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="embed-responsive" style="height: 70vh;">
                    <object data="{{ $fileInfo['pdf']['url'] }}" type="application/pdf" width="100%" height="100%">
                        <p>It appears you don't have a PDF plugin for this browser. 
                        You can <a href="{{ $fileInfo['pdf']['url'] }}" target="_blank">click here to download the PDF file.</a></p>
                    </object>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <small class="text-muted">{{ $fileInfo['pdf']['size'] }}</small>
                <div class="btn-group">
                    <a href="{{ $fileInfo['pdf']['url'] }}" target="_blank" class="btn btn-primary btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i> Open in New Tab
                    </a>
                    <a href="{{ $fileInfo['pdf']['download_url'] }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download me-1"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    .card {
        border-radius: 0.75rem;
        border: none;
        box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.05);
    }
    
    .lecture-details .detail-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .detail-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .bg-primary-light { background-color: rgba(13, 110, 253, 0.1); }
    .bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
    .bg-success-light { background-color: rgba(25, 135, 84, 0.1); }
    .bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
    .bg-danger-light { background-color: rgba(220, 53, 69, 0.1); }
    
    .detail-content h6 {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }
    
    .detail-content p {
        font-size: 0.95rem;
        font-weight: 500;
        margin-bottom: 0;
    }
    
    .modal-content {
        border-radius: 0.75rem;
        overflow: hidden;
    }
    
    .embed-responsive {
        position: relative;
        display: block;
        width: 100%;
        padding: 0;
        overflow: hidden;
    }
    
    .embed-responsive object {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }
    
    @media (max-width: 767.98px) {
        .breadcrumb {
            display: none;
        }
        
        .modal-dialog {
            margin: 0.5rem;
        }
        
        .card-body .row > div {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Reset video when modal is closed
    document.addEventListener('DOMContentLoaded', function() {
        const videoModal = document.getElementById('videoModal');
        if (videoModal) {
            videoModal.addEventListener('hidden.bs.modal', function () {
                const video = this.querySelector('video');
                if (video) {
                    video.pause();
                    video.currentTime = 0;
                }
            });
        }
    });
</script>
@endpush