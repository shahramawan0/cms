@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div>
                            <h3 class="card-title mb-0 text-white">
                                <i class="fas fa-chalkboard-teacher text-white"></i> Teachers Management
                            </h3>
                        </div>
                        <div>
                            <button id="addTeacherBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Add Teacher
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Teacher Form (Initially Hidden) -->
                <div class="card-body" id="teacherFormContainer" style="display: none;">
                    <form id="teacherForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="teacherId" name="id">
                        
                        <!-- Institute Information Section -->
                        <div class="row mb-3">
                            @if(auth()->user()->hasRole('Super Admin'))
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="institute_id">Institute <span class="text-danger">*</span></label>
                                    <select name="institute_id" id="institute_id" class="form-control" required>
                                        <option value="">Select Institute</option>
                                        @foreach($institutes as $institute)
                                            <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="institute_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="admin_id">Assigned Admin <span class="text-danger">*</span></label>
                                    <select name="admin_id" id="admin_id" class="form-control" required>
                                        <option value="">Select Admin</option>
                                        @foreach($admins as $admin)
                                            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="admin_id_error"></div>
                                </div>
                            </div>
                            @else
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Institute</label>
                                    <input type="text" class="form-control" value="{{ auth()->user()->institute->name }}" readonly>
                                    <input type="hidden" name="institute_id" value="{{ auth()->user()->institute_id }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Assigned Admin</label>
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                                    <input type="hidden" name="admin_id" value="{{ auth()->user()->id }}">
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Personal Information Section -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" required>
                                    <div class="invalid-feedback" id="name_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                    <div class="invalid-feedback" id="email_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" name="phone" id="phone" class="form-control">
                                    <div class="invalid-feedback" id="phone_error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="gender">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" id="gender" class="form-control" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <div class="invalid-feedback" id="gender_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="dob">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="dob" id="dob" class="form-control" required>
                                    <div class="invalid-feedback" id="dob_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="profile_image">Profile Image</label>
                                    <input type="file" name="profile_image" id="profile_image" class="form-control">
                                    <div class="invalid-feedback" id="profile_image_error"></div>
                                    <div id="profileImagePreview" class="mt-2" style="display: none;">
                                        <img id="previewImage" src="#" alt="Profile Image Preview" class="img-thumbnail" width="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Professional Information Section -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="designation">Designation</label>
                                    <input type="text" name="designation" id="designation" class="form-control">
                                    <div class="invalid-feedback" id="designation_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="qualification">Qualification <span class="text-danger">*</span></label>
                                    <input type="text" name="qualification" id="qualification" class="form-control" required>
                                    <div class="invalid-feedback" id="qualification_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="experience_years">Experience (Years) <span class="text-danger">*</span></label>
                                    <input type="number" name="experience_years" id="experience_years" class="form-control" required min="0">
                                    <div class="invalid-feedback" id="experience_years_error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="specialization">Specialization <span class="text-danger">*</span></label>
                                    <input type="text" name="specialization" id="specialization" class="form-control" required>
                                    <div class="invalid-feedback" id="specialization_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="joining_date">Joining Date <span class="text-danger">*</span></label>
                                    <input type="date" name="joining_date" id="joining_date" class="form-control" required>
                                    <div class="invalid-feedback" id="joining_date_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="salary">Salary <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="salary" id="salary" class="form-control" required min="0">
                                    <div class="invalid-feedback" id="salary_error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bank Information Section -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_title">Bank Account Title</label>
                                    <input type="text" name="account_title" id="account_title" class="form-control">
                                    <div class="invalid-feedback" id="account_title_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_number">Bank Account Number</label>
                                    <input type="text" name="account_number" id="account_number" class="form-control">
                                    <div class="invalid-feedback" id="account_number_error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Password Section -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password <span class="text-danger" id="passwordRequired">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control">
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback" id="password_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password <span class="text-danger" id="confirmPasswordRequired">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" id="submitBtn" class="btn btn-primary">
                                    <span id="submitBtnText">Submit</span>
                                    <span id="submitBtnLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                                <button type="button" id="cancelBtn" class="btn btn-secondary">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Teachers Table -->
                <div class="card-body">
                    <table id="teachers-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Institute</th>
                                <th>Assigned Admin</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#teachers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.teachers.data') }}",
        columns: [
            { data: 'profile_image', name: 'profile_image', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'institute', name: 'institute.name' },
            { data: 'admin', name: 'admin.name' },
            { data: 'status', name: 'email_verified_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        autoWidth: false,
        language: {
            paginate: {
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>'
            }
        }
    });

    // Show/hide form
    $('#addTeacherBtn').click(function() {
        $('#teacherForm')[0].reset();
        $('#teacherId').val('');
        $('#profileImagePreview').hide();
        $('#passwordRequired, #confirmPasswordRequired').show();
        $('#teacherFormContainer').show();
        $('html, body').animate({
            scrollTop: $('#teacherFormContainer').offset().top
        }, 500);
    });

    $('#cancelBtn').click(function() {
        $('#teacherFormContainer').hide();
    });

    // Toggle password visibility
    $(document).on('click', '.toggle-password', function() {
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

    // Preview profile image before upload
    $('#profile_image').change(function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                $('#previewImage').attr('src', e.target.result);
                $('#profileImagePreview').show();
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Load admins when institute changes (for Super Admin)
    @if(auth()->user()->hasRole('Super Admin'))
    $('#institute_id').change(function() {
        var institute_id = $(this).val();
        
        if (institute_id) {
            $.ajax({
                url: "{{ url('admin/teachers/getAdmins') }}/" + institute_id,
                type: 'GET',
                success: function(response) {
                    $('#admin_id').empty();
                    $('#admin_id').append('<option value="">Select Admin</option>');
                    $.each(response.admins, function(index, admin) {
                        $('#admin_id').append('<option value="' + admin.id + '">' + admin.name + '</option>');
                    });
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to load admins', 'error');
                }
            });
        } else {
            $('#admin_id').empty().append('<option value="">Select Admin</option>');
        }
    });
    @endif

    // Form submission
    $('#teacherForm').submit(function(e) {
        e.preventDefault();
        
        // Show loader
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').addClass('d-none');
        $('#submitBtnLoader').removeClass('d-none');
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        let formData = new FormData(this);
        let url = "{{ route('admin.teachers.store') }}";
        let method = "POST";
        
        // If updating, change URL and method
        if ($('#teacherId').val()) {
            url = "{{ url('admin/teachers/update') }}/" + $('#teacherId').val();
            method = "POST";
            formData.append('_method', 'POST');
        }
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Hide form
                $('#teacherFormContainer').hide();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Reload table
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    let errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $('#'+field).addClass('is-invalid');
                        $('#'+field+'_error').text(errors[field][0]);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON.message || 'Something went wrong!'
                    });
                }
            },
            complete: function() {
                // Hide loader
                $('#submitBtn').prop('disabled', false);
                $('#submitBtnText').removeClass('d-none');
                $('#submitBtnLoader').addClass('d-none');
            }
        });
    });

    // Edit button click
    $(document).on('click', '.edit-btn', function() {
        let teacherId = $(this).data('id');
        
        // Show loader on button
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        $.ajax({
            url: "{{ url('admin/teachers/edit') }}/" + teacherId,
            type: "GET",
            success: function(response) {
                // Fill form with data
                $('#teacherId').val(response.id);
                $('#name').val(response.name);
                $('#email').val(response.email);
                $('#phone').val(response.phone);
                $('#gender').val(response.gender);
                $('#dob').val(response.dob ? response.dob.split('T')[0] : '');
                $('#address').val(response.address);
                $('#designation').val(response.designation);
                $('#qualification').val(response.qualification);
                $('#experience_years').val(response.experience_years);
                $('#specialization').val(response.specialization);
                $('#joining_date').val(response.joining_date ? response.joining_date.split('T')[0] : '');
                $('#salary').val(response.salary);
                $('#account_title').val(response.account_title);
                $('#account_number').val(response.account_number);
                
                // For Super Admin, set institute and admin
                @if(auth()->user()->hasRole('Super Admin'))
                $('#institute_id').val(response.institute_id).trigger('change');
                setTimeout(function() {
                    $('#admin_id').val(response.admin_id);
                }, 500);
                @endif
                
                // Show profile image if exists
                if (response.profile_image) {
                    $('#previewImage').attr('src', "{{ asset('storage') }}/" + response.profile_image);
                    $('#profileImagePreview').show();
                } else {
                    $('#profileImagePreview').hide();
                }
                
                // Hide password requirements for edit
                $('#passwordRequired, #confirmPasswordRequired').hide();
                
                // Show form
                $('#teacherFormContainer').show();
                $('html, body').animate({
                    scrollTop: $('#teacherFormContainer').offset().top
                }, 500);
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load teacher data!'
                });
            },
            complete: function() {
                // Reset button text
                $('.edit-btn').html('<i class="fas fa-edit"></i> Edit');
            }
        });
    });

    // Delete button click
    $(document).on('click', '.delete-btn', function() {
        let teacherId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loader on button
                $(this).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                
                $.ajax({
                    url: "{{ url('admin/teachers/delete') }}/" + teacherId,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            response.message,
                            'success'
                        );
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'Something went wrong while deleting.',
                            'error'
                        );
                    },
                    complete: function() {
                        // Reset button text
                        $('.delete-btn').html('<i class="fas fa-trash"></i> Delete');
                    }
                });
            }
        });
    });
});
</script>
@endpush