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
                                <i class="fas fa-chalkboard-teacher text-white"></i> Lecture Management
                            </h3>
                        </div>
                        @can('Lecture.Add')
                        <div>
                            <button id="addLectureBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Add Lecture
                            </button>
                        </div>
                    @endcan
                    
                    </div>
                </div>
                
                <!-- Lecture Form (Initially Hidden) -->
                <div class="card-body" id="lectureFormContainer" style="display: none;">
                    <form id="lectureForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" id="lecture_id">
                        <input type="hidden" name="institute_id" id="institute_id" value="{{ auth()->user()->institute_id ?? '' }}">
                       
                        <div class="row">
                            @if(auth()->user()->hasRole('Super Admin'))
                            <div class="col-md-4">
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
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="session_id">Academic Session <span class="text-danger">*</span></label>
                                    <select name="session_id" id="session_id" class="form-control" required>
                                        <option value="">Select Session</option>
                                    </select>
                                    <div class="invalid-feedback" id="session_id_error"></div>
                                </div>
                            </div>
                        
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="class_id">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" id="class_id" class="form-control" required>
                                        <option value="">Select Class</option>
                                    </select>
                                    <div class="invalid-feedback" id="class_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="section_id">Section <span class="text-danger">*</span></label>
                                    <select name="section_id" id="section_id" class="form-control" required>
                                        <option value="">Select Section</option>
                                    </select>
                                    <div class="invalid-feedback" id="section_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="course_id">Course <span class="text-danger">*</span></label>
                                    <select name="course_id" id="course_id" class="form-control" required>
                                        <option value="">Select Course</option>
                                    </select>
                                    <div class="invalid-feedback" id="course_id_error"></div>
                                </div>
                            </div>
                            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin'))
                            <div class="col-md-4">
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
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Lecture Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" class="form-control" required>
                                    <div class="invalid-feedback" id="title_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lecture_date">Lecture Date <span class="text-danger">*</span></label>
                                    <input type="date" name="lecture_date" id="lecture_date" class="form-control" required>
                                    <div class="invalid-feedback" id="lecture_date_error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="video">Video File (MP4)</label>
                                    <input type="file" name="video" id="video" class="form-control" accept="video/mp4">
                                    <small class="text-muted">Max size: 50MB</small>
                                    <div class="invalid-feedback" id="video_error"></div>
                                    <div id="video_preview" class="mt-2"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pdf">PDF File</label>
                                    <input type="file" name="pdf" id="pdf" class="form-control" accept=".pdf">
                                    <small class="text-muted">Max size: 10MB</small>
                                    <div class="invalid-feedback" id="pdf_error"></div>
                                    <div id="pdf_preview" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">InActive</option>
                                    </select>
                                    <div class="invalid-feedback" id="status_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                                    <div class="invalid-feedback" id="description_error"></div>
                                </div>
                            </div>
                        </div>
                       
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-sm">
                                    <span id="submitBtnText">Submit</span>
                                    <span id="submitBtnLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                                <button type="button" id="cancelBtn" class="btn btn-secondary btn-sm">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>


                <!-- Filter Section -->
                <div class="card-body border-bottom">
                    <form id="filterForm">
                        <div class="row">
                            @if(auth()->user()->hasRole('Super Admin'))
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_institute">Institute</label>
                                    <select name="institute_id" id="filter_institute" class="form-control">
                                        <option value="">All Institutes</option>
                                        @foreach($institutes as $institute)
                                            <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_session">Session</label>
                                    <select name="session_id" id="filter_session" class="form-control">
                                        <option value="">All Sessions</option>
                                              
                                       
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_class">Class</label>
                                    <select name="class_id" id="filter_class" class="form-control">
                                        <option value="">All Classes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_section">Section</label>
                                    <select name="section_id" id="filter_section" class="form-control">
                                        <option value="">All Sections</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_course">Course</label>
                                    <select name="course_id" id="filter_course" class="form-control">
                                        <option value="">All Courses</option>
                                    </select>
                                </div>
                            </div>
                            @if(auth()->user()->hasRole('Admin') || auth()->user()->hasrole('Super Admin') || auth()->user()->hasRole('Student'))
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_teacher">Teacher</label>
                                    <select name="teacher_id" id="filter_teacher" class="form-control">
                                        <option value="">All Teachers</option>
                                    </select>
                                </div>
                            </div>
                            @endif
                            <!-- Lecture Date Filter -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_lecture_date">Lecture Date</label>
                                    <input type="date" name="lecture_date" id="filter_lecture_date" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-3 d-flex align-items-end my-3">
                                <div class="form-group w-100">
                                    <button type="button" id="applyFilter" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-filter"></i> Apply Filter
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-3 d-flex align-items-end my-3">
                                <div class="form-group w-100">
                                    <button type="button" id="resetFilter" class="btn btn-secondary btn-sm w-100">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Lectures Table -->
                <div class="card-body">
                    <div style="overflow-x:auto;">
                        <table id="lectures-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    @if(auth()->user()->hasRole('Super Admin'))
                                    <th>Institute</th>
                                    @endif
                                    <th>Session</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Course</th>
                                    <th>Teacher</th>

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

