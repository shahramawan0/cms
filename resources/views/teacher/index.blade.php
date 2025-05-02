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
                                <i class="fas fa-chalkboard-teacher text-white"></i> Teachers Management
                            </h3>
                        </div>
                        <div>
                            <a href="{{ route('admin.teachers.create') }}" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Add Teacher
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="teachers-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Institute</th>
                                <th>Admin</th>
                                <th>Qualification</th>
                                <th>Experience</th>
                                <th>Specialization</th>
                                <th>Joining Date</th>
                                <th>Salary</th>
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
    $('#teachers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.teachers.data') }}",
        columns: [
            { data: 'profile_image', name: 'profile_image', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { data: 'institute', name: 'institute.name' },
            { data: 'admin', name: 'admin.name' },
            { data: 'qualification', name: 'qualification' },
            { data: 'experience', name: 'experience_years' },
            { data: 'specialization', name: 'specialization' },
            { data: 'joining_date', name: 'joining_date' },
            { data: 'salary', name: 'salary' },
            { data: 'status', name: 'email_verified_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        autoWidth: false,
        scrollX: true, // Enable horizontal scrolling
        language: {
            paginate: {
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>'
            },
            search: "_INPUT_",
            searchPlaceholder: "Search teachers...",
            lengthMenu: "Show _MENU_ teachers",
            info: "Showing _START_ to _END_ of _TOTAL_ teachers",
            infoEmpty: "No teachers found",
            infoFiltered: "(filtered from _MAX_ total teachers)"
        },
        initComplete: function() {
            // Add custom filter for verified status
            this.api().columns([11]).every(function() {
                var column = this;
                var select = $('<select class="form-control form-control-sm"><option value="">All Status</option><option value="verified">Verified</option><option value="unverified">Unverified</option></select>')
                    .appendTo($(column.header()))
                    .on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val());
                        column.search(val ? '^' + val + '$' : '', true, false).draw();
                    });
            });
        }
    });

    // Delete confirmation
    $(document).on('click', '.delete-btn', function() {
        var teacherId = $(this).data('id');
        
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
                    url: "{{ url('admin/teachers/delete') }}/" + teacherId,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        $('#teachers-table').DataTable().ajax.reload(null, false);
                        Swal.fire(
                            'Deleted!',
                            response.success,
                            'success'
                        );
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'Failed to delete teacher',
                            'error'
                        );
                    }
                });
            }
        });
    });
});
</script>
@endpush

<style>
    /* Ensure table is responsive */
    .table-responsive {
        display: block;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    /* Style for status filter */
    .dataTables_wrapper .dataTables_filter {
        float: right;
        text-align: right;
    }
    .dataTables_wrapper .dataTables_length {
        float: left;
    }
    /* Add some padding to the table */
    #teachers-table {
        margin-top: 10px;
    }
</style>