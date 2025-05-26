@extends('layouts.app')

@section('content')
<!-- Add CSRF Token meta tag -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid">
    <!-- Session and Course Selection Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div>
                            <h3 class="card-title mb-0 text-white">
                                <i class="fas fa-graduation-cap"></i> Result Management
                            </h3>
                        </div>
                        <div>
                            <a href="{{ route('results.view') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-eye"></i> View Results
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Session Selection -->
                    <form id="resultForm" class="mb-4">
                        @csrf
                        <input type="hidden" name="institute_id" id="institute_id" value="{{ auth()->user()->institute_id ?? '' }}">
                       
                        <div class="row">
                            @if(auth()->user()->hasRole('Super Admin'))
                            <div class="col-md-4">
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
                            </div>
                            @endif
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="session_id" class="form-label fw-bold">
                                        <i class="fas fa-calendar-alt"></i> Academic Session
                                    </label>
                                    <select name="session_id" id="session_id" class="form-control form-select" required>
                                        <option value="">Select Session</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Course Cards -->
                    <div>
                        <h5 class="mb-3"><i class="fas fa-book"></i> Available Courses</h5>
                        <div id="courseListContainer">
                            <div class="row" id="courseListGroup">
                                <!-- Course cards will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="row">
        <div class="col-md-12">
            <div id="resultsContainer" style="display: none;">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 id="selectedCourseInfo" class="mb-0"></h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info" id="existingResultsAlert" style="display: none;">
                            <i class="fas fa-info-circle"></i> Results already exist for this combination. You can update them below.
                        </div>

                        <form id="storeResultsForm">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="studentsTable">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" style="width: 40px;">#</th>
                                            <th rowspan="2">Student</th>
                                            <th colspan="0" class="text-center" id="assessmentsColspan">Assessments</th>
                                            <th colspan="2" class="text-center bg-light">Final Marks</th>
                                        </tr>
                                        <tr id="assessmentHeaders">
                                            <!-- Assessment headers will be loaded here -->
                                            <th class="text-center bg-light" style="width: 85px;">
                                                Total<br>
                                                <small class="text-muted">(100%)</small>
                                            </th>
                                            <th class="text-center bg-light" style="width: 85px;">
                                                Obtained<br>
                                                <small class="text-muted">(Calculated)</small>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Students will be loaded here -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="0" class="text-end" id="weightageColspan"><strong>Total Weightage:</strong></td>
                                            <td colspan="2" class="bg-light" id="totalWeightageFooter">100%</td>
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

    // Set CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // For Admin - load data immediately on page load
    @if(!auth()->user()->hasRole('Super Admin'))
    loadInitialDataForAdmin();
    @endif

    // For Super Admin - load dropdowns when institute is selected
    @if(auth()->user()->hasRole('Super Admin'))
    $('#institute_select').change(function() {
        const instituteId = $(this).val();
        $('#institute_id').val(instituteId);
        if (instituteId) {
            loadSessionsAndSelectCurrent(instituteId);
        } else {
            $('#session_id').empty().append('<option value="">Select Session</option>');
            $('#courseListGroup').empty();
            $('#resultsContainer').hide();
        }
    });
    @endif

    // When session changes, load courses
    $('#session_id').change(function() {
        loadCourses();
    });

    // Function to load sessions and select current one
    function loadSessionsAndSelectCurrent(instituteId) {
        $('#courseListGroup').empty();
        $('#resultsContainer').hide();
        
        $.ajax({
            url: "{{ route('results.dropdowns') }}",
            type: "GET",
            data: { institute_id: instituteId },
            beforeSend: function() {
                $('#session_id').html('<option value="">Loading sessions...</option>');
            },
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
                } else {
                    Toast.fire({
                        icon: 'warning',
                        title: 'No sessions available'
                    });
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to load sessions'
                });
                $('#session_id').html('<option value="">Select Session</option>');
            }
        });
    }

    // Function to load courses
    function loadCourses() {
        const instituteId = $('#institute_id').val();
        const sessionId = $('#session_id').val();

        if (!sessionId) return;

        $('#courseListGroup').html(`
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading courses...</p>
            </div>
        `);

        $.get("{{ route('results.courses') }}", {
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
                        <div class="col-md-4 col-lg-3 mb-3">
                            <div class="list-group-item list-group-item-action course-item h-100" 
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

        // Show results container
        $('#resultsContainer').show();

        loadStudents(courseId, classId, sectionId, teacherId);
    });

    // Function to load students
    function loadStudents(courseId, classId, sectionId, teacherId) {
        const instituteId = $('#institute_id').val();
        const sessionId = $('#session_id').val();

        $.ajax({
            url: "{{ route('results.students') }}",
            type: "POST",
            data: {
                institute_id: instituteId,
                session_id: sessionId,
                class_id: classId,
                section_id: sectionId,
                course_id: courseId,
                teacher_id: teacherId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#studentsTable tbody').html(`
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading students...</p>
                        </td>
                    </tr>
                `);
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

                // Set the colspan for assessments
                const assessmentCount = response.assessments ? response.assessments.length : 0;
                $('#assessmentsColspan').attr('colspan', assessmentCount);

                // Add assessment headers with subheadings
                if (response.assessments && response.assessments.length > 0) {
                    response.assessments.forEach(function(assessment) {
                        $('#assessmentHeaders').append(`
                            <th class="text-center" style="width: 85px;">
                                ${assessment.title}<br>
                                <small class="text-muted">
                                    (${assessment.marks} - ${assessment.weightage_percent}%)
                                </small>
                            </th>
                        `);
                    });

                    // Add final marks headers
                    $('#assessmentHeaders').append(`
                        <th class="text-center bg-light" style="width: 85px;">
                            Total<br>
                            <small class="text-muted">(100%)</small>
                        </th>
                        <th class="text-center bg-light" style="width: 85px;">
                            Obtained<br>
                            <small class="text-muted">(Calculated)</small>
                        </th>
                    `);
                }

                // Add student rows
                if (response.students && response.students.length > 0) {
                    response.students.forEach(function(student, index) {
                        let row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${student.name}</td>
                        `;

                        // Add assessment inputs with consistent styling
                        response.assessments.forEach(function(assessment) {
                            const fieldName = assessment.title.toLowerCase().replace(/[^a-z0-9]/g, '');
                            const value = student[fieldName] !== undefined ? student[fieldName] : '';
                        
                            row += `
                                <td class="text-center">
                                    <input type="number" 
                                        class="form-control form-control-sm text-center assessment-marks"
                                        name="results[${index}][${fieldName}]"
                                        data-max="${assessment.marks}"
                                        data-weight="${assessment.weightage_percent}"
                                        value="${value}"
                                        min="0"
                                        max="${assessment.marks}"
                                        step="0.5"
                                        style="width: 70px; margin: auto;">
                                    <small class="text-muted d-block mt-1">
                                        Max: ${assessment.marks}
                                    </small>
                                </td>
                            `;
                        });

                        // Add total and obtained marks at the end
                        row += `
                            <td class="text-center bg-light">
                                <input type="number" 
                                    class="form-control form-control-sm text-center" 
                                    value="${response.course.total_marks}" 
                                    readonly
                                    style="width: 70px; margin: auto;">
                                <small class="text-muted d-block mt-1">Course Total</small>
                                <input type="hidden" name="results[${index}][student_enrollment_id]" value="${student.enrollment_id}">
                                <input type="hidden" name="results[${index}][student_id]" value="${student.student_id}">
                                <input type="hidden" name="results[${index}][total_marks]" value="${response.course.total_marks}">
                                <input type="hidden" name="results[${index}][course_total]" value="${response.course.total_marks}">
                            </td>
                            <td class="text-center bg-light">
                                <input type="number" 
                                    class="form-control form-control-sm text-center obtained-marks" 
                                    name="results[${index}][obtained_marks]" 
                                    value="${student.obtained_marks || ''}" 
                                    readonly
                                    style="width: 70px; margin: auto;">
                                <small class="text-muted d-block mt-1">Final Score</small>
                            </td>
                        </tr>
                        `;
                        $('#studentsTable tbody').append(row);
                    });

                    // Update colspan for weightage footer
                    const totalColumns = $('#studentsTable thead tr:first th').length;
                    $('#weightageColspan').attr('colspan', totalColumns - 2);

                    // Initialize assessment calculation
                    initializeAssessmentCalculation();
                } else {
                    $('#studentsTable tbody').html('<tr><td colspan="5" class="text-center">No students found</td></tr>');
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.error || 'Failed to load students'
                });
            }
        });
    }

    // Function to initialize assessment calculation
    function initializeAssessmentCalculation() {
        $('.assessment-marks').on('input', function() {
            const row = $(this).closest('tr');
            let totalObtained = 0;
            
            row.find('.assessment-marks').each(function() {
                const marks = parseFloat($(this).val()) || 0;
                const weight = parseFloat($(this).data('weight')) || 0;
                const maxMarks = parseFloat($(this).data('max')) || 0;
                
                if (marks > maxMarks) {
                    $(this).val(maxMarks);
                    marks = maxMarks;
                }
                
                totalObtained += (marks / maxMarks) * weight;
            });
            
            const totalMarks = parseFloat(row.find('input[name$="[total_marks]"]').val());
            const finalObtained = (totalObtained / 100) * totalMarks;
            
            row.find('.obtained-marks').val(finalObtained.toFixed(2));
        });
    }

    // Function to load initial data for Admin
    function loadInitialDataForAdmin() {
        const instituteId = $('#institute_id').val();
        if (instituteId) {
            loadSessionsAndSelectCurrent(instituteId);
        }
    }

    // Handle form submission
    $('#storeResultsForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitButton = $(this).find('button[type="submit"]');
        submitButton.prop('disabled', true).html(
            `<i class="fas fa-spinner fa-spin"></i> Saving...`
        );

        // Get form data
        const formData = new FormData();
        formData.append('institute_id', $('#institute_id').val());
        formData.append('session_id', $('#session_id').val());
        formData.append('class_id', $('.course-item.active').data('class-id'));
        formData.append('section_id', $('.course-item.active').data('section-id'));
        formData.append('course_id', $('.course-item.active').data('course-id'));
        formData.append('teacher_id', $('.course-item.active').data('teacher-id'));

        // Collect results data from each row
        $('#studentsTable tbody tr').each(function(index) {
            const row = $(this);
            
            // Basic result data
            formData.append(`results[${index}][student_enrollment_id]`, row.find('input[name$="[student_enrollment_id]"]').val());
            formData.append(`results[${index}][student_id]`, row.find('input[name$="[student_id]"]').val());
            formData.append(`results[${index}][obtained_marks]`, row.find('input[name$="[obtained_marks]"]').val());
            formData.append(`results[${index}][total_marks]`, row.find('input[name$="[total_marks]"]').val());
            formData.append(`results[${index}][course_total]`, row.find('input[name$="[course_total]"]').val());

            // Assessment marks
            const assessmentFields = ['assignment1', 'assignment2', 'quiz1', 'quiz2', 'midterm', 'final'];
            assessmentFields.forEach(field => {
                const value = row.find(`input[name$="[${field}]"]`).val() || '';
                formData.append(`results[${index}][${field}]`, value);
            });
        });

        // Send AJAX request
        $.ajax({
            url: "{{ route('results.store') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });

                // Update button text based on whether it was an update or new save
                $('#submitButtonText').text(response.has_existing_results ? 'Update Results' : 'Save Results');
                $('#existingResultsAlert').toggle(response.has_existing_results);
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.error || 'Failed to save results'
                });
            },
            complete: function() {
                submitButton.prop('disabled', false).html(
                    `<i class="fas fa-check-circle"></i> <span id="submitButtonText">${$('#submitButtonText').text()}</span>`
                );
            }
        });
    });
});
</script>
@endpush