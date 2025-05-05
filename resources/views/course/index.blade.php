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
                                <i class="fas fa-book text-white"></i> Courses Management
                            </h3>
                        </div>
                        <div>
                            <button id="addCourseBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Add Course
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Course Form (Initially Hidden) -->
                <div class="card-body" id="courseFormContainer" style="display: none;">
                    <form id="courseForm">
                        @csrf
                        <input type="hidden" id="courseId" name="id">
                        
                        <!-- Institute Information -->
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
                            @else
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Institute</label>
                                    <input type="text" class="form-control" value="{{ auth()->user()->institute->name }}" readonly>
                                    <input type="hidden" name="institute_id" value="{{ auth()->user()->institute_id }}">
                                </div>
                            </div>
                            @endif
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="course_name">Course Name <span class="text-danger">*</span></label>
                                    <input type="text" name="course_name" id="course_name" class="form-control" required>
                                    <div class="invalid-feedback" id="course_name_error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Course Details -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="course_code">Course Code</label>
                                    <input type="text" name="course_code" id="course_code" class="form-control">
                                    <div class="invalid-feedback" id="course_code_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="book_name">Book Name</label>
                                    <input type="text" name="book_name" id="book_name" class="form-control">
                                    <div class="invalid-feedback" id="book_name_error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Level and Language -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="level">Level <span class="text-danger">*</span></label>
                                    <select name="level" id="level" class="form-control" required>
                                        <option value="">Select Level</option>
                                        <option value="Beginner">Beginner</option>
                                        <option value="Intermediate">Intermediate</option>
                                        <option value="Advanced">Advanced</option>
                                    </select>
                                    <div class="invalid-feedback" id="level_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="language">Language <span class="text-danger">*</span></label>
                                    <select name="language" id="language" class="form-control" required>
                                        <option value="Urdu">Urdu</option>
                                        <option value="Arabic">Arabic</option>
                                        <option value="English">English</option>
                                        <option value="Urdu/English">Urdu/English</option>
                                    </select>
                                    <div class="invalid-feedback" id="language_error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Duration and Status -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duration_months">Duration (Months)</label>
                                    <input type="number" name="duration_months" id="duration_months" class="form-control" min="1">
                                    <div class="invalid-feedback" id="duration_months_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active">Status <span class="text-danger">*</span></label>
                                    <select name="is_active" id="is_active" class="form-control" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback" id="is_active_error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Date Range -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control">
                                    <div class="invalid-feedback" id="start_date_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control">
                                    <div class="invalid-feedback" id="end_date_error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
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
                
                <!-- Courses Table -->
                <div class="card-body">
                    <table id="courses-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Course Code</th>
                                <th>Institute</th>
                                <th>Level</th>
                                <th>Language</th>
                                <th>Duration</th>
                                <th>Date Range</th>
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
    var table = $('#courses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.courses.data') }}",
        columns: [
            { data: 'course_name', name: 'course_name' },
            { data: 'course_code', name: 'course_code' },
            { data: 'institute', name: 'institute.name' },
            { data: 'level', name: 'level' },
            { data: 'language', name: 'language' },
            { data: 'duration', name: 'duration_months' },
            { data: 'date_range', name: 'date_range' },
            { data: 'status', name: 'is_active' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        autoWidth: false,
        scrollX: true,
        language: {
            paginate: {
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>'
            }
        }
    });

    // Show/hide form
    $('#addCourseBtn').click(function() {
        $('#courseForm')[0].reset();
        $('#courseId').val('');
        $('#courseFormContainer').show();
        $('html, body').animate({
            scrollTop: $('#courseFormContainer').offset().top
        }, 500);
    });

    $('#cancelBtn').click(function() {
        $('#courseFormContainer').hide();
    });

    // Set end date min based on start date
    $('#start_date').change(function() {
        $('#end_date').attr('min', $(this).val());
    });

    // Form submission
    $('#courseForm').submit(function(e) {
        e.preventDefault();
        
        // Show loader
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').addClass('d-none');
        $('#submitBtnLoader').removeClass('d-none');
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        let formData = $(this).serialize();
        let url = "{{ route('admin.courses.store') }}";
        let method = "POST";
        
        // If updating, change URL and method
        if ($('#courseId').val()) {
            url = "{{ url('admin/courses/update') }}/" + $('#courseId').val();
            method = "PUT";
        }
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                // Hide form
                $('#courseFormContainer').hide();
                
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
        let courseId = $(this).data('id');
        
        // Show loader on button
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        $.ajax({
            url: "{{ url('admin/courses/edit') }}/" + courseId,
            type: "GET",
            success: function(response) {
                // Fill form with data
                $('#courseId').val(response.id);
                $('#course_name').val(response.course_name);
                $('#course_code').val(response.course_code);
                $('#book_name').val(response.book_name);
                $('#level').val(response.level);
                $('#language').val(response.language);
                $('#duration_months').val(response.duration_months);
                $('#is_active').val(response.is_active ? '1' : '0');
                $('#start_date').val(response.start_date ? response.start_date.split('T')[0] : '');
                $('#end_date').val(response.end_date ? response.end_date.split('T')[0] : '');
                $('#description').val(response.description);
                
                // For Super Admin, set institute
                @if(auth()->user()->hasRole('Super Admin'))
                $('#institute_id').val(response.institute_id);
                @endif
                
                // Show form
                $('#courseFormContainer').show();
                $('html, body').animate({
                    scrollTop: $('#courseFormContainer').offset().top
                }, 500);
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load course data!'
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
        let courseId = $(this).data('id');
        
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
                    url: "{{ url('admin/courses/delete') }}/" + courseId,
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