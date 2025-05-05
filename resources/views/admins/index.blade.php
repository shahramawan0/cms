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
                                <i class="fas fa-users-cog text-white"></i> Admin Users Management
                            </h3>
                        </div>
                        <div>
                            <button id="addUserBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Add Admin
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Admin User Form (Initially Hidden) -->
                <div class="card-body" id="adminUserFormContainer" style="display: none;">
                    <form id="adminUserForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="adminUserId" name="id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="institute_id">Institute <span class="text-danger">*</span></label>
                                    <select name="institute_id" id="institute_id" class="form-control" required>
                                        <option value="">Select Institute</option>
                                        @foreach($institutes as $institute)
                                            <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" name="phone" id="phone" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea name="address" id="address" class="form-control" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">Admin Role <span class="text-danger">*</span></label>
                                    <select name="role" id="role" class="form-control" required>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="password">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="profile_image">Profile Image</label>
                                    <input type="file" name="profile_image" id="profile_image" class="form-control">
                                    <!-- Display Profile Image if exists -->
                                    <div id="profileImagePreview" class="mt-2" style="display: none;">
                                        <img id="profileImage" src="#" alt="Profile Image Preview" class="img-thumbnail" width="100">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" class="btn btn-primary py-2 px-4">
                            <span id="submitBtnText">Submit</span>
                            <span id="submitBtnLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                        <button type="button" id="cancelBtn" class="btn btn-secondary py-2 px-4">Cancel</button>
                    </form>
                </div>

                <!-- Admin Users Table -->
                <div class="card-body">
                    <table id="admin-users-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Institute</th>
                                <th>Role</th>
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
    var table = $('#admin-users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.users.data') }}",
        columns: [
            { data: 'profile_image', name: 'profile_image', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'institute', name: 'institute.name' },
            { data: 'role', name: 'roles.name' },
            { data: 'status', name: 'email_verified_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        autoWidth: false,
    });

    // Show/hide form
    $('#addUserBtn').click(function() {
        $('#adminUserForm')[0].reset();
        $('#adminUserId').val('');
        $('#profileImagePreview').hide();
        $('#adminUserFormContainer').show();
        $('html, body').animate({
            scrollTop: $('#adminUserFormContainer').offset().top
        }, 500);
    });

    $('#cancelBtn').click(function() {
        $('#adminUserFormContainer').hide();
    });

    // Edit button click
    $(document).on('click', '.edit-btn', function() {
        let userId = $(this).data('id');

        // Show loader
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        $.ajax({
            url: "{{ url('admin/users/edit') }}/" + userId,
            type: 'GET',
            success: function(response) {
                let user = response.user;
                $('#adminUserId').val(user.id);
                $('#name').val(user.name);
                $('#email').val(user.email);
                $('#phone').val(user.phone);
                $('#address').val(user.address);
                $('#institute_id').val(user.institute_id);
                $('#role').val(user.role_id);

                // Display Profile Image if exists
                if (user.profile_image) {
                    $('#profileImagePreview').show();
                    $('#profileImage').attr('src', "{{ asset('storage') }}/" + user.profile_image);
                } else {
                    $('#profileImagePreview').hide();
                }

                // Show form
                $('#adminUserFormContainer').show();
                $('html, body').animate({
                    scrollTop: $('#adminUserFormContainer').offset().top
                }, 500);
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load admin data!'
                });
            }
        });
    });

    // Form submission
    $('#adminUserForm').submit(function(e) {
        e.preventDefault();

        // Show loader
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').addClass('d-none');
        $('#submitBtnLoader').removeClass('d-none');

        let formData = new FormData(this);
        let url = "{{ route('admin.users.store') }}";
        let method = "POST";

        // If updating, change URL and method
        if ($('#adminUserId').val()) {
            url = "{{ url('admin/users/update') }}/" + $('#adminUserId').val();
            method = "POST";
            formData.append('_method', 'PUT'); // Correct the method for updating
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Hide form
                $('#adminUserFormContainer').hide();

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

    // Delete button click
    $(document).on('click', '.delete-btn', function() {
        let userId = $(this).data('id');

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
                $.ajax({
                    url: "{{ url('admin/users/delete') }}/" + userId,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            response.success,
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
                    }
                });
            }
        });
    });
});
</script>
@endpush
