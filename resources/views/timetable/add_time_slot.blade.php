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
                                <i class="fas fa-clock text-white"></i> Class Slot Management
                            </h3>
                        </div>
                        <div>
                            <button id="addSlotBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Add Slot
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Slot Form (Initially Hidden) -->
                <div class="card-body" id="slotFormContainer" style="display: none;">
                    <form id="classSlotForm">
                        @csrf
                        <input type="hidden" id="slotId" name="id">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="session_id">Session <span class="text-danger">*</span></label>
                                    <select class="form-control" id="session_id" name="session_id" required>
                                        <option value="">Select Session</option>
                                        @foreach($sessions as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="session_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_time">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="start_time" name="start_time" required>
                                    <div class="invalid-feedback" id="start_time_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_time">End Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="end_time" name="end_time" required>
                                    <div class="invalid-feedback" id="end_time_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="break_start_time">Break Start <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="break_start_time" name="break_start_time" required>
                                    <div class="invalid-feedback" id="break_start_time_error"></div>
                                </div>
                            </div>
                           
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="week_number">Week Number</label>
                                    <select class="form-control" id="week_number" name="week_number">
                                        <option value="">All Weeks</option>
                                        <option value="1">Week 1</option>
                                        <option value="2">Week 2</option>
                                        <option value="3">Week 3</option>
                                        <option value="4">Week 4</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="break_end_time">Break End <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="break_end_time" name="break_end_time" required>
                                    <div class="invalid-feedback" id="break_end_time_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="duration">Duration <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="slot_duration" name="slot_duration" min="5" max="120" required>
                                    <div class="invalid-feedback" id="duration_error"></div>
                                </div>
                            </div>
                           
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-10 d-flex align-items-end">
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-sm me-1">
                                    <span id="submitBtnText">Submit</span>
                                    <span id="submitBtnLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                                <button type="button" id="cancelBtn" class="btn btn-secondary ml-2 btn-sm">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Filter Form -->
                <div class="card-body border-top" style="border-top:1px solid #000">
                    <form id="filterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_session">Filter by Session</label>
                                    <select class="form-control" id="filter_session" name="session_id">
                                        <option value="">All Sessions</option>
                                        @foreach($sessions as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_week">Filter by Week</label>
                                    <select class="form-control" id="filter_week" name="week_number">
                                        <option value="">All Weeks</option>
                                        <option value="1">Week 1</option>
                                        <option value="2">Week 2</option>
                                        <option value="3">Week 3</option>
                                        <option value="4">Week 4</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" id="applyFilterBtn" class="btn btn-info btn-sm">
                                    <i class="fas fa-filter"></i> Apply Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Slots Table -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="classSlotsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Session</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Break Time</th>
                                    <th>Duration</th>
                                    <th>Week</th>
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
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#classSlotsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('class-slots.list') }}",
            type: "GET",
            data: function(d) {
                d.session_id = $('#filter_session').val();
                d.week_number = $('#filter_week').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'session_name', name: 'session_name' },
            { data: 'start_time', name: 'start_time' },
            { data: 'end_time', name: 'end_time' },
            { data: 'break_time', name: 'break_time' },
            { data: 'slot_duration', name: 'slot_duration' },
            { data: 'week_number', name: 'week_number' },
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
    $('#addSlotBtn').click(function() {
        $('#slotFormContainer').show();
        $('#classSlotForm')[0].reset();
        $('#slotId').val('');
        $('html, body').animate({
            scrollTop: $('#slotFormContainer').offset().top
        }, 500);
    });

    $('#cancelBtn').click(function() {
        $('#slotFormContainer').hide();
    });

    // Apply filters
    $('#applyFilterBtn').click(function() {
        table.ajax.reload();
    });

    // Form submission
    $('#classSlotForm').submit(function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Show loading
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').addClass('d-none');
        $('#submitBtnLoader').removeClass('d-none');
        
        var formData = $(this).serialize();
        var url = $('#slotId').val() 
            ? "{{ url('class-slots') }}/" + $('#slotId').val()
            : "{{ route('class-slots.store') }}";
        var method = $('#slotId').val() ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                $('#slotFormContainer').hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Failed to save slot'
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

    // Edit slot
    $(document).on('click', '.edit-class-slot', function() {
        var slotId = $(this).data('id');
        var $button = $(this);
        
        $button.html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: "{{ url('class-slots') }}/" + slotId,
            type: "GET",
            success: function(response) {
                $('#slotId').val(response.id);
                $('#session_id').val(response.session_id);
                $('#start_time').val(response.start_time);
                $('#end_time').val(response.end_time);
                $('#break_start_time').val(response.break_start_time);
                $('#break_end_time').val(response.break_end_time);
                $('#slot_duration').val(response.slot_duration);
                $('#week_number').val(response.week_number);
                
                $('#slotFormContainer').show();
                $('html, body').animate({
                    scrollTop: $('#slotFormContainer').offset().top
                }, 500);
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load slot details'
                });
            },
            complete: function() {
                $button.html('<i class="fas fa-edit"></i>');
            }
        });
    });
    
    // Delete slot
    $(document).on('click', '.delete-class-slot', function() {
        var slotId = $(this).data('id');
        var $button = $(this);
        
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
                $button.html('<i class="fas fa-spinner fa-spin"></i>');
                
                $.ajax({
                    url: "{{ url('class-slots') }}/" + slotId,
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
                            xhr.responseJSON?.message || 'Something went wrong while deleting.',
                            'error'
                        );
                    },
                    complete: function() {
                        $button.html('<i class="fas fa-trash"></i>');
                    }
                });
            }
        });
    });
});
</script>
@endpush