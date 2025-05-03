@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title">
                        <i class="fas fa-book"></i> {{ isset($course) ? 'Edit Course' : 'Create New Course' }}
                    </h3>
                </div>
                <div class="card-body">
                    <form id="course-form" action="{{ isset($course) ? route('admin.courses.update', $course->id) : route('admin.courses.store') }}" method="POST">
                        @csrf
                        @if(isset($course))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="institute_id">Institute</label>
                                    <select name="institute_id" id="institute_id" class="form-control @error('institute_id') is-invalid @enderror" {{ auth()->user()->hasRole('Admin') ? 'disabled' : '' }}>
                                        <option value="">Select Institute</option>
                                        @foreach($institutes as $institute)
                                            <option value="{{ $institute->id }}" {{ (isset($course) && $course->institute_id == $institute->id) || old('institute_id') == $institute->id ? 'selected' : '' }}>
                                                {{ $institute->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(auth()->user()->hasRole('Admin'))
                                        <input type="hidden" name="institute_id" value="{{ auth()->user()->institute_id }}">
                                    @endif
                                    @error('institute_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="course_name">Course Name</label>
                                    <input type="text" name="course_name" id="course_name" class="form-control @error('course_name') is-invalid @enderror" 
                                           value="{{ isset($course) ? $course->course_name : old('course_name') }}" required>
                                    @error('course_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="course_code">Course Code</label>
                                    <input type="text" name="course_code" id="course_code" class="form-control @error('course_code') is-invalid @enderror" 
                                           value="{{ isset($course) ? $course->course_code : old('course_code') }}">
                                    @error('course_code')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="book_name">Book Name</label>
                                    <input type="text" name="book_name" id="book_name" class="form-control @error('book_name') is-invalid @enderror" 
                                           value="{{ isset($course) ? $course->book_name : old('book_name') }}">
                                    @error('book_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="level">Level</label>
                                    <select name="level" id="level" class="form-control @error('level') is-invalid @enderror" required>
                                        <option value="">Select Level</option>
                                        <option value="Beginner" {{ (isset($course) && $course->level == 'Beginner') || old('level') == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                        <option value="Intermediate" {{ (isset($course) && $course->level == 'Intermediate') || old('level') == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                        <option value="Advanced" {{ (isset($course) && $course->level == 'Advanced') || old('level') == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                                    </select>
                                    @error('level')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="language">Language</label>
                                    <select name="language" id="language" class="form-control @error('language') is-invalid @enderror" required>
                                        <option value="Urdu" {{ (isset($course) && $course->language == 'Urdu') || old('language') == 'Urdu' ? 'selected' : '' }}>Urdu</option>
                                        <option value="Arabic" {{ (isset($course) && $course->language == 'Arabic') || old('language') == 'Arabic' ? 'selected' : '' }}>Arabic</option>
                                        <option value="English" {{ (isset($course) && $course->language == 'English') || old('language') == 'English' ? 'selected' : '' }}>English</option>
                                        <option value="Urdu/English" {{ (isset($course) && $course->language == 'Urdu/English') || old('language') == 'Urdu/English' ? 'selected' : '' }}>Urdu/English</option>
                                    </select>
                                    @error('language')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duration_months">Duration (Months)</label>
                                    <input type="number" name="duration_months" id="duration_months" class="form-control @error('duration_months') is-invalid @enderror" 
                                           value="{{ isset($course) ? $course->duration_months : old('duration_months') }}" min="1">
                                    @error('duration_months')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active">Status</label>
                                    <select name="is_active" id="is_active" class="form-control @error('is_active') is-invalid @enderror" required>
                                        <option value="1" {{ (isset($course) && $course->is_active) || old('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ (isset($course) && !$course->is_active) || old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('is_active')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                                           value="{{ isset($course) && $course->start_date ? \Carbon\Carbon::parse($course->start_date)->format('Y-m-d') : old('start_date') }}">
                                    @error('start_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                                           value="{{ isset($course) && $course->end_date ? \Carbon\Carbon::parse($course->end_date)->format('Y-m-d') : old('end_date') }}">
                                    @error('end_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ isset($course) ? $course->description : old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary" id="submit-btn" data-loading>
                                <i class="fas fa-save"></i> {{ isset($course) ? 'Update Course' : 'Create Course' }}
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form submission with loading indicator
    $('#course-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('#submit-btn');
        const url = form.attr('action');
        const method = form.attr('method');
        
        // Show loading spinner
        submitBtn.prop('disabled', true);
        submitBtn.find('.spinner-border').removeClass('d-none');
        submitBtn.find('i').addClass('d-none');
        
        // Submit form via AJAX
        $.ajax({
            url: url,
            type: method,
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = '';
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        errorMessage += value[0] + '\n';
                    });
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage
                });
            },
            complete: function() {
                // Hide loading spinner
                submitBtn.prop('disabled', false);
                submitBtn.find('.spinner-border').addClass('d-none');
                submitBtn.find('i').removeClass('d-none');
            }
        });
    });
    
    // Set end date min based on start date
    $('#start_date').on('change', function() {
        $('#end_date').attr('min', $(this).val());
    });
});
</script>
@endpush