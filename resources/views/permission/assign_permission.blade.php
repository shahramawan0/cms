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
                            <div class="d-flex align-items-center mb-3">
                                <h4 class="mb-0 mr-2">Permissions for:</h4>
                                <div class="d-flex align-items-center">
                                    <span id="roleName" class="text-primary font-weight-bold" style="margin-right: 5px">Check All</span>
                                    <input class="form-check-input" type="checkbox" id="selectAllPermissions">
                                </div>
                            </div>
                            
                            <!-- FINAL PERFECTED 3-COLUMN LAYOUT -->
                            <div class="row">
                                @foreach($modules as $module)
                                <div class="col-md-4 mb-3">
                                    <div class="d-flex align-items-center">
                                        <!-- Module Name -->
                                        <span class="font-weight-bold text-capitalize mr-2" style="font-size:1rem; min-width: 110px;">
                                            {{ ucfirst($module['name']) }}
                                        </span>
                                        
                                        <!-- Actions in ONE LINE with PERFECT SPACING -->
                                        <div class="d-flex">
                                            @foreach(['view', 'edit', 'delete'] as $action)
                                                @if(isset($module['permissions'][$action]))
                                                <div class="d-flex align-items-center" style="margin-right: 12px;">
                                                    <label class="switch mr-1 mb-0">
                                                        <input type="checkbox" 
                                                               class="permission-toggle" 
                                                               name="permissions[]"
                                                               value="{{ $module['permissions'][$action]->id }}"
                                                               id="perm_{{ $module['permissions'][$action]->id }}">
                                                        <span class="slider round"></span>
                                                    </label>
                                                    <span class="text-capitalize">{{ $action }}</span>
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

@push('styles')
<style>
    /* Toggle Switch - Compact Style */
    .switch {
        position: relative;
        display: inline-block;
        width: 32px;
        height: 16px;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 16px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 12px;
        width: 12px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: #28a745;
    }
    input:checked + .slider:before {
        transform: translateX(16px);
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('#roleSelect').change(function() {
        const roleId = $(this).val();
        if (!roleId) return;
        
        $.get('/roles/' + roleId, function(role) {
            $('.permission-toggle').prop('checked', false);
            role.permissions.forEach(permission => {
                $('#perm_' + permission.id).prop('checked', true);
            });
            // Uncheck "Select All" when changing roles
            $('#selectAllPermissions').prop('checked', false);
        });
    });

    // Select All functionality
    $('#selectAllPermissions').change(function() {
        $('.permission-toggle').prop('checked', $(this).prop('checked'));
    });

    // Uncheck "Select All" if any permission is unchecked
    $(document).on('change', '.permission-toggle', function() {
        if(!$(this).prop('checked')) {
            $('#selectAllPermissions').prop('checked', false);
        }
        // Check if all permissions are now checked
        if($('.permission-toggle:not(:checked)').length === 0) {
            $('#selectAllPermissions').prop('checked', true);
        }
    });
});
</script>
@endpush