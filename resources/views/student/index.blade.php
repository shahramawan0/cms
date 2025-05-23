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
                                <i class="fas fa-users text-white"></i> Students Registraion
                            </h3>
                        </div>
                        <div>
                            <button id="addStudentBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Resgister Student
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Student Form (Initially Hidden) -->
                <div class="card-body" id="studentFormContainer" style="display: none;">
                    <form id="studentForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="studentId" name="id">

                        @if(auth()->user()->hasRole('Super Admin'))
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="institute_id">Institute <span class="text-danger">*</span></label>
                                    <select name="institute_id" id="institute_id" class="form-control" required>
                                        <option value="">Select Institute</option>
                                        @foreach(App\Models\Institute::get() as $institute)
                                            <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="institute_id_error"></div>
                                </div>
                            </div>
                        </div>
                        @else
                            <input type="hidden" name="institute_id" value="{{ auth()->user()->institute_id }}">
                        @endif

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" required>
                                    <div class="invalid-feedback" id="name_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="father_name">Father Name <span class="text-danger">*</span></label>
                                    <input type="text" name="father_name" id="father_name" class="form-control" required>
                                    <div class="invalid-feedback" id="father_name_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                    <div class="invalid-feedback" id="email_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cnic">CNIC <span class="text-danger">*</span></label>
                                    <input type="text" name="cnic" id="cnic" class="form-control" required>
                                    <div class="invalid-feedback" id="cnic_error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="dob">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="dob" id="dob" class="form-control" required>
                                    <div class="invalid-feedback" id="dob_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" name="phone" id="phone" class="form-control">
                                    <div class="invalid-feedback" id="phone_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="gender">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" id="gender" class="form-control" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <div class="invalid-feedback" id="gender_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="roll_number">Roll Number <span class="text-danger">*</span></label>
                                    <input type="text" name="roll_number" id="roll_number" class="form-control" required>
                                    <div class="invalid-feedback" id="roll_number_error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="admission_date">Admission Date <span class="text-danger">*</span></label>
                                    <input type="date" name="admission_date" id="admission_date" class="form-control" required>
                                    <div class="invalid-feedback" id="admission_date_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="password">Password <span id="passwordRequired">*</span></label>
                                    <input type="password" name="password" id="password" class="form-control">
                                    <div class="invalid-feedback" id="password_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password <span id="confirmPasswordRequired">*</span></label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="profile_image">Profile Image</label>
                                    <input type="file" name="profile_image" id="profile_image" class="form-control">
                                    <div class="invalid-feedback" id="profile_image_error"></div>
                                    <div id="imagePreview" class="mt-2" style="display: none;">
                                        <img id="previewImg" src="#" alt="Profile Image Preview" style="max-width: 100px; max-height: 100px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea name="address" id="address" rows="2" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="row mt-2">
                            <div class="col-md-12 text-right">
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-sm">
                                    <span id="submitBtnText">Submit</span>
                                    <span id="submitBtnLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                                <button type="button" id="cancelBtn" class="btn btn-secondary btn-sm">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>

                
                <!-- Students Table -->
                <div class="card-body" style="border-top:1px solid #000">
                    <!-- Filter Toggle Button -->
                    <div class="mb-2 text-right">
                        <button id="filterToggleBtn" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                    </div>

                    <!-- Filters Section (Initially Hidden) -->
                    <div id="filtersSection" style="display: none;" class="mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <select class="form-control form-select" id="filter_session">
                                    <option value="">All Sessions</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control form-select" id="filter_class">
                                    <option value="">All Classes</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control form-select" id="filter_section">
                                    <option value="">All Sections</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control form-select" id="filter_course">
                                    <option value="">All Courses</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control form-select" id="filter_teacher">
                                    <option value="">All Teachers</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" id="filter_enrollment_date">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="students-table" class="table  table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Name</th>
                                    <th>Father Name</th>
                                    <th>CNIC</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Institute</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Course</th>
                                    <th>Teacher</th>
                                    <th>Enrollment Date</th>
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

    // Toggle filters visibility
    $('#filterToggleBtn').click(function() {
        $('#filtersSection').toggle();
    });

    // Load filter dropdowns on page load
    loadFilterOptions();
    

    // Initialize DataTable
    var table = $('#students-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('students.data') }}",
            data: function(d) {
                d.session_id = $('#filter_session').val();
                d.class_id = $('#filter_class').val();
                d.section_id = $('#filter_section').val();
                d.course_id = $('#filter_course').val();
                d.teacher_id = $('#filter_teacher').val();
                d.enrollment_date = $('#filter_enrollment_date').val();
            }
        },
        columns: [
            { data: 'roll_number', name: 'users.roll_number' },
            { data: 'name', name: 'users.name' },
            { data: 'father_name', name: 'users.father_name' },
            { data: 'cnic', name: 'users.cnic' },
            { data: 'email', name: 'users.email' },
            { data: 'phone', name: 'users.phone' },
            { data: 'institute_name', name: 'institutes.institute_name' },
            { data: 'class_name', name: 'classes.name' },
            { data: 'section_name', name: 'sections.section_name' },
            { data: 'course_name', name: 'courses.course_name' },
            { data: 'teacher_name', name: 'teachers.name' },
            { data: 'enrollment_date', name: 'student_enrollments.enrollment_date' },
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

    // Function to load filter options
    function loadFilterOptions() {
        $.ajax({
            url: "{{ route('students.filter.options') }}",
            type: "GET",
            success: function(response) {
                // Populate session dropdown
                if (response.sessions) {
                    $('#filter_session').empty().append('<option value="">All Sessions</option>');
                    response.sessions.forEach(function(session) {
                        $('#filter_session').append(`<option value="${session.id}">${session.session_name}</option>`);
                    });
                }

                // Populate class dropdown
                if (response.classes) {
                    $('#filter_class').empty().append('<option value="">All Classes</option>');
                    response.classes.forEach(function(classItem) {
                        $('#filter_class').append(`<option value="${classItem.id}">${classItem.name}</option>`);
                    });
                }

                // Populate course dropdown
                if (response.courses) {
                    $('#filter_course').empty().append('<option value="">All Courses</option>');
                    response.courses.forEach(function(course) {
                        $('#filter_course').append(`<option value="${course.id}">${course.course_name}</option>`);
                    });
                }

                // Populate teacher dropdown
                if (response.teachers) {
                    $('#filter_teacher').empty().append('<option value="">All Teachers</option>');
                    response.teachers.forEach(function(teacher) {
                        $('#filter_teacher').append(`<option value="${teacher.id}">${teacher.name}</option>`);
                    });
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to load filter options'
                });
            }
        });
    }

    // Handle class change to load sections
    $('#filter_class').change(function() {
        const classId = $(this).val();
        if (classId) {
            $.ajax({
                url: "{{ route('students.sections.by.class') }}",
                type: "GET",
                data: { class_id: classId },
                success: function(response) {
                    $('#filter_section').empty().append('<option value="">All Sections</option>');
                    response.sections.forEach(function(section) {
                        $('#filter_section').append(`<option value="${section.id}">${section.section_name}</option>`);
                    });
                },
                error: function(xhr) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Failed to load sections'
                    });
                }
            });
        } else {
            $('#filter_section').empty().append('<option value="">All Sections</option>');
        }
    });

    // Handle filter changes
    $('#filter_session, #filter_class, #filter_section, #filter_course, #filter_teacher, #filter_enrollment_date').change(function() {
        table.ajax.reload();
    });

    // Handle form submission with toast messages
    $('#studentForm').submit(function(e) {
        e.preventDefault();
        
        // Show loader
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').addClass('d-none');
        $('#submitBtnLoader').removeClass('d-none');
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        let url = "{{ route('students.store') }}";
        let method = "POST";
        let formData = new FormData(this);

        if ($('#studentId').val()) {
            url = "{{ url('students/update') }}/" + $('#studentId').val();
            formData.append('_method', 'PUT');
        }
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#studentFormContainer').hide();
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    let errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $('#'+field).addClass('is-invalid');
                        $('#'+field+'_error').text(errors[field][0]);
                    }
                    Toast.fire({
                        icon: 'error',
                        title: 'Please check the form for errors'
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON.message || 'Something went wrong!'
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

    // Handle delete with toast messages
    $(document).on('click', '.delete-btn', function() {
        let studentId = $(this).data('id');
        
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
                $.ajax({
                    url: "{{ url('students/delete') }}/" + studentId,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Failed to delete student'
                        });
                    }
                });
            }
        });
    });

    // Show/hide form
    $('#addStudentBtn').click(function() {
        $('#studentForm')[0].reset();
        $('#studentId').val('');
        $('#password').val('').attr('required', true);
        $('#password_confirmation').val('').attr('required', true);
        $('#passwordRequired, #confirmPasswordRequired').show();
        $('#studentFormContainer').show();
        $('#imagePreview').hide();
        $('html, body').animate({
            scrollTop: $('#studentFormContainer').offset().top
        }, 500);
    });

    $('#cancelBtn').click(function() {
        $('#studentFormContainer').hide();
    });

    // Profile image preview
    $('#profile_image').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#imagePreview').show();
            }
            reader.readAsDataURL(file);
        }
    });

    // Edit button click
    $(document).on('click', '.edit-btn', function() {
        let studentId = $(this).data('id');
        
        $.ajax({
            url: "{{ url('students/edit') }}/" + studentId,
            type: "GET",
            success: function(response) {
                // Fill form with data
                $('#studentId').val(response.id);
                $('#name').val(response.name);
                $('#father_name').val(response.father_name);
                $('#cnic').val(response.cnic);
                $('#roll_number').val(response.roll_number);
                $('#gender').val(response.gender);
                $('#dob').val(response.dob);
                $('#admission_date').val(response.admission_date);
                $('#email').val(response.email);
                $('#phone').val(response.phone);
                $('#address').val(response.address);
                
                if ($('#institute_id').length) {
                    $('#institute_id').val(response.institute_id);
                }
                
                if (response.profile_image) {
                    $('#previewImg').attr('src', "{{ asset('storage') }}/" + response.profile_image);
                    $('#imagePreview').show();
                } else {
                    $('#imagePreview').hide();
                }
                
                $('#studentFormContainer').show();
                $('html, body').animate({
                    scrollTop: $('#studentFormContainer').offset().top
                }, 500);
                
                $('#password').removeAttr('required');
                $('#password_confirmation').removeAttr('required');
                $('#passwordRequired, #confirmPasswordRequired').hide();
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to load student data'
                });
            }
        });
    });
});
</script>
@endpush