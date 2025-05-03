@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-user-graduate mr-2"></i> 
                        {{ isset($student) ? 'Edit Student Details' : 'Register New Student' }}
                    </h3>
                </div>
                
                <div class="card-body px-5 py-4">
                    <form id="student-form" action="{{ isset($student) ? route('admin.students.update', $student->id) : route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($student))
                            @method('PUT')
                        @endif

                         <!-- Institute & Course Section -->
                         <div class="section mb-4">
                            <div class="section-header bg-light p-3 rounded-top">
                                <h5 class="mb-0 text-primary">
                                    <i class="fas fa-university mr-2"></i> Institute & Course Details
                                </h5>
                            </div>
                            <div class="section-body p-3 border rounded-bottom">
                                <!-- Institute Selection (Only for Super Admin) -->
                                @if(auth()->user()->hasRole('Super Admin'))
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="institute_id" class="font-weight-bold">Institute <span class="text-danger">*</span></label>
                                            <select name="institute_id" id="institute_id" class="form-control" required>
                                                <option value="">Select Institute</option>
                                                @foreach($institutes as $institute)
                                                    <option value="{{ $institute->id }}" {{ (isset($student) && $student->institute_id == $institute->id) || old('institute_id') == $institute->id ? 'selected' : '' }}>
                                                        {{ $institute->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="admin_id" class="font-weight-bold">Admin <span class="text-danger">*</span></label>
                                            <select name="admin_id" id="admin_id" class="form-control" required>
                                                <option value="">Select Admin</option>
                                                @if(isset($student) && $student->admin_id)
                                                    <option value="{{ $student->admin_id }}" selected>{{ $student->admin->name }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @else
                                    <input type="hidden" name="institute_id" value="{{ auth()->user()->institute_id }}">
                                    <input type="hidden" name="admin_id" value="{{ auth()->user()->hasRole('Admin') ? auth()->user()->id : auth()->user()->admin_id }}">
                                @endif

                                <!-- Teacher Selection -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="teacher_id" class="font-weight-bold">Teacher <span class="text-danger">*</span></label>
                                            <select name="teacher_id" id="teacher_id" class="form-control" required>
                                                <option value="">Select Teacher</option>
                                                @foreach($teachers as $teacher)
                                                    <option value="{{ $teacher->id }}" {{ (isset($student) && $student->teacher_id == $teacher->id) || old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                                        {{ $teacher->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="course_id" class="font-weight-bold">Course <span class="text-danger">*</span></label>
                                            <select name="course_id" id="course_id" class="form-control" required>
                                                <option value="">Select Course</option>
                                                @foreach($courses as $course)
                                                    <option value="{{ $course->id }}" {{ (isset($student) && $student->courses->contains($course->id)) || old('course_id') == $course->id ? 'selected' : '' }}>
                                                        {{ $course->course_name }} ({{ $course->course_code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information Section -->
                        <div class="section mb-4">
                            <div class="section-header bg-light p-3 rounded-top">
                                <h5 class="mb-0 text-primary">
                                    <i class="fas fa-id-card mr-2"></i> Personal Information
                                </h5>
                            </div>
                            <div class="section-body p-3 border rounded-bottom">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name" class="font-weight-bold">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" id="name" class="form-control" 
                                                   value="{{ isset($student) ? $student->name : old('name') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email" class="font-weight-bold">Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" id="email" class="form-control" 
                                                   value="{{ isset($student) ? $student->email : old('email') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password" class="font-weight-bold">{{ isset($student) ? 'New Password' : 'Password' }}</label>
                                            <div class="input-group">
                                                <input type="password" name="password" id="password" class="form-control" 
                                                       {{ isset($student) ? '' : 'required' }} minlength="8">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">Minimum 8 characters</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password_confirmation" class="font-weight-bold">Confirm Password</label>
                                            <div class="input-group">
                                                <input type="password" name="password_confirmation" id="password_confirmation" 
                                                       class="form-control" {{ isset($student) ? '' : 'required' }}>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone" class="font-weight-bold">Phone Number</label>
                                            <input type="text" name="phone" id="phone" class="form-control" 
                                                   value="{{ isset($student) ? $student->phone : old('phone') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="gender" class="font-weight-bold">Gender <span class="text-danger">*</span></label>
                                            <select name="gender" id="gender" class="form-control" required>
                                                <option value="male" {{ (isset($student) && $student->gender == 'male') || old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ (isset($student) && $student->gender == 'female') || old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="other" {{ (isset($student) && $student->gender == 'other') || old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="dob">Date of Birth <span class="text-danger">*</span></label>
                                            <input type="date" name="dob" id="dob" 
                                                   class="form-control @error('dob') is-invalid @enderror" 
                                                   value="{{ old('dob', isset($student) && $student->dob ? \Carbon\Carbon::parse($student->dob)->format('Y-m-d') : '') }}" required>
                                        
                                            @error('dob')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile_image" class="form-label">Profile Image</label>
                                            <input class="form-control @error('profile_image') is-invalid @enderror" 
                                                   type="file" id="profile_image" name="profile_image">
                                            @error('profile_image')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            @if(isset($teacher) && $teacher->profile_image)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/'.$teacher->profile_image) }}" 
                                                         alt="Profile Image" class="img-thumbnail" width="100">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information Section -->
                        <div class="section mb-4">
                            <div class="section-header bg-light p-3 rounded-top">
                                <h5 class="mb-0 text-primary">
                                    <i class="fas fa-graduation-cap mr-2"></i> Academic Information
                                </h5>
                            </div>
                            <div class="section-body p-3 border rounded-bottom">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="roll_number" class="font-weight-bold">Roll Number</label>
                                            <input type="text" name="roll_number" id="roll_number" class="form-control" 
                                                   value="{{ isset($student) ? $student->roll_number : old('roll_number') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="class" class="font-weight-bold">Class</label>
                                            <input type="text" name="class" id="class" class="form-control" 
                                                   value="{{ isset($student) ? $student->class : old('class') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="section" class="font-weight-bold">Section</label>
                                            <input type="text" name="section" id="section" class="form-control" 
                                                   value="{{ isset($student) ? $student->section : old('section') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="admission_date">Admission Date <span class="text-danger">*</span></label>
                                            <input type="date" name="admission_date" id="admission_date" 
                                                   class="form-control @error('admission_date') is-invalid @enderror" 
                                                   value="{{ old('admission_date', isset($student) && $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('Y-m-d') : '') }}" required>
                                        
                                            @error('admission_date')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="address" class="font-weight-bold">Address</label>
                                            <textarea name="address" id="address" class="form-control" rows="2">{{ isset($student) ? $student->address : old('address') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                       

                        <!-- Form Actions -->
                        <div class="form-group text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5 mr-3" id="submit-btn" data-loading>
                                <i class="fas fa-save mr-2"></i> {{ isset($student) ? 'Update Student' : 'Register Student' }}
                                <span class="spinner-border spinner-border-sm d-none ml-2" role="status" aria-hidden="true"></span>
                            </button>
                            <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary btn-lg px-5">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .card-header {
        padding: 1.25rem 1.5rem;
    }
    
    .section {
        margin-bottom: 2rem;
    }
    
    .section-header {
        border-bottom: 2px solid #dee2e6;
    }
    
    .section-body {
        background-color: #f8f9fa;
    }
    
     {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
    }
    
    .custom-file-label::after {
        content: "Browse";
    }
    
    .toggle-password {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    
    .btn-lg {
        padding: 0.5rem 1.5rem;
        font-size: 1.1rem;
        border-radius: 0.5rem;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%) !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Form submission with loading indicator
    $('#student-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('#submit-btn');
        const url = form.attr('action');
        const method = form.attr('method');
        
        // Show loading spinner
        submitBtn.prop('disabled', true);
        submitBtn.find('.spinner-border').removeClass('d-none');
        submitBtn.find('i').addClass('d-none');
        
        // Prepare form data for AJAX
        let formData = new FormData(form[0]);

        // Submit form via AJAX
        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
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

    // Toggle password visibility
    $('.toggle-password').click(function() {
        const input = $(this).closest('.input-group').find('input');
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Custom file input
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Dynamic dropdowns for Super Admin
    @if(auth()->user()->hasRole('Super Admin'))
    $('#institute_id').on('change', function() {
        const instituteId = $(this).val();
        
        if (instituteId) {
            // Load Admins
            $.get('/admin/get-institute-admins/' + instituteId, function(data) {
                const adminSelect = $('#admin_id');
                adminSelect.empty().append('<option value="">Select Admin</option>');
                
                $.each(data.admins, function(key, admin) {
                    adminSelect.append(`<option value="${admin.id}">${admin.name}</option>`);
                });
            });
            
            // Load Teachers
            $.get('/admin/get-institute-teachers/' + instituteId, function(data) {
                const teacherSelect = $('#teacher_id');
                teacherSelect.empty().append('<option value="">Select Teacher</option>');
                
                $.each(data.teachers, function(key, teacher) {
                    teacherSelect.append(`<option value="${teacher.id}">${teacher.name}</option>`);
                });
            });
            
            // Load Courses
            $.get('/admin/get-institute-courses/' + instituteId, function(data) {
                const courseSelect = $('#course_id');
                courseSelect.empty().append('<option value="">Select Course</option>');
                
                $.each(data.courses, function(key, course) {
                    courseSelect.append(`<option value="${course.id}">${course.course_name} (${course.course_code})</option>`);
                });
            });
        }
    });
    @endif
});
</script>
@endpush