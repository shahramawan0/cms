@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h3 class="card-title mb-0 text-white">
                            <i class="fas fa-file-alt text-white"></i> Attendance Report
                        </h3>
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="card-body border-bottom">
                    <form id="reportForm">
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
                                </div>
                            </div>
                            @endif
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="session_id">Academic Session <span class="text-danger">*</span></label>
                                    <select name="session_id" id="session_id" class="form-control" required>
                                        <option value="">Select Session</option>
                                    </select>
                                </div>
                            </div>
                        
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="class_id">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" id="class_id" class="form-control" required>
                                        <option value="">Select Class</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="section_id">Section <span class="text-danger">*</span></label>
                                    <select name="section_id" id="section_id" class="form-control" required>
                                        <option value="">Select Section</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="course_id">Course <span class="text-danger">*</span></label>
                                    <select name="course_id" id="course_id" class="form-control" required>
                                        <option value="">Select Course</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="teacher_id">Teacher <span class="text-danger">*</span></label>
                                    <select name="teacher_id" id="teacher_id" class="form-control" required>
                                        <option value="">Select Teacher</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" id="generateReportBtn" class="btn btn-primary mt-3 btn-sm">
                                    <i class="fas fa-chart-bar"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Report Display Section -->
                <div class="card-body" id="reportContainer" style="display: none;">
                    <div class="bg-white rounded shadow-sm p-4 mb-4">
                        <h2 id="reportTitle" class="text-center mb-4"></h2>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Institute:</strong> <span id="instituteName"></span></p>
                                <p><strong>Session:</strong> <span id="sessionName"></span></p>
                                <p><strong>Class:</strong> <span id="className"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Section:</strong> <span id="sectionName"></span></p>
                                <p><strong>Course:</strong> <span id="courseName"></span></p>
                                <p><strong>Teacher:</strong> <span id="teacherName"></span></p>
                            </div>
                            <div class="col-12">
                                <p><strong>Date Range:</strong> <span id="dateRange"></span></p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="reportTable">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Student Name</th>
                                        <th>Roll Number</th>
                                        <th>Email</th>
                                        <th>CNIC</th>
                                        <th>Phone</th>
                                        <th>Present</th>
                                        <th>Total Classes</th>
                                        <th>Percentage</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded here via AJAX -->
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 text-center">
                            <button id="generatePdfBtn" class="btn btn-success btn-sm">
                                <i class="fas fa-file-pdf"></i> Download PDF Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge-present {
        background-color: #28a745;
        color: white;
    }
    .badge-absent {
        background-color: #dc3545;
        color: white;
    }
    .table th {
        white-space: nowrap;
    }
