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
                                <i class="fas fa-chalkboard-teacher"></i> Lecture Management
                            </h3>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="row g-0">
                        <!-- Course Selection Sidebar -->
                        <div class="col-md-4 border-end">
                            <div class="p-3">
                                <form id="lectureForm">
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
                                            <div class="text-center py-5" id="courseLoading">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="mt-2">Loading courses...</p>
                                            </div>
                                            <div class="text-center py-5" id="noCourseFound" style="display: none;">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i> No courses found.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Time Slots Area -->
                        <div class="col-md-8" id="lectureContentContainer" style="display: none;">
                            <div class="p-3">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 id="selectedCourseInfo" class="mb-0"></h5>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center w-100">
                                            <h6 class="mb-0">
                                                <i class="fas fa-clock me-1"></i>Uploaded Lectures
                                            </h6>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="todayBtn">Today</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="weekBtn">This Week</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="allBtn">All</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Time</th>
                                                        <th>Week</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="timeSlotsTableBody">
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

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-upload"></i> Upload Lecture
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="lectureUploadForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="institute_id" id="modalInstituteId">
                <input type="hidden" name="session_id" id="modalSessionId">
                <input type="hidden" name="class_id" id="modalClassId">
                <input type="hidden" name="section_id" id="modalSectionId">
                <input type="hidden" name="course_id" id="modalCourseId">
                <input type="hidden" name="teacher_id" id="modalTeacherId">
                <input type="hidden" name="slot_date" id="modalSlotDate">
                <input type="hidden" name="slot_time" id="modalSlotTime">
                <input type="hidden" name="status" value="active">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label required">Title</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Video Upload</label>
                                <div id="videoPreview"></div>
                                <div class="input-group" id="videoUploadGroup">
                                    <input type="file" class="form-control" name="video_file" accept="video/*">
                                    <button type="button" class="btn btn-outline-danger" id="removeVideo" style="display: none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Supported formats: MP4, WebM (Max: 500MB)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Document Upload</label>
                                <div id="pdfPreview"></div>
                                <div class="input-group" id="fileUploadGroup">
                                    <input type="file" class="form-control" name="lecture_file" required>
                                    <button type="button" class="btn btn-outline-danger" id="removeFile" style="display: none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Supported formats: PDF, DOC, etc. (Max: 10MB)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Lecture
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Custom styles for enhanced UI */
.list-group-item {
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    margin-bottom: 10px;
    border-radius: 5px;
    position: relative;
    overflow: hidden;
}

.list-group-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.03);
    transform: translateX(-100%);
    transition: transform 0.3s ease;
}

.list-group-item:hover::before {
    transform: translateX(0);
}

