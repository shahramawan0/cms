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
                                <i class="fas fa-user-check text-white"></i> Attendance Management
                            </h3>
                        </div>
                        @if($hasAttendanceToday)
                        <div>
                            <button id="updateAttendanceBtn" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Update Today's Attendance
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="card-body border-bottom">
                    <form id="attendanceForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="institute_id" id="institute_id" value="{{ auth()->user()->institute_id ?? '' }}">
                        <input type="hidden" name="is_update" id="is_update" value="0">
                       
                        <div class="row">
                            @if(auth()->user()->hasRole('Super Admin'))
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="institute_id">Institute <span class="text-danger">*</span></label>
                                    <select name="institute_id" id="institute_idd" class="form-control" required>
                                        <option value="">Select Institute</option>
                                        @foreach($institutes as $institute)
                                            <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="institute_id_error"></div>
                                </div>
                            </div>
                            @endif
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="session_id">Academic Session <span class="text-danger">*</span></label>
                                    <select name="session_id" id="session_id" class="form-control" required>
                                        <option value="">Select Session</option>
                                    </select>
                                    <div class="invalid-feedback" id="session_id_error"></div>
                                </div>
                            </div>
                        
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="class_id">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" id="class_id" class="form-control" required>
                                        <option value="">Select Class</option>
                                    </select>
                                    <div class="invalid-feedback" id="class_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="section_id">Section <span class="text-danger">*</span></label>
                                    <select name="section_id" id="section_id" class="form-control" required>
                                        <option value="">Select Section</option>
                                    </select>
                                    <div class="invalid-feedback" id="section_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="course_id">Course <span class="text-danger">*</span></label>
                                    <select name="course_id" id="course_id" class="form-control" required>
                                        <option value="">Select Course</option>
                                    </select>
                                    <div class="invalid-feedback" id="course_id_error"></div>
                                </div>
                            </div>
                            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin'))
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="teacher_id">Teacher <span class="text-danger">*</span></label>
                                    <select name="teacher_id" id="teacher_id" class="form-control" required>
                                        <option value="">Select Teacher</option>
                                    </select>
                                    <div class="invalid-feedback" id="teacher_id_error"></div>
                                </div>
                            </div>
                            @else
                                <input type="hidden" name="teacher_id" id="teacher_id" value="{{ auth()->user()->id }}">
                            @endif
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" id="date" class="form-control" required>
                                    <div class="invalid-feedback" id="date_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="timetable_id">Class Time <span class="text-danger">*</span></label>
                                    <select name="timetable_id" id="timetable_id" class="form-control" required>
                                        <option value="">Select Time</option>
                                    </select>
                                    <div class="invalid-feedback" id="timetable_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" id="loadStudentsBtn" class="btn btn-primary btn-sm my-4">
                                    <i class="fas fa-users"></i> Load Students
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Students Table -->
                <div class="card-body" id="studentsTableContainer" style="display: none;">
                    <form id="markAttendanceForm">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="studentsTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>CNIC</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Students will be loaded here via AJAX -->
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check-circle"></i> <span id="submitButtonText">Mark Attendance</span>
                            </button>
                        </div>
                    </form>
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
    
        // For Admin - load data immediately on page load
        @if(!auth()->user()->hasRole('Super Admin'))
        loadInitialDataForAdmin();
        @endif
    
        // For Super Admin - load dropdowns when institute is selected
        @if(auth()->user()->hasRole('Super Admin'))
        $('#institute_idd').change(function() {
            var instituteId = $(this).val();
            if (instituteId) {
                loadSessions(instituteId);
                $('#class_id, #section_id, #course_id, #teacher_id, #timetable_id').empty().append('<option value="">Select</option>');
            } else {
                clearAllDropdowns();
            }
        });
        @endif
        
        // Update Attendance Button Click
        $('#updateAttendanceBtn').click(function() {
            // Set today's date
            var today = new Date().toISOString().split('T')[0];
            $('#date').val(today);
            
            // Set update flag
            $('#is_update').val(1);
            
            // Change button text
            $('#submitButtonText').text('Update Attendance');
            
            Toast.fire({
                icon: 'info',
                title: 'Update mode activated. Select the same criteria as the attendance you want to update.'
            });
        });
        
        // When session changes, load classes for that session
        $('#session_id').change(function() {
            var sessionId = $(this).val();
            var instituteId = $('#institute_id').val();
    
            if (sessionId && instituteId) {
                loadClasses(instituteId, sessionId);
                $('#section_id, #course_id, #teacher_id, #timetable_id').empty().append('<option value="">Select</option>');
            } else {
                $('#class_id, #section_id, #course_id, #teacher_id, #timetable_id').empty().append('<option value="">Select</option>');
            }
        });
    
        // When class changes, load sections for that class
        $('#class_id').change(function() {
            var classId = $(this).val();
            var instituteId = $('#institute_id').val();
            var sessionId = $('#session_id').val();
    
            if (classId && instituteId && sessionId) {
                loadSections(instituteId, sessionId, classId);
                $('#course_id, #teacher_id, #timetable_id').empty().append('<option value="">Select</option>');
            } else {
                $('#section_id, #course_id, #teacher_id, #timetable_id').empty().append('<option value="">Select</option>');
            }
        });
    
        // When section changes, load courses for that section
        $('#section_id').change(function() {
            var sectionId = $(this).val();
            var instituteId = $('#institute_id').val();
            var sessionId = $('#session_id').val();
            var classId = $('#class_id').val();
    
            if (sectionId && instituteId && sessionId && classId) {
                loadCourses(instituteId, sessionId, classId, sectionId);
                $('#teacher_id, #timetable_id').empty().append('<option value="">Select</option>');
            } else {
                $('#course_id, #teacher_id, #timetable_id').empty().append('<option value="">Select</option>');
            }
        });
    
       // When course changes, load teachers and timetable for that course
        $('#course_id').change(function() {
            var courseId = $(this).val();
            var instituteId = $('#institute_id').val();
            var sessionId = $('#session_id').val();
            var classId = $('#class_id').val();
            var sectionId = $('#section_id').val();
            var date = $('#date').val();

            if (courseId && instituteId && sessionId && classId && sectionId && date) {
                loadTeachers(instituteId, sessionId, classId, sectionId, courseId);
                loadTimetable(instituteId, sessionId, classId, sectionId, courseId);
            } else {
                $('#teacher_id, #timetable_id').empty().append('<option value="">Select</option>');
            }
        });
    
        // Load students when load button is clicked
        $('#loadStudentsBtn').click(function() {
            loadStudents();
        });
    
        // Function to load sessions
        function loadSessions(instituteId, callback) {
            $.ajax({
                url: "{{ route('attendances.dropdowns') }}",
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
    
        // Function to load classes
        function loadClasses(instituteId, sessionId) {
            $.ajax({
                url: "{{ route('attendances.dropdowns') }}",
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
                    }
                },
                error: function(xhr) {
                    console.error('Error loading classes:', xhr.responseText);
                    Toast.fire({
                        icon: 'error',
                        title: 'Failed to load classes'
                    });
                }
            });
        }
    
        // Function to load sections
        function loadSections(instituteId, sessionId, classId) {
            $.ajax({
                url: "{{ route('attendances.dropdowns') }}",
                type: "GET",
                data: {
                    institute_id: instituteId,
                    session_id: sessionId,
                    class_id: classId
                },
                success: function(data) {
                    $('#section_id').empty().append('<option value="">Select Section</option>');
                    if (data.sections && data.sections.length > 0) {
                        $.each(data.sections, function(key, value) {
                            $('#section_id').append(`<option value="${value.id}">${value.section_name}</option>`);
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error loading sections:', xhr.responseText);
                    Toast.fire({
                        icon: 'error',
                        title: 'Failed to load sections'
                    });
                }
            });
        }
    
        // Function to load courses
        function loadCourses(instituteId, sessionId, classId, sectionId) {
            $.ajax({
                url: "{{ route('attendances.dropdowns') }}",
                type: "GET",
                data: {
                    institute_id: instituteId,
                    session_id: sessionId,
                    class_id: classId,
                    section_id: sectionId
                },
                success: function(data) {
                    $('#course_id').empty().append('<option value="">Select Course</option>');
                    if (data.courses && data.courses.length > 0) {
                        $.each(data.courses, function(key, value) {
                            $('#course_id').append(`<option value="${value.id}">${value.course_name}</option>`);
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error loading courses:', xhr.responseText);
                    Toast.fire({
                        icon: 'error',
                        title: 'Failed to load courses'
                    });
                }
            });
        }
    
        // Function to load teachers
        function loadTeachers(instituteId, sessionId, classId, sectionId, courseId) {
            $.ajax({
                url: "{{ route('lectures.teachers') }}",
                type: "GET",
                data: {
                    institute_id: instituteId,
                    session_id: sessionId,
                    class_id: classId,
                    section_id: sectionId,
                    course_id: courseId
                },
                success: function(data) {
                    $('#teacher_id').empty().append('<option value="">Select Teacher</option>');
                    if (data.length > 0) {
                        $.each(data, function(key, teacher) {
                            $('#teacher_id').append(`<option value="${teacher.id}">${teacher.name}</option>`);
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error loading teachers:', xhr.responseText);
                    Toast.fire({
                        icon: 'error',
                        title: 'Failed to load teachers'
                    });
                }
            });
        }
    
        function loadTimetable(instituteId, sessionId, classId, sectionId, courseId) {
            var date = $('#date').val();
            if (!date) {
                Toast.fire({
                    icon: 'error',
                    title: 'Please select a date first'
                });
                return;
            }

            $.ajax({
                url: "{{ route('attendances.timetable') }}",
                type: "GET",
                data: {
                    institute_id: instituteId,
                    session_id: sessionId,
                    class_id: classId,
                    section_id: sectionId,
                    course_id: courseId,
                    date: date
                },
                success: function(data) {
                    $('#timetable_id').empty().append('<option value="">Select Time</option>');
                    if (data.timetables && data.timetables.length > 0) {
                        $.each(data.timetables, function(key, timetable) {
                            $('#timetable_id').append(
                                `<option value="${timetable.id}" 
                                data-slot-time="${timetable.time_slot}"
                                data-date="${timetable.date}">
                                ${timetable.time_slot}
                                </option>`
                            );
                        });
                    } else {
                        Toast.fire({
                            icon: 'warning',
                            title: 'No slots available for selected date'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error loading timetable:', xhr.responseText);
                    Toast.fire({
                        icon: 'error',
                        title: 'Failed to load timetable'
                    });
                }
            });
        }

        // Set current date by default
        var today = new Date().toISOString().split('T')[0];
        $('#date').val(today);
        
        // Reload timetable when date changes
        $('#date').change(function() {
            var courseId = $('#course_id').val();
            if (courseId) {
                var instituteId = $('#institute_id').val();
                var sessionId = $('#session_id').val();
                var classId = $('#class_id').val();
                var sectionId = $('#section_id').val();
                
                loadTimetable(instituteId, sessionId, classId, sectionId, courseId);
            }
        });
    
       // Function to load students
        function loadStudents() {
            // Validate form
            var isValid = true;
            $('#attendanceForm select, #attendanceForm input').each(function() {
                if ($(this).prop('required') && !$(this).val()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) {
                Toast.fire({
                    icon: 'error',
                    title: 'Please fill all required fields'
                });
                return;
            }

            // Get selected slot time
            var selectedOption = $('#timetable_id option:selected');
            var slotTime = selectedOption.data('slot-time');
            var isUpdate = $('#is_update').val();

            var formData = $('#attendanceForm').serialize();
            formData += '&slot_times=' + encodeURIComponent(slotTime);

            $.ajax({
                url: "{{ route('attendances.students') }}",
                type: "POST",
                data: formData,
                beforeSend: function() {
                    $('#loadStudentsBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
                },
                success: function(response) {
                    if (response.error) {
                        Toast.fire({
                            icon: 'error',
                            title: response.error
                        });
                        return;
                    }

                    $('#studentsTable tbody').empty();
                    if (response.students && response.students.length > 0) {
                        $.each(response.students, function(index, student) {
                            var isChecked = response.is_update ? (student.status == 1) : true;
                            var presentText = response.is_update && !isChecked ? 'd-none' : '';
                            var absentText = response.is_update && !isChecked ? '' : 'd-none';
                            
                            var row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${student.name}</td>
                                    <td>${student.cnic || 'N/A'}</td>
                                    <td>${student.phone || 'N/A'}</td>
                                    <td>${student.email || 'N/A'}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="attendances[${index}][student_enrollment_id]" value="${student.enrollment_id}">
                                            <input type="hidden" name="attendances[${index}][student_id]" value="${student.student_id}">
                                            <input type="hidden" name="attendances[${index}][status]" value="0">
                                            <input class="form-check-input attendance-toggle" type="checkbox" name="attendances[${index}][status]" 
                                                id="attendance_${index}" value="1" ${isChecked ? 'checked' : ''}>
                                            <label class="form-check-label" for="attendance_${index}">
                                                <span class="present-text ${presentText}">Present</span>
                                                <span class="absent-text ${absentText}">Absent</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            `;
                            $('#studentsTable tbody').append(row);
                        });
                        $('#studentsTableContainer').show();
                    } else {
                        Toast.fire({
                            icon: 'warning',
                            title: 'No students found for this selection'
                        });
                        $('#studentsTableContainer').hide();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $(`#${key}`).addClass('is-invalid');
                            $(`#${key}_error`).text(value[0]);
                        });
                        
                        Toast.fire({
                            icon: 'error',
                            title: 'Please fix the errors in the form'
                        });
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.error || 'Failed to load students'
                        });
                    }
                },
                complete: function() {
                    $('#loadStudentsBtn').prop('disabled', false).html('<i class="fas fa-users"></i> Load Students');
                }
            });
        }
    
        // Toggle attendance status text
        $(document).on('change', '.attendance-toggle', function() {
            var parent = $(this).closest('.form-check');
            if ($(this).is(':checked')) {
                parent.find('.present-text').removeClass('d-none');
                parent.find('.absent-text').addClass('d-none');
            } else {
                parent.find('.present-text').addClass('d-none');
                parent.find('.absent-text').removeClass('d-none');
            }
        });
    
       // Mark attendance form submission
        $('#markAttendanceForm').submit(function(e) {
            e.preventDefault();

            var formData = $(this).serialize();
            var attendanceFormData = $('#attendanceForm').serializeArray();
            
            // Get selected slot time
            var selectedOption = $('#timetable_id option:selected');
            var slotTime = selectedOption.data('slot-time');
            var isUpdate = $('#is_update').val();
            
            // Combine both form data
            $.each(attendanceFormData, function(key, field) {
                formData += '&' + field.name + '=' + field.value;
            });
            
            // Add slot_times to form data
            formData += '&slot_times=' + encodeURIComponent(slotTime);

            $.ajax({
                url: "{{ route('attendances.mark') }}",
                type: "POST",
                data: formData,
                beforeSend: function() {
                    $('#markAttendanceForm button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                },
                success: function(response) {
                    Toast.fire({
                        icon: 'success',
                        title: response.message || 'Attendance marked successfully'
                    });
                    
                    // Reset the form
                    $('#studentsTableContainer').hide();
                    $('#attendanceForm')[0].reset();
                    $('#timetable_id').empty().append('<option value="">Select Time</option>');
                    $('#is_update').val(0);
                    $('#submitButtonText').text('Mark Attendance');
                    
                    // Reload page to show/hide update button
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $(`#${key}`).addClass('is-invalid');
                            $(`#${key}_error`).text(value[0]);
                        });
                        
                        Toast.fire({
                            icon: 'error',
                            title: 'Please fix the errors in the form'
                        });
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.error || 'Failed to mark attendance'
                        });
                    }
                },
                complete: function() {
                    $('#markAttendanceForm button[type="submit"]').prop('disabled', false).html(`<i class="fas fa-check-circle"></i> ${isUpdate == 1 ? 'Update' : 'Mark'} Attendance`);
                }
            });
        });
    
        // Function to load initial data for Admin
        function loadInitialDataForAdmin() {
            var instituteId = $('#institute_id').val();
            if (instituteId) {
                loadSessions(instituteId);
            }
        }
    
        // Function to clear all dropdowns
        function clearAllDropdowns() {
            $('#session_id, #class_id, #section_id, #course_id, #teacher_id, #timetable_id').empty().append('<option value="">Select</option>');
        }
    });
</script>
@endpush