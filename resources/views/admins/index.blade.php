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
                                <i class="fas fa-users-cog text-white"></i> Admin Users Management
                            </h3>
                        </div>
                        <div>
                            <a href="{{ route('admin.users.create') }}" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i>Add Admin
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="admin-users-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Institute</th>
                                <th>Admin Role</th>
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
    $('#admin-users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.users.data') }}",
        columns: [
            { data: 'profile_image', name: 'profile_image', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'institute', name: 'institute.name' },
            { data: 'role', name: 'roles.name' },
            { data: 'status', name: 'email_verified_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        autoWidth: false,
        language: {
            paginate: {
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>'
            },
            search: "_INPUT_",
            searchPlaceholder: "Search admin users...",
            lengthMenu: "Show _MENU_ admin users",
            info: "Showing _START_ to _END_ of _TOTAL_ admin users",
            infoEmpty: "No admin users found",
            infoFiltered: "(filtered from _MAX_ total admin users)"
        }
    });

    // Delete confirmation
    $(document).on('click', '.delete-btn', function() {
        var userId = $(this).data('id');
        
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
                    url: "{{ url('admin/users/delete') }}/" + userId,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        $('#admin-users-table').DataTable().ajax.reload(null, false);
                        Swal.fire(
                            'Deleted!',
                            response.success,
                            'success'
                        );
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'Failed to delete admin user',
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