@push('styles')
<style>
    .video-thumbnail {
        width: 120px;
        height: 80px;
        object-fit: cover;
        cursor: pointer;
    }
    .pdf-icon {
        font-size: 3rem;
        color: #d32f2f;
    }
    .file-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .file-info small {
        display: block;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            allowClear: true
        });
    });
</script>
<script>
$(document).ready(function() {
   // Initialize DataTable
var table = $('#lectures-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: "{{ route('lectures.data') }}",
        data: function(d) {
            // Add filter parameters to the request
            @if(auth()->user()->hasRole('Super Admin'))
            d.institute_id = $('#filter_institute').val();
            @endif
            
            d.session_id = $('#filter_session').val();
            
            @if(auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Teacher'))
            d.class_id = $('#filter_class').val();
            d.section_id = $('#filter_section').val();
            @endif
            
            @if(auth()->user()->hasRole('Admin'))
            d.teacher_id = $('#filter_teacher').val();
            @endif
            
            d.course_id = $('#filter_course').val();
            d.status = $('#filter_status').val();
            d.lecture_date = $('#filter_lecture_date').val();
        }
    },
    columns: [
        { data: 'title', name: 'title' },
        @if(auth()->user()->hasRole('Super Admin'))
        { data: 'institute', name: 'institute' },
        @endif
        { data: 'session', name: 'session' },
        { data: 'class', name: 'class' },
        { data: 'section', name: 'section' },
        { data: 'course', name: 'course' },
        { data: 'teacher', name: 'teacher' },
        { data: 'lecture_date', name: 'lecture_date' }, // Add this new column
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

    
    // Load filter dropdowns based on user role
    function loadFilterDropdowns() {
        @if(auth()->user()->hasRole('Super Admin'))
        // For Super Admin - load sessions when institute is selected
        $('#filter_institute').change(function() {
            var instituteId = $(this).val();
            if (instituteId) {
                loadFilterSessions(instituteId);
                $('#filter_class, #filter_section, #filter_course, #filter_teacher').empty().append('<option value="">All</option>');
            } else {
                $('#filter_session, #filter_class, #filter_section, #filter_course, #filter_teacher').empty().append('<option value="">All</option>');
            }
        });
        @endif
        
        @if(!auth()->user()->hasRole('Super Admin'))
    loadFilterSessions("{{ auth()->user()->institute_id }}");
@endif

        
        // When session changes, load classes
        $('#filter_session').change(function() {
            var sessionId = $(this).val();
            var instituteId = @if(auth()->user()->hasRole('Super Admin')) $('#filter_institute').val() @else "{{ auth()->user()->institute_id }}" @endif;
            
            if (sessionId && instituteId) {
                loadFilterClasses(instituteId, sessionId);
                $('#filter_section, #filter_course, #filter_teacher').empty().append('<option value="">All</option>');
            } else {
                $('#filter_class, #filter_section, #filter_course, #filter_teacher').empty().append('<option value="">All</option>');
            }
        });
        
        // When class changes, load sections
        $('#filter_class').change(function() {
            var classId = $(this).val();
            var instituteId = @if(auth()->user()->hasRole('Super Admin')) $('#filter_institute').val() @else "{{ auth()->user()->institute_id }}" @endif;
            var sessionId = $('#filter_session').val();
            
            if (classId && instituteId && sessionId) {
                loadFilterSections(instituteId, sessionId, classId);
                $('#filter_course, #filter_teacher').empty().append('<option value="">All</option>');
            } else {
                $('#filter_section, #filter_course, #filter_teacher').empty().append('<option value="">All</option>');
            }
        });
        
        // When section changes, load courses
        $('#filter_section').change(function() {
            var sectionId = $(this).val();
            var instituteId = @if(auth()->user()->hasRole('Super Admin')) $('#filter_institute').val() @else "{{ auth()->user()->institute_id }}" @endif;
            var sessionId = $('#filter_session').val();
            var classId = $('#filter_class').val();
            
            if (sectionId && instituteId && sessionId && classId) {
                loadFilterCourses(instituteId, sessionId, classId, sectionId);
                $('#filter_teacher').empty().append('<option value="">All</option>');
            } else {
                $('#filter_course, #filter_teacher').empty().append('<option value="">All</option>');
            }
        });
        
        // When course changes, load teachers (only for Admin)
        
        $('#filter_course').change(function() {
            var courseId = $(this).val();
            var instituteId = @if(auth()->user()->hasRole('Super Admin')) $('#filter_institute').val() @else "{{ auth()->user()->institute_id }}" @endif;
            var sessionId = $('#filter_session').val();
            var classId = $('#filter_class').val();
            var sectionId = $('#filter_section').val();
            
            if (courseId && instituteId && sessionId && classId && sectionId) {
                loadFilterTeachers(instituteId, sessionId, classId, sectionId, courseId);
            } else {
                $('#filter_teacher').empty().append('<option value="">All</option>');
            }
        });
        
        
    }
    
    // Function to load sessions for filter
    function loadFilterSessions(instituteId) {
        $.ajax({
            url: "{{ route('lectures.dropdowns') }}",
            type: "GET",
            data: { institute_id: instituteId },
            success: function(data) {
                $('#filter_session').empty().append('<option value="">All Sessions</option>');
                if(data.sessions && data.sessions.length > 0) {
                    $.each(data.sessions, function(key, value) {
                        $('#filter_session').append(`<option value="${value.id}">${value.session_name}</option>`);
                    });
                    
                    // For Student - select the latest session by default
                    @if(auth()->user()->hasRole('Student'))
                    if(data.sessions.length > 0) {
                        $('#filter_session').val(data.sessions[0].id).trigger('change');
                    }
                    @endif
                }
            }
        });
    }
    
    // Function to load classes for filter
    function loadFilterClasses(instituteId, sessionId) {
        $.ajax({
            url: "{{ route('lectures.dropdowns') }}",
            type: "GET",
            data: { 
                institute_id: instituteId,
                session_id: sessionId
            },
            success: function(data) {
                $('#filter_class').empty().append('<option value="">All Classes</option>');
                if (data.classes && data.classes.length > 0) {
                    $.each(data.classes, function(key, value) {
                        $('#filter_class').append(`<option value="${value.id}">${value.name}</option>`);
                    });
                }
            }
        });
    }
    
    // Function to load sections for filter
    function loadFilterSections(instituteId, sessionId, classId) {
        $.ajax({
            url: "{{ route('lectures.dropdowns') }}",
            type: "GET",
            data: {
                institute_id: instituteId,
                session_id: sessionId,
                class_id: classId
            },
            success: function(data) {
                $('#filter_section').empty().append('<option value="">All Sections</option>');
                if (data.sections && data.sections.length > 0) {
                    $.each(data.sections, function(key, value) {
                        $('#filter_section').append(`<option value="${value.id}">${value.section_name}</option>`);
                    });
                }
            }
        });
    }
    
    // Function to load courses for filter
    function loadFilterCourses(instituteId, sessionId, classId, sectionId) {
        $.ajax({
            url: "{{ route('lectures.dropdowns') }}",
            type: "GET",
            data: {
                institute_id: instituteId,
                session_id: sessionId,
                class_id: classId,
                section_id: sectionId
            },
            success: function(data) {
                $('#filter_course').empty().append('<option value="">All Courses</option>');
                if (data.courses && data.courses.length > 0) {
                    $.each(data.courses, function(key, value) {
                        $('#filter_course').append(`<option value="${value.id}">${value.course_name}</option>`);
                    });
                }
            }
        });
    }
    
    // Function to load teachers for filter (Admin only)
    function loadFilterTeachers(instituteId, sessionId, classId, sectionId, courseId) {
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
                $('#filter_teacher').empty().append('<option value="">All Teachers</option>');
                if (data.length > 0) {
                    $.each(data, function(key, teacher) {
                        $('#filter_teacher').append(`<option value="${teacher.id}">${teacher.name}</option>`);
                    });
                }
            }
        });
    }
    
    // Apply filter button click
    $('#applyFilter').click(function() {
        table.ajax.reload();
    });
    
    // Reset filter button click
    $('#resetFilter').click(function() {
        $('#filterForm')[0].reset();
        @if(auth()->user()->hasRole('Super Admin'))
        $('#filter_institute').val('').trigger('change');
        @else
        $('#filter_session').val('').trigger('change');
        @endif
        table.ajax.reload();
    });
    
    // Initialize filter dropdowns
    loadFilterDropdowns();

    // Show/hide form
    $('#addLectureBtn').click(function() {
        $('#lectureForm')[0].reset();
        $('#lectureFormContainer').show();
        $('#lecture_id').val('');
        $('#submitBtnText').text('Submit');
        $('html, body').animate({
            scrollTop: $('#lectureFormContainer').offset().top
        }, 500);
        
        // Set default lecture date to today
        $('#lecture_date').val(new Date().toISOString().split('T')[0]);
        
        // For Admin - load data immediately
        @if(!auth()->user()->hasRole('Super Admin'))
        loadInitialDataForAdmin();
        @endif
    });

    $('#cancelBtn').click(function() {
        $('#lectureFormContainer').hide();
    });

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
            $.ajax({
                url: "{{ route('lectures.dropdowns') }}",
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
                    } else {
                        $('#class_id').append('<option value="">No classes found</option>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading classes:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load classes. Please check console for details.'
                    });
                }
            });
        } else {
            $('#class_id').empty().append('<option value="">Select Class</option>');
        }
    });

    // When class changes, load sections for that class
    $('#class_id').change(function() {
        var classId = $(this).val();
        var instituteId = $('#institute_id').val();
        var sessionId = $('#session_id').val();

        if (classId && instituteId && sessionId) {
            $.ajax({
                url: "{{ route('lectures.dropdowns') }}",
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
                    } else {
                        $('#section_id').append('<option value="">No sections found</option>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading sections:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load sections. Please check console for details.'
                    });
                }
            });
        } else {
            $('#section_id').empty().append('<option value="">Select Section</option>');
        }
    });

    // When section changes, load courses for that section
    $('#section_id').change(function() {
        var sectionId = $(this).val();
        var instituteId = $('#institute_id').val();
        var sessionId = $('#session_id').val();
        var classId = $('#class_id').val();

        if (sectionId && instituteId && sessionId && classId) {
            $.ajax({
                url: "{{ route('lectures.dropdowns') }}",
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
                    } else {
                        $('#course_id').append('<option value="">No courses found</option>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading courses:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load courses. Please check console for details.'
                    });
                }
            });
        } else {
            $('#course_id').empty().append('<option value="">Select Course</option>');
        }
    });

    // When course changes, load teachers for that course
    $('#course_id').change(function() {
        var courseId = $(this).val();
        var instituteId = $('#institute_id').val();
        var sessionId = $('#session_id').val();
        var classId = $('#class_id').val();
        var sectionId = $('#section_id').val();

        if (courseId && instituteId && sessionId && classId && sectionId) {
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
                    } else {
                        $('#teacher_id').append('<option value="">No teachers found for this course</option>');
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Teachers Found',
                            text: 'There are no teachers enrolled in this course/section combination.'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error loading teachers:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load teachers. Please check console for details.'
                    });
                }
            });
        } else {
            $('#teacher_id').empty().append('<option value="">Select Teacher</option>');
        }
    });

    // Function to load sessions
    function loadSessions(instituteId, callback) {
        $.ajax({
            url: "{{ route('lectures.dropdowns') }}",
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

    // Form submission handler
    $('#lectureForm').submit(function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Show loader
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').addClass('d-none');
        $('#submitBtnLoader').removeClass('d-none');
        
        // Get form data
        var formData = new FormData(this);
        var url = $('#lecture_id').val()
            ? '/lectures/update/' + $('#lecture_id').val()
            : "{{ route('lectures.store') }}";

        var method = 'POST';
        
        // If updating, add the ID to the form data
        if ($('#lecture_id').val()) {
            formData.append('_method', 'PUT');
        }
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Hide form
                $('#lectureFormContainer').hide();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Reload table
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $('#'+field).addClass('is-invalid');
                        $('#'+field+'_error').text(errors[field][0]);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong!'
                    });
                }
            },
            complete: function() {
                // Hide loader
                $('#submitBtn').prop('disabled', false);
                $('#submitBtnText').removeClass('d-none');
                $('#submitBtnLoader').addClass('d-none');
            }
        });
    });

    // Edit button click
    $(document).on('click', '.edit-btn', function() {
        var lectureId = $(this).data('id');
        
        // Show loader on button
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        $.ajax({
            url: "{{ url('lectures/edit') }}/" + lectureId,
            type: "GET",
            success: function(response) {
                // Reset button text
                $('.edit-btn').html('<i class="fas fa-edit"></i> Edit');
                
                if (response.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to load lecture data'
                    });
                    return;
                }

                // Populate form with lecture data
                $('#lectureForm')[0].reset();
                $('#lecture_id').val(response.lecture.id);
                $('#title').val(response.lecture.title);
                $('#description').val(response.lecture.description);
                $('#lecture_date').val(response.lecture.lecture_date);
                $('#status').val(response.lecture.status).trigger('change');
                
                // For Super Admin - set institute and load data
                @if(auth()->user()->hasRole('Super Admin'))
                $('#institute_idd').val(response.lecture.institute_id).trigger('change', [function() {
                    loadInitialDataForEdit(response);
                }]);
                @else
                loadInitialDataForEdit(response);
                @endif
                
                // Show the form
                $('#lectureFormContainer').show();
                $('html, body').animate({
                    scrollTop: $('#lectureFormContainer').offset().top
                }, 500);
                
                // Show file previews if they exist
                if (response.video_url) {
                    $('#video_preview').html(`
                        <div class="alert alert-info p-2">
                            <i class="fas fa-video me-2"></i>
                            <a href="${response.video_url}" target="_blank">Current Video</a>
                            <button type="button" class="btn btn-sm btn-danger float-end remove-file" data-type="video">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `);
                }
                
                if (response.pdf_url) {
                    $('#pdf_preview').html(`
                        <div class="alert alert-info p-2">
                            <i class="fas fa-file-pdf me-2"></i>
                            <a href="${response.pdf_url}" target="_blank">Current PDF</a>
                            <button type="button" class="btn btn-sm btn-danger float-end remove-file" data-type="pdf">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                $('.edit-btn').html('<i class="fas fa-edit"></i> Edit');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to load lecture data'
                });
            }
        });
    });
    
    // Function to load initial data for edit
    function loadInitialDataForEdit(response) {
        var instituteId = response.lecture.institute_id;
        var sessionId = response.lecture.session_id;
        var classId = response.lecture.class_id;
        var sectionId = response.lecture.section_id;
        var courseId = response.lecture.course_id;
        var teacherId = response.lecture.teacher_id;

        // Load sessions
        loadSessions(instituteId, function() {
            $('#session_id').val(sessionId);
            
            // Load classes for this session
            $.ajax({
                url: "{{ route('lectures.dropdowns') }}",
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
                    $('#class_id').val(classId);
                    
                    // Load sections for this class
                    $.ajax({
                        url: "{{ route('lectures.dropdowns') }}",
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
                            $('#section_id').val(sectionId);
                            
                            // Load courses for this section
                            $.ajax({
                                url: "{{ route('lectures.dropdowns') }}",
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
                                    $('#course_id').val(courseId);
                                    
                                    // Load teachers for this course
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
                                            $('#teacher_id').val(teacherId);
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });
    }

    // Remove file button click
    $(document).on('click', '.remove-file', function() {
        var type = $(this).data('type');
        $('#' + type + '_preview').empty();
        $('#' + type).val('');
    });

    // Delete button click
    $(document).on('click', '.delete-btn', function() {
        let lectureId = $(this).data('id');
        let $button = $(this);
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loader on button
                $button.html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                
                $.ajax({
                    url: "{{ url('lectures/delete') }}/" + lectureId,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            response.message,
                            'success'
                        );
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON?.message || 'Something went wrong while deleting.',
                            'error'
                        );
                    },
                    complete: function() {
                        // Reset button text
                        $button.html('<i class="fas fa-trash"></i> Delete');
                    }
                });
            }
        });
    });

    // File preview handlers
    $('#video').change(function() {
        var file = this.files[0];
        if (file) {
            $('#video_preview').html(`
                <div class="alert alert-info p-2">
                    <i class="fas fa-video me-2"></i> ${file.name}
                    <button type="button" class="btn btn-sm btn-danger float-end remove-file" data-type="video">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `);
        }
    });

    $('#pdf').change(function() {
        var file = this.files[0];
        if (file) {
            $('#pdf_preview').html(`
                <div class="alert alert-info p-2">
                    <i class="fas fa-file-pdf me-2"></i> ${file.name}
                    <button type="button" class="btn btn-sm btn-danger float-end remove-file" data-type="pdf">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `);
        }
    });
});
</script>
@endpush