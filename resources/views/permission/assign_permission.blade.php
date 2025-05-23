@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title text-white">
                        <i class="fas fa-key text-white"></i> 
                        @if(isset($selectedRole)) Update @else Assign @endif Permissions
                    </h3>
                </div>
                <div class="card-body">
                    <form id="assignPermissionForm">
                        @csrf
                        @if(isset($selectedRole))
                            @method('PUT')
                        @endif
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="font-weight-bold">Select Role</label>
                                <select name="role_id" id="roleSelect" class="form-control" required
                                    @if(isset($selectedRole)) disabled @endif>
                                    <option value="">-- Select Role --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" 
                                            @if(isset($selectedRole) && $selectedRole->id == $role->id) selected @endif>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="permissionSection">
                            <div class="d-flex align-items-center mb-3">
                                <h4 class="mb-0 mr-2">@if(isset($selectedRole)) Update @else Add @endif Permissions:</h4>
                                <div class="d-flex align-items-center checkbox-success">
                                    <span id="roleName" class="text-primary font-weight-bold" style="margin-right: 5px">Check All</span>
                                    <input class="form-check-input" type="checkbox" id="selectAllPermissions">
                                </div>
                            </div>
                            
                            <div class="row">
                                @foreach($modules as $module)
                                <div class="col-md-12 mb-3">
                                    <div class="d-flex align-items-center">
                                        <span class="font-weight-bold text-capitalize mr-2" style="font-size:1rem; min-width: 110px;">
                                            {{ ucfirst($module['name']) }}
                                        </span>
                                        <div class="d-flex flex-wrap">
                                            @foreach($module['permissions'] as $action => $permission)
                                                <div class="d-flex align-items-center me-3 mb-2">
                                                    <label class="switch me-2 mb-0">
                                                        <input type="checkbox" 
                                                            class="form-check-input permission-checkbox" 
                                                            name="permissions[]"
                                                            value="{{ $permission->id }}"
                                                            id="perm_{{ $permission->id }}"
                                                            @if(isset($selectedPermissions) && in_array($permission->id, $selectedPermissions)) checked @endif>
                                                        <span class="slider round"></span>
                                                    </label>
                                                    <span class="text-capitalize">{{ $action }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-save"></i> @if(isset($selectedRole)) Update @else Add @endif Permissions
                                </button>
                            </div>
                            <div class="alert alert-success alert-dismissible fade show my-3" id="successMessage" style="display: none;">
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close">
                                    <span><i class="fa-solid fa-xmark"></i></span>
                                </button>
                                <strong>Success!</strong> <span id="successText"></span>
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
    // Check if we're editing (role in URL)
    const urlParams = new URLSearchParams(window.location.search);
    const roleId = urlParams.get('role');
    const isEditMode = roleId !== null;
    
    // When "Select All" checkbox is clicked
    $('#selectAllPermissions').change(function() {
        $('.permission-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Adjust "Select All" checkbox based on individual checkboxes
    $(document).on('change', '.permission-checkbox', function() {
        if(!$(this).prop('checked')) {
            $('#selectAllPermissions').prop('checked', false);
        }
        if($('.permission-checkbox:not(:checked)').length === 0) {
            $('#selectAllPermissions').prop('checked', true);
        }
    });

    // Form submission to save/update permissions
    $('#assignPermissionForm').submit(function(e) {
        e.preventDefault();

        const formData = $(this).serialize();
        const url = isEditMode 
            ? "/permissions/update/" + roleId 
            : "{{ route('permissions.assign') }}";
        const method = isEditMode ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                $('#successText').text(response.message);
                $('#successMessage').fadeIn().delay(3000).fadeOut();
                
                // In add mode, keep the checkboxes checked
                if (!isEditMode) {
                    $('.permission-checkbox:checked').prop('checked', true);
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.message);
            }
        });
    });
});
</script>
@endpush