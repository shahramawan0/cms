@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div>
                            <h3 class="card-title mb-0 text-white">
                                <i class="fas fa-user-graduate"></i> Student Enrollments
                            </h3>
                        </div>
                        <div>
                            <button id="addEnrollmentBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Enroll Student
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Enrollment Form (Initially Hidden) -->
                <div class="card-body p-0" id="enrollmentFormContainer" style="display: none;">
                    <div class="row g-0">
                        <!-- Class/Section Selection Sidebar -->
                        <div class="col-md-4 border-end">
                            <div class="p-3">
                    <form id="enrollmentForm">
                        @csrf
                        <input type="hidden" name="id" id="enrollment_id">
                                    <input type="hidden" name="institute_id" id="institute_id" value="{{ auth()->user()->institute_id ?? '' }}">
                                    <input type="hidden" name="class_id" id="class_id">
                                    <input type="hidden" name="section_id" id="section_id">
                                    <input type="hidden" name="status" id="status" value="active">
                       
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
                            
                                        <!-- Session and Date Fields in One Row -->
                                        <div class="row">
                                            <div class="col-md-6">
                                <div class="form-group">
                                                    <label for="session_id" class="form-label fw-bold">
                                                        <i class="fas fa-calendar-alt"></i> Academic Session
                                                    </label>
                                                    <select name="session_id" id="session_id" class="form-control form-select" required>
                                        <option value="">Select Session</option>
                                    </select>
                                </div>
                            </div>
                                            <div class="col-md-6">
                                <div class="form-group">
                                                    <label for="enrollment_date" class="form-label fw-bold">
                                                        <i class="fas fa-calendar"></i> Enrollment Date
                                                    </label>
                                                    <input type="date" class="form-control" id="enrollment_date" name="enrollment_date" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
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
                                                    <i class="fas fa-exclamation-circle"></i> Please select a section to proceed.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Course Selection and Student Enrollment Area -->
                        <div class="col-md-8" id="courseSelectionContainer" style="display: none;">
                            <div class="p-3">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 id="selectedClassInfo" class="mb-0"></h5>
                                </div>

                                <!-- Course Selection Area -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">Available Courses</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                            <div class="col-md-12">
                                                <div id="coursesContainer">
                                                    <!-- Courses will be loaded here as checkboxes -->
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                            
                                <!-- Student Selection Area -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">Select Students</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row align-items-end mb-3">
                                            <div class="col-md-8">
                                <div class="form-group">
                                                    <label for="csv_file" class="form-label">Upload CSV with Roll Numbers</label>
                                                    <div class="input-group">
                                                        <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv">
                                                        <button type="button" id="processCsvBtn" class="btn btn-info">
                                        <i class="fas fa-upload"></i> Process CSV
                                    </button>
                                </div>
                                                    <small class="text-muted">CSV should contain a column named "roll_number"</small>
                            </div>
                        </div>
                                        </div>
                                        <div id="studentsContainer" style="display: none;">
                                <div class="form-group">
                                                <label for="student_ids" class="form-label">Selected Students</label>
                                                <select name="student_ids[]" id="student_ids" class="form-control select2-container" multiple>
                                        <!-- Students will be loaded here -->
                                    </select>
                                            </div>
                                </div>
                            </div>
                        </div>
                        
                                <!-- Submit Button -->
                                <div class="text-end">
                                    <button type="button" id="cancelBtn" class="btn btn-secondary btn-sm me-2">Cancel</button>
                                    <button type="submit" form="enrollmentForm" id="submitBtn" class="btn btn-primary btn-sm">
                                        <span id="submitBtnText">Enroll Students</span>
                                    <span id="submitBtnLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
                
                <!-- Enrollments Table -->
                <div class="card-body" style="border-top:1px solid #000">
                    <div style="overflow-x:auto;">
                        <table id="enrollments-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Institute</th>
                                    <th>Session</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Courses</th>
                                    <th>Enrollment Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data loaded via AJAX -->
                            </tbody>
                        </table>
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

    // Initialize DataTable
    var table = $('#enrollments-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('enrollments.data') }}",
        columns: [
            { data: 'student_name', name: 'student_name' },
            { data: 'institute', name: 'institute' },
            { data: 'session', name: 'session' },
            { data: 'class', name: 'class' },
            { data: 'section', name: 'section' },
            { data: 'courses', name: 'courses' },
            { data: 'enrollment_date', name: 'enrollment_date' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        autoWidth: false,
        language: {
            paginate: {
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>'
            }
        }
    });

    // Show/hide form
    $('#addEnrollmentBtn').click(function() {
        $('#enrollmentForm')[0].reset();
        $('#enrollment_id').val('');
        $('#enrollmentFormContainer').show();
        $('#studentsContainer').hide();
        $('#selectSectionMessage').show();
        $('#courseSelectionContainer').hide();
        clearFormErrors();
        
        // For Admin - load data immediately
        @if(!auth()->user()->hasRole('Super Admin'))
        loadInitialDataForAdmin();
        @endif
    });

    $('#cancelBtn').click(function() {
        resetForm();
        $('#enrollmentFormContainer').hide();
    });

    // For Super Admin - handle institute change
    @if(auth()->user()->hasRole('Super Admin'))
    $('#institute_select').change(function() {
        const instituteId = $(this).val();
        $('#institute_id').val(instituteId);
        if (instituteId) {
            loadSessionsAndSelectCurrent(instituteId);
        } else {
            clearDropdowns();
        }
    });
    @endif

    // When session changes, load classes
    $('#session_id').change(function() {
        const sessionId = $(this).val();
        if (sessionId) {
            loadClasses(sessionId);
                            } else {
            $('#classListGroup').empty();
            $('#selectSectionMessage').show();
            $('#courseSelectionContainer').hide();
        }
    });

    // Function to initialize user data
    function initializeUserData() {
        const instituteId = $('#institute_id').val();
        @if(!auth()->user()->hasRole('Super Admin'))
        if (instituteId) {
            loadSessionsAndSelectCurrent(instituteId);
        }
        @endif
    }

    // Function to check if session is current
    function isCurrentYear(session) {
        if (!session.start_date || !session.end_date) return false;
        const now = new Date();
        const startDate = new Date(session.start_date);
        const endDate = new Date(session.end_date);
        return (now >= startDate && now <= endDate);
    }

    // Function to load sessions and auto-select current
    function loadSessionsAndSelectCurrent(instituteId, callback) {
        $.ajax({
            url: "{{ route('enrollments.dropdowns') }}",
            type: "GET",
            data: { institute_id: instituteId },
            success: function(data) {
                $('#session_id').empty().append('<option value="">Select Session</option>');
                
                if(data.sessions && data.sessions.length > 0) {
                    let currentSessionId = null;
                    
                    $.each(data.sessions, function(key, session) {
                        const isCurrent = isCurrentYear(session);
                        const selected = isCurrent ? 'selected' : '';
                        $('#session_id').append(
                            `<option value="${session.id}" ${selected}>${session.session_name}</option>`
                        );
                        
                        if (isCurrent) {
                            currentSessionId = session.id;
                        }
                    });
                    
                    if (currentSessionId) {
                        $('#session_id').val(currentSessionId).trigger('change');
                    }
                }
                
                if (typeof callback === 'function') {
                    callback();
                }
            }
        });
    }

    // Function to load classes
    function loadClasses(sessionId) {
        $('#classLoading').show();
        $('#noClassFound').hide();
        $('#selectSectionMessage').hide();
        $('#courseSelectionContainer').hide();
        
            $.ajax({
                url: "{{ route('enrollments.dropdowns') }}",
                type: "GET",
                data: { 
                institute_id: $('#institute_id').val(),
                    session_id: sessionId
                },
                success: function(data) {
                $('#classListGroup').empty();
                
                    if (data.classes && data.classes.length > 0) {
                    data.classes.forEach(function(classItem) {
                        getSectionsForClass($('#institute_id').val(), classItem.id, classItem);
                    });
                    
                    // If we're in edit mode, wait for sections to load then select the correct one
                    if (window.editData) {
                        const checkSection = setInterval(function() {
                            const $sectionBtn = $(`.section-btn[data-class-id="${window.editData.class_id}"][data-section-id="${window.editData.section_id}"]`);
                            if ($sectionBtn.length) {
                                clearInterval(checkSection);
                                $sectionBtn.click();
                            }
                        }, 100);
                    }
        } else {
                    $('#classLoading').hide();
                    $('#noClassFound').show();
                }
            }
        });
    }

    // Function to get sections for a class
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
                    createClassCard(classItem, data.sections);
                }
                $('#classLoading').hide();
            }
        });
    }

    // Function to create class card with sections
    function createClassCard(classItem, sections) {
        // Check if card already exists
        const existingCard = $(`.list-group-item[data-class-id="${classItem.id}"]`);
        if (existingCard.length) {
            return; // Skip if card already exists
        }
        
        const listItemHtml = `
            <div class="list-group-item" data-class-id="${classItem.id}">
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
        
        // Handle section button click
        $('.section-btn').off('click').on('click', function() {
            $('.section-btn').removeClass('active-section');
            $(this).addClass('active-section');
            
            const classId = $(this).data('class-id');
            const sectionId = $(this).data('section-id');
            const className = $(this).data('class-name');
            const sectionName = $(this).data('section-name');
            
            $('#class_id').val(classId);
            $('#section_id').val(sectionId);
            $('#selectedClassInfo').text(`${className} - ${sectionName}`);
            
            // First show containers
            $('#courseSelectionContainer').show();
            $('#selectSectionMessage').hide();
            
            // Then load courses
            loadCourses($('#institute_id').val(), function() {
                // Check if we're in edit mode and have course IDs
                if (window.editData && window.editData.course_ids) {
                    console.log('Edit Data:', window.editData);
                    console.log('Current Class/Section:', classId, sectionId);
                    console.log('Edit Class/Section:', window.editData.class_id, window.editData.section_id);
                    
                    // Only check courses if we're on the correct class/section
                    if (parseInt(classId) === parseInt(window.editData.class_id) && 
                        parseInt(sectionId) === parseInt(window.editData.section_id)) {
                        console.log('Checking courses:', window.editData.course_ids);
                        window.editData.course_ids.forEach(function(courseId) {
                            const checkbox = $(`#course_${courseId}`);
                            console.log('Checking course:', courseId, checkbox.length);
                            checkbox.prop('checked', true);
                        });
                    }
                }
            });
        });
    }

    // Function to load courses
    function loadCourses(instituteId, callback) {
        $.ajax({
            url: "{{ route('enrollments.dropdowns') }}",
            type: "GET",
            data: { institute_id: instituteId },
            success: function(data) {
                $('#coursesContainer').empty();
                
                if(data.courses && data.courses.length > 0) {
                    data.courses.forEach(course => {
                        const courseCheckbox = `
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" 
                                        name="courses[]" id="course_${course.id}" 
                                        value="${course.id}">
                                    <label class="form-check-label" for="course_${course.id}">
                                        ${course.course_name}
                                    </label>
                                </div>
                        `;
                        $('#coursesContainer').append(courseCheckbox);
                    });
                    
                    if (typeof callback === 'function') {
                        setTimeout(callback, 100); // Small delay to ensure checkboxes are in DOM
                    }
                } else {
                    $('#coursesContainer').html('<p class="text-muted">No courses available</p>');
                }
            }
        });
    }

    // Process CSV file
    $('#processCsvBtn').click(function() {
        var fileInput = $('#csv_file')[0];
        if (!fileInput.files || !fileInput.files[0]) {
            Swal.fire('Error', 'Please select a CSV file first', 'error');
            return;
        }
        
        var formData = new FormData();
        formData.append('csv_file', fileInput.files[0]);
        formData.append('institute_id', $('#institute_id').val());
        formData.append('session_id', $('#session_id').val());
        formData.append('class_id', $('#class_id').val());
        formData.append('section_id', $('#section_id').val());
        
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: "{{ route('enrollments.upload-csv') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#processCsvBtn').html('<i class="fas fa-upload"></i> Process CSV');
                
                if (response.success) {
                    handleStudentSelection(response.data);
                    
                    // Show stats in a nice popup
                    let message = `
                        <div class="text-left">
                            <p><strong>Total Roll Numbers:</strong> ${response.stats.total}</p>
                            <p><strong>Found:</strong> ${response.stats.found}</p>
                            <p><strong>Not Found:</strong> ${response.stats.not_found}</p>
                            <p><strong>Already Enrolled:</strong> ${response.stats.already_enrolled}</p>
                        </div>
                    `;
                    
                    if (response.not_found && response.not_found.length > 0) {
                        message += `
                            <div class="alert alert-warning mt-3">
                                <strong>Roll Numbers Not Found:</strong><br>
                                ${response.not_found.join(', ')}
                            </div>
                        `;
                    }
                    
                    Swal.fire({
                        title: 'CSV Processing Results',
                        html: message,
                        icon: 'info'
                    });
                }
            },
            error: function(xhr) {
                $('#processCsvBtn').html('<i class="fas fa-upload"></i> Process CSV');
                Swal.fire('Error', xhr.responseJSON?.error || 'Failed to process CSV file', 'error');
            }
        });
    });

    // Handle student selection
    function handleStudentSelection(students) {
        $('#student_ids').empty();
        
        if (students.length > 0) {
            students.forEach(function(student) {
                const option = new Option(
                    `${student.name} (${student.roll_number})`,
                    student.id,
                    false,
                    !student.is_enrolled
                );
                
                if (student.is_enrolled) {
                    $(option).addClass('text-danger');
                    $(option).attr('title', 'Already enrolled');
                }
                
                $('#student_ids').append(option);
            });
            
            // Destroy existing Select2 if it exists
            if ($('#student_ids').data('select2')) {
                $('#student_ids').select2('destroy');
            }
            
            // Initialize Select2 with custom configuration
            $('#student_ids').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Select students',
                allowClear: true,
                closeOnSelect: false,
                templateResult: formatStudent,
                templateSelection: formatStudent,
                dropdownParent: $('#studentsContainer'),
                language: {
                    noResults: function() {
                        return 'No students found';
                    }
                }
            });
            
            $('#studentsContainer').show();
        }
    }

    // Format student option
    function formatStudent(student) {
        if (!student.id) return student.text;
        
        let $container = $(
            '<div class="d-flex align-items-center py-1">' +
                '<div class="avatar-sm bg-light rounded-circle text-center me-2" style="width: 32px; height: 32px; line-height: 32px;">' +
                    '<i class="fas fa-user text-primary"></i>' +
                '</div>' +
                '<div class="flex-grow-1">' + student.text + '</div>' +
            '</div>'
        );
        
        if ($(student.element).hasClass('text-danger')) {
            $container.find('.flex-grow-1').addClass('text-danger');
            $container.append('<span class="ms-2 badge bg-warning">Already Enrolled</span>');
        }
        
        return $container;
    }

    // Handle edit button clicks
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        
        // Reset everything first
        resetForm();
        
        $('#enrollment_id').val(id);
        $('#enrollmentFormContainer').show();
        
        // Scroll to form smoothly
        $('html, body').animate({
            scrollTop: $('#enrollmentFormContainer').offset().top - 20
        }, 500);
        
        // Load enrollment data
        $.ajax({
            url: `/enrollments/edit/${id}`,
            type: 'GET',
            success: function(response) {
                // Store complete response in window.editData
                window.editData = response;
                
                // Set form values
                $('#institute_id').val(response.institute_id);
                $('#enrollment_date').val(response.enrollment_date);
                
                // Load sessions and trigger change to load classes
                loadSessionsAndSelectCurrent(response.institute_id, function() {
                    $('#session_id').val(response.session_id).trigger('change');
                });
                
                // Load and select student
                $('#student_ids').empty();
                const option = new Option(
                    `${response.student_name} (${response.student_roll_number})`,
                    response.student_id,
                    true,
                    true
                );
                $('#student_ids').append(option);
                
                // Initialize Select2
                if ($('#student_ids').data('select2')) {
                    $('#student_ids').select2('destroy');
                }
                initializeSelect2();
                
                $('#studentsContainer').show();
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to load enrollment data', 'error');
            }
        });
    });

    // Handle delete button clicks
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete all course enrollments for this student in this class and section. This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/enrollments/delete/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            table.ajax.reload(null, false);
                            Toast.fire({
                                icon: 'success',
                                title: 'Enrollment deleted successfully'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Failed to delete enrollment'
                        });
                    }
                });
            }
        });
    });

    // Form submission
   $('#enrollmentForm').submit(function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return false;
        }
        
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').addClass('d-none');
        $('#submitBtnLoader').removeClass('d-none');
        
        clearFormErrors();
        
        const formData = new FormData(this);
        const enrollmentId = $('#enrollment_id').val();
        
        // Get checked courses
        const courses = [];
        $('input[name="courses[]"]:checked').each(function() {
            courses.push($(this).val());
        });
        formData.delete('courses[]');
        courses.forEach(courseId => formData.append('courses[]', courseId));
        
        // Handle student_id for edit mode
        if (enrollmentId && window.editData) {
            formData.append('student_id', window.editData.student_id);
        } else {
            // Get selected students for new enrollment
            const studentIds = $('#student_ids').val() || [];
            formData.delete('student_ids[]');
            studentIds.forEach(id => formData.append('student_ids[]', id));
        }
        
        // Set up the request
        const url = enrollmentId 
            ? `/enrollments/update/${enrollmentId}`
            : "{{ route('enrollments.store') }}";
            
        if (enrollmentId) {
            formData.append('_method', 'PUT');
        }
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#enrollmentFormContainer').hide();
                table.ajax.reload(null, false);
                
                // Show success toast
                Toast.fire({
                    icon: 'success',
                    title: enrollmentId 
                        ? 'Enrollment updated successfully'
                        : `Successfully enrolled ${response.enrollments_count || 0} student(s)`
                });
                
                // If there are skipped enrollments, show warning toast
                if (response.skipped && response.skipped.length > 0) {
                    setTimeout(() => {
                        Toast.fire({
                            icon: 'warning',
                            title: 'Some enrollments were skipped (already exists)',
                            html: response.skipped.join('<br>')
                        });
                    }, 1000);
                }
                
                // Clear edit data after successful update
                window.editData = null;
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    showFormErrors(xhr.responseJSON.errors);
                    // Show validation error toast
                    Toast.fire({
                        icon: 'error',
                        title: 'Please check the form for errors'
                    });
                } else {
                    // Show error toast
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.message || 'Something went wrong!'
                    });
                }
            },
            complete: function() {
                $('#submitBtn').prop('disabled', false);
                $('#submitBtnText').removeClass('d-none');
                $('#submitBtnLoader').addClass('d-none');
            }
        });
    });

    // Form validation and error handling
    function validateForm() {
        clearFormErrors();
        
        let isValid = true;
        let errors = {};
        
        if (!$('#session_id').val()) {
            errors.session_id = ['Please select a session'];
            isValid = false;
        }
        
        if (!$('#class_id').val()) {
            errors.class_id = ['Please select a class'];
            isValid = false;
        }
        
        if (!$('#section_id').val()) {
            errors.section_id = ['Please select a section'];
            isValid = false;
        }
        
        if (!$('input[name="courses[]"]:checked').length) {
            errors.courses = ['Please select at least one course'];
            isValid = false;
        }
        
        // Check student selection based on mode
        const enrollmentId = $('#enrollment_id').val();
        if (enrollmentId) {
            if (!window.editData || !window.editData.student_id) {
                errors.student_id = ['Student information is missing'];
                isValid = false;
            }
        } else {
            if (!$('#student_ids').val() || !$('#student_ids').val().length) {
                errors.student_ids = ['Please select at least one student'];
                isValid = false;
            }
        }
        
        if (!isValid) {
            showFormErrors(errors);
        }
        
        return isValid;
    }

    function showFormErrors(errors) {
        for (let field in errors) {
            let errorMsg = errors[field][0];
            let $element = $('#' + field);
            
            $element.addClass('is-invalid');
            
            // Create error feedback if it doesn't exist
            let $feedback = $element.next('.invalid-feedback');
            if (!$feedback.length) {
                $feedback = $('<div class="invalid-feedback"></div>');
                $element.after($feedback);
            }
            
            $feedback.text(errorMsg);
        }
        
        // Show first error in Toast
        const firstError = Object.values(errors)[0][0];
        Toast.fire({
            icon: 'error',
            title: firstError
        });
    }

    function clearFormErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }

    // Helper functions
    function clearDropdowns() {
        $('#session_id').empty().append('<option value="">Select Session</option>');
        $('#classListGroup').empty();
        $('#courseSelectionContainer').hide();
        $('#selectSectionMessage').show();
    }

    // For Admin - load initial data
    function loadInitialDataForAdmin() {
        const instituteId = $('#institute_id').val();
        if (instituteId) {
            loadSessionsAndSelectCurrent(instituteId);
        }
    }

    // Add helper functions for form reset and Select2 initialization
    function resetForm() {
        // Reset form fields
        $('#enrollmentForm')[0].reset();
        $('#enrollment_id').val('');
        
        // Clear and hide containers
        $('#classListGroup').empty();
        $('#coursesContainer').empty();
                            $('#student_ids').empty();
        
        // Reset Select2 if it exists
        if ($('#student_ids').data('select2')) {
            $('#student_ids').select2('destroy');
        }
        
        // Hide containers
        $('#studentsContainer').hide();
        $('#courseSelectionContainer').hide();
        $('#selectSectionMessage').show();
        
        // Clear any stored edit data
        window.editData = null;
        
        // Clear form errors
        clearFormErrors();
    }

    function initializeSelect2() {
                            $('#student_ids').select2({
            theme: 'bootstrap-5',
                                width: '100%',
            placeholder: 'Select students',
            allowClear: true,
            closeOnSelect: false,
            templateResult: formatStudent,
            templateSelection: formatStudent,
            dropdownParent: $('#studentsContainer'),
            language: {
                noResults: function() {
                    return 'No students found';
                }
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
#classListGroup {
    max-height: calc(100vh - 300px);
    overflow-y: auto;
}
.form-check-input:checked + .form-check-label {
    color: var(--bs-primary);
    font-weight: bold;
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

/* Select2 Custom Styles */
.select2-container--bootstrap-5 {
    width: 100% !important;
}

.select2-container--bootstrap-5 .select2-selection {
    min-height: 38px;
    border: 1px solid #dee2e6;
}

.select2-container--bootstrap-5 .select2-selection--multiple {
    padding: 6px;
}

.select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
    background-color: #e9ecef;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 2px 8px;
    margin: 2px 4px 2px 0;
}

.select2-container--bootstrap-5 .select2-selection__choice__remove {
    margin-right: 4px;
    color: #6c757d;
}

.select2-container--bootstrap-5 .select2-search__field {
    margin-top: 4px;
}

.select2-container--bootstrap-5 .select2-results__option {
    padding: 6px 12px;
}

.select2-container--bootstrap-5 .select2-results__option--highlighted {
    background-color: #007bff;
}

.select2-container--bootstrap-5 .select2-results__option[aria-selected=true] {
    background-color: #e9ecef;
}

.avatar-sm {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.select2-container--bootstrap-5 .select2-dropdown {
    border-color: #dee2e6;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
</style>
@endpush