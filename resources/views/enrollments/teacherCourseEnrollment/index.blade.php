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
                                <i class="fas fa-user-graduate text-white"></i> Teacher Course Enrollments
                            </h3>
                        </div>
                        <div>
                            <button id="addEnrollmentBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Enroll Course
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Enrollment Form (Initially Hidden) -->
                <div class="card-body" id="enrollmentFormContainer" style="display: none;">
                    <form id="enrollmentForm">
                        @csrf
                        <input type="hidden" name="id" id="enrollment_id">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="student_id">Student <span class="text-danger">*</span></label>
                                    <select name="student_id" id="student_id" class="form-control" required>
                                        <option value="">Select Student</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" id="submitBtn" class="btn btn-primary">
                                    <span id="submitBtnText">Submit</span>
                                </button>
                                <button type="button" id="cancelBtn" class="btn btn-secondary">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Enrollments Table -->
                <div class="card-body">
                    <table id="enrollments-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Student</th>
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
    // Show/hide form
    $('#addEnrollmentBtn').click(function() {
        $('#enrollmentForm')[0].reset();
        $('#enrollmentFormContainer').show();
        $('#enrollment_id').val('');
        $('#submitBtnText').text('Submit');
    });

    $('#cancelBtn').click(function() {
        $('#enrollmentFormContainer').hide();
    });
});
</script>
@endpush