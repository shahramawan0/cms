@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title text-white">
                        <i class="fas fa-chalkboard-teacher text-white"></i> 
                        {{ isset($teacher) ? 'Edit' : 'Add' }} Teacher
                    </h3>
                </div>
                <form action="{{ isset($teacher) ? route('admin.teachers.update', $teacher->id) : route('admin.teachers.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($teacher))
                        @method('PUT')
                    @endif
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="institute_id">Institute <span class="text-danger">*</span></label>
                                    <select name="institute_id" id="institute_id" class="form-control @error('institute_id') is-invalid @enderror" required>
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

                                <!-- Admin Dropdown (Dynamic) -->
                                <div class="form-group">
                                    <label for="admin_id">Assigned Admin <span class="text-danger">*</span></label>
                                    <select name="admin_id" id="admin_id" class="form-control @error('admin_id') is-invalid @enderror" required>
                                        <!-- Admin options will be populated via JavaScript -->
                                    </select>
                                    @error('admin_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $teacher->name ?? '') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $teacher->email ?? '') }}" required>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" name="phone" id="phone" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $teacher->phone ?? '') }}">
                                    @error('phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="designation">Designation</label>
                                    <input type="text" name="designation" id="designation" 
                                           class="form-control @error('designation') is-invalid @enderror" 
                                           value="{{ old('designation', $teacher->designation ?? '') }}">
                                    @error('designation')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea name="address" id="address" 
                                              class="form-control @error('address') is-invalid @enderror" 
                                              rows="3">{{ old('address', $teacher->address ?? '') }}</textarea>
                                    @error('address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Teacher Specific Fields -->
                                <div class="form-group">
                                    <label for="qualification">Qualification <span class="text-danger">*</span></label>
                                    <input type="text" name="qualification" id="qualification" 
                                           class="form-control @error('qualification') is-invalid @enderror" 
                                           value="{{ old('qualification', $teacher->qualification ?? '') }}" required>
                                    @error('qualification')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="experience_years">Experience (Years) <span class="text-danger">*</span></label>
                                    <input type="number" name="experience_years" id="experience_years" 
                                           class="form-control @error('experience_years') is-invalid @enderror" 
                                           value="{{ old('experience_years', $teacher->experience_years ?? '') }}" required min="0">
                                    @error('experience_years')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="specialization">Specialization <span class="text-danger">*</span></label>
                                    <input type="text" name="specialization" id="specialization" 
                                           class="form-control @error('specialization') is-invalid @enderror" 
                                           value="{{ old('specialization', $teacher->specialization ?? '') }}" required>
                                    @error('specialization')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="joining_date">Joining Date <span class="text-danger">*</span></label>
                                    <input type="date" name="joining_date" id="joining_date"
                                    class="form-control @error('joining_date') is-invalid @enderror" 
                                    value="{{ old('joining_date', isset($teacher) ? $teacher->joining_date : '') }}" required>
                             
                                    @error('joining_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="salary">Salary <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="salary" id="salary" 
                                           class="form-control @error('salary') is-invalid @enderror" 
                                           value="{{ old('salary', $teacher->salary ?? '') }}" required min="0">
                                    @error('salary')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="account_title">Bank Account Title</label>
                                    <input type="text" name="account_title" id="account_title" 
                                           class="form-control @error('account_title') is-invalid @enderror" 
                                           value="{{ old('account_title', $teacher->account_title ?? '') }}">
                                    @error('account_title')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="account_number">Bank Account Number</label>
                                    <input type="text" name="account_number" id="account_number" 
                                           class="form-control @error('account_number') is-invalid @enderror" 
                                           value="{{ old('account_number', $teacher->account_number ?? '') }}">
                                    @error('account_number')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="gender">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror" required>
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

                                <div class="form-group">
                                    <label for="dob">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="dob" id="dob" 
                                    class="form-control @error('dob') is-invalid @enderror" 
                                    value="{{ old('dob', isset($teacher) ? $teacher->dob : '') }}" required>

                                  
                                    @error('dob')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="password">{{ isset($teacher) ? 'New ' : '' }}Password @if(!isset($teacher))<span class="text-danger">*</span>@endif</label>
                                    <input type="password" name="password" id="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           {{ !isset($teacher) ? 'required' : '' }}>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password @if(!isset($teacher))<span class="text-danger">*</span>@endif</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" 
                                           class="form-control" {{ !isset($teacher) ? 'required' : '' }}>
                                </div>

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
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ isset($teacher) ? 'Update' : 'Save' }}
                        </button>
                        <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        // When the institute changes, fetch admins for the selected institute
        $('#institute_id').change(function() {
            var institute_id = $(this).val();

            if (institute_id) {
                $.ajax({
                    url: '/admin/teachers/getAdmins/' + institute_id, // URL to fetch admins based on the institute
                    type: 'GET',
                    success: function(response) {
                        // Clear the current options in the admin dropdown
                        $('#admin_id').empty();

                        // Add a default option
                        $('#admin_id').append('<option value="">Select Admin</option>');

                        // Populate the dropdown with new options
                        $.each(response.admins, function(index, admin) {
                            $('#admin_id').append('<option value="' + admin.id + '">' + admin.name + ' (' + admin.email + ')</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        alert('Something went wrong while fetching the admins!');
                    }
                });
            } else {
                // If no institute is selected, clear the admin dropdown
                $('#admin_id').empty().append('<option value="">Select Admin</option>');
            }
        });
    });
</script>
@endpush
