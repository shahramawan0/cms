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
                                <i class="fas fa-chalkboard-teacher text-white"></i> Teacher Course Assignment
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs" id="assignmentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="insert-tab" data-bs-toggle="tab" data-bs-target="#insert" type="button" role="tab" aria-controls="insert" aria-selected="true">
                                <i class="fas fa-plus-circle"></i> Enroll Course
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="showdata-tab" data-bs-toggle="tab" data-bs-target="#showdata" type="button" role="tab" aria-controls="showdata" aria-selected="false">
                                <i class="fas fa-edit"></i> Edit Enrollment
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content mt-3" id="assignmentTabsContent">
                        <!-- Insert Tab -->
                        <div class="tab-pane fade show active" id="insert" role="tabpanel" aria-labelledby="insert-tab">
                            <div class="row mb-3">
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
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="session_id">Session <span class="text-danger">*</span></label>
                                        <select name="session_id" id="session_id" class="form-control" required>
                                            <option value="">Select Session</option>
                                            @foreach($sessions as $session)
                                                <option value="{{ $session->id }}" {{ isset($activeSession) && $activeSession->id == $session->id ? 'selected' : '' }}>
                                                    {{ $session->session_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Cards Container -->
                            <div id="classCardsContainer" class="row">
                                <!-- Cards will be loaded here via AJAX -->
                            </div>
                        </div>

                        <!-- Show Data Tab -->
                        <div class="tab-pane fade" id="showdata" role="tabpanel" aria-labelledby="showdata-tab">
                            <div class="row mb-3">
                                @if(auth()->user()->hasRole('Super Admin'))
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
                                @endif
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="filter_session">Filter by Session</label>
                                        <select name="filter_session" id="filter_session" class="form-control">
                                            <option value="">All Sessions</option>
                                            @foreach($sessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Assigned Cards Container -->
                            <div id="assignedCardsContainer" class="row">
                                <!-- Assigned cards will be loaded here via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Teacher Selection Modal -->
<div class="modal fade" id="teacherModal" tabindex="-1" aria-labelledby="teacherModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="teacherModalLabel">Select Teacher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <select id="teacher_id" class="form-control">
                    <option value="">Select Teacher</option>
                    <!-- Teachers will be loaded here -->
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="assignTeacherBtn">Assign</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.class-card {
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 450px;
}

.class-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.class-card .card-header {
    border-radius: 10px 10px 0 0;
    padding: 15px;
    flex-shrink: 0;
}

.class-card .card-body {
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    padding: 15px;
    overflow: hidden;
}

.class-card .card-footer {
    padding: 15px;
    background: rgba(0,0,0,0.02);
    border-top: 1px solid rgba(0,0,0,0.05);
    flex-shrink: 0;
    margin-top: auto;
}

.assign-teacher-link {
    cursor: pointer;
    color: #666;
    text-decoration: none;
    transition: color 0.2s;
}

.assign-teacher-link:hover {
    color: #333;
}

.class-card small.text-muted {
    font-size: 0.85rem;
    display: block;
    margin-top: 2px;
}

.class-card .badge {
    font-size: 0.85rem;
    padding: 0.4rem 0.6rem;
}

.courses-list {
    flex: 1;
    overflow-y: auto;
    padding-right: 10px;
    min-height: 200px;
    max-height: 100%;
}

.courses-list::-webkit-scrollbar {
    width: 6px;
}

.courses-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.courses-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.courses-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.teacher-name {
    font-size: 0.9rem;
    color: #666;
    display: flex;
    align-items: center;
    gap: 5px;
}

.dropdown-menu-end {
    right: 0;
    left: auto;
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

    // Function to load class cards
    function loadClassCards(sessionId) {
        $.ajax({
            url: "{{ route('teacher.enrollments.session-data') }}",
            type: "GET",
            data: {
                session_id: sessionId,
                institute_id: $('#institute_id').val()
            },
            success: function(response) {
                if (response.success) {
                    let cardsHtml = '';
                    response.courses.forEach(data => {
                        cardsHtml += createClassCard(data);
                    });
                    $('#classCardsContainer').html(cardsHtml);
                }
            }
        });
    }

    // Function to create a class card with improved design
    function createClassCard(data) {
        let coursesList = data.courses.map(course => `
            <div class="form-check">
                <input type="checkbox" class="form-check-input course-checkbox" 
                    data-course-id="${course.course_id}">
                <label class="form-check-label">${course.course_name}</label>
            </div>
        `).join('');

        return `
            <div class="col-md-4 mb-4">
                <div class="class-card" style="background: linear-gradient(135deg, ${data.background_color}22, white);">
                    <div class="card-header" style="background: ${data.background_color}44; border-bottom: 2px solid ${data.background_color}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">${data.class_name}</h5>
                                <small class="text-muted">Section: ${data.section_name}</small>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <span class="badge bg-info me-2">
                                <i class="fas fa-users me-1"></i> ${data.student_count} Students
                            </span>
                            <span class="badge bg-secondary">
                                <i class="fas fa-book me-1"></i> ${data.courses.length} Courses
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="courses-list">
                            ${coursesList}
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <a class="assign-teacher-link" data-class-id="${data.class_id}" 
                           data-section-id="${data.section_id}">
                            <i class="fas fa-user-plus"></i> Assign Teacher
                        </a>
                        <button class="btn btn-sm enroll-btn" 
                            data-class-id="${data.class_id}"
                            data-section-id="${data.section_id}"
                            style="background-color: ${data.background_color}; color: white;">
                            <i class="fas fa-check"></i> Enroll
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    // Session change event
    $('#session_id').change(function() {
        let sessionId = $(this).val();
        if (sessionId) {
            loadClassCards(sessionId);
            // Sync the session filter in show data tab
            $('#filter_session').val(sessionId);
            if ($('#showdata-tab').hasClass('active')) {
                loadAssignedCards();
            }
        } else {
            $('#classCardsContainer').html('');
        }
    });

    // Assign teacher link click
    $(document).on('click', '.assign-teacher-link', function() {
        let classId = $(this).data('class-id');
        let sectionId = $(this).data('section-id');
        
        // Store the current card reference
        let $currentCard = $(this).closest('.class-card');
        
        // Load teachers and show modal
        loadTeachers($currentCard);
    });

    // Enroll button click
    $(document).on('click', '.enroll-btn', function() {
        let $card = $(this).closest('.class-card');
        let classId = $(this).data('class-id');
        let sectionId = $(this).data('section-id');
        
        let teacherId = $('#teacher_id').val();
        if (!teacherId) {
            Toast.fire({
                icon: 'warning',
                title: 'Please select a teacher first by clicking "Assign Teacher"'
            });
            return;
        }
        
        let selectedCourses = [];
        $card.find('.course-checkbox:checked').each(function() {
            selectedCourses.push($(this).data('course-id'));
        });

        if (selectedCourses.length === 0) {
            Toast.fire({
                icon: 'warning',
                title: 'Please select at least one course'
            });
            return;
        }

        // Call API to assign teacher
        $.ajax({
            url: "{{ route('teacher.enrollments.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                teacher_id: teacherId,
                session_id: $('#session_id').val(),
                class_id: classId,
                section_id: sectionId,
                course_ids: selectedCourses
            },
            success: function(response) {
                if (response.success) {
                    loadClassCards($('#session_id').val());
                    if ($('#showdata-tab').hasClass('active')) {
                        loadAssignedCards();
                    }
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.message || 'Something went wrong!'
                });
            }
        });
    });

    // Function to load teachers
    function loadTeachers($card) {
        $.ajax({
            url: "{{ route('teacher.enrollments.session-data') }}",
            type: "GET",
            data: { 
                institute_id: $('#institute_id').val(),
                session_id: $('#session_id').val()
            },
            success: function(response) {
                if (response.success && response.teachers) {
                    let teacherOptions = '<option value="">Select Teacher</option>';
                    response.teachers.forEach(teacher => {
                        teacherOptions += `<option value="${teacher.id}">${teacher.name}</option>`;
                    });
                    $('#teacher_id').html(teacherOptions);
                    
                    // Store the card reference in the modal
                    $('#teacherModal').data('card', $card);
                    $('#teacherModal').modal('show');
                }
            }
        });
    }

    // Assign teacher button click in modal
    $('#assignTeacherBtn').click(function() {
        let teacherId = $('#teacher_id').val();
        if (!teacherId) {
            alert('Please select a teacher');
            return;
        }

        let $card = $('#teacherModal').data('card');
        let classId = $card.find('.assign-teacher-link').data('class-id');
        let sectionId = $card.find('.assign-teacher-link').data('section-id');
        let selectedCourses = [];
        
        $card.find('.course-checkbox:checked').each(function() {
            selectedCourses.push($(this).data('course-id'));
        });

        if (selectedCourses.length === 0) {
            alert('Please select at least one course');
            return;
        }

        // Call API to assign teacher
        $.ajax({
            url: "{{ route('teacher.enrollments.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                teacher_id: teacherId,
                session_id: $('#session_id').val(),
                class_id: classId,
                section_id: sectionId,
                course_ids: selectedCourses
            },
            success: function(response) {
                if (response.success) {
                    $('#teacherModal').modal('hide');
                    // Reload cards
                    loadClassCards($('#session_id').val());
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.message || 'Something went wrong!'
                });
            }
        });
    });

    // Load initial data if active session is selected
    let activeSessionId = $('#session_id').val();
    if (activeSessionId) {
        loadClassCards(activeSessionId);
    }

    // Add this after the existing loadClassCards function
    function loadAssignedCards() {
        let sessionId = $('#filter_session').val();
        let instituteId = $('#filter_institute').val();
        
        $.ajax({
            url: "{{ route('teacher.enrollments.assigned-data') }}",
            type: "GET",
            data: {
                session_id: sessionId,
                institute_id: instituteId
            },
            success: function(response) {
                if (response.success) {
                    let cardsHtml = '';
                    response.assignments.forEach(data => {
                        cardsHtml += createAssignedCard(data);
                    });
                    $('#assignedCardsContainer').html(cardsHtml || '<div class="col-12 text-center">No Enrollments found</div>');
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.message || 'Failed to load assignments'
                });
            }
        });
    }

    function createAssignedCard(data) {
        let coursesList = data.all_courses.map(course => `
            <div class="form-check">
                <input type="checkbox" class="form-check-input course-checkbox" 
                    data-course-id="${course.id}"
                    ${data.assigned_courses.includes(course.id) ? 'checked' : ''}>
                <label class="form-check-label ${data.assigned_courses.includes(course.id) ? 'fw-bold' : 'text-muted'}">
                    ${course.course_name}
                </label>
            </div>
        `).join('');

        return `
            <div class="col-md-4 mb-4">
                <div class="class-card" style="background: linear-gradient(135deg, ${data.background_color}22, white);">
                    <div class="card-header" style="background: ${data.background_color}44; border-bottom: 2px solid ${data.background_color}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">${data.class_name}</h5>
                                <small class="text-muted">Section: ${data.section_name}</small>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <span class="badge bg-info me-2">
                                <i class="fas fa-users me-1"></i> ${data.student_count} Students
                            </span>
                            <span class="badge bg-secondary">
                                <i class="fas fa-book me-1"></i> ${data.all_courses.length} Courses
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="courses-list">
                            ${coursesList}
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <span class="teacher-name">
                            <i class="fas fa-user-tie"></i> ${data.teacher_name}
                        </span>
                        <div class="d-flex align-items-center gap-2">
                            <div class="dropdown">
                                <button class="btn btn-link text-dark p-0" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item edit-assignment" href="#" 
                                            data-class-id="${data.class_id}"
                                            data-section-id="${data.section_id}"
                                            data-teacher-id="${data.teacher_id}">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger unassign-teacher" href="#"
                                            data-class-id="${data.class_id}"
                                            data-section-id="${data.section_id}"
                                            data-teacher-id="${data.teacher_id}">
                                            <i class="fas fa-user-minus"></i> Unassign
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <button class="btn btn-sm update-courses" 
                                style="background-color: ${data.background_color}; color: white; display: none;"
                                data-class-id="${data.class_id}"
                                data-section-id="${data.section_id}"
                                data-teacher-id="${data.teacher_id}">
                                <i class="fas fa-save"></i> Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Add these event handlers
    $('#filter_session, #filter_institute').change(function() {
        loadAssignedCards();
    });

    $(document).on('click', '.edit-assignment', function(e) {
        e.preventDefault();
        let $card = $(this).closest('.class-card');
        $card.find('.course-checkbox').prop('disabled', false);
        $card.find('.update-courses').show();
    });

    $(document).on('click', '.update-courses', function() {
        let $card = $(this).closest('.class-card');
        let classId = $(this).data('class-id');
        let sectionId = $(this).data('section-id');
        let teacherId = $(this).data('teacher-id');
        
        let selectedCourses = [];
        $card.find('.course-checkbox:checked').each(function() {
            selectedCourses.push($(this).data('course-id'));
        });

        $.ajax({
            url: "{{ route('teacher.enrollments.update') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                teacher_id: teacherId,
                session_id: $('#filter_session').val(),
                class_id: classId,
                section_id: sectionId,
                course_ids: selectedCourses
            },
            success: function(response) {
                if (response.success) {
                    loadAssignedCards();
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.message || 'Failed to update courses'
                });
            }
        });
    });

    $(document).on('click', '.unassign-teacher', function(e) {
        e.preventDefault();
        let $card = $(this).closest('.class-card');
        let classId = $(this).data('class-id');
        let sectionId = $(this).data('section-id');
        let teacherId = $(this).data('teacher-id');
        
        let courseIds = [];
        $card.find('.course-checkbox:checked').each(function() {
            courseIds.push($(this).data('course-id'));
        });

        if (courseIds.length === 0) {
            Toast.fire({
                icon: 'warning',
                title: 'Please select at least one course to unassign'
            });
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: "This will unassign the teacher from the selected courses.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, unassign'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('teacher.enrollments.unassign') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        teacher_id: teacherId,
                        session_id: $('#filter_session').val(),
                        class_id: classId,
                        section_id: sectionId,
                        course_ids: courseIds
                    },
                    success: function(response) {
                        if (response.success) {
                            loadAssignedCards();
                            let currentSessionId = $('#session_id').val() || $('#filter_session').val();
                            if (currentSessionId) {
                                loadClassCards(currentSessionId);
                            }
                            Toast.fire({
                                icon: 'success',
                                title: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Failed to unassign teacher'
                        });
                    }
                });
            }
        });
    });

    // Also update the tab change handler to ensure proper sync
    $('#assignmentTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        let targetTab = $(e.target).attr('id');
        let currentSessionId;
        
        if (targetTab === 'insert-tab') {
            currentSessionId = $('#filter_session').val();
            if (currentSessionId) {
                $('#session_id').val(currentSessionId);
                loadClassCards(currentSessionId);
            }
        } else if (targetTab === 'showdata-tab') {
            currentSessionId = $('#session_id').val();
            if (currentSessionId) {
                $('#filter_session').val(currentSessionId);
                loadAssignedCards();
            }
        }
    });
});
</script>
@endpush 