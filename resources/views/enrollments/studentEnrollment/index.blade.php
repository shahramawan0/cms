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
                                <i class="fas fa-user-graduate text-white"></i> Student Enrollments
                            </h3>
                        </div>
                        <div>
                            <button id="addEnrollmentBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Enroll Student
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Enrollment Form (Initially Hidden) -->
                <div class="card-body" id="enrollmentFormContainer" style="display: none;">
                    <form id="enrollmentForm">
                        @csrf
                        <input type="hidden" name="id" id="enrollment_id">
                        <div class="row">
                            @if(auth()->user()->hasRole('Super Admin'))
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="institute_id">Institute <span class="text-danger">*</span></label>
                                    <select name="institute_id" id="institute_id" class="form-control select2" required>
                                        <option value="">Select Institute</option>
                                        @foreach($institutes as $institute)
                                            <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="institute_id_error"></div>
                                </div>
                            </div>
                            @else
                                <input type="hidden" name="institute_id" id="institute_id" value="{{ auth()->user()->institute_id }}">
                            @endif
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="student_id">Student <span class="text-danger">*</span></label>
                                    <select name="student_id" id="student_id" class="form-control select2" required>
                                        <option value="">Select Student</option>
                                        @if(!auth()->user()->hasRole('Super Admin'))
                                            @foreach(\App\Models\User::role('Student')->where('institute_id', auth()->user()->institute_id)->get() as $student)
                                                <option value="{{ $student->id }}">{{ $student->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="invalid-feedback" id="student_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="session_id">Academic Session <span class="text-danger">*</span></label>
                                    <select name="session_id" id="session_id" class="form-control select2" required>
                                        <option value="">Select Session</option>
                                    </select>
                                    <div class="invalid-feedback" id="session_id_error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="class_id">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" id="class_id" class="form-control select2" required>
                                        <option value="">Select Class</option>
                                    </select>
                                    <div class="invalid-feedback" id="class_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="section_id">Section <span class="text-danger">*</span></label>
                                    <select name="section_id" id="section_id" class="form-control select2" required>
                                        <option value="">Select Section</option>
                                    </select>
                                    <div class="invalid-feedback" id="section_id_error"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="course_id">Course <span class="text-danger">*</span></label>
                                    <select name="course_id" id="course_id" class="form-control select2" required>
                                        <option value="">Select Course</option>
                                    </select>
                                    <div class="invalid-feedback" id="course_id_error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control select2" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                    <div class="invalid-feedback" id="status_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="enrollment_date">Enrollment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="enrollment_date" id="enrollment_date" class="form-control" required>
                                    <div class="invalid-feedback" id="enrollment_date_error"></div>
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
                
                <!-- Enrollments Table -->
                <div class="card-body">
                    <table id="enrollments-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Institute</th>
                                <th>Session</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Course</th>
                                <th>Enrollment Date</th>
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
@push('styles')
 <style>
    
    </style>

@endpush
@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
        allowClear: true

        });
    });
