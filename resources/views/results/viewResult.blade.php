@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div>
                            <h3 class="card-title mb-0 text-white">
                                <i class="fas fa-file-alt text-white"></i> View Results
                            </h3>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="card-body border-bottom">
                    <form id="resultViewForm">
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
                                <button type="button" id="loadResultsBtn" class="btn btn-primary btn-sm my-4">
                                    <i class="fas fa-search"></i> Search Results
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

           
                <!-- Results Header Info -->
                <div class="card-body" id="resultsHeaderContainer" style="display: none; position: relative;"> <!-- Added position relative -->
                    <div class="alert alert-info" style="padding: 1.25rem; border-radius: 0.5rem;">
                    
                    <!-- PDF Button - Top Right -->
                    <button id="generatePdfBtn" class="btn btn-warning btn-sm" 
                            style="position: absolute; top: 10px; right: 10px;">
                        <i class="fas fa-file-pdf"></i> Generate PDF
                    </button>
                    
                    <!-- Session - Centered Top (with padding for button) -->
                    <div class="text-center mb-3" style="font-size: 20px; font-weight: bold; padding-top: 8px;">
                        <span id="headerSession"></span>
                    </div>
                    
                    <!-- Two Column Layout -->
                    <div class="d-flex justify-content-between">
                        <!-- Left Column -->
                        <div style="text-align: left;">
                        <div><strong>Class:</strong> <span id="headerClass"></span></div>
                        <div><strong>Section:</strong> <span id="headerSection"></span></div>
                        </div>
                        
                        <!-- Right Column -->
                        <div style="text-align: right;">
                        <div><strong>Course:</strong> <span id="headerCourse"></span></div>
                        <div><strong>Teacher:</strong> <span id="headerTeacher"></span></div>
                        </div>
                    </div>
                    </div>
                </div>
                <!-- Results Table -->
                <div class="card-body" id="resultsTableContainer" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="resultsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student Name</th>
                                    <th>Registration No.</th>
                                    <th>Total Marks</th>
                                    <th>Obtained Marks</th>
                                    <th>Percentage</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Results will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Result Detail Modal -->
