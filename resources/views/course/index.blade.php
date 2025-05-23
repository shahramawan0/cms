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
                                <i class="fas fa-book text-white"></i> Courses Management
                            </h3>
                        </div>
                        <div>
                            <button id="addCourseBtn" class="btn btn-secondary btn-sm text-white">
                                <i class="fas fa-plus"></i> Add Course
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Course Form (Initially Hidden) -->
                <div class="card-body" id="courseFormContainer" style="display: none;">
                    <form id="courseForm">
                        @csrf
                        <input type="hidden" id="courseId" name="id">
                        
                        <!-- Institute Information -->
                        <div class="row mb-2">
                            @if(auth()->user()->hasRole('Super Admin'))
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="institute_id">Institute <span class="text-danger">*</span></label>
                                    <select name="institute_id" id="institute_id" class="form-control" required>
                                        <option value="">Select Institute</option>
                                        @foreach($institutes as $institute)
                                            <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="institute_id_error"></div>
                                </div>
                            </div>
                            @else
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Institute</label>
                                    <input type="text" class="form-control" value="{{ auth()->user()->institute->name }}" readonly>
                                    <input type="hidden" name="institute_id" value="{{ auth()->user()->institute_id }}">
                                </div>
                            </div>
                            @endif
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="course_name">Course Name <span class="text-danger">*</span></label>
                                    <input type="text" name="course_name" id="course_name" class="form-control" required>
                                    <div class="invalid-feedback" id="course_name_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="course_code">Course Code</label>
                                    <input type="text" name="course_code" id="course_code" class="form-control">
                                    <div class="invalid-feedback" id="course_code_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="duration_months">Duration (Months)</label>
                                    <input type="number" name="duration_months" id="duration_months" class="form-control" min="1">
                                    <div class="invalid-feedback" id="duration_months_error"></div>
                                </div>
                            </div>
                        </div>  
                        
                        
                        <!-- Marks and Credit Hours -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="total_marks">Total Marks</label>
                                    <input type="number" name="total_marks" id="total_marks" class="form-control" min="0">
                                    <div class="invalid-feedback" id="total_marks_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="credit_hours">Credit Hours</label>
                                    <input type="number" name="credit_hours" id="credit_hours" class="form-control" min="0">
                                    <div class="invalid-feedback" id="credit_hours_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="is_active">Status <span class="text-danger">*</span></label>
                                    <select name="is_active" id="is_active" class="form-control" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback" id="is_active_error"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-sm">
                                    <span id="submitBtnText">Submit</span>
                                    <span id="submitBtnLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                                <button type="button" id="cancelBtn" class="btn btn-secondary btn-sm">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Courses Table -->
                <div class="card-body" style="border-top:1px solid #000">
                    <div class="table-responsive">
                        <table id="courses-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Course Name</th>
                                    <th>Course Code</th>
                                    <th>Institute</th>
                                    <th>Duration</th>
                                    <th>Total Marks</th>
                                    <th>Credit Hours</th>
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
</div>

<!-- Assessment Modal -->
<div class="modal fade" id="assessmentModal" tabindex="-1" aria-labelledby="assessmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="assessmentModalLabel">Course Assessments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assessmentForm">
                    @csrf
                    <input type="hidden" id="assessmentCourseId" name="course_id">
                    
                    <div id="assessmentsContainer">
                        <!-- Assessments will be added here -->
                    </div>
                    
                    <div class="text-end mt-3">
                        <button type="button" id="addAssessmentBtn" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Add Assessment
                        </button>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <strong>Total Weightage: <span id="totalWeightage">0</span>%</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <strong>Total Marks: <span id="totalMarks">0</span></strong>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="saveAssessmentsBtn" class="btn btn-primary">Save Assessments</button>
            </div>
        </div>
    </div>
</div>

