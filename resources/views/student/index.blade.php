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
                                <i class="fas fa-users text-white"></i> Students Registraion
                            </h3>
                        </div>
                        <div>
                            <button id="addStudentBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Resgister Student
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Student Form (Initially Hidden) -->
                <div class="card-body" id="studentFormContainer" style="display: none;">
                    <form id="studentForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="studentId" name="id">
                        @if(auth()->user()->hasRole('Super Admin'))
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="institute_id">Institute <span class="text-danger">*</span></label>
                                    <select name="institute_id" id="institute_id" class="form-control" required>
                                        <option value="">Select Institute</option>
                                        @foreach(App\Models\Institute::get() as $institute)
                                            <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="institute_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" required>
                                    <div class="invalid-feedback" id="name_error"></div>
                                </div>
                            </div>
                        </div>
                        @else
                            <input type="hidden" name="institute_id" value="{{ auth()->user()->institute_id }}">
                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="roll_number">Roll Number <span class="text-danger">*</span></label>
                                    <input type="text" name="roll_number" id="roll_number" class="form-control" required>
                                    <div class="invalid-feedback" id="roll_number_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cnic">CNIC <span class="text-danger">*</span></label>
                                    <input type="text" name="cnic" id="cnic" class="form-control" required>
                                    <div class="invalid-feedback" id="cnic_error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="father_name">Father Name <span class="text-danger">*</span></label>
                                    <input type="text" name="father_name" id="father_name" class="form-control" required>
                                    <div class="invalid-feedback" id="father_name_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gender">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" id="gender" class="form-control" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <div class="invalid-feedback" id="gender_error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dob">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="dob" id="dob" class="form-control" required>
                                    <div class="invalid-feedback" id="dob_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="admission_date">Admission Date <span class="text-danger">*</span></label>
                                    <input type="date" name="admission_date" id="admission_date" class="form-control" required>
                                    <div class="invalid-feedback" id="admission_date_error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                    <div class="invalid-feedback" id="email_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" name="phone" id="phone" class="form-control">
                                    <div class="invalid-feedback" id="phone_error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password <span id="passwordRequired">*</span></label>
                                    <input type="password" name="password" id="password" class="form-control">
                                    <div class="invalid-feedback" id="password_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password <span id="confirmPasswordRequired">*</span></label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="profile_image">Profile Image</label>
                                    <input type="file" name="profile_image" id="profile_image" class="form-control">
                                    <div class="invalid-feedback" id="profile_image_error"></div>
                                    <div id="imagePreview" class="mt-2" style="display: none;">
                                        <img id="previewImg" src="#" alt="Profile Image Preview" style="max-width: 100px; max-height: 100px;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea type="text" name="address" id="address" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                       
                        <div class="row mt-2">
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
                
                <!-- Students Table -->
                <div class="card-body">
                    <table id="students-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Name</th>
                                <th>Father Name</th>
                                <th>CNIC</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Institute</th>
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
    var table = $('#students-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('students.data') }}",
        columns: [
            { data: 'roll_number', name: 'roll_number' },
            { data: 'name', name: 'name' },
            { data: 'father_name', name: 'father_name' },
            { data: 'cnic', name: 'cnic' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { data: 'institute', name: 'institute.name' },
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
    $('#addStudentBtn').click(function() {
        $('#studentForm')[0].reset();
        $('#studentId').val('');
        $('#password').val('').attr('required', true);
        $('#password_confirmation').val('').attr('required', true);
        $('#passwordRequired, #confirmPasswordRequired').show();
        $('#studentFormContainer').show();
        $('#imagePreview').hide();
        $('html, body').animate({
            scrollTop: $('#studentFormContainer').offset().top
        }, 500);
    });

    $('#cancelBtn').click(function() {
        $('#studentFormContainer').hide();
    });

    // Profile image preview
    $('#profile_image').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#imagePreview').show();
            }
            reader.readAsDataURL(file);
        }
    });

    // Form submission
    $('#studentForm').submit(function(e) {
        e.preventDefault();
        
        // Show loader
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').addClass('d-none');
        $('#submitBtnLoader').removeClass('d-none');
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        let url = "{{ route('students.store') }}";
        let method = "POST";
        let formData = new FormData(this);

        if ($('#studentId').val()) {
            url = "{{ url('students/update') }}/" + $('#studentId').val();
            formData.append('_method', 'PUT');
        }
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Hide form
                $('#studentFormContainer').hide();
                
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
   // Edit button click
$(document).on('click', '.edit-btn', function() {
    let studentId = $(this).data('id');
    
    // Show loader on button
    $(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
    
    $.ajax({
        url: "{{ url('students/edit') }}/" + studentId,
        type: "GET",
        success: function(response) {
            // Fill form with data
            $('#studentId').val(response.id);
            $('#name').val(response.name);
            $('#father_name').val(response.father_name);
            $('#cnic').val(response.cnic);
            $('#roll_number').val(response.roll_number);
            
            // Set gender properly - ensure case matches select options
            $('#gender').val(response.gender.charAt(0).toUpperCase() + response.gender.slice(1).toLowerCase());
            
            // Set date fields - they should be in YYYY-MM-DD format
            $('#dob').val(response.dob);
            $('#admission_date').val(response.admission_date);
            
            $('#email').val(response.email);
            $('#phone').val(response.phone);
            $('#address').val(response.address);
            
            // For super admin, set the institute value
            if ($('#institute_id').length) {
                $('#institute_id').val(response.institute_id);
            }
            
            // Show profile image preview if exists
            if (response.profile_image) {
                $('#previewImg').attr('src', "{{ asset('storage') }}/" + response.profile_image);
                $('#imagePreview').show();
            } else {
                $('#imagePreview').hide();
            }
            
            // Show form
            $('#studentFormContainer').show();
            $('html, body').animate({
                scrollTop: $('#studentFormContainer').offset().top
            }, 500);
            
            // Remove password requirement for editing
            $('#password').removeAttr('required');
            $('#password_confirmation').removeAttr('required');
            $('#passwordRequired, #confirmPasswordRequired').hide();
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load student data!'
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
        let studentId = $(this).data('id');
        
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
                    url: "{{ url('students/delete') }}/" + studentId,
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