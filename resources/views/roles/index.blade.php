@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Column starts -->
            <div class="col-md-12">
                <div class="card dz-card" id="accordion-one">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title mb-0 text-white">
                                <i class="fas fa-user-shield text-white"></i> Roles
                            </h3>
                        </div>
                        <div>
                            <button class="btn  btn-secondary text-white" data-bs-toggle="modal" data-bs-target="#roleModal">
                                <i class="fas fa-plus"></i> Add Role
                            </button>
                        </div>
                    </div>
                    
                    {{-- <div class="card-header flex-wrap">
                        <div>
                            <h4 class="card-title">Role Management</h4>
                           
                        </div>
                        <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#roleModal">Add Role</button>
                    </div> --}}
                    <!--tab-content-->
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="Preview" role="tabpanel" aria-labelledby="home-tab">
                            <div class="card-body pt-0 px-0">
                                <div class="table-responsive">
                                    <table id="roleTable" class="table table-sm table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Role Name</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- /Default accordion -->
                        </div>
                    </div>
                    <!--/tab-content-->
                </div>
            </div>
        </div>
    </div>



    <!-- Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog bd-example-modal-lg" role="document">
            <form id="roleForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="_method" id="_method">
                        <input type="hidden" name="id" id="role_id">
                        <div class="mb-3">
                            <label>Role Name</label>
                            <input type="text" class="form-control" name="name" id="role_name" required>
                        </div>
                        <hr>
                        <hr>
                    <h5>Role Information</h5>
                    <p>This form allows you to add or edit a user role in the system. Please enter a valid role name and click Save to proceed.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        let table = $('#roleTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: "{{ route('roles.index') }}",
            columns: [
                { data: 'id' },
                { data: 'name' },
                {
                    data: null,
                    render: function (data, type, row) {
                        return `
                        <div class="btn-group">
                            <button class="btn btn-sm btn-info me-1" onclick="editRole(${row.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger me-1" onclick="deleteRole(${row.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            <a href="{{ url('permissions/assign') }}?role=${row.id}" class="btn btn-sm btn-warning me-1">
                                <i class="fas fa-key"></i> Permissions
                            </a>
                        </div>
                    `;
                    },
                    orderable: false,
                    searchable: false
                }
            ],
            responsive: true,
            autoWidth: false,
            language: {
                paginate: {
                    previous: '<i class="fas fa-angle-left"></i>',
                    next: '<i class="fas fa-angle-right"></i>'
                },
                search: "_INPUT_",
                searchPlaceholder: "Search roles...",
                lengthMenu: "Show _MENU_ roles",
                info: "Showing _START_ to _END_ of _TOTAL_ roles",
                infoEmpty: "Showing 0 to 0 of 0 roles",
                infoFiltered: "(filtered from _MAX_ total roles)"
            },
            dom: '<"top"lf>rt<"bottom"ip><"clear">',
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
        });

        $('#roleForm').submit(function (e) {
            e.preventDefault();

            let id = $('#role_id').val();
            let url = id ? `/roles/${id}` : "{{ route('roles.store') }}";
            let method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                type: method,
                data: $(this).serialize(),
                success: function (res) {
                    $('#roleModal').modal('hide');
                    $('#roleForm')[0].reset();
                    table.ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.success,
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON.message || 'Something went wrong!'
                    });
                }
            });
        });
    });

    function addRole() {
        $('#roleForm')[0].reset();
        $('#_method').val('');
        $('#role_id').val('');
        $('#roleModal').modal('show');
        $('#roleModalLabel').text('Add New Role');
    }

    function editRole(id) {
        $.get(`/roles/${id}/edit`, function (data) {
            $('#role_name').val(data.name);
            $('#role_id').val(data.id);
            $('#_method').val('PUT');
            $('#roleModal').modal('show');
            $('#roleModalLabel').text('Edit Role');
        }).fail(function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Could not load role data'
            });
        });
    }

    function deleteRole(id) {
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
                    url: `/roles/${id}`,
                    type: 'DELETE',
                    data: { 
                        _token: "{{ csrf_token() }}" 
                    },
                    success: function (res) {
                        $('#roleTable').DataTable().ajax.reload(null, false);
                        Swal.fire(
                            'Deleted!',
                            res.success,
                            'success'
                        );
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON.message || 'Failed to delete role',
                            'error'
                        );
                    }
                });
            }
        });
    }
</script>
@endpush