.list-group-item:hover {
    border-left-color: var(--bs-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Dynamic class colors */
.class-card-1 { border-left-color: #4e73df; }
.class-card-2 { border-left-color: #1cc88a; }
.class-card-3 { border-left-color: #f6c23e; }
.class-card-4 { border-left-color: #e74a3b; }
.class-card-5 { border-left-color: #36b9cc; }
.class-card-6 { border-left-color: #6f42c1; }
.class-card-7 { border-left-color: #fd7e14; }
.class-card-8 { border-left-color: #20c9a6; }
.class-card-9 { border-left-color: #858796; }
.class-card-10 { border-left-color: #5a5c69; }

.class-card-1:hover { background-color: rgba(78, 115, 223, 0.05); }
.class-card-2:hover { background-color: rgba(28, 200, 138, 0.05); }
.class-card-3:hover { background-color: rgba(246, 194, 62, 0.05); }
.class-card-4:hover { background-color: rgba(231, 74, 59, 0.05); }
.class-card-5:hover { background-color: rgba(54, 185, 204, 0.05); }
.class-card-6:hover { background-color: rgba(111, 66, 193, 0.05); }
.class-card-7:hover { background-color: rgba(253, 126, 20, 0.05); }
.class-card-8:hover { background-color: rgba(32, 201, 166, 0.05); }
.class-card-9:hover { background-color: rgba(133, 135, 150, 0.05); }
.class-card-10:hover { background-color: rgba(90, 92, 105, 0.05); }

.course-card {
    transition: all 0.2s ease;
    cursor: pointer;
}

.course-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.course-card.active {
    border-color: var(--bs-primary);
    background-color: #f8f9fa;
}

#courseListGroup {
    max-height: calc(100vh - 300px);
    overflow-y: auto;
}

.time-slot-item {
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
}

.time-slot-item:hover {
    background-color: #f8f9fa;
}

.time-slot-item.available {
    border-left: 3px solid var(--bs-success);
}

.time-slot-item.occupied {
    border-left: 3px solid var(--bs-danger);
}

@media (max-width: 767.98px) {
    .border-end {
        border-right: none !important;
        border-bottom: 1px solid #dee2e6;
    }
    #courseListGroup {
        max-height: 300px;
    }
}

/* Action buttons styling */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.action-buttons .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.action-buttons .btn:hover {
    transform: translateY(-1px);
}

/* New styles */
.form-label.required::after {
    content: "*";
    color: red;
    margin-left: 4px;
}

.table > :not(caption) > * > * {
    padding: 1rem 1rem;
}

.badge {
    font-size: 85%;
    padding: 0.5em 0.8em;
}

.btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
}
</style>
@endpush

@push('scripts')
<script>
    // Global function to edit lecture
    function editLecture(lectureId) {
        console.log('Edit lecture called with ID:', lectureId); // Debug log
        
        if (!lectureId || lectureId === 'undefined') {
            Toast.fire({
                icon: 'error',
                title: 'Invalid lecture ID'
            });
            return;
        }

        // Show loading state
        Swal.fire({
            title: 'Loading...',
            text: 'Please wait while we fetch the lecture details',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Reset form and clear previews
        $('#lectureUploadForm')[0].reset();
        $('#videoPreview').empty();
        $('#pdfPreview').empty();
        
        $.ajax({
            url: `/lectures/edit/${lectureId}`,
            type: 'GET',
            success: function(response) {
                Swal.close();
                
                if (response.success) {
                    const lecture = response.lecture;
                    
                    // Add lecture ID to form
                    $('#lectureUploadForm').append(`<input type="hidden" name="lecture_id" value="${lecture.id}">`);
                    
                    // Set form values
                    $('input[name="title"]').val(lecture.title);
                    $('textarea[name="description"]').val(lecture.description);
                    
                    // Set hidden fields
                    $('#modalInstituteId').val(lecture.institute_id);
                    $('#modalSessionId').val(lecture.session_id);
                    $('#modalClassId').val(lecture.class_id);
                    $('#modalSectionId').val(lecture.section_id);
                    $('#modalCourseId').val(lecture.course_id);
                    $('#modalTeacherId').val(lecture.teacher_id);
                    $('#modalSlotDate').val(lecture.slot_date);
                    $('#modalSlotTime').val(lecture.slot_time);
                    
                    // Show existing files if any
                    if (response.video_url) {
                        $('#videoPreview').html(`
                            <div class="mb-2">
                                <small class="text-muted">Current Video:</small>
                                <a href="${response.video_url}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-play-circle"></i> View Current Video
                                </a>
                            </div>
                        `);
                        $('#removeVideo').show();
                    }
                    
                    if (response.pdf_url) {
                        $('#pdfPreview').html(`
                            <div class="mb-2">
                                <small class="text-muted">Current PDF:</small>
                                <div class="btn-group">
                                    <a href="${response.pdf_url}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-file-pdf"></i> View PDF
                                    </a>
                                    <a href="${response.pdf_url}" download class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        `);
                        $('#removeFile').show();
                    }
                    
                    // Update modal title and button text
                    $('.modal-title').html('<i class="fas fa-edit"></i> Edit Lecture');
                    $('button[type="submit"]').html('<i class="fas fa-save"></i> Update Lecture');
                    
                    // Show the modal
                    $('#uploadModal').modal('show');
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Failed to load lecture details'
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.message || 'Failed to load lecture details'
                });
            }
        });
    }

    // Global function to open upload modal
    function openUploadModal(courseId, date, time, classId, sectionId, teacherId) {
        // Reset form and clear previews
        $('#lectureUploadForm')[0].reset();
        $('#videoPreview').empty();
        $('#pdfPreview').empty();
        $('input[name="lecture_id"]').remove();
        
        // Reset modal title and button text
        $('.modal-title').html('<i class="fas fa-upload"></i> Upload Lecture');
        $('#submitBtnText').text('Upload Lecture');
        
        // Set modal values
        $('#modalInstituteId').val($('#institute_id').val());
        $('#modalSessionId').val($('#session_id').val());
        $('#modalCourseId').val(courseId);
        $('#modalClassId').val(classId);
        $('#modalSectionId').val(sectionId);
        $('#modalTeacherId').val(teacherId);
        $('#modalSlotDate').val(date);
        $('#modalSlotTime').val(time);
        
        // Show modal
        $('#uploadModal').modal('show');
    }

    $(document).ready(function() {
        // Initialize Toast
        window.Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // Set up CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // For Admin - load data immediately
        @if(!auth()->user()->hasRole('Super Admin'))
        loadInitialDataForAdmin();
        @endif
        
        // For Super Admin - handle institute change
        @if(auth()->user()->hasRole('Super Admin'))
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
        @endif
        
        // When session changes, load courses
        $('#session_id').change(function() {
            const sessionId = $(this).val();
            if (sessionId) {
                loadCourses();
            } else {
                $('#courseListGroup').empty();
                $('#lectureContentContainer').hide();
            }
        });

        function loadInitialDataForAdmin() {
            const instituteId = $('#institute_id').val();
            if (instituteId) {
                loadSessionsAndSelectCurrent(instituteId);
            }
        }

        function loadSessionsAndSelectCurrent(instituteId) {
            $.ajax({
                url: "{{ route('lectures.dropdowns') }}",
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
        
        function loadCourses() {
            $('#courseLoading').show();
            $('#noCourseFound').hide();
            $('#lectureContentContainer').hide();

            const data = {
                institute_id: $('#institute_id').val(),
                session_id: $('#session_id').val()
            };

            $.ajax({
                url: "{{ route('lectures.get-courses') }}",
                type: "GET",
                data: data,
                success: function(courses) {
                    $('#courseLoading').hide();
                    $('#courseListGroup').empty();

                    if (courses && courses.length > 0) {
                        courses.forEach(function(course) {
                            const courseCard = `
                                <div class="list-group-item course-card" 
                                    data-course-id="${course.id}" 
                                    data-class-id="${course.class_id}" 
                                    data-section-id="${course.section_id}" 
                                    data-teacher-id="${course.teacher_id}"
                                    style="border-left: 4px solid ${course.background_color || '#3490dc'}; background: linear-gradient(135deg, ${course.background_color}11 0%, #ffffff 100%);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-1">${course.course_name}</h6>
                                        <span class="badge rounded-pill" style="background-color: ${course.background_color || '#3490dc'}">
                                            ${course.class_name} - ${course.section_name}
                                        </span>
                                    </div>
                                    <p class="mb-1 text-muted small">
                                        <i class="fas fa-user-tie"></i> ${course.teacher_name}
                                    </p>
                                </div>
                            `;
                            $('#courseListGroup').append(courseCard);
                        });

                        // Handle course card click
                        $('.course-card').click(function() {
                            $('.course-card').removeClass('active');
                            $(this).addClass('active');
                            
                            const courseId = $(this).data('course-id');
                            const classId = $(this).data('class-id');
                            const sectionId = $(this).data('section-id');
                            const teacherId = $(this).data('teacher-id');
                            const courseName = $(this).find('h6').text();
                            const courseInfo = $(this).find('.badge').text();
                            
                            $('#selectedCourseInfo').html(`
                                <span class="text-primary">${courseName}</span>
                                <small class="text-muted ms-2">${courseInfo}</small>
                            `);
                            
                            loadTimeSlots(courseId, classId, sectionId, teacherId);
                            $('#lectureContentContainer').show();
                        });
                    } else {
                        $('#noCourseFound').show();
                    }
                },
                error: function(xhr) {
                    $('#courseLoading').hide();
                    $('#noCourseFound').show();
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.error || 'Failed to load courses'
                    });
                }
            });
        }
        
        function loadTimeSlots(courseId, classId, sectionId, teacherId) {
            const data = {
                institute_id: $('#institute_id').val(),
                session_id: $('#session_id').val(),
                course_id: courseId,
                class_id: classId,
                section_id: sectionId,
                teacher_id: teacherId
            };
            
            $.ajax({
                url: "{{ route('lectures.get-time-slots') }}",
                type: "GET",
                data: data,
                success: function(slots) {
                    displayTimeSlots(courseId, slots);
                },
                error: function(xhr) {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.error || 'Failed to load time slots'
                    });
                }
            });
        }
        
        function displayTimeSlots(courseId, slots) {
            const tbody = $('#timeSlotsTableBody');
            tbody.empty();
            const isStudent = {{ auth()->user()->hasRole('Student') ? 'true' : 'false' }};

            if (slots && slots.length > 0) {
                slots.forEach(function(slot) {
                    const isAvailable = slot.status === 'Available';
                    const formattedDate = moment(slot.date).format('YYYY-MM-DD');
                    const displayDate = moment(slot.date).format('DD MMM YYYY');
                    
                    const row = `
                        <tr class="${isAvailable ? '' : 'table-success'}">
                            <td>${displayDate}</td>
                            <td>${slot.slot_times}</td>
                            <td>Week ${slot.week}</td>
                            <td class="text-center">
                                <span class="badge bg-${isAvailable ? 'warning' : 'success'} rounded-pill">
                                    ${slot.status}
                                </span>
                            </td>
                            <td class="text-center">
                                ${isAvailable && !isStudent ? `
                                    <button class="btn btn-sm btn-primary upload-btn" 
                                        onclick="openUploadModal('${courseId}', '${formattedDate}', '${slot.slot_times}', '${slot.class_id}', '${slot.section_id}', '${slot.teacher_id}')">
                                        <i class="fas fa-upload"></i> Upload
                                    </button>
                                ` : !isAvailable ? `
                                    <div class="action-buttons">
                                        <div class="btn-group">
                                            <a href="/lectures/view/${slot.lecture_id}" target="_blank" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            ${!isStudent ? `
                                                <button type="button" class="btn btn-primary btn-sm edit-lecture-btn" 
                                                    data-lecture-id="${slot.lecture_id}" 
                                                    onclick="editLecture(${slot.lecture_id})">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            ` : ''}
                                        </div>
                                    </div>
                                ` : ''}
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            } else {
                tbody.html(`
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fas fa-info-circle me-2"></i>No time slots available
                        </td>
                    </tr>
                `);
            }
        }

        // Handle file input changes and removal
        $('input[name="video_file"]').change(function() {
            if (this.files.length > 0) {
                $('#removeVideo').show();
            } else {
                $('#removeVideo').hide();
            }
        });

        $('input[name="lecture_file"]').change(function() {
            if (this.files.length > 0) {
                $('#removeFile').show();
            } else {
                $('#removeFile').hide();
            }
        });

        $('#removeVideo').click(function() {
            $('input[name="video_file"]').val('');
            $(this).hide();
        });

        $('#removeFile').click(function() {
            $('input[name="lecture_file"]').val('');
            $(this).hide();
        });

        // Handle form submission
        $('#lectureUploadForm').submit(function(e) {
            e.preventDefault();
            
            const submitBtn = $(this).find('button[type="submit"]');
            const spinner = submitBtn.find('.spinner-border');
            const lectureId = $('input[name="lecture_id"]').val();
            
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');
            
            const formData = new FormData(this);
            if (lectureId) {
                formData.append('_method', 'PUT');
            }
            
            // Determine if this is an update or new upload
            const url = lectureId 
                ? `/lectures/update/${lectureId}`
                : "{{ route('lectures.store') }}";
            
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#uploadModal').modal('hide');
                        Toast.fire({
                            icon: 'success',
                            title: lectureId ? 'Lecture updated successfully' : 'Lecture uploaded successfully'
                        });
                        
                        // Reload time slots to show updated status
                        loadTimeSlots(
                            $('#modalCourseId').val(),
                            $('#modalClassId').val(),
                            $('#modalSectionId').val(),
                            $('#modalTeacherId').val()
                        );
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: response.message || 'Failed to process lecture'
                        });
                    }
                },
                error: function(xhr) {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.message || 'Something went wrong!'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');
                }
            });
        });

        // Filter buttons functionality
        $('#todayBtn').click(function() {
            filterTimeSlots('today');
        });

        $('#weekBtn').click(function() {
            filterTimeSlots('week');
        });

        $('#allBtn').click(function() {
            filterTimeSlots('all');
        });

        function filterTimeSlots(filter) {
            const rows = $('#timeSlotsTableBody tr');
            const today = moment();
            
            rows.each(function() {
                const date = moment($(this).find('td:first').text(), 'DD MMM YYYY');
                
                if (filter === 'today') {
                    $(this).toggle(date.isSame(today, 'day'));
                } else if (filter === 'week') {
                    $(this).toggle(date.isSame(today, 'week'));
                } else {
                    $(this).show();
                }
            });
        }

        // For students, automatically load their courses
        @if(auth()->user()->hasRole('Student') && isset($currentSession))
            $('#session_id').val('{{ $currentSession['id'] }}');
            loadCourses();
        @endif
    });
</script>
@endpush