// resources/views/permissions/assign.blade.php
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-key"></i> Assign Permissions
                    </h3>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="font-weight-bold">Select Role</label>
                                <select name="role_id" id="roleSelect" class="form-control" required>
                                    <option value="">-- Select Role --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="permissionSection">
                            <h4 class="mb-3">Permissions for: <span id="roleName" class="text-primary">All Roles</span></h4>
                            
                            <div class="row">
                                @foreach($modules as $module)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0 text-capitalize">{{ $module['name'] }}</h5>
                                        </div>
                                        <div class="card-body">
                                            @foreach(['view', 'create', 'edit', 'delete'] as $action)
                                                @if(isset($module['permissions'][$action]))
                                                <div class="form-check">
                                                    <input class="form-check-input permission-check" 
                                                           type="checkbox" 
                                                           name="permissions[]"
                                                           value="{{ $module['permissions'][$action]->id }}"
                                                           id="perm_{{ $module['permissions'][$action]->id }}">
                                                    <label class="form-check-label" for="perm_{{ $module['permissions'][$action]->id }}">
                                                        {{ ucfirst($action) }}
                                                    </label>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-save"></i> Save Permissions
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // When role is selected
    $('#roleSelect').change(function() {
        const roleId = $(this).val();
        if (!roleId) return;
        
        // Update form action URL
        $('#permissionForm').attr('action', $('#permissionForm').attr('action').replace(/\/\d+$/, '') + '/' + roleId);
        
        // Get selected role name
        const roleName = $(this).find('option:selected').text();
        $('#roleName').text(roleName);
        
        // Fetch role permissions
        $.get('/roles/' + roleId, function(role) {
            // Uncheck all permissions first
            $('.permission-check').prop('checked', false);
            
            // Check the permissions this role has
            role.permissions.forEach(permission => {
                $('#perm_' + permission.id).prop('checked', true);
            });
        });
    });
});
</script>
@endpush