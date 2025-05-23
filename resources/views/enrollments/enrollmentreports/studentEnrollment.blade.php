@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm rounded">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <div class="fs-5 fw-semibold">Student Background Detail</div>
                    <a href="{{ route('enrollments.report') }}" class="btn btn-warning btn-sm">← Back</a>
                </div>

                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <img src="{{ $student->profile_image ? asset('storage/' . $student->profile_image) : 'https://i.pravatar.cc/120?u=' . $student->id }}" 
                             alt="Student Photo" 
                             class="rounded-circle border border-3 border-primary me-4" width="120" height="120" />
                        <div>
                            <h2 class="fw-bold mb-1">{{ $student->name }}</h2>
                            <p class="text-muted mb-0">Roll Number: <strong>{{ $student->roll_number ?? 'N/A' }}</strong></p>
                        </div>
                    </div>

                    <hr />

                    <section class="mb-4">
                        <h5 class="text-primary fw-semibold mb-3">Personal Details</h5>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Full Name:</strong> {{ $student->name }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Email:</strong> {{ $student->email ?? 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>CNIC:</strong> {{ $student->cnic ?? 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Phone:</strong> {{ $student->phone ?? 'N/A' }}
                            </div>
                            <div class="col-md-12 mb-2">
                                <strong>Address:</strong> {{ $student->address ?? 'N/A' }}
                            </div>
                        </div>
                    </section>

                    <hr />

                    <section class="mb-4">
                        <h5 class="text-primary fw-semibold mb-3">Academic History</h5>
                        
                        @foreach($enrollments as $sessionId => $sessionEnrollments)
                        @php
                            $firstEnrollment = $sessionEnrollments->first();
                        @endphp
                        
                        <div class="card mb-4">
                            <div class="card-header bg-info">
                                <h6 class="mb-0">
                                    {{ $firstEnrollment->session->session_name }} - 
                                    {{ $firstEnrollment->class->name }} - 
                                    {{ $firstEnrollment->section->section_name }}
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <thead class="bg-info text-white">
                                            <tr>
                                                <th>#</th>
                                                <th>Course</th>
                                                <th>Teacher</th>
                                                <th>Enrollment Date</th>
                                                <th>Attendance</th>
                                                <th>Result</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sessionEnrollments as $index => $enrollment)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $enrollment->course->course_name ?? 'N/A' }}</td>
                                                <td>{{ $enrollment->teacher->name ?? 'N/A' }}</td>
                                                <td>{{ $enrollment->enrollment_date }}</td>
                                                <td>
                                                    @if($enrollment->attendance_percentage < 65)
                                                    <span class="badge bg-danger">Not Eligible</span>
                                                        @else
                                                            <span class="badge 
                                                                {{ $enrollment->attendance_percentage >= 75 ? 'bg-success' : 'bg-warning' }}">
                                                                {{ $enrollment->attendance_percentage }}%
                                                            </span>
                                                        @endif
                                                    <button class="btn btn-sm btn-outline-primary view-attendance-btn" 
                                                            data-enrollment-id="{{ $enrollment->id }}"
                                                            data-student-id="{{ $student->id }}"
                                                            data-course-id="{{ $enrollment->course_id }}">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    

                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info view-result-btn" 
                                                            data-enrollment-id="{{ $enrollment->id }}"
                                                            data-student-id="{{ $student->id }}"
                                                            data-course-id="{{ $enrollment->course_id }}">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </section>

                    <hr />

                    <div class="text-center">
                        <button id="generateStudentPdfBtn" class="btn btn-success">
                            <i class="fas fa-file-pdf"></i> Download Student PDF
                        </button>
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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="attendanceModalLabel">Attendance Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="attendanceModalBody">
                <!-- Content will be loaded here via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="generateAttendancePdfBtn">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="resultModalLabel">Result Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="resultModalBody">
                <!-- Content will be loaded here via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info" id="generateResultPdfBtn">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Replace the existing script with this updated version

$(document).ready(function() {
    // View Attendance Button Click
    $(document).on('click', '.view-attendance-btn', function() {
        var enrollmentId = $(this).data('enrollment-id');
        var studentId = $(this).data('student-id');
        var courseId = $(this).data('course-id');
        
        loadAttendanceDetails(enrollmentId, studentId, courseId);
    });

    // View Result Button Click
    $(document).on('click', '.view-result-btn', function() {
        var enrollmentId = $(this).data('enrollment-id');
        var studentId = $(this).data('student-id');
        var courseId = $(this).data('course-id');
        
        loadResultDetails(enrollmentId, studentId, courseId);
    });

    // Generate Attendance PDF Button
    $(document).on('click', '#generateAttendancePdfBtn', function() {
        var data = $(this).data('modal-data');
        generateAttendancePdf(data);
    });

    // Generate Result PDF Button
    $(document).on('click', '#generateResultPdfBtn', function() {
        var data = $(this).data('modal-data');
        generateResultPdf(data);
    });

    // Function to load attendance details
    function loadAttendanceDetails(enrollmentId, studentId, courseId) {
        $.ajax({
            url: "{{ route('enrollments.report.attendance-details') }}",
            type: "GET",
            data: {
                enrollment_id: enrollmentId,
                student_id: studentId,
                course_id: courseId
            },
            beforeSend: function() {
                $('#attendanceModalBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
                $('#attendanceModal').modal('show');
            },
            success: function(response) {
                if (response.error) {
                    $('#attendanceModalBody').html('<div class="alert alert-danger">' + response.error + '</div>');
                    return;
                }

                var modalContent = `
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <p><strong>Student:</strong> ${response.student.name}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Roll No:</strong> ${response.student.roll_number || 'N/A'}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Course:</strong> ${response.course.course_name}</p>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <p><strong>Session:</strong> ${response.session.session_name}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Class:</strong> ${response.class.name}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Section:</strong> ${response.section.section_name}</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th>Date</th>
                                    <th>Time Slot</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>`;

                response.attendance.forEach(function(record) {
                    modalContent += `
                        <tr>
                            <td>${record.date}</td>
                            <td>${record.slot_times}</td>
                            <td>
                                <span class="badge ${record.status === 'Present' ? 'bg-success' : 'bg-danger'}">
                                    ${record.status}
                                </span>
                            </td>
                        </tr>`;
                });

                modalContent += `
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 alert ${response.percentage >= 65 ? 'alert-success' : 'alert-danger'}">
                        <h5 class="mb-0">
                            Overall Attendance: ${response.percentage}% 
                            <span class="float-end">
                                ${response.percentage >= 65 ? 'Eligible' : 'Not Eligible'}
                            </span>
                        </h5>
                    </div>`;

                $('#attendanceModalBody').html(modalContent);
                
                // Set up PDF download button with all modal data
                $('#generateAttendancePdfBtn').data('modal-data', response);
            },
            error: function(xhr) {
                $('#attendanceModalBody').html('<div class="alert alert-danger">Failed to load attendance details</div>');
                console.error('Error loading attendance details:', xhr.responseText);
            }
        });
    }

    // Function to load result details
    function loadResultDetails(enrollmentId, studentId, courseId) {
        $.ajax({
            url: "{{ route('enrollments.report.result-details') }}",
            type: "GET",
            data: {
                enrollment_id: enrollmentId,
                student_id: studentId,
                course_id: courseId
            },
            beforeSend: function() {
                $('#resultModalBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
                $('#resultModal').modal('show');
            },
            success: function(response) {
                if (response.error) {
                    $('#resultModalBody').html('<div class="alert alert-danger">' + response.error + '</div>');
                    return;
                }

                var modalContent = `
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <p><strong>Student:</strong> ${response.student.name}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Roll No:</strong> ${response.student.roll_number || 'N/A'}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Course:</strong> ${response.course.course_name}</p>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <p><strong>Session:</strong> ${response.session.session_name}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Class:</strong> ${response.class.name}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Section:</strong> ${response.section.section_name}</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-info">
                                <tr>
                                    <th>Component</th>
                                    <th>Obtained Marks</th>
                                    <th>Total Marks</th>
                                    <th>Weight %</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>`;

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

                modalContent += `
                                <tr class="table-info">
                                    <th>Total</th>
                                    <th>${response.result.obtained_marks}</th>
                                    <th>${response.result.total_marks}</th>
                                    <th>100%</th>
                                    <th>
                                        <span class="badge ${response.result.status === 'Pass' ? 'bg-success' : 'bg-danger'}">
                                            ${response.result.status}
                                        </span>
                                    </th>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <p><strong>Percentage:</strong> <span class="badge bg-primary">${response.percentage}%</span></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Grade:</strong> <span class="badge bg-primary">${response.grade}</span></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Status:</strong> <span class="badge ${response.result.status === 'Pass' ? 'bg-success' : 'bg-danger'}">${response.result.status}</span></p>
                        </div>
                    </div>`;

                $('#resultModalBody').html(modalContent);
                
                // Set up PDF download button with all modal data
                $('#generateResultPdfBtn').data('modal-data', response);
            },
            error: function(xhr) {
                $('#resultModalBody').html('<div class="alert alert-danger">Failed to load result details</div>');
                console.error('Error loading result details:', xhr.responseText);
            }
        });
    }

    // Function to generate attendance PDF
    function generateAttendancePdf(data) {
        $.ajax({
            url: "{{ route('enrollments.report.generate-attendance-pdf') }}",
            type: "POST",
            data: data,
            xhrFields: {
                responseType: 'blob'
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#generateAttendancePdfBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating PDF...');
            },
            success: function(response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'attendance-' + data.student.id + '-' + data.course.id + '.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function(xhr) {
                alert('Failed to generate PDF');
            },
            complete: function() {
                $('#generateAttendancePdfBtn').prop('disabled', false).html('<i class="fas fa-file-pdf"></i> Download PDF');
            }
        });
    }

    // Function to generate result PDF
    function generateResultPdf(data) {
        $.ajax({
            url: "{{ route('enrollments.report.generate-result-pdf') }}",
            type: "POST",
            data: data,
            xhrFields: {
                responseType: 'blob'
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#generateResultPdfBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating PDF...');
            },
            success: function(response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'result-' + data.student.id + '-' + data.course.id + '.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function(xhr) {
                alert('Failed to generate PDF');
            },
            complete: function() {
                $('#generateResultPdfBtn').prop('disabled', false).html('<i class="fas fa-file-pdf"></i> Download PDF');
            }
        });
    }

    // PDF generation for student report (existing code)
    $('#generateStudentPdfBtn').click(function() {
        var studentData = {
            student: @json($student),
            enrollments: @json($enrollments)
        };

        $.ajax({
            url: "{{ route('enrollments.report.student-pdf') }}",
            type: "POST",
            data: studentData,
            xhrFields: {
                responseType: 'blob'
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#generateStudentPdfBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating PDF...');
            },
            success: function(response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'student-{{ $student->id }}-enrollment.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function(xhr) {
                alert('Failed to generate PDF');
            },
            complete: function() {
                $('#generateStudentPdfBtn').prop('disabled', false).html('<i class="fas fa-file-pdf"></i> Download Student PDF');
            }
        });
    });
});
</script>
@endpush