<!-- Assessment Item Template (Hidden) -->
<div id="assessmentTemplate" class="d-none">
    <div class="assessment-item card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Type</label>
                        <select name="assessments[0][type]" class="form-control assessment-type">
                            <option value="Assignment">Assignment</option>
                            <option value="Quiz">Quiz</option>
                            <option value="Midterm">Midterm</option>
                            <option value="Final">Final</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="assessments[0][title]" class="form-control assessment-title" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Marks</label>
                        <input type="number" name="assessments[0][marks]" class="form-control assessment-marks" min="1" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Weightage %</label>
                        <input type="number" name="assessments[0][weightage_percent]" class="form-control assessment-weightage" step="0.01" min="0" max="100" required>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-assessment">
                       
                        <i class="fas fa-times-circle"></i>

                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .assessment-item {
        position: relative;
    }
    .remove-assessment {
        margin-bottom: 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // Initialize DataTable
    var table = $('#courses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.courses.data') }}",
        columns: [
            { data: 'course_name', name: 'course_name' },
            { data: 'course_code', name: 'course_code' },
            { data: 'institute', name: 'institute.name' },
            { data: 'duration', name: 'duration_months' },
            { data: 'total_marks', name: 'total_marks' },
            { data: 'credit_hours', name: 'credit_hours' },
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
            }
        }
    });

    // Show/hide form
    $('#addCourseBtn').click(function() {
        $('#courseForm')[0].reset();
        $('#courseId').val('');
        $('#courseFormContainer').show();
        $('html, body').animate({
            scrollTop: $('#courseFormContainer').offset().top
        }, 500);
    });

    $('#cancelBtn').click(function() {
        $('#courseFormContainer').hide();
    });

    // Form submission
    $('#courseForm').submit(function(e) {
        e.preventDefault();
        
        // Show loader
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').addClass('d-none');
        $('#submitBtnLoader').removeClass('d-none');
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        let formData = $(this).serialize();
        let url = "{{ route('admin.courses.store') }}";
        let method = "POST";
        
        // If updating, change URL and method
        if ($('#courseId').val()) {
            url = "{{ url('admin/courses/update') }}/" + $('#courseId').val();
            method = "PUT";
        }
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                // Hide form
                $('#courseFormContainer').hide();
                
                // Show success toast
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                
                // Reload table
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    let errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $('#'+field).addClass('is-invalid');
                        $('#'+field+'_error').text(errors[field][0]);
                    }
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON.message || 'Something went wrong!'
                    });
                }
            },
            complete: function() {
                // Hide loader
                $('#submitBtn').prop('disabled', false);
                $('#submitBtnText').removeClass('d-none');
                $('#submitBtnLoader').addClass('d-none');
            }
        });
    });

    // Edit button click
    $(document).on('click', '.edit-btn', function() {
        let courseId = $(this).data('id');
        
        // Show loader on button
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        $.ajax({
            url: "{{ url('admin/courses/edit') }}/" + courseId,
            type: "GET",
            success: function(response) {
                // Fill form with data
                $('#courseId').val(response.id);
                $('#course_name').val(response.course_name);
                $('#course_code').val(response.course_code);
                $('#duration_months').val(response.duration_months);
                $('#total_marks').val(response.total_marks);
                $('#credit_hours').val(response.credit_hours);
                $('#is_active').val(response.is_active ? '1' : '0');
                $('#description').val(response.description);
                
                // For Super Admin, set institute
                @if(auth()->user()->hasRole('Super Admin'))
                $('#institute_id').val(response.institute_id);
                @endif
                
                // Show form
                $('#courseFormContainer').show();
                $('html, body').animate({
                    scrollTop: $('#courseFormContainer').offset().top
                }, 500);
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to load course data!'
                });
            },
            complete: function() {
                // Reset button text
                $('.edit-btn').html('<i class="fas fa-edit"></i> Edit');
            }
        });
    });

    // Delete button click
    $(document).on('click', '.delete-btn', function() {
        let courseId = $(this).data('id');
        
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
                // Show loader on button
                $(this).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                
                $.ajax({
                    url: "{{ url('admin/courses/delete') }}/" + courseId,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Something went wrong while deleting.'
                        });
                    },
                    complete: function() {
                        // Reset button text
                        $('.delete-btn').html('<i class="fas fa-trash"></i> Delete');
                    }
                });
            }
        });
    });

    // Assessment button click - shows different text based on whether assessments exist
    $(document).on('click', '.assessment-btn', function() {
        let courseId = $(this).data('id');
        $('#assessmentCourseId').val(courseId);
        
        // Load existing assessments
        $.ajax({
            url: "{{ url('admin/courses') }}/" + courseId + "/assessments",
            type: "GET",
            success: function(response) {
                $('#assessmentsContainer').empty();
                
                if (response.length > 0) {
                    // Add existing assessments
                    response.forEach(function(assessment, index) {
                        addAssessmentItem(index, assessment);
                    });
                } else {
                    // Add one empty assessment by default
                    addAssessmentItem(0);
                }
                
                calculateTotals();
                $('#assessmentModal').modal('show');
            },
            error: function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to load assessments'
                });
            }
        });
    });

    // Add new assessment item
    $('#addAssessmentBtn').click(function() {
        let index = $('.assessment-item').length;
        addAssessmentItem(index);
    });

    // Remove assessment item
    $(document).on('click', '.remove-assessment', function() {
        $(this).closest('.assessment-item').remove();
        reindexAssessmentItems();
        calculateTotals();
    });

    // Recalculate totals when values change
    $(document).on('input', '.assessment-marks, .assessment-weightage', function() {
        calculateTotals();
    });

    // Save assessments
    $('#saveAssessmentsBtn').click(function() {
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: "{{ route('admin.courses.assessments.store') }}",
            type: "POST",
            data: $('#assessmentForm').serialize(),
            success: function(response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                    $('#assessmentModal').modal('hide');
                    table.ajax.reload(null, false); // Reload to update button text
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message
                    });
                }
            },
            error: function(xhr) {
                let message = 'Error saving assessments';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Toast.fire({
                    icon: 'error',
                    title: message
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('Save Assessments');
            }
        });
    });

    // Helper function to add assessment item
    function addAssessmentItem(index, assessment = null) {
        let template = $('#assessmentTemplate').html();
        template = template.replace(/\[0\]/g, '[' + index + ']');
        
        let $item = $(template);
        if (assessment) {
            $item.find('.assessment-type').val(assessment.type);
            $item.find('.assessment-title').val(assessment.title);
            $item.find('.assessment-marks').val(assessment.marks);
            $item.find('.assessment-weightage').val(assessment.weightage_percent);
        }
        
        $('#assessmentsContainer').append($item);
    }

    // Helper function to reindex assessment items
    function reindexAssessmentItems() {
        $('.assessment-item').each(function(index) {
            $(this).find('select, input').each(function() {
                let name = $(this).attr('name');
                name = name.replace(/\[\d+\]/, '[' + index + ']');
                $(this).attr('name', name);
            });
        });
    }

    // Helper function to calculate totals
    function calculateTotals() {
        let totalWeightage = 0;
        let totalMarks = 0;
        
        $('.assessment-item').each(function() {
            let weightage = parseFloat($(this).find('.assessment-weightage').val()) || 0;
            let marks = parseInt($(this).find('.assessment-marks').val()) || 0;
            
            totalWeightage += weightage;
            totalMarks += marks;
        });
        
        $('#totalWeightage').text(totalWeightage.toFixed(2));
        $('#totalMarks').text(totalMarks);
    }
});
</script>
@endpush