<div class="modal fade" id="resultDetailModal" tabindex="-1" aria-labelledby="resultDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="resultDetailModalLabel">
                    <i class="fas fa-user-graduate me-2"></i> Student Result Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBodyContent">
                <!-- Modal content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="generateStudentPdfBtn" class="btn btn-primary">
                    <i class="fas fa-file-pdf"></i> Download Result
                </button>
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

    // Load results when search button is clicked
    $('#loadResultsBtn').click(function() {
        loadResults();
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

    // Function to load results
    function loadResults() {
        // Validate form
        var isValid = true;
        $('#resultViewForm select').each(function() {
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

        var formData = $('#resultViewForm').serialize();

        $.ajax({
            url: "{{ route('results.view-data') }}",
            type: "POST",
            data: formData,
            beforeSend: function() {
                $('#loadResultsBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
            },
            success: function(response) {
                if (response.error) {
                    Toast.fire({
                        icon: 'error',
                        title: response.error
                    });
                    return;
                }

                // Update header information
                $('#headerSession').text(response.session_name);
                $('#headerClass').text(response.class_name);
                $('#headerSection').text(response.section_name);
                $('#headerCourse').text(response.course_name);
                $('#headerTeacher').text(response.teacher_name);
                $('#resultsHeaderContainer').show();

                // Clear existing table
                $('#resultsTable tbody').empty();

                // Add results to table
                if (response.results && response.results.length > 0) {
                    $.each(response.results, function(index, result) {
                        var percentage = (result.obtained_marks / result.total_marks * 100).toFixed(2);
                        var grade = calculateGrade(percentage);
                        var statusBadge = result.status === 'Pass' ? 
                            '<span class="badge bg-success">Pass</span>' : 
                            '<span class="badge bg-danger">Fail</span>';

                        var row = `
                            <tr data-student-id="${result.student_id}" data-result-id="${result.id}">
                                <td>${index + 1}</td>
                                <td>${result.student_name}</td>
                                <td>${result.roll_number || 'N/A'}</td>
                                <td>${result.total_marks}</td>
                                <td>${result.obtained_marks}</td>
                                <td>${percentage}%</td>
                                <td>${grade}</td>
                                <td>${statusBadge}</td>
                                <td>
                                    <button class="btn btn-info btn-sm view-result-btn" data-result-id="${result.id}">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>`;
                        $('#resultsTable tbody').append(row);
                    });

                    // Initialize view buttons
                    $('.view-result-btn').click(function() {
                        var resultId = $(this).data('result-id');
                        loadResultDetails(resultId);
                    });

                    $('#resultsTableContainer').show();
                } else {
                    Toast.fire({
                        icon: 'warning',
                        title: 'No results found for this selection'
                    });
                    $('#resultsTableContainer').hide();
                    $('#resultsHeaderContainer').hide();
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
                        title: xhr.responseJSON?.error || 'Failed to load results'
                    });
                }
            },
            complete: function() {
                $('#loadResultsBtn').prop('disabled', false).html('<i class="fas fa-search"></i> Search Results');
            }
        });
    }

    // Function to calculate grade based on percentage
    function calculateGrade(percentage) {
        if (percentage >= 90) return 'A+';
        if (percentage >= 85) return 'A';
        if (percentage >= 80) return 'A-';
        if (percentage >= 75) return 'B+';
        if (percentage >= 70) return 'B';
        if (percentage >= 65) return 'B-';
        if (percentage >= 60) return 'C+';
        if (percentage >= 55) return 'C';
        if (percentage >= 50) return 'C-';
        if (percentage >= 45) return 'D';
        return 'F';
    }

    // Function to load result details for modal
// Function to load result details for modal
function loadResultDetails(resultId) {
    $.ajax({
        url: "{{ route('results.details') }}",
        type: "GET",
        data: { result_id: resultId },
        beforeSend: function() {
            $('#modalBodyContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
            $('#resultDetailModal').modal('show');
        },
        success: function(response) {
            if (response.error) {
                $('#modalBodyContent').html('<div class="alert alert-danger">' + response.error + '</div>');
                return;
            }

            // Format the date - handle both string and Date formats
            var formattedDate;
            if (response.result.updated_at) {
                if (typeof response.result.updated_at === 'string') {
                    // If date is already formatted as "20-05-2025", use it directly
                    if (/^\d{2}-\d{2}-\d{4}$/.test(response.result.updated_at)) {
                        formattedDate = response.result.updated_at;
                    } else {
                        // Try parsing as ISO date string
                        var dateParts = response.result.updated_at.split('-');
                        if (dateParts.length === 3) {
                            // Rearrange to YYYY-MM-DD format for Date parsing
                            formattedDate = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0];
                        } else {
                            // Fallback to current date
                            formattedDate = new Date().toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                        }
                    }
                } else {
                    // If it's a Date object or timestamp
                    var updatedDate = new Date(response.result.updated_at);
                    formattedDate = updatedDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                }
            } else {
                formattedDate = 'N/A';
            }

            // Rest of your modal content building code...
            var modalContent = `
                <!-- Student Info -->
                <div class="row mb-3">
                    <div class="col-lg-4">
                        <div class="student-meta"><strong>Name:</strong> ${response.result.student_name}</div>
                    </div>
                    <div class="col-lg-4">
                        <div class="student-meta"><strong>Roll No.:</strong> ${response.result.roll_number || 'N/A'}</div>
                    </div>
                    <div class="col-lg-4">
                        <div class="student-meta"><strong>Course:</strong> ${response.result.course_name}</div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-lg-4">
                        <div class="student-meta"><strong>Session:</strong> ${response.result.session_name}</div>
                    </div>
                    <div class="col-lg-4">
                        <div class="student-meta"><strong>Teacher:</strong> ${response.result.teacher_name}</div>
                    </div>
                    <div class="col-lg-4">
                        <div class="student-meta"><strong>Date:</strong> ${formattedDate}</div>
                    </div>
                </div>

                <!-- Marks Breakdown Table -->
                <div class="table-responsive">
                    <table class="table table-bordered border-secondary">
                        <thead class="table-light">
                            <tr>
                                <th>Component</th>
                                <th>Obtained Marks</th>
                                <th>Total Marks</th>
                                <th>Weight %</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>`;

            // Add assessment rows
            response.assessments.forEach(function(assessment) {
                modalContent += `
                    <tr>
                        <td>${assessment.name}</td>
                        <td>${assessment.obtained}</td>
                        <td>${assessment.total}</td>
                        <td>${assessment.weightage}%</td>
                        <td>${assessment.remarks}</td>
                    </tr>`;
            });

            // Add total row
            modalContent += `
                <tr class="table-info">
                    <th>Total</th>
                    <th>${response.result.obtained_marks}</th>
                    <th>${response.result.total_marks}</th>
                    <th>${response.total_weightage}%</th>
                    <th><span class="badge bg-${response.result.status === 'Pass' ? 'success' : 'danger'}">${response.result.status}</span></th>
                </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Summary Info -->
                <div class="mt-3 row">
                    <div class="col-md-4">
                        <strong>Percentage:</strong> <span class="badge bg-primary">${response.percentage}%</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Grade:</strong> <span class="badge bg-primary">${response.grade}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Status:</strong> <span class="badge bg-${response.result.status === 'Pass' ? 'success' : 'danger'}">${response.result.status}</span>
                    </div>
                </div>
                <div class="mt-2 text-end">
                    <small class="text-muted">Result last updated: ${formattedDate}</small>
                </div>`;

            $('#modalBodyContent').html(modalContent);
            
            // Set up PDF download button
            $('#generateStudentPdfBtn').off('click').on('click', function() {
                generateStudentPdf(response.result.id);
            });
        },
        error: function(xhr) {
            $('#modalBodyContent').html('<div class="alert alert-danger">Failed to load result details</div>');
            console.error('Error loading result details:', xhr.responseText);
        }
    });
}

    // Function to generate remarks based on marks
    function getRemarks(obtained, total) {
        if (obtained === null || total === null) return 'N/A';
        
        var percentage = (obtained / total * 100);
        if (percentage >= 90) return 'Outstanding';
        if (percentage >= 80) return 'Excellent';
        if (percentage >= 70) return 'Very Good';
        if (percentage >= 60) return 'Good';
        if (percentage >= 50) return 'Satisfactory';
        if (percentage >= 40) return 'Needs Improvement';
        return 'Poor';
    }

    // Function to generate PDF for all results
    $('#generatePdfBtn').click(function() {
        var formData = $('#resultViewForm').serialize();
        
        // Validate if results are loaded
        if ($('#resultsTable tbody tr').length === 0) {
            Toast.fire({
                icon: 'error',
                title: 'No results to generate PDF'
            });
            return;
        }

        // Open PDF in new tab
        window.open("{{ route('results.generate-pdf') }}?" + formData, '_blank');
    });





    // Function to generate PDF for single student
    function generateStudentPdf(resultId) {
        window.open("{{ route('results.generate-student-pdf') }}?result_id=" + resultId, '_blank');
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