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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="session_id">Session <span class="text-danger">*</span></label>
                                    <select name="session_id" id="session_id" class="form-control" required>
                                        <option value="">Select Session</option>
                                        @foreach($sessions as $session)
                                            <option value="{{ $session->id }}" {{ isset($activeSession) && $activeSession->id == $session->id ? 'selected' : '' }}>
                                                {{ $session->session_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="session_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Class Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control" required>
                                    <div class="invalid-feedback" id="name_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="background_color">Class Color <span class="text-danger">*</span></label>
                                    <input type="color" name="background_color" id="background_color" 
                                           class="form-control form-control-color w-100" 
                                           value="#3490dc" required
                                           title="Choose a color for the class">
                                    <div class="invalid-feedback" id="background_color_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" 
                                              class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                       
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-sm">
                                    <span id="submitBtnText">Submit</span>
                                    <span id="submitBtnLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                                <button type="button" id="cancelBtn" class="btn btn-secondary btn-sm">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Classes Table -->
                <div class="card-body" style="border-top:1px solid #000">
                    <table id="classes-table" class="table table-striped">
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
    // Initialize Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // Initialize DataTable
    var table = $('#classes-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('classes.data') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'session.session_name', name: 'session.session_name' },
            { data: 'name_with_background_color', name: 'name' },
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
        $('#background_color').val('#3490dc'); // Reset color to default
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
        
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').addClass('d-none');
        $('#submitBtnLoader').removeClass('d-none');
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        let formData = $(this).serialize();
        let url = "{{ route('classes.store') }}";
        let method = "POST";
        
        if ($('#classId').val()) {
            url = "{{ url('classes/update') }}/" + $('#classId').val();
            method = "PUT";
        }
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                $('#classFormContainer').hide();
                
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $('#'+field).addClass('is-invalid');
                        $('#'+field+'_error').text(errors[field][0]);
                    }
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Something went wrong!'
                    });
                }
            },
            complete: function() {
                $('#submitBtn').prop('disabled', false);
                $('#submitBtnText').removeClass('d-none');
                $('#submitBtnLoader').addClass('d-none');
            }
        });
    });

    // Edit button click
    $(document).on('click', '.edit-btn', function() {
        let classId = $(this).data('id');
        
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        $.ajax({
            url: "{{ url('classes/edit') }}/" + classId,
            type: "GET",
            success: function(response) {
                $('#classId').val(response.id);
                $('#name').val(response.name);
                $('#session_id').val(response.session_id);
                $('#description').val(response.description);
                $('#background_color').val(response.background_color || '#3490dc');
                
                $('#classFormContainer').show();
                $('html, body').animate({
                    scrollTop: $('#classFormContainer').offset().top
                }, 500);
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to load class data!'
                });
            },
            complete: function() {
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
                $(this).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                
                $.ajax({
                    url: "{{ url('classes/delete') }}/" + classId,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Something went wrong while deleting.'
                        });
                    },
                    complete: function() {
                        $('.delete-btn').html('<i class="fas fa-trash"></i> Delete');
                    }
                });
            }
        });
    });
});
</script>

<style>
.form-control-color {
    height: 38px;
    padding: 0.375rem;
}

.color-box {
    border: 1px solid #dee2e6;
}
</style>
@endpush