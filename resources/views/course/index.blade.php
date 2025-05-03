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
                                <i class="fas fa-book"></i> Courses Management
                            </h3>
                        </div>
                        <div>
                            <a href="{{ route('admin.courses.create') }}" class="btn btn-secondary btn-sm text-white" id="add-course-btn">
                                <i class="fas fa-plus"></i> Add Course
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="courses-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Course Code</th>
                                <th>Institute</th>
                                <th>Level</th>
                                <th>Language</th>
                                <th>Duration</th>
                                <th>Date Range</th>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#courses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.courses.data') }}",
        columns: [
            { data: 'course_name', name: 'course_name' },
            { data: 'course_code', name: 'course_code' },
            { data: 'institute', name: 'institute.name' },
            { data: 'level', name: 'level' },
            { data: 'language', name: 'language' },
            { data: 'duration', name: 'duration_months' },
            { data: 'date_range', name: 'date_range' },
            { data: 'status', name: 'is_active' },
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
            searchPlaceholder: "Search courses...",
            lengthMenu: "Show _MENU_ courses",
            info: "Showing _START_ to _END_ of _TOTAL_ courses",
            infoEmpty: "No courses found",
            infoFiltered: "(filtered from _MAX_ total courses)"
        },
        
    });

    // Add loading spinner to all buttons with data-loading attribute
    $(document).on('click', '[data-loading]', function() {
        const btn = $(this);
        btn.prop('disabled', true);
        btn.find('.spinner-border').removeClass('d-none');
        btn.find('i').addClass('d-none');
    });

    // Delete confirmation
    $(document).on('click', '.delete-btn', function() {
        var courseId = $(this).data('id');
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
                // Show loading on delete button
                btn.prop('disabled', true);
                btn.find('.spinner-border').removeClass('d-none');
                btn.find('i').addClass('d-none');
                
                $.ajax({
                    url: "{{ url('admin/courses/delete') }}/" + courseId,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        $('#courses-table').DataTable().ajax.reload(null, false);
                        Swal.fire(
                            'Deleted!',
                            response.success,
                            'success'
                        );
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'Failed to delete course',
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

<style>
    .table-responsive {
        display: block;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .dataTables_wrapper .dataTables_filter {
        float: right;
        text-align: right;
    }
    .dataTables_wrapper .dataTables_length {
        float: left;
    }
    #courses-table {
        margin-top: 10px;
    }
</style>