@extends('layouts.app')

@section('content')
    <div class="container px-2">
        <div class="row">
            <!-- Column starts -->
            <div class="col-xl-12 px-0">
                <div class="card dz-card" id="accordion-one">
                    <div class="card-header flex-wrap">
                        <div>
                            <h4 class="card-title">Role Management</h4>
                           
                        </div>
                    </div>
                    <!--tab-content-->
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="Preview" role="tabpanel" aria-labelledby="home-tab">
                            <div class="card-body pt-0 px-0">
                                <div class="table-responsive">
                                    <table id="roleTable" class="display table">
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
        <div class="modal-dialog">
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
                    </div>
                    <div class="modal-footer">
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
                            <button class="btn btn-sm btn-info" onclick="editRole(${row.id})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteRole(${row.id})">Delete</button>
                        `;
                        }
                    }
                ]
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
                        table.ajax.reload();
                        alert(res.success);
                    }
                });
            });
        });

        function addRole() {
            $('#roleForm')[0].reset();
            $('#_method').val('');
            $('#role_id').val('');
            $('#roleModal').modal('show');
        }

        function editRole(id) {
            $.get(`/roles/${id}/edit`, function (data) {
                $('#role_name').val(data.name);
                $('#role_id').val(data.id);
                $('#_method').val('PUT');
                $('#roleModal').modal('show');
            });
        }

        function deleteRole(id) {
            if (confirm("Are you sure to delete this role?")) {
                $.ajax({
                    url: `/roles/${id}`,
                    type: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (res) {
                        $('#roleTable').DataTable().ajax.reload();
                        alert(res.success);
                    }
                });
            }
        }
    </script>
@endpush