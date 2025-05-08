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
                                <i class="fas fa-chalkboard-teacher text-white"></i> Teacher Course Assignments
                            </h3>
                        </div>
                        <div>
                            <button id="addAssignmentBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Assign Teacher
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Assignment Form (Initially Hidden) -->
                <div class="card-body" id="assignmentFormContainer" style="display: none;">
                    <form id="assignmentForm">
                        @csrf
                        <input type="hidden" name="id" id="assignment_id">
                        
                        <div class="row">
                            @if(auth()->user()->hasRole('Super Admin'))
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="institute_id">Institute <span class="text-danger">*</span></label>
                                    <select name="institute_id" id="institute_id" class="form-control" required>
                                        <option value="">Select Institute</option>
                                        @foreach($institutes as $institute)
                                            <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @else
                                <input type="hidden" name="institute_id" id="institute_id" value="{{ auth()->user()->institute_id }}">
                            @endif
                            
                            @if(auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin'))
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="teacher_id">Teacher <span class="text-danger">*</span></label>
                                    <select name="teacher_id" id="teacher_id" class="form-control" required>
                                        <option value="">Select Teacher</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @else
                                <input type="hidden" name="teacher_id" id="teacher_id" value="{{ auth()->user()->id }}">
                            @endif
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="session_id">Session <span class="text-danger">*</span></label>
                                    <select name="session_id" id="session_id" class="form-control" required>
                                        <option value="">Select Session</option>
                                        @foreach($sessions as $session)
                                            <option value="{{ $session->id }}">{{ $session->session_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Courses Table (Will be shown after session selection) -->
                        <div class="row mt-3" id="coursesTableContainer" style="display: none;">
                            <div class="col-md-12">
                                <button type="button" id="assignSelectedBtn" class="btn btn-primary mb-3" style="display: none;">
                                    <i class="fas fa-user-plus"></i> Assign Selected Courses
                                </button>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="coursesTable">
                                        <thead>
                                            <tr>
                                                <th width="5%"><input type="checkbox" id="selectAll"></th>
                                                <th>Course</th>
                                                <th>Class</th>
                                                <th>Section</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Will be populated via AJAX -->
                                        </tbody>
                                    </table>
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
                
                <!-- Assignments Table -->
                <div class="card-body">
                    @if(auth()->user()->hasRole('Super Admin'))
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="filter_institute">Filter by Institute</label>
                                <select name="filter_institute" id="filter_institute" class="form-control">
                                    <option value="">All Institutes</option>
                                    @foreach($institutes as $institute)
                                        <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <table id="assignments-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Teacher</th>
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

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var assignmentsTable = $('#assignments-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('teacher.enrollments.data') }}",
            data: function(d) {
                @if(auth()->user()->hasRole('Super Admin'))
                d.institute_id = $('#filter_institute').val();
                @endif
            }
        },
        columns: [
            { data: 'teacher_name', name: 'teacher_name' },
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

    @if(auth()->user()->hasRole('Super Admin'))
    $('#filter_institute').change(function() {
        assignmentsTable.ajax.reload();
    });
    @endif

    // Show/hide form
    $('#addAssignmentBtn').click(function() {
        $('#assignmentForm')[0].reset();
        $('#assignmentFormContainer').show();
        $('#coursesTableContainer').hide();
        $('#assignSelectedBtn').hide();
        $('#assignment_id').val('');
        $('#submitBtnText').text('Submit');
    });

    $('#cancelBtn').click(function() {
        $('#assignmentFormContainer').hide();
    });

    // When session changes, load courses and teachers
    $('#session_id').change(function() {
        var sessionId = $(this).val();
        var instituteId = $('#institute_id').val();
        
        if (sessionId) {
            loadSessionData(sessionId, instituteId);
        } else {
            $('#coursesTableContainer').hide();
            $('#assignSelectedBtn').hide();
        }
    });

    // For Super Admin - load teachers when institute changes
    @if(auth()->user()->hasRole('Super Admin'))
    $('#institute_id').change(function() {
        var instituteId = $(this).val();
        if (instituteId) {
            loadTeachers(instituteId);
        } else {
            $('#teacher_id').empty().append('<option value="">Select Teacher</option>');
        }
    });
    @endif

    function loadTeachers(instituteId) {
        $.ajax({
            url: "{{ route('teacher.enrollments.session-data') }}",
            type: "GET",
            data: { institute_id: instituteId },
            success: function(response) {
                if (response.success && response.teachers) {
                    $('#teacher_id').empty().append('<option value="">Select Teacher</option>');
                    $.each(response.teachers, function(key, teacher) {
                        $('#teacher_id').append(`<option value="${teacher.id}">${teacher.name}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Error loading teachers:', xhr.responseText);
            }
        });
    }

    function loadSessionData(sessionId, instituteId) {
        var data = { session_id: sessionId };
        if (instituteId) {
            data.institute_id = instituteId;
        }

        $.ajax({
            url: "{{ route('teacher.enrollments.session-data') }}",
            type: "GET",
            data: data,
            beforeSend: function() {
                $('#submitBtn').prop('disabled', true);
                $('#submitBtnText').addClass('d-none');
                $('#submitBtnLoader').removeClass('d-none');
            },
            success: function(response) {
                if (response.success) {
                    // Populate courses table
                    var tbody = $('#coursesTable tbody');
                    tbody.empty();
                    
                    if (response.courses && response.courses.length > 0) {
                        $.each(response.courses, function(key, course) {
                            var row = `
                                <tr>
                                    <td><input type="checkbox" class="course-checkbox" 
                                        data-course-id="${course.course_id}"
                                        data-class-id="${course.class_id}"
                                        data-section-id="${course.section_id}"></td>
                                    <td>${course.course_name}</td>
                                    <td>${course.class_name}</td>
                                    <td>${course.section_name}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary assign-btn" 
                                            data-course-id="${course.course_id}"
                                            data-class-id="${course.class_id}"
                                            data-section-id="${course.section_id}">
                                            <i class="fas fa-user-plus"></i> Assign
                                        </button>
                                    </td>
                                </tr>
                            `;
                            tbody.append(row);
                        });
                        $('#coursesTableContainer').show();
                        $('#assignSelectedBtn').show();
                    } else {
                        tbody.append('<tr><td colspan="5" class="text-center">No unassigned courses found for this session</td></tr>');
                        $('#coursesTableContainer').show();
                        $('#assignSelectedBtn').hide();
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to load data'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to load data'
                });
            },
            complete: function() {
                $('#submitBtn').prop('disabled', false);
                $('#submitBtnText').removeClass('d-none');
                $('#submitBtnLoader').addClass('d-none');
            }
        });
    }

    // Select all checkboxes
    $('#selectAll').click(function() {
        $('.course-checkbox').prop('checked', this.checked);
    });

    // Assign selected courses button click
    $('#assignSelectedBtn').click(function() {
        var teacherId = $('#teacher_id').val();
        var sessionId = $('#session_id').val();
        var instituteId = $('#institute_id').val();
        
        if (!teacherId || !sessionId) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select both teacher and session first'
            });
            return;
        }

        var selectedCourses = [];
        var courseIds = [];
        var classIds = [];
        var sectionIds = [];
        
        $('.course-checkbox:checked').each(function() {
            var courseId = $(this).data('course-id');
            var classId = $(this).data('class-id');
            var sectionId = $(this).data('section-id');
            
            selectedCourses.push(`Course: ${$(this).closest('tr').find('td:eq(1)').text()}, Class: ${$(this).closest('tr').find('td:eq(2)').text()}, Section: ${$(this).closest('tr').find('td:eq(3)').text()}`);
            courseIds.push(courseId);
            classIds.push(classId);
            sectionIds.push(sectionId);
        });

        if (selectedCourses.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select at least one course to assign'
            });
            return;
        }

        Swal.fire({
            title: 'Confirm Assignment',
            html: `You are about to assign the teacher to ${selectedCourses.length} courses:<br><br>` + 
                  selectedCourses.join('<br>'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, assign them',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).html('<i class="fas fa-spinner fa-spin"></i> Assigning...').prop('disabled', true);
                
                $.ajax({
                    url: "{{ route('teacher.enrollments.store') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        teacher_id: teacherId,
                        session_id: sessionId,
                        course_ids: courseIds,
                        class_ids: classIds,
                        section_ids: sectionIds
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                html: response.message,
                                timer: 3000,
                                showConfirmButton: false
                            });
                            
                            // Reload both tables
                            assignmentsTable.ajax.reload(null, false);
                            loadSessionData(sessionId, instituteId); // Refresh the courses table
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Something went wrong!'
                        });
                    },
                    complete: function() {
                        $('#assignSelectedBtn').html('<i class="fas fa-user-plus"></i> Assign Selected Courses').prop('disabled', false);
                    }
                });
            }
        });
    });

    // Assign teacher button click (single course)
    $(document).on('click', '.assign-btn', function() {
        var teacherId = $('#teacher_id').val();
        var sessionId = $('#session_id').val();
        var instituteId = $('#institute_id').val();
        var courseId = $(this).data('course-id');
        var classId = $(this).data('class-id');
        var sectionId = $(this).data('section-id');
        
        if (!teacherId || !sessionId) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select both teacher and session first'
            });
            return;
        }
        
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Assigning...').prop('disabled', true);
        
        $.ajax({
            url: "{{ route('teacher.enrollments.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                teacher_id: teacherId,
                session_id: sessionId,
                course_ids: [courseId],
                class_ids: [classId],
                section_ids: [sectionId]
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Reload both tables
                    assignmentsTable.ajax.reload(null, false);
                    loadSessionData(sessionId, instituteId); // Refresh the courses table
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Something went wrong!'
                });
            },
            complete: function() {
                $('.assign-btn').html('<i class="fas fa-user-plus"></i> Assign').prop('disabled', false);
            }
        });
    });

    // Unassign button click
    $(document).on('click', '.unassign-btn', function() {
        let sessionId = $(this).data('session-id');
        let courseId = $(this).data('course-id');
        let classId = $(this).data('class-id');
        let sectionId = $(this).data('section-id');
        let teacherId = $(this).data('teacher-id');
        let $button = $(this);
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, unassign it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loader on button
                $button.html('<i class="fas fa-spinner fa-spin"></i> Unassigning...');
                
                $.ajax({
                    url: "{{ route('teacher.enrollments.unassign') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        session_id: sessionId,
                        course_id: courseId,
                        class_id: classId,
                        section_id: sectionId,
                        teacher_id: teacherId
                    },
                    success: function(response) {
                        Swal.fire(
                            'Unassigned!',
                            response.message,
                            'success'
                        );
                        assignmentsTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON?.message || 'Something went wrong while unassigning.',
                            'error'
                        );
                    },
                    complete: function() {
                        // Reset button text
                        $button.html('<i class="fas fa-trash"></i> Unassign');
                    }
                });
            }
        });
    });
});
</script>
@endpush