@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chalkboard-teacher mr-2"></i> 
                        {{ isset($teacher) ? 'Edit Teacher Details' : 'Register New Teacher' }}
                    </h3>
                </div>
                
                <div class="card-body px-5 py-4">
                    <form id="teacherForm" enctype="multipart/form-data">
                        @csrf
                        @if(isset($teacher))
                            @method('PUT')
                        @endif

                        <!-- Institute Information Section (For Super Admin) -->
                        @if(auth()->user()->hasRole('Super Admin'))
                        <div class="section mb-4">
                            <div class="section-header bg-light p-3 rounded-top">
                                <h5 class="mb-0 text-primary">
                                    <i class="fas fa-university mr-2"></i> Institute Information
                                </h5>
                            </div>
                            <div class="section-body p-3 border rounded-bottom">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="institute_id" class="font-weight-bold">Institute <span class="text-danger">*</span></label>
                                            <select name="institute_id" id="institute_id" class="form-control  @error('institute_id') is-invalid @enderror" required>
                                                <option value="">Select Institute</option>
                                                @foreach($institutes as $institute)
                                                    <option value="{{ $institute->id }}" {{ (isset($teacher) && $teacher->institute_id == $institute->id) || old('institute_id') == $institute->id ? 'selected' : '' }}>
                                                        {{ $institute->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('institute_id')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="admin_id" class="font-weight-bold">Assigned Admin <span class="text-danger">*</span></label>
                                            <select name="admin_id" id="admin_id" class="form-control  @error('admin_id') is-invalid @enderror" required>
                                                @if(isset($teacher) && $teacher->admin_id)
                                                    <option value="{{ $teacher->admin_id }}" selected>{{ $teacher->admin->name }}</option>
                                                @else
                                                    <option value="">Select Admin</option>
                                                @endif
                                            </select>
                                            @error('admin_id')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                            <!-- Admin sees simplified form with auto-set institute -->
                            <div class="form-group">
                                <label for="institute_id" class="font-weight-bold">Institute</label>
                                <input type="text" class="form-control " value="{{ auth()->user()->institute->name }}" readonly>
                                <input type="hidden" name="institute_id" value="{{ auth()->user()->institute_id }}">
                            </div>
                            <input type="hidden" name="admin_id" value="{{ auth()->id() }}">
                        @endif

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
                                            <input type="text" name="name" id="name" class="form-control  @error('name') is-invalid @enderror" 
                                                   value="{{ old('name', $teacher->name ?? '') }}" required>
                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email" class="font-weight-bold">Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" id="email" class="form-control  @error('email') is-invalid @enderror" 
                                                   value="{{ old('email', $teacher->email ?? '') }}" required>
                                            @error('email')
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
                                            <label for="password" class="font-weight-bold">{{ isset($teacher) ? 'New Password' : 'Password' }} @if(!isset($teacher))<span class="text-danger">*</span>@endif</label>
                                            <div class="input-group">
                                                <input type="password" name="password" id="password" class="form-control  @error('password') is-invalid @enderror" 
                                                       {{ !isset($teacher) ? 'required' : '' }}>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            @if(!isset($teacher))
                                                <small class="form-text text-muted">Minimum 8 characters</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password_confirmation" class="font-weight-bold">Confirm Password @if(!isset($teacher))<span class="text-danger">*</span>@endif</label>
                                            <div class="input-group">
                                                <input type="password" name="password_confirmation" id="password_confirmation" 
                                                       class="form-control " {{ !isset($teacher) ? 'required' : '' }}>
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
                                            <input type="text" name="phone" id="phone" class="form-control  @error('phone') is-invalid @enderror" 
                                                   value="{{ old('phone', $teacher->phone ?? '') }}">
                                            @error('phone')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="gender" class="font-weight-bold">Gender <span class="text-danger">*</span></label>
                                            <select name="gender" id="gender" class="form-control  @error('gender') is-invalid @enderror" required>
                                                <option value="">Select Gender</option>
                                                <option value="male" {{ (isset($teacher) && $teacher->gender == 'male') || old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ (isset($teacher) && $teacher->gender == 'female') || old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="other" {{ (isset($teacher) && $teacher->gender == 'other') || old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                            @error('gender')
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
                                            <label for="dob" class="font-weight-bold">Date of Birth <span class="text-danger">*</span></label>
                                            <input type="date" name="dob" id="dob" class="form-control  @error('dob') is-invalid @enderror" 
                                                   value="{{ old('dob', isset($teacher) && $teacher->dob ? \Carbon\Carbon::parse($teacher->dob)->format('Y-m-d') : '') }}" required>
                                            @error('dob')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile_image" class="font-weight-bold">Profile Image</label>
                                            <div class="custom-file">
                                                <input type="file" name="profile_image" id="profile_image" class="custom-file-input @error('profile_image') is-invalid @enderror">
                                                <label class="custom-file-label" for="profile_image">Choose file</label>
                                            </div>
                                            @error('profile_image')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            @if(isset($teacher) && $teacher->profile_image)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/'.$teacher->profile_image) }}" alt="Profile Image" class="img-thumbnail" width="100">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="address" class="font-weight-bold">Address</label>
                                            <textarea name="address" id="address" class="form-control  @error('address') is-invalid @enderror" 
                                                      rows="2">{{ old('address', $teacher->address ?? '') }}</textarea>
                                            @error('address')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Information Section -->
                        <div class="section mb-4">
                            <div class="section-header bg-light p-3 rounded-top">
                                <h5 class="mb-0 text-primary">
                                    <i class="fas fa-briefcase mr-2"></i> Professional Information
                                </h5>
                            </div>
                            <div class="section-body p-3 border rounded-bottom">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="designation" class="font-weight-bold">Designation</label>
                                            <input type="text" name="designation" id="designation" class="form-control  @error('designation') is-invalid @enderror" 
                                                   value="{{ old('designation', $teacher->designation ?? '') }}">
                                            @error('designation')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="qualification" class="font-weight-bold">Qualification <span class="text-danger">*</span></label>
                                            <input type="text" name="qualification" id="qualification" class="form-control  @error('qualification') is-invalid @enderror" 
                                                   value="{{ old('qualification', $teacher->qualification ?? '') }}" required>
                                            @error('qualification')
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
                                            <label for="experience_years" class="font-weight-bold">Experience (Years) <span class="text-danger">*</span></label>
                                            <input type="number" name="experience_years" id="experience_years" class="form-control  @error('experience_years') is-invalid @enderror" 
                                                   value="{{ old('experience_years', $teacher->experience_years ?? '') }}" required min="0">
                                            @error('experience_years')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="specialization" class="font-weight-bold">Specialization <span class="text-danger">*</span></label>
                                            <input type="text" name="specialization" id="specialization" class="form-control  @error('specialization') is-invalid @enderror" 
                                                   value="{{ old('specialization', $teacher->specialization ?? '') }}" required>
                                            @error('specialization')
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
                                            <label for="joining_date" class="font-weight-bold">Joining Date <span class="text-danger">*</span></label>
                                            <input type="date" name="joining_date" id="joining_date" class="form-control  @error('joining_date') is-invalid @enderror" 
                                                   value="{{ old('joining_date', isset($teacher) && $teacher->joining_date ? \Carbon\Carbon::parse($teacher->joining_date)->format('Y-m-d') : '') }}" required>
                                            @error('joining_date')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="salary" class="font-weight-bold">Salary <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" name="salary" id="salary" class="form-control  @error('salary') is-invalid @enderror" 
                                                   value="{{ old('salary', $teacher->salary ?? '') }}" required min="0">
                                            @error('salary')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Information Section -->
                        <div class="section mb-4">
                            <div class="section-header bg-light p-3 rounded-top">
                                <h5 class="mb-0 text-primary">
                                    <i class="fas fa-piggy-bank mr-2"></i> Bank Information
                                </h5>
                            </div>
                            <div class="section-body p-3 border rounded-bottom">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="account_title" class="font-weight-bold">Bank Account Title</label>
                                            <input type="text" name="account_title" id="account_title" class="form-control  @error('account_title') is-invalid @enderror" 
                                                   value="{{ old('account_title', $teacher->account_title ?? '') }}">
                                            @error('account_title')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="account_number" class="font-weight-bold">Bank Account Number</label>
                                            <input type="text" name="account_number" id="account_number" class="form-control  @error('account_number') is-invalid @enderror" 
                                                   value="{{ old('account_number', $teacher->account_number ?? '') }}">
                                            @error('account_number')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-group text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5 mr-3">
                                <i class="fas fa-save mr-2"></i> {{ isset($teacher) ? 'Update Teacher' : 'Register Teacher' }}
                            </button>
                            <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary btn-lg px-5">
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
    
    . {
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

    @if(auth()->user()->hasRole('Super Admin'))
        // Only Super Admin needs the institute-admin AJAX functionality
        $('#institute_id').change(function() {
            var institute_id = $(this).val();

            if (institute_id) {
                $.ajax({
                    url: '/admin/teachers/getAdmins/' + institute_id,
                    type: 'GET',
                    success: function(response) {
                        $('#admin_id').empty();
                        $('#admin_id').append('<option value="">Select Admin</option>');
                        $.each(response.admins, function(index, admin) {
                            $('#admin_id').append('<option value="' + admin.id + '">' + admin.name + ' (' + admin.email + ')</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        alert('Something went wrong while fetching the admins!');
                    }
                });
            } else {
                $('#admin_id').empty().append('<option value="">Select Admin</option>');
            }
        });

        // Trigger change event if editing and institute is already selected
        @if(isset($teacher) && $teacher->institute_id)
            $('#institute_id').trigger('change');
        @endif
    @endif
});
</script>
@endpush