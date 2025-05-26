@extends('layouts.app')

@section('content')
<!-- Add CSRF Token meta tag -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div>
                            <h3 class="card-title mb-0 text-white">
                                <i class="fas fa-user-check"></i> Attendance Management
                            </h3>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="row g-0">
                        <!-- Course Selection Sidebar -->
                        <div class="col-md-4 border-end">
                            <div class="p-3">
                                <form id="attendanceForm">
                                    @csrf
                                    <input type="hidden" name="institute_id" id="institute_id" value="{{ auth()->user()->institute_id ?? '' }}">
                                
                                    <div class="mb-4">
                                        @if(auth()->user()->hasRole('Super Admin'))
                                        <div class="form-group mb-3">
                                            <label for="institute_select" class="form-label fw-bold">
                                                <i class="fas fa-university"></i> Institute
                                            </label>
                                            <select name="institute_select" id="institute_select" class="form-control form-select" required>
                                                <option value="">Select Institute</option>
                                                @foreach($institutes as $institute)
                                                    <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif
                            
                                        <div class="form-group mb-3">
                                            <label for="session_id" class="form-label fw-bold">
                                                <i class="fas fa-calendar-alt"></i> Academic Session
                                            </label>
                                            <select name="session_id" id="session_id" class="form-control form-select" required>
                                                <option value="">Select Session</option>
                                            </select>
                                        </div>
                                    </div>
                        
                                    <h5 class="mb-3"><i class="fas fa-book"></i> Available Courses</h5>
                                    <div id="courseListContainer">
                                        <div class="list-group" id="courseListGroup">
                                            <!-- Course list items will be loaded here -->
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Time Slots Area -->
                        <div class="col-md-8" id="timeSlotsContainer" style="display: none;">
                            <div class="p-3">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 id="selectedCourseInfo" class="mb-0"></h5>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-clock me-1"></i>Available Time Slots
                                        </h6>
                                    </div>
                                    
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Time</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="timeSlotsList">
                                                    <!-- Time slots will be loaded here -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="attendanceModalLabel">Mark Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="markAttendanceForm">
                @csrf
                <input type="hidden" name="institute_id" id="modal_institute_id">
                <input type="hidden" name="session_id" id="modal_session_id">
                <input type="hidden" name="class_id" id="modal_class_id">
                <input type="hidden" name="section_id" id="modal_section_id">
                <input type="hidden" name="course_id" id="modal_course_id">
                <input type="hidden" name="teacher_id" id="modal_teacher_id">
                <input type="hidden" name="timetable_id" id="modal_timetable_id">
                <input type="hidden" name="date" id="modal_date">
                <input type="hidden" name="slot_times" id="modal_slot_times">
                <input type="hidden" name="is_update" id="is_update">
                
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="studentsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Roll Number</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Students will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Set CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

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

    // Load sessions when institute changes
    $('#institute_select').change(function() {
        const instituteId = $(this).val();
        $('#institute_id').val(instituteId);
        if (instituteId) {
            loadSessionsAndSelectCurrent(instituteId);
        } else {
            $('#session_id').empty().append('<option value="">Select Session</option>');
            $('#courseListGroup').empty();
        }
    });

    // Load courses when session changes
    $('#session_id').change(function() {
        loadCourses();
    });

    // Function to load sessions and select current one
    function loadSessionsAndSelectCurrent(instituteId) {
        $.ajax({
            url: "{{ route('attendances.dropdowns') }}",
            type: "GET",
            data: { institute_id: instituteId },
            success: function(data) {
                $('#session_id').empty().append('<option value="">Select Session</option>');
                
                if(data.sessions && data.sessions.length > 0) {
                    let currentSessionId = null;
                    const now = new Date();
                    
                    data.sessions.forEach(function(session) {
                        const startDate = session.start_date ? new Date(session.start_date) : null;
                        const endDate = session.end_date ? new Date(session.end_date) : null;
                        const isCurrent = startDate && endDate && now >= startDate && now <= endDate;
                        
                        if (isCurrent) {
                            currentSessionId = session.id;
                        }
                        
                        $('#session_id').append(`
                            <option value="${session.id}" ${isCurrent ? 'selected' : ''}>
                                ${session.session_name}
                                ${isCurrent ? ' (Current)' : ''}
                            </option>
                        `);
                    });
                    
                    if (currentSessionId) {
                        $('#session_id').val(currentSessionId).trigger('change');
                        Toast.fire({
                            icon: 'success',
                            title: 'Current session loaded successfully'
                        });
                    } else {
                        Toast.fire({
                            icon: 'info',
                            title: 'No current session found'
                        });
                    }
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to load sessions'
                });
            }
        });
    }

    // Function to load courses
    function loadCourses() {
        const instituteId = $('#institute_id').val();
        const sessionId = $('#session_id').val();

        if (!sessionId) return;

        $('#courseListGroup').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading courses...</p>
            </div>
        `);

        $.get("{{ route('attendances.courses') }}", {
            institute_id: instituteId,
            session_id: sessionId
        })
        .done(function(courses) {
            $('#courseListGroup').empty();
            if (courses && courses.length > 0) {
                // Add CSS for dynamic hover and active effects
                let styleElement = document.getElementById('hover-styles');
                if (!styleElement) {
                    styleElement = document.createElement('style');
                    styleElement.id = 'hover-styles';
                    document.head.appendChild(styleElement);
                }
                
                let styles = '';
                courses.forEach((course, index) => {
                    styles += `
                        .course-item[data-course-id="${course.id}"]:hover {
                            background-color: ${course.background_color}20 !important;
                            border-color: ${course.background_color} !important;
                        }
                        .course-item[data-course-id="${course.id}"].active {
                            background-color: ${course.background_color}20 !important;
                            border-color: ${course.background_color} !important;
                            color: inherit !important;
                        }
                        .course-item[data-course-id="${course.id}"].active small {
                            color: inherit !important;
                        }
                        .course-item[data-course-id="${course.id}"].active .text-muted {
                            color: inherit !important;
                        }
                    `;
                });
                styleElement.textContent = styles;

                courses.forEach(function(course) {
                    const card = `
                        <div class="list-group-item list-group-item-action course-item" 
                            data-course-id="${course.id}"
                            data-class-id="${course.class_id}"
                            data-section-id="${course.section_id}"
                            data-teacher-id="${course.teacher_id}">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-2 fw-bold">${course.course_name}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-chalkboard-teacher"></i> ${course.teacher_name}
                                    </small>
                                </div>
                                <div>
                                    <span class="badge" style="background-color: ${course.background_color}">
                                        ${course.class_name} - ${course.section_name}
                                    </span>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#courseListGroup').append(card);
                });
            } else {
                $('#courseListGroup').html('<div class="alert alert-info">No courses found</div>');
            }
        })
        .fail(function(xhr) {
            Toast.fire({
                icon: 'error',
                title: 'Failed to load courses'
            });
        });
    }

    // Handle course item click
    $(document).on('click', '.course-item', function() {
        const courseId = $(this).data('course-id');
        const classId = $(this).data('class-id');
        const sectionId = $(this).data('section-id');
        const teacherId = $(this).data('teacher-id');

        // Update selected course info
        const courseInfo = $(this).find('h6').text();
        const courseDetails = $(this).find('small').text();
        $('#selectedCourseInfo').html(`${courseInfo}<br><small class="text-muted">${courseDetails}</small>`);

        // Remove active class from all items and add to clicked one
        $('.course-item').removeClass('active');
        $(this).addClass('active');

        // Show time slots container
        $('#timeSlotsContainer').show();

        loadTimeSlots(courseId, classId, sectionId, teacherId);
    });

    // Function to load time slots
    function loadTimeSlots(courseId, classId, sectionId, teacherId) {
        const instituteId = $('#institute_id').val();
        const sessionId = $('#session_id').val();

        $('#timeSlotsList').html(`
            <tr>
                <td colspan="4" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading time slots...</p>
                </td>
            </tr>
        `);

        $.get("{{ route('attendances.slots') }}", {
            institute_id: instituteId,
            session_id: sessionId,
            class_id: classId,
            section_id: sectionId,
            course_id: courseId,
            teacher_id: teacherId
        })
        .done(function(slots) {
            $('#timeSlotsList').empty();

            if (slots && slots.length > 0) {
                slots.forEach(function(slot) {
                    const isUploaded = slot.status === 'Uploaded';
                    const buttonClass = isUploaded ? 'btn-success' : 'btn-primary';
                    const buttonText = isUploaded ? 'Edit Attendance' : 'Mark Attendance';
                    const isDisabled = isUploaded && !slot.can_edit;
                    
                    const row = `
                        <tr>
                            <td>${slot.date}</td>
                            <td>${slot.slot_times}</td>
                            <td class="text-center">
                                <span class="badge bg-${isUploaded ? 'success' : 'warning'}">
                                    ${slot.status}
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn ${buttonClass} btn-sm mark-attendance" 
                                    data-timetable-id="${slot.id}"
                                    data-date="${slot.date}"
                                    data-slot-time="${slot.slot_times}"
                                    data-course-id="${courseId}"
                                    data-class-id="${classId}"
                                    data-section-id="${sectionId}"
                                    data-teacher-id="${teacherId}"
                                    data-is-update="${isUploaded ? '1' : '0'}"
                                    ${isDisabled ? 'disabled' : ''}>
                                    <i class="fas fa-${isUploaded ? 'edit' : 'check'}"></i> ${buttonText}
                                </button>
                            </td>
                        </tr>
                    `;
                    $('#timeSlotsList').append(row);
                });
            } else {
                $('#timeSlotsList').html(`
                    <tr>
                        <td colspan="4" class="text-center">
                            <div class="alert alert-info mb-0">No time slots available</div>
                        </td>
                    </tr>
                `);
            }
        })
        .fail(function(xhr) {
            Toast.fire({
                icon: 'error',
                title: 'Failed to load time slots'
            });
        });
    }

    // Handle Mark Attendance button click
    $(document).on('click', '.mark-attendance', function() {
        const button = $(this);
        const instituteId = $('#institute_id').val();
        const sessionId = $('#session_id').val();
        const courseId = button.data('course-id');
        const classId = button.data('class-id');
        const sectionId = button.data('section-id');
        const teacherId = button.data('teacher-id');
        const timetableId = button.data('timetable-id');
        const date = button.data('date');
        const slotTime = button.data('slot-time');
        const isUpdate = button.data('is-update') === 1;

        // Update modal title based on action
        $('#attendanceModalLabel').text(isUpdate ? 'Edit Attendance' : 'Mark Attendance');
        
        // Update submit button text
        $('#markAttendanceForm button[type="submit"]').html(
            `<i class="fas fa-${isUpdate ? 'save' : 'check-circle'}"></i> ${isUpdate ? 'Update' : 'Mark'} Attendance`
        );

        // Set modal form values
        $('#modal_institute_id').val(instituteId);
        $('#modal_session_id').val(sessionId);
        $('#modal_class_id').val(classId);
        $('#modal_section_id').val(sectionId);
        $('#modal_course_id').val(courseId);
        $('#modal_teacher_id').val(teacherId);
        $('#modal_timetable_id').val(timetableId);
        $('#modal_date').val(date);
        $('#modal_slot_times').val(slotTime);
        $('#is_update').val(isUpdate ? 1 : 0);

        // Load students in modal
        loadStudentsForAttendance(isUpdate);
    });

    // Function to load students in modal
    function loadStudentsForAttendance(isUpdate = false) {
        const formData = {
            institute_id: $('#modal_institute_id').val(),
            session_id: $('#modal_session_id').val(),
            class_id: $('#modal_class_id').val(),
            section_id: $('#modal_section_id').val(),
            course_id: $('#modal_course_id').val(),
            teacher_id: $('#modal_teacher_id').val(),
            timetable_id: $('#modal_timetable_id').val(),
            date: $('#modal_date').val(),
            slot_times: $('#modal_slot_times').val(),
            is_update: isUpdate ? 1 : 0,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        $.post("{{ route('attendances.students') }}", formData)
            .done(function(response) {
                $('#studentsTable tbody').empty();
                if (response.students && response.students.length > 0) {
                    response.students.forEach(function(student, index) {
                        const isChecked = response.is_update ? (student.status == 1) : true;
                        const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${student.roll_number || 'N/A'}</td>
                                <td>${student.name}</td>
                                <td>${student.email || 'N/A'}</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="attendances[${index}][student_enrollment_id]" value="${student.enrollment_id}">
                                        <input type="hidden" name="attendances[${index}][student_id]" value="${student.student_id}">
                                        <input type="hidden" name="attendances[${index}][status]" value="0">
                                        <input class="form-check-input attendance-toggle" type="checkbox" 
                                            name="attendances[${index}][status]" value="1" ${isChecked ? 'checked' : ''}>
                                        <label class="form-check-label">
                                            <span class="present-text ${isChecked ? '' : 'd-none'}">Present</span>
                                            <span class="absent-text ${isChecked ? 'd-none' : ''}">Absent</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        `;
                        $('#studentsTable tbody').append(row);
                    });
                    $('#attendanceModal').modal('show');
                } else {
                    Toast.fire({
                        icon: 'warning',
                        title: 'No students found'
                    });
                }
            })
            .fail(function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.error || 'Failed to load students'
                });
            });
    }

    // Handle attendance toggle
    $(document).on('change', '.attendance-toggle', function() {
        const parent = $(this).closest('.form-check');
        if ($(this).is(':checked')) {
            parent.find('.present-text').removeClass('d-none');
            parent.find('.absent-text').addClass('d-none');
        } else {
            parent.find('.present-text').addClass('d-none');
            parent.find('.absent-text').removeClass('d-none');
        }
    });

    // Handle form submission
    $('#markAttendanceForm').on('submit', function(e) {
        e.preventDefault();
        const submitButton = $(this).find('button[type="submit"]');
        const isUpdate = $('#is_update').val() === '1';
        
        submitButton.prop('disabled', true).html(
            `<i class="fas fa-spinner fa-spin"></i> ${isUpdate ? 'Updating' : 'Marking'} Attendance...`
        );
        
        const formData = new FormData(this);
        formData.append('is_update', isUpdate ? '1' : '0');
        
        $.ajax({
            url: "{{ route('attendances.mark') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                
                // Close modal
                $('#attendanceModal').modal('hide');
                
                // Reload time slots to update status
                const activeCourse = $('.course-item.active');
                if (activeCourse.length) {
                    setTimeout(() => {
                        loadTimeSlots(
                            activeCourse.data('course-id'),
                            activeCourse.data('class-id'),
                            activeCourse.data('section-id'),
                            activeCourse.data('teacher-id')
                        );
                    }, 1000);
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.error || 'Failed to mark attendance'
                });
            },
            complete: function() {
                submitButton.prop('disabled', false).html(
                    `<i class="fas fa-${isUpdate ? 'save' : 'check-circle'}"></i> ${isUpdate ? 'Update' : 'Mark'} Attendance`
                );
            }
        });
    });

    // Load initial data for non-Super Admin users
    @if(!auth()->user()->hasRole('Super Admin'))
    const instituteId = $('#institute_id').val();
    if (instituteId) {
        loadSessionsAndSelectCurrent(instituteId);
    }
    @endif
});
</script>
@endpush 