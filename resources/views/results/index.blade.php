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
                                <i class="fas fa-graduation-cap text-white"></i> Result Management
                            </h3>
                        </div>
                        <div>
                            <a href="{{ route('results.view') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-eye"></i> View Results
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="card-body border-bottom">
                    <form id="resultForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="institute_id" id="institute_id" value="{{ auth()->user()->institute_id ?? '' }}">
                       
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
                    <div class="alert alert-info" id="existingResultsAlert" style="display: none;">
                        <i class="fas fa-info-circle"></i> Results already exist for this combination. You can update them below.
                    </div>
                    <form id="storeResultsForm">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="studentsTable">
                                <thead>
                                    <tr>
                                        <th rowspan="2">#</th>
                                        <th rowspan="2">Student</th>
                                        <th rowspan="2">Total Marks</th>
                                        <th rowspan="2">Obtained Marks</th>
                                        <th colspan="0" class="text-center" id="assessmentsColspan">Assessments</th>
                                    </tr>
                                    <tr id="assessmentHeaders">
                                        <!-- Assessment headers will be loaded here -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Students will be loaded here via AJAX -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Total Weightage:</strong></td>
                                        <td colspan="0" id="totalWeightageFooter">100%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check-circle"></i> <span id="submitButtonText">Save Results</span>
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
            $('#class_id, #section_id, #course_id, #teacher_id').empty().append('<option value="">Select</option>');
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
            loadClasses(instituteId, sessionId);
            $('#section_id, #course_id, #teacher_id').empty().append('<option value="">Select</option>');
        } else {
            $('#class_id, #section_id, #course_id, #teacher_id').empty().append('<option value="">Select</option>');
        }
    });

    // When class changes, load sections for that class
    $('#class_id').change(function() {
        var classId = $(this).val();
        var instituteId = $('#institute_id').val();
        var sessionId = $('#session_id').val();

        if (classId && instituteId && sessionId) {
            loadSections(instituteId, sessionId, classId);
            $('#course_id, #teacher_id').empty().append('<option value="">Select</option>');
        } else {
            $('#section_id, #course_id, #teacher_id').empty().append('<option value="">Select</option>');
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
            $('#teacher_id').empty().append('<option value="">Select</option>');
        } else {
            $('#course_id, #teacher_id').empty().append('<option value="">Select</option>');
        }
    });

    // When course changes, load teachers
    $('#course_id').change(function() {
        var courseId = $(this).val();
        var instituteId = $('#institute_id').val();
        var sessionId = $('#session_id').val();
        var classId = $('#class_id').val();
        var sectionId = $('#section_id').val();

        if (courseId && instituteId && sessionId && classId && sectionId) {
            loadTeachers(instituteId, sessionId, classId, sectionId, courseId);
        } else {
            $('#teacher_id').empty().append('<option value="">Select</option>');
        }
    });

    // Load students when load button is clicked
    $('#loadStudentsBtn').click(function() {
        loadStudents();
    });

    // Function to load sessions
    function loadSessions(instituteId, callback) {
        $.ajax({
            url: "{{ route('results.dropdowns') }}",
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
            url: "{{ route('results.dropdowns') }}",
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
            url: "{{ route('results.dropdowns') }}",
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
            url: "{{ route('results.dropdowns') }}",
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
                        $('#course_id').append(`<option value="${value.id}" data-total-marks="${value.total_marks}" data-credit-hours="${value.credit_hours}">${value.course_name}</option>`);
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

    // Function to load students with dynamic assessments
    function loadStudents() {
    // Validate form
    var isValid = true;
    $('#resultForm select').each(function() {
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

    var formData = $('#resultForm').serialize();

    $.ajax({
        url: "{{ route('results.students') }}",
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

            // Clear existing table
            $('#studentsTable tbody').empty();
            $('#assessmentHeaders').empty();

            // Show/hide existing results alert
            if (response.has_existing_results) {
                $('#existingResultsAlert').show();
                $('#submitButtonText').text('Update Results');
            } else {
                $('#existingResultsAlert').hide();
                $('#submitButtonText').text('Save Results');
            }

            // Process assessments to group by type
            var assessmentsByType = {
                assignments: [],
                quizzes: [],
                midterm: null,
                final: null
            };

            // Sort assessments by type and title
            response.assessments.forEach(function(assessment) {
                var type = assessment.type.toLowerCase();
                if (type.includes('assignment')) {
                    assessmentsByType.assignments.push(assessment);
                } else if (type.includes('quiz')) {
                    assessmentsByType.quizzes.push(assessment);
                } else if (type.includes('midterm')) {
                    assessmentsByType.midterm = assessment;
                } else if (type.includes('final')) {
                    assessmentsByType.final = assessment;
                }
            });

            // Sort assignments and quizzes by their numbers
            assessmentsByType.assignments.sort((a, b) => {
                const numA = parseInt(a.title.match(/\d+/)?.[0]) || 0;
                const numB = parseInt(b.title.match(/\d+/)?.[0]) || 0;
                return numA - numB;
            });

            assessmentsByType.quizzes.sort((a, b) => {
                const numA = parseInt(a.title.match(/\d+/)?.[0]) || 0;
                const numB = parseInt(b.title.match(/\d+/)?.[0]) || 0;
                return numA - numB;
            });

            // Add assessment headers in correct order
            if (response.assessments && response.assessments.length > 0) {
                $('#assessmentsColspan').attr('colspan', response.assessments.length);
                
                // Add assignments
                assessmentsByType.assignments.forEach(function(assessment) {
                    $('#assessmentHeaders').append(`
                        <th class="text-center">
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold">${assessment.title}</span>
                                <small class="text-muted">Assignment (${assessment.marks} marks, ${assessment.weightage_percent}%)</small>
                            </div>
                        </th>
                    `);
                });

                // Add quizzes
                assessmentsByType.quizzes.forEach(function(assessment) {
                    $('#assessmentHeaders').append(`
                        <th class="text-center">
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold">${assessment.title}</span>
                                <small class="text-muted">Quiz (${assessment.marks} marks, ${assessment.weightage_percent}%)</small>
                            </div>
                        </th>
                    `);
                });

                // Add midterm if exists
                if (assessmentsByType.midterm) {
                    $('#assessmentHeaders').append(`
                        <th class="text-center">
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold">${assessmentsByType.midterm.title}</span>
                                <small class="text-muted">Midterm (${assessmentsByType.midterm.marks} marks, ${assessmentsByType.midterm.weightage_percent}%)</small>
                            </div>
                        </th>
                    `);
                }

                // Add final if exists
                if (assessmentsByType.final) {
                    $('#assessmentHeaders').append(`
                        <th class="text-center">
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold">${assessmentsByType.final.title}</span>
                                <small class="text-muted">Final (${assessmentsByType.final.marks} marks, ${assessmentsByType.final.weightage_percent}%)</small>
                            </div>
                        </th>
                    `);
                }
            }

            // Add student rows
            if (response.students && response.students.length > 0) {
                $.each(response.students, function(index, student) {
                    var statusBadge = student.has_existing_result ? 
                        '<span class="badge badge-success">Existing</span>' : 
                        '<span class="badge badge-warning">New</span>';

                    var row = `
                        <tr data-student-id="${student.student_id}">
                            <td>${index + 1}</td>
                            <td>
                                ${student.name}
                                <input type="hidden" name="results[${index}][student_enrollment_id]" value="${student.enrollment_id}">
                                <input type="hidden" name="results[${index}][student_id]" value="${student.student_id}">
                                ${statusBadge}
                            </td>
                            <td>
                                ${student.total_marks}
                                <input type="hidden" name="results[${index}][total_marks]" value="${student.total_marks}">
                                <input type="hidden" name="results[${index}][course_total]" value="${student.total_marks}">
                            </td>
                            <td>
                                <input type="number" class="form-control obtained-marks" 
                                    name="results[${index}][obtained_marks]" 
                                    value="${student.obtained_marks || ''}"
                                    max="${student.total_marks}"
                                    min="0"
                                    step="0.01"
                                    required readonly>
                                <div class="invalid-feedback"></div>
                            </td>`;

                    // Add assignment inputs in order
                    assessmentsByType.assignments.forEach(function(assessment) {
                        var fieldNumber = assessment.title.match(/\d+/)?.[0] || '';
                        var fieldName = 'assignment' + fieldNumber;
                        var value = student[fieldName] || '';
                        
                        row += `
                            <td>
                                <input type="number" 
                                    class="form-control assessment-input" 
                                    name="results[${index}][${fieldName}]"
                                    data-assessment-id="${assessment.id}"
                                    data-assessment-marks="${assessment.marks}"
                                    data-weightage="${assessment.weightage_percent}"
                                    value="${value}"
                                    max="${assessment.marks}"
                                    min="0"
                                    step="0.01"
                                    required>
                                <div class="invalid-feedback"></div>
                                <small class="text-muted">Max: ${assessment.marks}</small>
                            </td>`;
                    });

                    // Add quiz inputs in order
                    assessmentsByType.quizzes.forEach(function(assessment) {
                        var fieldNumber = assessment.title.match(/\d+/)?.[0] || '';
                        var fieldName = 'quiz' + fieldNumber;
                        var value = student[fieldName] || '';
                        
                        row += `
                            <td>
                                <input type="number" 
                                    class="form-control assessment-input" 
                                    name="results[${index}][${fieldName}]"
                                    data-assessment-id="${assessment.id}"
                                    data-assessment-marks="${assessment.marks}"
                                    data-weightage="${assessment.weightage_percent}"
                                    value="${value}"
                                    max="${assessment.marks}"
                                    min="0"
                                    step="0.01"
                                    required>
                                <div class="invalid-feedback"></div>
                                <small class="text-muted">Max: ${assessment.marks}</small>
                            </td>`;
                    });

                    // Add midterm input if exists
                    if (assessmentsByType.midterm) {
                        var value = student.midterm || '';
                        row += `
                            <td>
                                <input type="number" 
                                    class="form-control assessment-input" 
                                    name="results[${index}][midterm]"
                                    data-assessment-id="${assessmentsByType.midterm.id}"
                                    data-assessment-marks="${assessmentsByType.midterm.marks}"
                                    data-weightage="${assessmentsByType.midterm.weightage_percent}"
                                    value="${value}"
                                    max="${assessmentsByType.midterm.marks}"
                                    min="0"
                                    step="0.01"
                                    required>
                                <div class="invalid-feedback"></div>
                                <small class="text-muted">Max: ${assessmentsByType.midterm.marks}</small>
                            </td>`;
                    }

                    // Add final input if exists
                    if (assessmentsByType.final) {
                        var value = student.final || '';
                        row += `
                            <td>
                                <input type="number" 
                                    class="form-control assessment-input" 
                                    name="results[${index}][final]"
                                    data-assessment-id="${assessmentsByType.final.id}"
                                    data-assessment-marks="${assessmentsByType.final.marks}"
                                    data-weightage="${assessmentsByType.final.weightage_percent}"
                                    value="${value}"
                                    max="${assessmentsByType.final.marks}"
                                    min="0"
                                    step="0.01"
                                    required>
                                <div class="invalid-feedback"></div>
                                <small class="text-muted">Max: ${assessmentsByType.final.marks}</small>
                            </td>`;
                    }

                    row += `</tr>`;
                    $('#studentsTable tbody').append(row);
                });

                // Initialize calculation on input change
                $('.assessment-input').on('input', function() {
                    calculateStudentTotal($(this).closest('tr'));
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

// Store results form submission
$('#storeResultsForm').submit(function(e) {
    e.preventDefault();

    // Validate obtained marks and assessment marks
    var isValid = true;
    $('.assessment-input').each(function() {
        var max = parseFloat($(this).attr('max'));
        var value = parseFloat($(this).val()) || 0; // Default to 0 if empty
        
        if (value < 0 || value > max) {
            $(this).addClass('is-invalid');
            $(this).next('.invalid-feedback').text(`Please enter a value between 0 and ${max}`);
            isValid = false;
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    if (!isValid) {
        Toast.fire({
            icon: 'error',
            title: 'Please fix the validation errors'
        });
        return;
    }

    // Prepare form data
    var formData = new FormData();
    formData.append('_token', $('input[name="_token"]').val());
    formData.append('institute_id', $('#institute_id').val());
    formData.append('session_id', $('#session_id').val());
    formData.append('class_id', $('#class_id').val());
    formData.append('section_id', $('#section_id').val());
    formData.append('course_id', $('#course_id').val());
    formData.append('teacher_id', $('#teacher_id').val());

    // Collect all student results
    $('#studentsTable tbody tr').each(function(index) {
        var row = $(this);
        var studentId = row.data('student-id');
        var prefix = `results[${index}]`;
        
        formData.append(`${prefix}[student_enrollment_id]`, row.find('input[name*="[student_enrollment_id]"]').val());
        formData.append(`${prefix}[student_id]`, row.find('input[name*="[student_id]"]').val());
        formData.append(`${prefix}[obtained_marks]`, row.find('.obtained-marks').val());
        formData.append(`${prefix}[total_marks]`, row.find('input[name*="[total_marks]"]').val());
        formData.append(`${prefix}[course_total]`, row.find('input[name*="[course_total]"]').val());
        
        // Get all assessment inputs - properly handle each input by using the exact field name
        row.find('.assessment-input').each(function() {
            var inputName = $(this).attr('name');
            // Extract the field name from the input name (e.g., 'results[0][assignment1]' -> 'assignment1')
            var matches = inputName.match(/\[(\d+)\]\[([^\]]+)\]/);
            if (matches && matches.length >= 3) {
                var fieldName = matches[2]; // This gives us 'assignment1', 'quiz1', etc.
                var value = $(this).val();
                // Only append if we have a valid value (empty strings will be converted to null in the backend)
                formData.append(`${prefix}[${fieldName}]`, value !== "" ? value : "0");
            }
        });
    });

    $.ajax({
        url: "{{ route('results.store') }}",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#storeResultsForm button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        },
        success: function(response) {
            Toast.fire({
                icon: 'success',
                title: response.message
            });
            
            // Reload the students to show updated status
            loadStudents();
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                var errors = xhr.responseJSON.errors;
                $.each(errors, function(key, value) {
                    var field = key.replace(/results\.\d+\./, ''); // Clean up nested field paths
                    $(`[name*="[${field}]"]`).addClass('is-invalid');
                    $(`[name*="[${field}]"]`).next('.invalid-feedback').text(value[0]);
                });
                
                Toast.fire({
                    icon: 'error',
                    title: 'Please fix the errors in the form'
                });
            } else {
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.error || 'Failed to save results'
                });
            }
        },
        complete: function() {
            $('#storeResultsForm button[type="submit"]').prop('disabled', false).html(`<i class="fas fa-check-circle"></i> ${$('#existingResultsAlert').is(':visible') ? 'Update' : 'Save'} Results`);
        }
    });
});

    // Calculate student total based on assessment marks
    function calculateStudentTotal(row) {
        var total = 0;
        var totalPossible = 0;
        var totalWeighted = 0;
        var totalWeightage = 0;

        row.find('.assessment-input').each(function() {
            var marks = parseFloat($(this).val()) || 0;
            var maxMarks = parseFloat($(this).data('assessment-marks'));
            var weightage = parseFloat($(this).data('weightage'));

            total += marks;
            totalPossible += maxMarks;
            totalWeighted += (marks / maxMarks) * weightage;
            totalWeightage += weightage;
        });

        // Update obtained marks field (weighted average)
        var weightedPercentage = totalWeighted / totalWeightage;
        var courseTotalMarks = parseFloat(row.find('td:eq(2)').text());
        var obtainedMarks = Math.round((weightedPercentage * courseTotalMarks) * 100) / 100;

        row.find('.obtained-marks').val(obtainedMarks.toFixed(2));
    }

   
    // Function to load initial data for Admin
    function loadInitialDataForAdmin() {
        var instituteId = $('#institute_id').val();
        if (instituteId) {
            loadSessions(instituteId);
        }
    }

    // Function to clear all dropdowns
    function clearAllDropdowns() {
        $('#session_id, #class_id, #section_id, #course_id, #teacher_id').empty().append('<option value="">Select</option>');
    }
});
</script>
@endpush