</script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#enrollments-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('enrollments.data') }}",
        columns: [
            { data: 'student_name', name: 'student_name' },
            { data: 'institute', name: 'institute' },
            { data: 'session', name: 'session' },
            { data: 'class', name: 'class' },
            { data: 'section', name: 'section' },
            { data: 'course', name: 'course' },
            { data: 'enrollment_date', name: 'enrollment_date' },
            { data: 'status', name: 'status' },
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
    $('#addEnrollmentBtn').click(function() {
        $('#enrollmentForm')[0].reset();
        $('#enrollmentFormContainer').show();
        $('#enrollment_id').val('');
        $('#submitBtnText').text('Submit');
        $('html, body').animate({
            scrollTop: $('#enrollmentFormContainer').offset().top
        }, 500);
        
        // Set default enrollment date to today
        $('#enrollment_date').val(new Date().toISOString().split('T')[0]);
        
        // For Admin - load data immediately
        @if(!auth()->user()->hasRole('Super Admin'))
        loadInitialDataForAdmin();
        @endif
    });

    $('#cancelBtn').click(function() {
        $('#enrollmentFormContainer').hide();
    });

    // For Super Admin - load students and dropdowns when institute is selected
    @if(auth()->user()->hasRole('Super Admin'))
    $('#institute_id').change(function() {
        var instituteId = $(this).val();
        if (instituteId) {
            loadStudents(instituteId);
            loadSessions(instituteId);
            loadCourses(instituteId);
            $('#class_id, #section_id').empty().append('<option value="">Select</option>');
        } else {
            clearAllDropdowns();
        }
    });
    @endif
    
    // When session changes, load classes for that session
    $('#session_id').change(function() {
        var sessionId = $(this).val();
        var instituteId = $('#institute_id').val();

        if (sessionId && instituteId) {
            $.ajax({
                url: "{{ route('enrollments.dropdowns') }}",
                type: "GET",
                data: { 
                    institute_id: instituteId,
                    session_id: sessionId
                },
                success: function(data) {
                    $('#class_id').empty().append('<option value="">Select Class</option>');
                    if (data.classes && data.classes.length > 0) {
                        $.each(data.classes, function(key, value) {
                            $('#class_id').append(`<option value="${value.id}">${value.name}</option>`);
                        });
                    } else {
                        $('#class_id').append('<option value="">No classes found</option>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading classes:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load classes. Please check console for details.'
                    });
                }
            });
        } else {
            $('#class_id').empty().append('<option value="">Select Class</option>');
        }
    });

    // When class changes, load sections for that class
    $('#class_id').change(function() {
        var classId = $(this).val();
        var instituteId = $('#institute_id').val();

        if (classId && instituteId) {
            $.ajax({
                url: "{{ route('enrollments.dropdowns') }}",
                type: "GET",
                data: {
                    class_id: classId,
                    institute_id: instituteId
                },
                success: function(data) {
                    $('#section_id').empty().append('<option value="">Select Section</option>');
                    if (data.sections && data.sections.length > 0) {
                        $.each(data.sections, function(key, value) {
                            $('#section_id').append(`<option value="${value.id}">${value.section_name}</option>`);
                        });
                    } else {
                        $('#section_id').append('<option value="">No sections found</option>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading sections:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load sections. Please check console for details.'
                    });
                }
            });
        } else {
            $('#section_id').empty().append('<option value="">Select Section</option>');
        }
    });

    // Function to load students
    function loadStudents(instituteId, callback) {
        $.ajax({
            url: "{{ route('enrollments.students') }}",
            type: "GET",
            data: { institute_id: instituteId },
            success: function(data) {
                $('#student_id').empty().append('<option value="">Select Student</option>');
                if (data && data.length > 0) {
                    $.each(data, function(key, value) {
                        $('#student_id').append(`<option value="${value.id}">${value.name}</option>`);
                    });
                } else {
                    $('#student_id').append('<option value="">No students found</option>');
                }
                if (callback) callback();
            },
            error: function(xhr) {
                console.error('Error loading students:', xhr.responseText);
                $('#student_id').empty().append('<option value="">Error loading students</option>');
                if (callback) callback();
            }
        });
    }

    // Function to load sessions
    function loadSessions(instituteId, callback) {
        $.ajax({
            url: "{{ route('enrollments.dropdowns') }}",
            type: "GET",
            data: { institute_id: instituteId },
            success: function(data) {
                $('#session_id').empty().append('<option value="">Select Session</option>');
                if(data.sessions && data.sessions.length > 0) {
                    $.each(data.sessions, function(key, value) {
                        $('#session_id').append(`<option value="${value.id}">${value.session_name}</option>`);
                    });
                }
                if (callback) callback();
            },
            error: function(xhr) {
                console.error('Error loading sessions:', xhr.responseText);
                if (callback) callback();
            }
        });
    }

    // Function to load courses
    function loadCourses(instituteId, callback) {
        $.ajax({
            url: "{{ route('enrollments.dropdowns') }}",
            type: "GET",
            data: { institute_id: instituteId },
            success: function(data) {
                $('#course_id').empty().append('<option value="">Select Course</option>');
                if(data.courses && data.courses.length > 0) {
                    $.each(data.courses, function(key, value) {
                        $('#course_id').append(`<option value="${value.id}">${value.course_name}</option>`);
                    });
                }
                if (callback) callback();
            },
            error: function(xhr) {
                console.error('Error loading courses:', xhr.responseText);
                if (callback) callback();
            }
        });
    }

    // Function to load initial data for Admin
    function loadInitialDataForAdmin() {
        var instituteId = $('#institute_id').val();
        if (instituteId) {
            loadStudents(instituteId);
            loadSessions(instituteId);
            loadCourses(instituteId);
        }
    }

    // Function to clear all dropdowns
    function clearAllDropdowns() {
        $('#student_id, #session_id, #class_id, #section_id, #course_id').empty().append('<option value="">Select</option>');
    }

    // Form submission
    $('#enrollmentForm').submit(function(e) {
        e.preventDefault();
        
        // Show loader
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').addClass('d-none');
        $('#submitBtnLoader').removeClass('d-none');
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        var formData = $(this).serialize();
        var url = $('#enrollment_id').val()
    ? '/enrollments/update/' + $('#enrollment_id').val()
    : "{{ route('enrollments.store') }}";

        // var method = $('#enrollment_id').val() ? 'PUT' : 'POST';
        var method = 'POST'; // Always POST, Laravel will detect method via _method spoofing

        
        // If updating, add the ID to the form data
       if ($('#enrollment_id').val()) {
    formData += '&_method=PUT&id=' + $('#enrollment_id').val();
}

        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                // Hide form
                $('#enrollmentFormContainer').hide();
                
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
                    let errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $('#'+field).addClass('is-invalid');
                        $('#'+field+'_error').text(errors[field][0]);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong!'
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
        var enrollmentId = $(this).data('id');
        
        // Show loader on button
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        $.ajax({
            url: "{{ url('enrollments/edit') }}/" + enrollmentId,
            type: "GET",
            success: function(response) {
                // Reset button text
                $('.edit-btn').html('<i class="fas fa-edit"></i> Edit');
                
                // Check if we got valid response
                if (response.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to load enrollment data'
                    });
                    return;
                }

                // Populate form with enrollment data
                $('#enrollment_id').val(response.id);
                $('#student_id').val(response.student_id);
                $('#institute_id').val(response.institute_id);
                $('#enrollment_date').val(response.enrollment_date);
                $('#status').val(response.status);
                $('#submitBtnText').text('Update');
                
                // Show the form
                $('#enrollmentFormContainer').show();
                $('html, body').animate({
                    scrollTop: $('#enrollmentFormContainer').offset().top
                }, 500);
                
                // Load dependent dropdowns with current values
                loadInitialDataForEdit(response);
            },
            error: function(xhr) {
                $('.edit-btn').html('<i class="fas fa-edit"></i> Edit');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to load enrollment data'
                });
            }
        });
    });
    
    // Function to load initial data for edit
    function loadInitialDataForEdit(enrollmentData) {
        var instituteId = enrollmentData.institute_id;
        
        // Load students
        loadStudents(instituteId, function() {
            $('#student_id').val(enrollmentData.student_id);
        });
        
        // Load sessions
        loadSessions(instituteId, function() {
            $('#session_id').val(enrollmentData.session_id);
            
            // Load classes for this session
            $.ajax({
                url: "{{ route('enrollments.dropdowns') }}",
                type: "GET",
                data: { 
                    institute_id: instituteId,
                    session_id: enrollmentData.session_id
                },
                success: function(data) {
                    $('#class_id').empty().append('<option value="">Select Class</option>');
                    if (data.classes && data.classes.length > 0) {
                        $.each(data.classes, function(key, value) {
                            $('#class_id').append(`<option value="${value.id}">${value.name}</option>`);
                        });
                        $('#class_id').val(enrollmentData.class_id);
                        
                        // Load sections for this class
                        $.ajax({
                            url: "{{ route('enrollments.dropdowns') }}",
                            type: "GET",
                            data: {
                                class_id: enrollmentData.class_id,
                                institute_id: instituteId
                            },
                            success: function(data) {
                                $('#section_id').empty().append('<option value="">Select Section</option>');
                                if (data.sections && data.sections.length > 0) {
                                    $.each(data.sections, function(key, value) {
                                        $('#section_id').append(`<option value="${value.id}">${value.section_name}</option>`);
                                    });
                                }
                                $('#section_id').val(enrollmentData.section_id);
                            }
                        });
                    }
                }
            });
        });
        
        // Load courses
        loadCourses(instituteId, function() {
            $('#course_id').val(enrollmentData.course_id);
        });
    }

    // Delete button click
    $(document).on('click', '.delete-btn', function() {
        let enrollmentId = $(this).data('id');
        let $button = $(this);
        
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
                $button.html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                
                $.ajax({
                    url: "{{ url('enrollments/delete') }}/" + enrollmentId,
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
                        // Reset button text
                        $button.html('<i class="fas fa-trash"></i> Delete');
                    }
                });
            }
        });
    });
});
</script>
@endpush