</style>
@endpush

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
            $('#class_id, #section_id, #course_id').empty().append('<option value="">Select</option>');
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
            $('#section_id, #course_id').empty().append('<option value="">Select</option>');
        } else {
            $('#class_id, #section_id, #course_id').empty().append('<option value="">Select</option>');
        }
    });

    // When class changes, load sections for that class
    $('#class_id').change(function() {
        var classId = $(this).val();
        var instituteId = $('#institute_id').val();
        var sessionId = $('#session_id').val();

        if (classId && instituteId && sessionId) {
            loadSections(instituteId, sessionId, classId);
            $('#course_id').empty().append('<option value="">Select</option>');
        } else {
            $('#section_id, #course_id').empty().append('<option value="">Select</option>');
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
        } else {
            $('#course_id').empty().append('<option value="">Select</option>');
        }
    });
    $('#course_id').change(function() {
        var courseId = $(this).val();
        var instituteId = $('#institute_id').val();
        var sessionId = $('#session_id').val();
        var classId = $('#class_id').val();
        var sectionId = $('#section_id').val();

        if (courseId && instituteId && sessionId && classId && sectionId) {
            loadTeachers(instituteId, sessionId, classId, sectionId, courseId);
        } else {
            $('#teacher_id').empty().append('<option value="">Select Teacher</option>');
        }
    });


    // Generate report when button is clicked
    $('#generateReportBtn').click(function() {
        generateReport();
    });

    // Generate PDF when button is clicked
    $(document).on('click', '#generatePdfBtn', function() {
        generatePdf();
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

    // Function to generate report
    function generateReport() {
        // Validate form
        var isValid = true;
        $('#reportForm select, #reportForm input').each(function() {
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

        var formData = $('#reportForm').serialize();

        $.ajax({
            url: "{{ route('attendances.report.generate') }}",
            type: "POST",
            data: formData,
            beforeSend: function() {
                $('#generateReportBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating...');
            },
            success: function(response) {
                if (response.success) {
                    displayReport(response.data);
                    $('#reportContainer').show();
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Failed to generate report'
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $(`#${key}`).addClass('is-invalid');
                    });
                    
                    Toast.fire({
                        icon: 'error',
                        title: 'Please fix the errors in the form'
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.error || 'Failed to generate report'
                    });
                }
            },
            complete: function() {
                $('#generateReportBtn').prop('disabled', false).html('<i class="fas fa-chart-bar"></i> Generate Report');
            }
        });
    }

    // Function to display report data
    function displayReport(data) {
    // Set report header information
    $('#reportTitle').text(`${data.institute} - Attendance Report`);
    $('#instituteName').text(data.institute);
    $('#sessionName').text(data.session);
    $('#className').text(data.class);
    $('#sectionName').text(data.section);
    $('#courseName').text(data.course);
    $('#teacherName').text(data.teacher);
    $('#dateRange').text(`${data.start_date} to ${data.end_date}`);

    // Populate students table
    var tbody = $('#reportTable tbody');
    tbody.empty();

    if (data.students && data.students.length > 0) {
        $.each(data.students, function(index, student) {
            var statusClass = student.status === 'Present' ? 'badge-present' : 'badge-absent';
            
            var row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${student.student.name}</td>
                    <td>${student.student.roll_number || 'N/A'}</td>
                    <td>${student.student.email || 'N/A'}</td>
                    <td>${student.student.cnic || 'N/A'}</td>
                    <td>${student.student.phone || 'N/A'}</td>
                    <td>${student.present_count}</td>
                    <td>${student.total_classes}</td>
                    <td>${student.percentage}%</td>
                    <td><span class="badge ${statusClass}">${student.status}</span></td>
                </tr>
            `;
            tbody.append(row);
        });
    } else {
        tbody.append('<tr><td colspan="10" class="text-center">No attendance records found</td></tr>');
    }
}

    // Function to generate PDF
    function generatePdf() {
    var reportData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        institute: $('#instituteName').text(),
        session: $('#sessionName').text(),
        class: $('#className').text(),
        section: $('#sectionName').text(),
        course: $('#courseName').text(),
        teacher: $('#teacherName').text(),
        start_date: $('#reportForm input[name="start_date"]').val(),
        end_date: $('#reportForm input[name="end_date"]').val(),
        students: []
    };

    // Collect all student data
    $('#reportTable tbody tr').each(function() {
        reportData.students.push({
            name: $(this).find('td:eq(1)').text(),
            roll_number: $(this).find('td:eq(2)').text(),
            email: $(this).find('td:eq(3)').text(),
            cnic: $(this).find('td:eq(4)').text(),
            phone: $(this).find('td:eq(5)').text(),
            present_count: $(this).find('td:eq(6)').text(),
            total_classes: $(this).find('td:eq(7)').text(),
            percentage: $(this).find('td:eq(8)').text(),
            status: $(this).find('td:eq(9)').text()
        });
    });

    // AJAX request to generate PDF
    $.ajax({
        url: "{{ route('attendances.report.pdf') }}",
        type: "POST",
        data: reportData,
        xhrFields: {
            responseType: 'blob'
        },
        beforeSend: function() {
            $('#generatePdfBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating PDF...');
        },
        success: function(response) {
            var blob = new Blob([response], { type: 'application/pdf' });
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = 'attendance-report-' + new Date().toISOString().slice(0, 10) + '.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
        error: function(xhr) {
            Toast.fire({
                icon: 'error',
                title: 'Failed to generate PDF'
            });
        },
        complete: function() {
            $('#generatePdfBtn').prop('disabled', false).html('<i class="fas fa-file-pdf"></i> Download PDF Report');
        }
    });
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
        $('#session_id, #class_id, #section_id, #course_id').empty().append('<option value="">Select</option>');
    }
});
</script>
@endpush