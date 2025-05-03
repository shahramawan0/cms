@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div>
                            <h3 class="card-title text-white">
                                <i class="fas fa-users"></i> Students Management
                            </h3>
                        </div>
                        <div>
                            <a href="{{ route('admin.students.create') }}" class="btn btn-secondary btn-sm text-white" id="add-student-btn">
                                <i class="fas fa-plus"></i> Add Student
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="students-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Roll Number</th>
                                <th>Class / Section</th>
                                <th>Admission Date</th>
                                <th>Institute</th>
                                <th>Admin</th>
                                <th>Teacher</th>
                                <th>Courses</th>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#students-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.students.data') }}",
        columns: [
        { data: 'profile_image', name: 'profile_image', orderable: false, searchable: false },
        { data: 'name', name: 'name' },
        { data: 'email', name: 'email' },
        { data: 'phone', name: 'phone' },
        { data: 'roll_number', name: 'roll_number' },
        { data: 'class_section', name: 'class_section', orderable: false, searchable: false },
        { data: 'admission_date', name: 'admission_date' },
        { data: 'institute', name: 'institute.name' },
        { data: 'admin', name: 'admin.name' },
        { data: 'teacher', name: 'teacher.name' },
        { data: 'courses', name: 'courses.course_name', orderable: false, searchable: false },
        { data: 'action', name: 'action', orderable: false, searchable: false }
    ],

        responsive: true,
        autoWidth: false,
        scrollX: true,
        language: {
            paginate: {
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>'
            },
            search: "_INPUT_",
            searchPlaceholder: "Search students...",
            lengthMenu: "Show _MENU_ students",
            info: "Showing _START_ to _END_ of _TOTAL_ students",
            infoEmpty: "No students found",
            infoFiltered: "(filtered from _MAX_ total students)"
        }
    });

    // Add loading spinner to all buttons
    $(document).on('click', '[data-loading]', function() {
        const btn = $(this);
        btn.prop('disabled', true);
        btn.find('.spinner-border').removeClass('d-none');
        btn.find('i').addClass('d-none');
    });

    // Delete confirmation
    $(document).on('click', '.delete-btn', function() {
        var studentId = $(this).data('id');
        var btn = $(this);
        
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
                btn.prop('disabled', true);
                btn.find('.spinner-border').removeClass('d-none');
                btn.find('i').addClass('d-none');
                
                $.ajax({
                    url: "{{ url('admin/students/delete') }}/" + studentId,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        $('#students-table').DataTable().ajax.reload(null, false);
                        Swal.fire(
                            'Deleted!',
                            response.success,
                            'success'
                        );
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'Failed to delete student',
                            'error'
                        );
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                        btn.find('.spinner-border').addClass('d-none');
                        btn.find('i').removeClass('d-none');
                    }
                });
            }
        });
    });
});
</script>
@endpush