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
                                <i class="fas fa-chalkboard-teacher text-white"></i> Teacher Course Assignments
                            </h3>
                        </div>
                        <div>
                            <a href="{{ route('teacher.enrollments.form') }}" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Assign Teacher
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Assignments Table -->
                <div class="card-body">
                    @if(auth()->user()->hasRole('Super Admin'))
                    <div class="row mb-3">
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
                    </div>
                    @endif
                    
                    {{-- <table id="assignments-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Teacher</th>
                                <th>Institute</th>
                                <th>Session</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Course</th>
                                <th>Students</th>
                                <th>Enrollment Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data loaded via AJAX -->
                        </tbody>
                    </table> --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
        // var assignmentsTable = $('#assignments-table').DataTable({
        //     processing: true,
        //     serverSide: true,
        //     ajax: {
        //         url: "{{ route('enrollments.data') }}",
        //         data: function(d) {
        //             @if(auth()->user()->hasRole('Super Admin'))
        //             d.institute_id = $('#filter_institute').val();
        //             @endif
        //         }
        //     },
        //     columns: [
        //         { 
        //             data: 'teacher_name',
        // name: 'teacher_name', // <-- match the alias from select
        // orderable: false,
        // searchable: true
        //         },
        //         { 
        //             data: 'institute',
        //             name: 'institute.name',
        //             orderable: true,
        //             searchable: true
        //         },
        //         { 
        //             data: 'session',
        //             name: 'session.session_name',
        //             orderable: true,
        //             searchable: true
        //         },
        //         { 
        //             data: 'class',
        //             name: 'class.name',
        //             orderable: true,
        //             searchable: true
        //         },
        //         { 
        //             data: 'section',
        //             name: 'section.section_name',
        //             orderable: true,
        //             searchable: true
        //         },
        //         { 
        //             data: 'course',
        //             name: 'course.course_name',
        //             orderable: true,
        //             searchable: true
        //         },
        //         { 
        //             data: 'student_count',
        //             name: 'student_count',
        //             orderable: true,
        //             searchable: false
        //         },
        //         { 
        //             data: 'enrollment_date',
        //             name: 'enrollment_date',
        //             orderable: true,
        //             searchable: false
        //         },
        //         { 
        //             data: 'status',
        //             name: 'status',
        //             orderable: true,
        //             searchable: true
        //         },
        //         { 
        //             data: 'action',
        //             name: 'action',
        //             orderable: false,
        //             searchable: false
        //         }
        //     ],
        //     responsive: true,
        //     autoWidth: false,
        //     language: {
        //         paginate: {
        //             previous: '<i class="fas fa-angle-left"></i>',
        //             next: '<i class="fas fa-angle-right"></i>'
        //         }
        //     },
        //     order: [[0, 'asc']] // Sort by teacher name by default
        // });

    @if(auth()->user()->hasRole('Super Admin'))
    $('#filter_institute').change(function() {
        assignmentsTable.ajax.reload();
    });
    @endif

    // Unassign button click
    $(document).on('click', '.unassign-btn', function() {
        let sessionId = $(this).data('session-id');
        let courseId = $(this).data('course-id');
        let classId = $(this).data('class-id');
        let sectionId = $(this).data('section-id');
        let teacherId = $(this).data('teacher-id');
        let $button = $(this);
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, unassign it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loader on button
                $button.html('<i class="fas fa-spinner fa-spin"></i> Unassigning...');
                
                $.ajax({
                    url: "{{ route('teacher.enrollments.unassign') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        session_id: sessionId,
                        course_id: courseId,
                        class_id: classId,
                        section_id: sectionId,
                        teacher_id: teacherId
                    },
                    success: function(response) {
                        Swal.fire(
                            'Unassigned!',
                            response.message,
                            'success'
                        );
                        assignmentsTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON?.message || 'Something went wrong while unassigning.',
                            'error'
                        );
                    },
                    complete: function() {
                        // Reset button text
                        $button.html('<i class="fas fa-trash"></i> Unassign');
                    }
                });
            }
        });
    });
});
</script>
@endpush