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
                                <i class="fas fa-chalkboard text-white"></i> Classes
                            </h3>
                        </div>
                        <div>
                            <button id="addClassBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Add Class
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Class Form (Initially Hidden) -->
                <div class="card-body" id="classFormContainer" style="display: none;">
                    <form id="classForm">
                        @csrf
                        <input type="hidden" id="classId" name="id">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="session_id">Session <span class="text-danger">*</span></label>
                                    <select name="session_id" id="session_id" class="form-control" required>
                                        <option value="">Select Session</option>
                                        @foreach($sessions as $session)
                                            <option value="{{ $session->id }}">{{ $session->session_name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="session_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Class Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control" required>
                                    <div class="invalid-feedback" id="name_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                    <div class="invalid-feedback" id="status_error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" 
                                              class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
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
                
                <!-- Classes Table -->
                <div class="card-body">
                    <table id="classes-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Session</th>
                                <th>Class Name</th>
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
    var table = $('#classes-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('classes.data') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'session.session_name', name: 'session.session_name' },
            { data: 'name', name: 'name' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        autoWidth: false,
        language: {
            paginate: {
                previous: '&laquo;',
                next: '&raquo;'
            }
        }
    });

    // Show/hide form
    $('#addClassBtn').click(function() {
        $('#classForm')[0].reset();
        $('#classId').val('');
        $('#classFormContainer').show();
        $('html, body').animate({
            scrollTop: $('#classFormContainer').offset().top
        }, 500);
    });

    $('#cancelBtn').click(function() {
        $('#classFormContainer').hide();
    });

    // Form submission
    $('#classForm').submit(function(e) {
        e.preventDefault();
        
        // Show loader
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').addClass('d-none');
        $('#submitBtnLoader').removeClass('d-none');
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        let formData = $(this).serialize();
        let url = "{{ route('classes.store') }}";
        let method = "POST";
        
        // If updating, change URL and method
        if ($('#classId').val()) {
            url = "{{ url('classes/update') }}/" + $('#classId').val();
            method = "PUT";
        }
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                // Hide form
                $('#classFormContainer').hide();
                
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
                        text: 'Something went wrong!'
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
        let classId = $(this).data('id');
        
        // Show loader on button
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        $.ajax({
            url: "{{ url('classes/edit') }}/" + classId,
            type: "GET",
            success: function(response) {
                // Fill form with data
                $('#classId').val(response.id);
                $('#name').val(response.name);
                $('#session_id').val(response.session_id);
                $('#status').val(response.status);
                $('#description').val(response.description);
                
                // Show form
                $('#classFormContainer').show();
                $('html, body').animate({
                    scrollTop: $('#classFormContainer').offset().top
                }, 500);
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load class data!'
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
        let classId = $(this).data('id');
        
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
                    url: "{{ url('classes/delete') }}/" + classId,
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