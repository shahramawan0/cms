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
                                <i class="fas fa-calendar-alt text-white"></i> Sessions
                            </h3>
                        </div>
                        <div>
                            <button id="addSessionBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Add Session
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Session Form (Initially Hidden) -->
                <div class="card-body" id="sessionFormContainer" style="display: none;">
                    <form id="sessionForm">
                        @csrf
                        <input type="hidden" id="sessionId" name="id">
                        <div class="row">
                            @if(auth()->user()->hasRole('Super Admin'))
                                <div class="col-md-3">
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
                            @else
                                <input type="hidden" name="institute_id" value="{{ auth()->user()->institute_id }}">
                            @endif
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="session_name">Session Name <span class="text-danger">*</span></label>
                                    <input type="text" name="session_name" id="session_name" 
                                           class="form-control" required>
                                    <div class="invalid-feedback" id="session_name_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" 
                                           class="form-control" required>
                                    <div class="invalid-feedback" id="start_date_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" 
                                           class="form-control" required>
                                    <div class="invalid-feedback" id="end_date_error"></div>
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
                
                <!-- Sessions Table -->
                <div class="card-body" style="border-top:1px solid #000">
                    <table id="sessions-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                @if(auth()->user()->hasRole('Super Admin'))
                                <th>Institute</th>
                                @endif
                                <th>Session Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
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
    var table = $('#sessions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('sessions.data') }}",
        columns: [
            { data: 'id', name: 'id' },
            @if(auth()->user()->hasRole('Super Admin'))
            { data: 'institute.name', name: 'institute.name' },
            @endif
            { data: 'session_name', name: 'session_name' },
            { data: 'start_date', name: 'start_date' },
            { data: 'end_date', name: 'end_date' },
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
    $('#addSessionBtn').click(function() {
        $('#sessionForm')[0].reset();
        $('#sessionId').val('');
        $('#sessionFormContainer').show();
        $('html, body').animate({
            scrollTop: $('#sessionFormContainer').offset().top
        }, 500);
    });

    $('#cancelBtn').click(function() {
        $('#sessionFormContainer').hide();
    });

    // Form submission
    $('#sessionForm').submit(function(e) {
        e.preventDefault();
        
        // Show loader
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').addClass('d-none');
        $('#submitBtnLoader').removeClass('d-none');
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        let formData = $(this).serialize();
        let url = "{{ route('sessions.store') }}";
        let method = "POST";
        
        // If updating, change URL and method
        if ($('#sessionId').val()) {
            url = "{{ url('sessions/update') }}/" + $('#sessionId').val();
            method = "PUT";
        }
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                // Hide form
                $('#sessionFormContainer').hide();
                
                // Show success toast
                Toast.fire({
                    icon: 'success',
                    title: response.message
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
                    Toast.fire({
                        icon: 'error',
                        title: 'Something went wrong!'
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
        let sessionId = $(this).data('id');
        
        // Show loader on button
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        $.ajax({
            url: "{{ url('sessions/edit') }}/" + sessionId,
            type: "GET",
            success: function(response) {
                // Fill form with data
                $('#sessionId').val(response.id);
                $('#session_name').val(response.session_name);
                $('#start_date').val(response.start_date);
                $('#end_date').val(response.end_date);
                $('#description').val(response.description);
                
                @if(auth()->user()->hasRole('Super Admin'))
                $('#institute_id').val(response.institute_id);
                @endif
                
                // Show form
                $('#sessionFormContainer').show();
                $('html, body').animate({
                    scrollTop: $('#sessionFormContainer').offset().top
                }, 500);
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to load session data!'
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
        let sessionId = $(this).data('id');
        
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
                    url: "{{ url('sessions/delete') }}/" + sessionId,
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