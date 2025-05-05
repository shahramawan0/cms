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
                                <i class="fas fa-layer-group text-white"></i> Sections
                            </h3>
                        </div>
                        <div>
                            <button id="addSectionBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Add Section
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Section Form (Initially Hidden) -->
                <div class="card-body" id="sectionFormContainer" style="display: none;">
                    <form id="sectionForm">
                        @csrf
                        <input type="hidden" id="sectionId" name="id">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="class_id">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" id="class_id" class="form-control" required>
                                        <option value="">Select Class</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="class_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="section_name">Section Name <span class="text-danger">*</span></label>
                                    <input type="text" name="section_name" id="section_name" 
                                           class="form-control" required>
                                    <div class="invalid-feedback" id="section_name_error"></div>
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
                
                <!-- Sections Table -->
                <div class="card-body">
                    <table id="sections-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Class</th>
                                <th>Section Name</th>
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
    var table = $('#sections-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('sections.data') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'class.name', name: 'class.name' },
            { data: 'section_name', name: 'section_name' },
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
    $('#addSectionBtn').click(function() {
        $('#sectionForm')[0].reset();
        $('#sectionId').val('');
        $('#sectionFormContainer').show();
        $('html, body').animate({
            scrollTop: $('#sectionFormContainer').offset().top
        }, 500);
    });

    $('#cancelBtn').click(function() {
        $('#sectionFormContainer').hide();
    });

    // Form submission
    $('#sectionForm').submit(function(e) {
        e.preventDefault();
        
        // Show loader
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').addClass('d-none');
        $('#submitBtnLoader').removeClass('d-none');
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        let formData = $(this).serialize();
        let url = "{{ route('sections.store') }}";
        let method = "POST";
        
        // If updating, change URL and method
        if ($('#sectionId').val()) {
            url = "{{ url('sections/update') }}/" + $('#sectionId').val();
            method = "PUT";
        }
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                // Hide form
                $('#sectionFormContainer').hide();
                
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
        let sectionId = $(this).data('id');
        
        // Show loader on button
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        $.ajax({
            url: "{{ url('sections/edit') }}/" + sectionId,
            type: "GET",
            success: function(response) {
                // Fill form with data
                $('#sectionId').val(response.id);
                $('#section_name').val(response.section_name);
                $('#class_id').val(response.class_id);
                $('#status').val(response.status);
                $('#description').val(response.description);
                
                // Show form
                $('#sectionFormContainer').show();
                $('html, body').animate({
                    scrollTop: $('#sectionFormContainer').offset().top
                }, 500);
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load section data!'
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
        let sectionId = $(this).data('id');
        
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
                    url: "{{ url('sections/delete') }}/" + sectionId,
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