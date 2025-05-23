@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-user-graduate"></i> Enrollment Report
                    </h3>
                </div>
                
                <!-- Main Content Area with Sidebar and Report -->
                <div class="card-body p-0">
                    <div class="row g-0">
                        <!-- Class/Section Selection Sidebar (col-md-4) -->
                        <div class="col-md-4 border-end">
                            <div class="p-3">
                                <form id="reportForm">
                                    @csrf
                                    <input type="hidden" name="institute_id" id="institute_id" value="{{ auth()->user()->institute_id ?? '' }}">
                                    <input type="hidden" name="session_id" id="session_id" value="">
                                    
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
                                            <label for="session_select" class="form-label fw-bold">
                                                <i class="fas fa-calendar-alt"></i> Academic Session
                                            </label>
                                            <select name="session_select" id="session_select" class="form-control form-select">
                                                <!-- Sessions will be loaded here -->
                                            </select>
                                        </div>
                                    </div>

                                    <h5 class="mb-3"><i class="fas fa-chalkboard"></i> Select Class & Section</h5>
                                    <div id="classListContainer">
                                        <div class="list-group" id="classListGroup">
                                            <!-- Class list items will be loaded here -->
                                            <div class="text-center py-5" id="classLoading">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="mt-2">Loading available classes...</p>
                                            </div>
                                            <div class="text-center py-5" id="noClassFound" style="display: none;">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i> No classes found for this session.
                                                </div>
                                            </div>
                                            <div class="text-center py-5" id="selectSectionMessage" style="display: block;">
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-circle"></i> Please select a section to view report.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Report Display Area (col-md-8) -->
                        <div class="col-md-8" id="reportContainer" style="display: none;">
                            <div class="p-3">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h2 id="reportTitle" class="mb-0"></h2>
                                    <button id="backToSelection" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-arrow-left"></i> Change Selection
                                    </button>
                                </div>
                                
                                <div class="row mb-4 g-2">
                                    <div class="col-md-3 col-6">
                                        <div class="card bg-light h-100">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Institute</h6>
                                                <h5 id="instituteName" class="mb-0"></h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="card bg-light h-100">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Session</h6>
                                                <h5 id="sessionName" class="mb-0"></h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="card bg-light h-100">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Class</h6>
                                                <h5 id="className" class="mb-0"></h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="card bg-light h-100">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Section</h6>
                                                <h5 id="sectionName" class="mb-0"></h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive mt-4">
                                    <table class="table table-bordered table-hover" id="reportTable">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th>Reg.No#</th>
                                                <th>Student Name</th>
                                                <th>Courses</th>
                                                <th>Teachers</th>
                                                <th>Enrollment Date</th>
                                                <th width="80">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Report data will be loaded here via AJAX -->
                                            <tr id="reportLoading">
                                                <td colspan="6" class="text-center py-5">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <p class="mt-2">Loading enrollment data...</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-4 text-center">
                                    <button id="generatePdfBtn" class="btn btn-success">
                                        <i class="fas fa-file-pdf"></i> Download PDF Report
                                    </button>
                                </div>
                            </div>
                        </div>
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

    // Setup AJAX headers
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Track currently selected section
    let currentSelectedSection = null;
    
    // Load initial data based on user role
    initializeUserData();
    
    // Handle session change
    $('#session_select').change(function() {
        const sessionId = $(this).val();
        $('#session_id').val(sessionId);
        if (sessionId) {
            loadClassesWithSections(sessionId);
        } else {
            $('#classListGroup').empty();
            $('#selectSectionMessage').show();
            $('#reportContainer').hide();
        }
    });
    
    // For Super Admin - handle institute change
    @if(auth()->user()->hasRole('Super Admin'))
    $('#institute_select').change(function() {
        const instituteId = $(this).val();
        $('#institute_id').val(instituteId);
        if (instituteId) {
            loadSessions(instituteId);
            $('#classListGroup').empty();
            $('#noClassFound').hide();
            $('#selectSectionMessage').show();
            $('#classLoading').show();
            $('#reportContainer').hide();
        } else {
            $('#session_select').empty().append('<option value="">Select Session</option>');
            $('#classListGroup').empty();
            $('#selectSectionMessage').show();
            $('#reportContainer').hide();
        }
    });
    @endif
    
    // Handle back to selection button
    $('#backToSelection').click(function() {
        $('#reportContainer').hide();
    });
    
    // Generate PDF when button is clicked
    $(document).on('click', '#generatePdfBtn', function() {
        generatePdf();
    });
    
    // Function to initialize user data based on role
    function initializeUserData() {
        const instituteId = $('#institute_id').val();
        
        @if(!auth()->user()->hasRole('Super Admin'))
        if (instituteId) {
            loadSessionsAndSelectCurrent(instituteId);
        }
        @endif
    }
    
    // Function to check if session is in current year
    function isCurrentYear(session) {
        if (!session.start_date || !session.end_date) return false;
        
        const now = new Date();
        const currentYear = now.getFullYear();
        const startDate = new Date(session.start_date);
        const endDate = new Date(session.end_date);
        
        // Check if current date falls within session dates
        return (now >= startDate && now <= endDate);
    }

    // Function to load sessions and auto-select current one
    function loadSessionsAndSelectCurrent(instituteId) {
        $.ajax({
            url: "{{ route('enrollments.dropdowns') }}",
            type: "GET",
            data: { institute_id: instituteId },
            success: function(data) {
                $('#session_select').empty().append('<option value="">Select Session</option>');
                
                if(data.sessions && data.sessions.length > 0) {
                    let currentSessionId = null;
                    const now = new Date();
                    // Populate sessions dropdown
                    $.each(data.sessions, function(key, session) {
                        const isCurrent = isCurrentYear(session);
                        const disabled = @if(auth()->user()->hasRole('Teacher')) !isCurrent ? 'disabled' : '' @else '' @endif;
                        const selected = isCurrent ? 'selected' : '';
                        $('#session_select').append(
                            `<option value="${session.id}" ${selected} ${disabled}>
                                ${session.session_name}
                            </option>`
                        );
                        if (selected) {
                            currentSessionId = session.id;
                        }
                    });
                    
                    // If found current session, set it and load classes
                    if (currentSessionId) {
                        $('#session_id').val(currentSessionId);
                        loadClassesWithSections(currentSessionId);
                        Toast.fire({
                            icon: 'success',
                            title: 'Current session loaded successfully'
                        });
                    } else {
                        // If no current session, don't select anything
                        $('#session_select').val('');
                        $('#selectSectionMessage').show();
                        Toast.fire({
                            icon: 'info',
                            title: 'No current session found'
                        });
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading sessions:', xhr.responseText);
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to load sessions'
                });
            }
        });
    }

    // Function to load sessions for Super Admin
    function loadSessions(instituteId) {
        $.ajax({
            url: "{{ route('enrollments.dropdowns') }}",
            type: "GET",
            data: { institute_id: instituteId },
            success: function(data) {
                $('#session_select').empty().append('<option value="">Select Session</option>');
                
                if(data.sessions && data.sessions.length > 0) {
                    let currentSessionId = null;
                    
                    $.each(data.sessions, function(key, session) {
                        const selected = isCurrentYear(session) ? 'selected' : '';
                        $('#session_select').append(`<option value="${session.id}" ${selected}>${session.session_name}</option>`);
                        
                        if (selected) {
                            currentSessionId = session.id;
                        }
                    });
                    
                    // If found current session, set it
                    if (currentSessionId) {
                        $('#session_id').val(currentSessionId);
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading sessions:', xhr.responseText);
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to load sessions'
                });
            }
        });
    }
    
    // Function to load classes with sections as list items
    function loadClassesWithSections(sessionId) {
        const instituteId = $('#institute_id').val();
        $('#classLoading').show();
        $('#noClassFound').hide();
        $('#selectSectionMessage').hide();
        
        $.ajax({
            url: "{{ route('enrollments.dropdowns') }}",
            type: "GET",
            data: { 
                institute_id: instituteId,
                session_id: sessionId
            },
            success: function(data) {
                $('#classListGroup').empty();
                
                if (data.classes && data.classes.length > 0) {
                    // Get classes and build list
                    $.each(data.classes, function(key, classItem) {
                        // Load sections for each class
                        getSectionsForClass(instituteId, classItem.id, classItem);
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Classes loaded successfully'
                    });
                } else {
                    $('#classLoading').hide();
                    $('#noClassFound').show();
                    $('#selectSectionMessage').hide();
                    Toast.fire({
                        icon: 'info',
                        title: 'No classes found for this session'
                    });
                }
            },
            error: function(xhr) {
                $('#classLoading').hide();
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to load classes'
                });
            }
        });
    }
    
    // Function to get sections for a class and build list item
    function getSectionsForClass(instituteId, classId, classItem) {
        $.ajax({
            url: "{{ route('enrollments.dropdowns') }}",
            type: "GET",
            data: {
                institute_id: instituteId,
                class_id: classId
            },
            success: function(data) {
                if (data.sections && data.sections.length > 0) {
                    // Create class list item with sections
                    createClassListItem(classItem, data.sections);
                    Toast.fire({
                        icon: 'success',
                        title: 'Sections loaded successfully'
                    });
                } else {
                    $('#selectSectionMessage').show();
                    Toast.fire({
                        icon: 'info',
                        title: 'No sections found for this class'
                    });
                }
                
                $('#classLoading').hide();
            },
            error: function(xhr) {
                $('#classLoading').hide();
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to load sections'
                });
            }
        });
    }
    
    // Function to create class list item with sections
    function createClassListItem(classItem, sections) {
        const listItemHtml = `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">${classItem.name}</h6>
                    <span class="badge bg-primary rounded-pill">${sections.length} sections</span>
                </div>
                <div class="mt-2">
                    ${sections.map(section => `
                        <button type="button" 
                            class="btn btn-sm btn-outline-primary me-2 mb-2 section-btn" 
                            data-class-id="${classItem.id}" 
                            data-section-id="${section.id}"
                            data-class-name="${classItem.name}"
                            data-section-name="${section.section_name}">
                            <i class="fas fa-users me-1"></i> ${section.section_name}
                        </button>
                    `).join('')}
                </div>
            </div>
        `;
        
        $('#classListGroup').append(listItemHtml);
        
        // Attach event handler to section buttons
        $('.section-btn').off('click').on('click', function() {
            // Remove active class from previously selected section
            if (currentSelectedSection) {
                currentSelectedSection.removeClass('active-section');
            }
            
            // Add active class to newly selected section
            $(this).addClass('active-section');
            currentSelectedSection = $(this);
            
            const classId = $(this).data('class-id');
            const sectionId = $(this).data('section-id');
            const className = $(this).data('class-name');
            const sectionName = $(this).data('section-name');
            
            // Generate report for this class and section
            generateReportForClassSection(classId, sectionId, className, sectionName);
        });
    }
    
    // Function to generate report for selected class and section
    function generateReportForClassSection(classId, sectionId, className, sectionName) {
        const instituteId = $('#institute_id').val();
        const sessionId = $('#session_id').val();
        
        const formData = {
            institute_id: instituteId,
            session_id: sessionId,
            class_id: classId,
            section_id: sectionId
        };
        
        $.ajax({
            url: "{{ route('enrollments.report.generate') }}",
            type: "POST",
            data: formData,
            beforeSend: function() {
                $('#reportTable tbody').html(`
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading enrollment data...</p>
                        </td>
                    </tr>
                `);
                $('#reportContainer').show();
                $('#selectSectionMessage').hide();
            },
            success: function(response) {
                if (response.success) {
                    displayReport(response.data, className, sectionName);
                    Toast.fire({
                        icon: 'success',
                        title: 'Report generated successfully'
                    });
                } else {
                    $('#reportTable tbody').html(`
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i> Failed to generate report
                                </div>
                            </td>
                        </tr>
                    `);
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Failed to generate report'
                    });
                }
            },
            error: function(xhr) {
                $('#reportTable tbody').html(`
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> Error: ${xhr.responseText}
                            </div>
                        </td>
                    </tr>
                `);
                Toast.fire({
                    icon: 'error',
                    title: 'Error generating report'
                });
            }
        });
    }
    
    // Function to display report data
    function displayReport(data, className, sectionName) {
        // Set report header information
        $('#reportTitle').text(`${data.institute} - Enrollment Report`);
        $('#instituteName').text(data.institute);
        $('#sessionName').text(data.session);
        $('#className').text(className || data.class);
        $('#sectionName').text(sectionName || data.section);

        // Populate students table
        var tbody = $('#reportTable tbody');
        tbody.empty();

        if (data.students && data.students.length > 0) {
            $.each(data.students, function(index, student) {
                const isActive = student.status === 'active';
                const nameColor = isActive ? 'text-success' : '';
                const rowClass = isActive ? 'cursor-pointer' : '';
                
                var row = `
                    <tr class="${rowClass}" onclick="window.open('/enrollments/report/student/${student.student_id}', '_blank')">
                        <td>${student.student.roll_number || 'N/A'}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-light rounded-circle text-center me-2" style="width: 32px; height: 32px; line-height: 32px;">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                                <div class="${nameColor}">${student.student.name}</div>
                            </div>
                        </td>
                        <td>${student.courses}</td>
                        <td>${student.teachers}</td>
                        <td>${student.enrollment_date}</td>
                        <td>
                            <a href="/enrollments/report/student/${student.student_id}" 
                            class="btn btn-sm btn-info" 
                            target="_blank" rel="noopener noreferrer" 
                            onclick="event.stopPropagation()">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        } else {
            tbody.append('<tr><td colspan="6" class="text-center py-4">No enrollment records found</td></tr>');
            Toast.fire({
                icon: 'info',
                title: 'No enrollment records found'
            });
        }
    }

    // Function to generate PDF
    function generatePdf() {
        var formData = {
            institute: $('#instituteName').text(),
            session: $('#sessionName').text(),
            class: $('#className').text(),
            section: $('#sectionName').text(),
            students: []
        };

        $('#reportTable tbody tr').each(function() {
            // Skip empty result rows
            if ($(this).find('td').length < 3) return;
            
            formData.students.push({
                name: $(this).find('td:eq(1)').text().trim(),
                roll_number: $(this).find('td:eq(0)').text(),
                courses: $(this).find('td:eq(2)').text(),
                teachers: $(this).find('td:eq(3)').text(),
                enrollment_date: $(this).find('td:eq(4)').text()
            });
        });

        $.ajax({
            url: "{{ route('enrollments.report.pdf') }}",
            type: "POST",
            data: formData,
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
                link.download = 'enrollment-report-' + new Date().toISOString().slice(0, 10) + '.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                Toast.fire({
                    icon: 'success',
                    title: 'PDF generated successfully'
                });
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
});
</script>

<style>
/* Custom styles for enhanced UI */
.list-group-item {
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    margin-bottom: 10px;
    border-radius: 5px;
}
.list-group-item:hover {
    border-left-color: var(--bs-primary);
    background-color: #f8f9fa;
}
.section-btn {
    border-radius: 5px;
    transition: all 0.2s ease;
}
.section-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}
.section-btn.active-section {
    background-color: var(--bs-primary);
    color: white !important;
}
.avatar-sm {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
#classListGroup {
    max-height: calc(100vh - 300px);
    overflow-y: auto;
}
.cursor-pointer {
    cursor: pointer;
}
.cursor-pointer:hover {
    background-color: rgba(0, 0, 0, 0.03);
}
@media (max-width: 767.98px) {
    .border-end {
        border-right: none !important;
        border-bottom: 1px solid #dee2e6;
    }
    #classListGroup {
        max-height: 300px;
    }
}
</style>
@endpush