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
                                <i class="fas fa-calendar-alt text-white"></i> Time Table Management
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card-body border-bottom">
                    <form id="filterForm">
                        @csrf
                        <input type="hidden" name="institute_id" id="institute_id" value="{{ auth()->user()->institute_id ?? '' }}">
                        
                        <!-- Primary Selection Row -->
                        <div class="row mb-4">
                            @if(auth()->user()->hasRole('Super Admin'))
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="institute_id">Institute <span class="text-danger">*</span></label>
                                    <select name="institute_id" id="institute_idd" class="form-control" required>
                                        <option value="">Select Institute</option>
                                        @foreach($institutes as $institute)
                                            <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="institute_id_error"></div>
                                </div>
                            </div>
                            @endif
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="session_id">Academic Session <span class="text-danger">*</span></label>
                                    <select name="session_id" id="session_id" class="form-control" required>
                                        <option value="">Select Session</option>
                                    </select>
                                    <div class="invalid-feedback" id="session_id_error"></div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" id="date" class="form-control" required>
                                    <div class="invalid-feedback" id="date_error"></div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="week_number">Week Number <span class="text-danger">*</span></label>
                                    <select name="week_number" id="week_number" class="form-control" required>
                                        <option value="">Select Week</option>
                                        <option value="1">Week 1</option>
                                        <option value="2">Week 2</option>
                                        <option value="3">Week 3</option>
                                        <option value="4">Week 4</option>
                                    </select>
                                    <div class="invalid-feedback" id="week_number_error"></div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="time_slot_id">Time Slot Template <span class="text-danger">*</span></label>
                                    <select name="time_slot_id" id="time_slot_id" class="form-control" required>
                                        <option value="">Select Time Slot</option>
                                    </select>
                                    <div class="invalid-feedback" id="time_slot_id_error"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden Fields for Card Selection -->
                        <input type="hidden" name="class_id" id="class_id">
                        <input type="hidden" name="section_id" id="section_id">
                        <input type="hidden" name="course_id" id="course_id">
                        @if(!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('Admin'))
                            <input type="hidden" name="teacher_id" id="teacher_id" value="{{ auth()->user()->id }}">
                        @else
                            <input type="hidden" name="teacher_id" id="teacher_id">
                        @endif

                        <!-- Course Cards Section -->
                        <div>
                            <h5 class="mb-3"><i class="fas fa-book"></i> Available Courses</h5>
                            <div id="courseListContainer">
                                <div class="row" id="courseListGroup">
                                    <!-- Course cards will be loaded here -->
                                </div>
                            </div>
                        </div>

                        <!-- Slot Selection Section -->
                        <div class="mt-4">
                            <div class="d-flex align-items-center mb-3">
                                <button type="button" id="checkAvailabilityBtn" class="btn btn-info btn-sm me-2" disabled>
                                    <i class="fas fa-check"></i> Check Availability
                                </button>
                                <button type="button" id="addSlotBtn" class="btn btn-primary btn-sm" disabled>
                                    <i class="fas fa-plus"></i> Add Slot
                                </button>
                            </div>
                            <div id="slotContainer">
                                <!-- Time slots will be displayed here dynamically -->
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Time Table Display -->
                <div class="card-body">
                    <!-- Header section with View Week on left and buttons on right -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <label for="view_week" class="col-form-label mr-2">Select Week:</label>
                            <select name="view_week" id="view_week" class="form-control form-control-sm d-inline-block">
                                <option value="1">Week 1</option>
                                <option value="2">Week 2</option>
                                <option value="3">Week 3</option>
                                <option value="4">Week 4</option>
                            </select>
                        </div>
                        <div>
                            <button type="button" id="loadTimetableBtn" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-sync"></i> Load Timetable
                            </button>
                            <a href="{{ route('time-table.report') }}" class="btn btn-info btn-sm">
                                <i class="fas fa-file-export"></i> Generate Report
                            </a>
                        </div>
                    </div>
                
                    <div class="table-responsive">
                        <table class="table table-bordered" id="timeTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <!-- Slots will be loaded dynamically -->
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Table body will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.course-item {
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    background-color: #fff;
    height: 100%;
}

.course-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    background-color: rgba(23, 162, 184, 0.1);
    border-color: #17a2b8;
}

.course-item.active {
    background-color: rgba(23, 162, 184, 0.1);
    border-color: #17a2b8;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.course-item h6 {
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: bold;
}

.course-item .badge {
    font-size: 0.8rem;
    padding: 5px 10px;
    background-color: #17a2b8;
}

.course-item small {
    color: #6c757d;
}

.course-item.active small,
.course-item.active .text-muted {
    color: inherit !important;
}

.list-group-item-action:focus, 
.list-group-item-action:hover {
    background-color: rgba(23, 162, 184, 0.1);
}
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize SweetAlert
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // Initialize dropdowns
    $('.select2').select2({
        width: '100%',
        allowClear: true
    });
    
    // For Admin - load data immediately on page load
    @if(!auth()->user()->hasRole('Super Admin'))
    loadInitialDataForAdmin();
    @endif
    
    // For Super Admin - load dropdowns when institute is selected
    @if(auth()->user()->hasRole('Super Admin'))
    $('#institute_idd').change(function() {
        var instituteId = $(this).val();
        if (instituteId) {
            loadSessionsAndSelectCurrent(instituteId);
            clearCourseList();
            $('#class_id, #section_id, #course_id, #teacher_id').val('');
        } else {
            clearAllDropdowns();
        }
    });
    @endif
    
    // When session changes, load courses and time slot templates
    $('#session_id').change(function() {
        var sessionId = $(this).val();
        var instituteId = $('#institute_id').val();
        
        console.log('Session changed:', {sessionId, instituteId});

        if (sessionId && instituteId) {
            loadCourseList(instituteId, sessionId);
            loadTimeSlotTemplates(sessionId);
        } else {
            clearCourseList();
            $('#time_slot_id').empty().append('<option value="">Select Time Slot</option>');
        }
    });
    
    // When week number changes, load time slot templates
    $('#week_number').change(function() {
        var weekNumber = $(this).val();
        var sessionId = $('#session_id').val();
        
        if (weekNumber && sessionId) {
            loadTimeSlotTemplates(sessionId, weekNumber);
        }
    });
    
    // When time slot template changes, calculate slots
    $('#time_slot_id').change(function() {
        var templateId = $(this).val();
        if (templateId) {
            calculateTimeSlots(templateId);
        } else {
            $('#slotContainer').html('');
        }
    });
    
    // Check availability button click
    $('#checkAvailabilityBtn').click(function() {
        checkAvailability();
    });
    
    // Add slot button click
    $('#addSlotBtn').click(function() {
        addTimeTableEntry();
    });
    
    // Load timetable button click
    $('#loadTimetableBtn').click(function() {
        loadTimetable();
    });

    // Handle course card click
    $(document).on('click', '.course-item', function() {
        // Remove active class from all cards
        $('.course-item').removeClass('active');
        // Add active class to clicked card
        $(this).addClass('active');

        // Set hidden field values
        $('#class_id').val($(this).data('class-id'));
        $('#section_id').val($(this).data('section-id'));
        $('#course_id').val($(this).data('course-id'));
        $('#teacher_id').val($(this).data('teacher-id'));

        // Enable check availability button if all required fields are filled
        checkRequiredFields();
    });

    // Function to check if all required fields are filled
    function checkRequiredFields() {
        var requiredFields = [
            'institute_id',
            'session_id',
            'class_id',
            'section_id',
            'course_id',
            'teacher_id',
            'date',
            'week_number',
            'time_slot_id'
        ];

        var allFilled = requiredFields.every(function(field) {
            return $('#' + field).val();
        });

        $('#checkAvailabilityBtn').prop('disabled', !allFilled);
    }

    // Add change event listeners to all required fields
    $('select, input').on('change', function() {
        checkRequiredFields();
    });

    // Function to load sessions and select current one
    function loadSessionsAndSelectCurrent(instituteId) {
        $('#courseListGroup').empty();
        $('#resultsContainer').hide();
        
        $.ajax({
            url: "{{ route('time-table.dropdowns') }}",
            type: "GET",
            data: { institute_id: instituteId },
            beforeSend: function() {
                $('#session_id').html('<option value="">Loading sessions...</option>');
            },
            success: function(data) {
                $('#session_id').empty().append('<option value="">Select Session</option>');
                
                if(data.sessions && data.sessions.length > 0) {
                    let currentSessionId = null;
                    const now = new Date();
                    
                    data.sessions.forEach(function(session) {
                        const startDate = session.start_date ? new Date(session.start_date) : null;
                        const endDate = session.end_date ? new Date(session.end_date) : null;
                        const isCurrent = startDate && endDate && now >= startDate && now <= endDate;
                        
                        if (isCurrent) {
                            currentSessionId = session.id;
                        }
                        
                        $('#session_id').append(`
                            <option value="${session.id}" ${isCurrent ? 'selected' : ''}>
                                ${session.name}
                                ${isCurrent ? ' (Current)' : ''}
                            </option>
                        `);
                    });
                    
                    if (currentSessionId) {
                        $('#session_id').val(currentSessionId).trigger('change');
                        Toast.fire({
                            icon: 'success',
                            title: 'Current session loaded successfully'
                        });
                    } else {
                        Toast.fire({
                            icon: 'info',
                            title: 'No current session found'
                        });
                    }
                } else {
                    Toast.fire({
                        icon: 'warning',
                        title: 'No sessions available'
                    });
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to load sessions'
                });
                $('#session_id').html('<option value="">Select Session</option>');
            }
        });
    }

    // Function to load course list
    function loadCourseList(instituteId, sessionId) {
        console.log('Loading course list:', {instituteId, sessionId});
        $.ajax({
            url: "{{ route('time-table.dropdowns') }}",
            type: "GET",
            data: {
                institute_id: instituteId,
                session_id: sessionId,
                include_all: true
            },
            success: function(data) {
                console.log('Course list response:', data);
                $('#courseListGroup').empty();

                if (!data.classes || data.classes.length === 0) {
                    $('#courseListGroup').html('<div class="col-12"><div class="alert alert-info">No courses available for this session.</div></div>');
                    return;
                }

                // Add CSS for dynamic hover and active effects
                let styleElement = document.getElementById('hover-styles');
                if (!styleElement) {
                    styleElement = document.createElement('style');
                    styleElement.id = 'hover-styles';
                    document.head.appendChild(styleElement);
                }
                
                let styles = '';
                data.classes.forEach((classData) => {
                    const bgColor = classData.background_color || '#17a2b8';
                    styles += `
                        .course-item[data-class-id="${classData.id}"]:hover {
                            background-color: ${bgColor}20 !important;
                            border-color: ${bgColor} !important;
                        }
                        .course-item[data-class-id="${classData.id}"].active {
                            background-color: ${bgColor}20 !important;
                            border-color: ${bgColor} !important;
                            color: inherit !important;
                        }
                        .course-item[data-class-id="${classData.id}"].active small,
                        .course-item[data-class-id="${classData.id}"].active .text-muted {
                            color: inherit !important;
                        }
                    `;
                });
                styleElement.textContent = styles;

                data.classes.forEach(function(classData) {
                    // Get sections for this class
                    var sections = data.sections ? data.sections.filter(function(section) {
                        return section.class_id === classData.id;
                    }) : [];

                    sections.forEach(function(section) {
                        // Get courses for this section
                        var courses = data.courses ? data.courses.filter(function(course) {
                            return course.class_id === classData.id && course.section_id === section.id;
                        }) : [];

                        courses.forEach(function(course) {
                            // Get teacher for this course
                            var teacher = data.teachers ? data.teachers.find(function(teacher) {
                                return teacher.id === course.teacher_id;
                            }) : null;

                            if (teacher) {
                                const bgColor = classData.background_color || '#17a2b8';
                                var card = `
                                    <div class="col-md-4 col-lg-3 mb-3">
                                        <div class="list-group-item list-group-item-action course-item" 
                                            data-class-id="${classData.id}"
                                            data-section-id="${section.id}"
                                            data-course-id="${course.id}"
                                            data-teacher-id="${teacher.id}">
                                            <div class="d-flex w-100 justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-2 fw-bold">${course.course_name || course.name}</h6>
                                                    <small class="text-muted">
                                                        <i class="fas fa-chalkboard-teacher"></i> ${teacher.name}
                                                    </small>
                                                </div>
                                                <div>
                                                    <span class="badge" style="background-color: ${bgColor}">
                                                        ${classData.name} - ${section.section_name || section.name}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                $('#courseListGroup').append(card);
                            }
                        });
                    });
                });

                if ($('#courseListGroup').children().length === 0) {
                    $('#courseListGroup').html('<div class="col-12"><div class="alert alert-info">No courses available for this session.</div></div>');
                }
            },
            error: function(xhr) {
                console.error('Error loading courses:', xhr);
                $('#courseListGroup').html('<div class="col-12"><div class="alert alert-danger">Error loading courses. Please try again.</div></div>');
                Toast.fire({
                    icon: 'error',
                    title: 'Error loading courses'
                });
            }
        });
    }

    // Function to clear course list
    function clearCourseList() {
        $('#courseListGroup').empty();
        $('.course-item').removeClass('active');
        $('#checkAvailabilityBtn').prop('disabled', true);
        $('#addSlotBtn').prop('disabled', true);
    }

    // Function to clear all dropdowns
    function clearAllDropdowns() {
        $('#session_id').empty().append('<option value="">Select Session</option>');
        $('#time_slot_id').empty().append('<option value="">Select Time Slot</option>');
        clearCourseList();
    }

    // Function to load initial data for admin
    function loadInitialDataForAdmin() {
        var instituteId = $('#institute_id').val();
        if (instituteId) {
            loadSessionsAndSelectCurrent(instituteId);
        }
    }

    // Function to load time slot templates
    function loadTimeSlotTemplates(sessionId, weekNumber = null) {
        $.ajax({
            url: "{{ route('time-table.time-slot-templates') }}",
            type: "GET",
            data: { 
                session_id: sessionId,
                week_number: weekNumber
            },
            success: function(data) {
                $('#time_slot_id').empty().append('<option value="">Select Time Slot</option>');
                if(data.templates && data.templates.length > 0) {
                    $.each(data.templates, function(key, value) {
                        $('#time_slot_id').append(`<option value="${value.id}">${value.text}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Error loading time slot templates:', xhr.responseText);
                Toast.fire({
                    icon: 'error',
                    title: 'Error loading time slot templates'
                });
            }
        });
    }
    
    // Function to calculate time slots
    function calculateTimeSlots(templateId) {
        $.ajax({
            url: "{{ route('time-table.calculate-slots') }}",
            type: "GET",
            data: { time_slot_id: templateId },
            success: function(data) {
                displayTimeSlots(data.slots);
            },
            error: function(xhr) {
                console.error('Error calculating time slots:', xhr.responseText);
                $('#slotContainer').html('<div class="alert alert-danger">Error calculating time slots</div>');
                Toast.fire({
                    icon: 'error',
                    title: 'Error calculating time slots'
                });
            }
        });
    }
    
    // Function to display time slots
    function displayTimeSlots(slots) {
        var container = $('<div class="slot-container"></div>');
        var header = $('<h5 class="mb-3">Available Slots</h5>');
        var slotList = $('<div class="row slot-list"></div>');
        
        $.each(slots, function(index, slot) {
            var slotCol = $('<div class="col-md-3 mb-2"></div>');
            var slotDiv = $('<div class="form-check"></div>');
            var input = $(`<input type="checkbox" class="form-check-input slot-checkbox" 
                            id="slot_${index}" value="${slot.formatted}">`);
            var label = $(`<label class="form-check-label" for="slot_${index}">${slot.formatted}</label>`);
            
            slotDiv.append(input).append(label);
            slotCol.append(slotDiv);
            slotList.append(slotCol);
        });
        
        container.append(header).append(slotList);
        
        // Add preview container
        var previewContainer = $(`
            <div class="preview-container mt-3">
                <h5>Selected Slots Preview</h5>
                <div class="selected-slots-preview alert alert-info">
                    No slots selected
                </div>
                <input type="hidden" name="selected_slots" id="selected_slots">
                <input type="hidden" name="total_slots" id="total_slots">
            </div>
        `);
        
        container.append(previewContainer);
        
        $('#slotContainer').html(container);
        
        // Handle checkbox changes
        $('.slot-checkbox').change(function() {
            updateSelectedSlotsPreview();
        });
    }
    
    // Function to update selected slots preview
    function updateSelectedSlotsPreview() {
        var selectedSlots = [];
        $('.slot-checkbox:checked').each(function() {
            selectedSlots.push($(this).val());
        });
        
        var previewText = selectedSlots.length > 0 
            ? selectedSlots.join(', ') + ` (${selectedSlots.length} slots selected)`
            : 'No slots selected';
            
        $('.selected-slots-preview').text(previewText);
        $('#selected_slots').val(selectedSlots.join(','));
        $('#total_slots').val(selectedSlots.length);
        
        // Enable/disable add button based on selection
        $('#addSlotBtn').prop('disabled', selectedSlots.length === 0);
    }
    
    // Function to check availability
    function checkAvailability() {
        var instituteId = $('#institute_id').val();
        var sessionId = $('#session_id').val();
        var classId = $('#class_id').val();
        var sectionId = $('#section_id').val();
        var courseId = $('#course_id').val();
        var teacherId = $('#teacher_id').val();
        var date = $('#date').val();
        var weekNumber = $('#week_number').val();
        var timeSlotId = $('#time_slot_id').val();
        
        if (!instituteId || !sessionId || !classId || !sectionId || !date || !weekNumber || !timeSlotId) {
            Toast.fire({
                icon: 'error',
                title: 'Please fill all required fields'
            });
            return;
        }
        
        $.ajax({
            url: "{{ route('time-table.check-availability') }}",
            type: "POST",
            data: {
                institute_id: instituteId,
                session_id: sessionId,
                class_id: classId,
                section_id: sectionId,
                course_id: courseId,
                teacher_id: teacherId,
                date: date,
                week_number: weekNumber,
                time_slot_id: timeSlotId,
                _token: "{{ csrf_token() }}"
            },
            beforeSend: function() {
                $('#checkAvailabilityBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Checking...');
            },
            complete: function() {
                $('#checkAvailabilityBtn').prop('disabled', false).html('<i class="fas fa-check"></i> Check Availability');
            },
            success: function(response) {
                displayAvailabilityResults(response.slots, response.template);
            },
            error: function(xhr) {
                console.error('Error checking availability:', xhr.responseText);
                Toast.fire({
                    icon: 'error',
                    title: 'Error checking availability'
                });
            }
        });
    }
    
    // Function to display availability results
    function displayAvailabilityResults(slots, template) {
        var container = $('<div class="availability-container"></div>');
        var header = $('<h5 class="mb-3">Availability Results</h5>');
        var slotList = $('<div class="row slot-list"></div>');
        
        $.each(slots, function(index, slot) {
            var slotCol = $('<div class="col-md-3 mb-2"></div>');
            var slotDiv = $('<div class="form-check"></div>');
            
            var isDisabled = slot.isOccupied;
            var input = $(`<input type="checkbox" class="form-check-input slot-checkbox" 
                            id="slot_${index}" value="${slot.formatted}" ${isDisabled ? 'disabled' : ''}>`);
            
            var label = $(`<label class="form-check-label" for="slot_${index}">${slot.formatted}</label>`);
            
            if (slot.isOccupied) {
                var occupiedInfo = $('<div class="occupied-info small text-danger mt-1"></div>');
                occupiedInfo.html(`Occupied by: ${slot.occupiedBy.course} (${slot.occupiedBy.teacher})`);
                slotDiv.append(input).append(label).append(occupiedInfo);
            } else {
                slotDiv.append(input).append(label);
            }
            
            slotCol.append(slotDiv);
            slotList.append(slotCol);
        });
        
        container.append(header).append(slotList);
        
        // Add preview container
        var previewContainer = $(`
            <div class="preview-container mt-3">
                <h5>Selected Slots Preview</h5>
                <div class="selected-slots-preview alert alert-info">
                    No slots selected
                </div>
                <input type="hidden" name="selected_slots" id="selected_slots">
                <input type="hidden" name="total_slots" id="total_slots">
            </div>
        `);
        
        container.append(previewContainer);
        
        $('#slotContainer').html(container);
        
        // Handle checkbox changes
        $('.slot-checkbox').change(function() {
            updateSelectedSlotsPreview();
        });
    }
    
    // Function to add timetable entry
    function addTimeTableEntry() {
        var selectedSlots = $('#selected_slots').val();
        var totalSlots = $('#total_slots').val();
        var date = $('#date').val();
        var instituteId = $('#institute_id').val();
        var sessionId = $('#session_id').val();
        var classId = $('#class_id').val();
        var sectionId = $('#section_id').val();
        
        if (!selectedSlots || !totalSlots) {
            Toast.fire({
                icon: 'error',
                title: 'Please select at least one time slot'
            });
            return;
        }

        // First check if slots are already taken for this date
        $.ajax({
            url: "{{ route('time-table.check-availability') }}",
            type: "POST",
            data: {
                institute_id: instituteId,
                session_id: sessionId,
                class_id: classId,
                section_id: sectionId,
                date: date,
                week_number: $('#week_number').val(),
                time_slot_id: $('#time_slot_id').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                var selectedSlotsArray = selectedSlots.split(',');
                var hasConflict = false;
                var conflictingSlots = [];

                // Check each selected slot against occupied slots
                selectedSlotsArray.forEach(function(selectedSlot) {
                    response.slots.forEach(function(slot) {
                        if (slot.formatted === selectedSlot.trim() && slot.isOccupied) {
                            hasConflict = true;
                            conflictingSlots.push({
                                slot: slot.formatted,
                                course: slot.occupiedBy.course,
                                teacher: slot.occupiedBy.teacher
                            });
                        }
                    });
                });

                if (hasConflict) {
                    let conflictMessage = 'The following slots are already occupied:<br><br>';
                    conflictingSlots.forEach(function(conflict) {
                        conflictMessage += `Slot ${conflict.slot} is occupied by ${conflict.course} (${conflict.teacher})<br>`;
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Time Slot Conflict',
                        html: conflictMessage,
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // If no conflicts, proceed with adding the time table entry
                proceedWithTimeTableAdd();
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Error checking slot availability'
                });
            }
        });
    }

    // Function to proceed with adding time table entry after validation
    function proceedWithTimeTableAdd() {
        var formData = {
            institute_id: $('#institute_id').val(),
            session_id: $('#session_id').val(),
            class_id: $('#class_id').val(),
            section_id: $('#section_id').val(),
            course_id: $('#course_id').val(),
            teacher_id: $('#teacher_id').val(),
            date: $('#date').val(),
            week_number: $('#week_number').val(),
            time_slot_id: $('#time_slot_id').val(),
            slot_times: $('#selected_slots').val(),
            total_slots: $('#total_slots').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        // Submit the form data to the server
        $.ajax({
            url: "{{ route('time-table.store') }}",
            type: "POST",
            data: formData,
            beforeSend: function() {
                $('#addSlotBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');
            },
            complete: function() {
                $('#addSlotBtn').prop('disabled', false).html('<i class="fas fa-plus"></i> Add Slot');
            },
            success: function(response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        title: 'Time table entry added successfully'
                    });
                    // Refresh the timetable
                    loadTimetable();
                    // Clear the form
                    $('#slotContainer').html('');
                    $('#selected_slots').val('');
                    $('#total_slots').val('');
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Error adding time table entry'
                    });
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    $.each(errors, function(key, value) {
                        $(`#${key}`).addClass('is-invalid');
                        $(`#${key}_error`).text(value[0]);
                    });
                    
                    Toast.fire({
                        icon: 'error',
                        title: 'Please fix the errors in the form'
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'An error occurred while adding the time table entry'
                    });
                }
            }
        });
    }
    
    // Function to load timetable
    function loadTimetable() {
        var instituteId = $('#institute_id').val();
        var sessionId = $('#session_id').val();
        var classId = $('#class_id').val();
        var sectionId = $('#section_id').val();
        var weekNumber = $('#view_week').val();

        if (!instituteId || !sessionId || !classId || !sectionId || !weekNumber) {
            Toast.fire({
                icon: 'error',
                title: 'Please select institute, session, class, section and week number'
            });
            return;
        }

        $.ajax({
            url: "{{ route('time-table.load-timetable') }}",
            type: "GET",
            data: {
                institute_id: instituteId,
                session_id: sessionId,
                class_id: classId,
                section_id: sectionId,
                week_number: weekNumber
            },
            success: function (response) {
                displayTimetable(response);
            },
            error: function (xhr) {
                console.error('Error loading timetable:', xhr.responseText);
                Toast.fire({
                    icon: 'error',
                    title: 'Error loading timetable'
                });
            }
        });
    }

    // Function to display timetable
    function displayTimetable(data) {
        var $table = $('#timeTable');
        var $thead = $table.find('thead');
        var $tbody = $table.find('tbody');

        $thead.empty();
        $tbody.empty();

        // Create header row with compact styling
        var $headerRow = $('<tr class="bg-light"></tr>');
        $headerRow.append('<th class="align-middle" style="min-width: 100px;">Days</th>');

        if (data.slots && data.slots.length > 0) {
            data.slots.forEach(function(slot) {
                $headerRow.append(`<th class="text-center align-middle" style="min-width: 150px;">${slot}</th>`);
            });
        }

        // Add actions column
        $headerRow.append('<th class="text-center" style="min-width: 100px;">Actions</th>');

        $thead.append($headerRow);

        // Create table body with compact styling
        if (data.dates && data.dates.length > 0) {
            data.dates.forEach(function(dateInfo) {
                var $row = $('<tr></tr>');
                $row.append(`
                    <td class="font-weight-bold" style="min-width: 100px;">
                        <small>${dateInfo.date}<br>
                        <span class="text-muted">${dateInfo.day}</span></small>
                    </td>
                `);

                var entryIds = new Set(); // Track unique entry IDs for this row

                if (data.slots && data.slots.length > 0) {
                    data.slots.forEach(function(slot) {
                        var matchingEntries = data.entries.filter(function(entry) {
                            return entry.date === dateInfo.date && entry.slot_time === slot;
                        });

                        var $cell = $('<td class="p-0" style="min-width: 150px;"></td>');

                        if (matchingEntries.length > 0) {
                            matchingEntries.forEach(function(entry) {
                                entryIds.add(entry.id); // Add entry ID to set

                                // Create a compact styled container for each entry
                                var $entryDiv = $('<div></div>')
                                    .addClass('p-1 h-100 border-left')
                                    .css({
                                        'background-color': entry.background_color + '15',
                                        'border-left': '3px solid ' + entry.background_color
                                    });

                                // Add course name with icon
                                $entryDiv.append(`
                                    <div class="font-weight-bold" style="font-size: 0.85rem;">
                                        <i class="fas fa-book-open fa-sm mr-1"></i>
                                        ${entry.course}
                                    </div>
                                `);

                                // Add teacher name with icon
                                $entryDiv.append(`
                                    <div style="font-size: 0.8rem;">
                                        <i class="fas fa-user fa-sm mr-1"></i>
                                        ${entry.teacher}
                                    </div>
                                `);

                                // Add class-section info
                                $entryDiv.append(`
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        ${entry.class}-${entry.section}
                                    </div>
                                `);

                                $cell.append($entryDiv);
                            });
                        }

                        $row.append($cell);
                    });
                }

                // Add action buttons if there are entries
                var $actionCell = $('<td class="text-center"></td>');
                if (entryIds.size > 0) {
                    // Convert Set to Array and get the first ID (since we're editing one entry at a time)
                    var firstId = Array.from(entryIds)[0];
                    
                    // Add edit button
                    var $editBtn = $('<button type="button" class="btn btn-warning btn-sm mr-1">')
                        .html('<i class="fas fa-edit"></i>')
                        .attr('title', 'Edit')
                        .click(function() {
                            editTimeTableEntry(firstId);
                        });
                    
                    // Add delete button
                    var $deleteBtn = $('<button type="button" class="btn btn-danger btn-sm">')
                        .html('<i class="fas fa-trash"></i>')
                        .attr('title', 'Delete')
                        .click(function() {
                            deleteTimeTableEntry(firstId);
                        });
                    
                    $actionCell.append($editBtn).append($deleteBtn);
                } else {
                    $actionCell.html('-');
                }
                
                $row.append($actionCell);
                $tbody.append($row);
            });
        }

        // Add table styling
        $table.addClass('table-bordered table-sm');
        $table.find('th').addClass('align-middle');
        
        // Add responsive container
        if (!$table.parent().hasClass('table-responsive')) {
            $table.wrap('<div class="table-responsive"></div>');
        }
    }

    // Function to edit timetable entry
    function editTimeTableEntry(id) {
        $.ajax({
            url: `/time-table/${id}/edit`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    // Store the entry ID for update
                    $('#filterForm').data('edit-id', id);
                    
                    // Populate form fields
                    $('#institute_id').val(response.data.timetable.institute_id).trigger('change');
                    
                    // Use setTimeout to ensure institute change has processed
                    setTimeout(function() {
                        $('#session_id').val(response.data.timetable.session_id).trigger('change');
                        
                        // Use setTimeout to ensure session change has processed
                        setTimeout(function() {
                            // Find and click the correct course card
                            $(`.course-item[data-class-id="${response.data.timetable.class_id}"]` +
                              `[data-section-id="${response.data.timetable.section_id}"]` +
                              `[data-course-id="${response.data.timetable.course_id}"]` +
                              `[data-teacher-id="${response.data.timetable.teacher_id}"]`).click();
                            
                            $('#date').val(response.data.timetable.date);
                            
                            // First set the week number without triggering change
                            $('#week_number').val(response.data.timetable.week_number);
                            
                            // Then trigger change event and wait for time slots to load
                            $('#week_number').trigger('change');
                            
                            // Use setTimeout to ensure week number change has processed
                            setTimeout(function() {
                                $('#time_slot_id').val(response.data.timetable.time_slot_id).trigger('change');
                                
                                // Use setTimeout to ensure time slots have loaded
                                setTimeout(function() {
                                    // Check the previously selected slots
                                    const selectedSlots = response.data.timetable.slot_times.split(',');
                                    selectedSlots.forEach(slot => {
                                        $(`input.slot-checkbox[value="${slot.trim()}"]`).prop('checked', true);
                                    });
                                    updateSelectedSlotsPreview();
                                    
                                    // Change button text and action
                                    $('#addSlotBtn')
                                        .html('<i class="fas fa-save"></i> Update Slot')
                                        .removeClass('btn-primary')
                                        .addClass('btn-warning')
                                        .off('click')
                                        .on('click', function() {
                                            updateTimeTableEntry(id);
                                        });
                                }, 500);
                            }, 500);
                        }, 500);
                    }, 500);

                    // Scroll to form
                    $('html, body').animate({
                        scrollTop: $("#filterForm").offset().top - 20
                    }, 500);
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Error loading timetable entry'
                });
            }
        });
    }

    // Function to update timetable entry
    function updateTimeTableEntry(id) {
        var selectedSlots = $('#selected_slots').val();
        var totalSlots = $('#total_slots').val();
        
        if (!selectedSlots || !totalSlots) {
            Toast.fire({
                icon: 'error',
                title: 'Please select at least one time slot'
            });
            return;
        }
        
        var formData = {
            institute_id: $('#institute_id').val(),
            session_id: $('#session_id').val(),
            class_id: $('#class_id').val(),
            section_id: $('#section_id').val(),
            course_id: $('#course_id').val(),
            teacher_id: $('#teacher_id').val(),
            date: $('#date').val(),
            week_number: $('#week_number').val(),
            time_slot_id: $('#time_slot_id').val(),
            slot_times: selectedSlots,
            total_slots: totalSlots,
            _token: $('meta[name="csrf-token"]').attr('content'),
            _method: 'PUT'
        };
        
        $.ajax({
            url: `/time-table/${id}`,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#addSlotBtn').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Updating...');
            },
            complete: function() {
                $('#addSlotBtn').prop('disabled', false)
                    .html('<i class="fas fa-save"></i> Update Slot');
            },
            success: function(response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        title: 'Time table entry updated successfully'
                    });
                    
                    // Reset form and button
                    $('#filterForm').removeData('edit-id');
                    $('#addSlotBtn')
                        .html('<i class="fas fa-plus"></i> Add Slot')
                        .removeClass('btn-warning')
                        .addClass('btn-primary')
                        .off('click')
                        .on('click', function() {
                            addTimeTableEntry();
                        });
                    
                    // Clear form
                    $('#slotContainer').html('');
                    $('#selected_slots').val('');
                    $('#total_slots').val('');
                    $('.course-item').removeClass('active');
                    
                    // Refresh timetable
                    loadTimetable();
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    $.each(errors, function(key, value) {
                        $(`#${key}`).addClass('is-invalid');
                        $(`#${key}_error`).text(value[0]);
                    });
                    
                    Toast.fire({
                        icon: 'error',
                        title: 'Please fix the errors in the form'
                    });
                } else if (xhr.responseJSON && xhr.responseJSON.error === 'Conflict detected') {
                    // Handle conflict error
                    var conflicts = xhr.responseJSON.conflicts;
                    var conflictMessage = 'Conflict detected:\n';
                    
                    conflicts.forEach(function(conflict) {
                        conflictMessage += `- ${conflict.course.course_name} with ${conflict.teacher.name} on ${conflict.slot_times}\n`;
                    });
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Conflict Detected',
                        html: conflictMessage.replace(/\n/g, '<br>'),
                        confirmButtonText: 'OK'
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'An error occurred while updating the time table entry'
                    });
                }
            }
        });
    }

    // Function to delete timetable entry
    function deleteTimeTableEntry(id) {
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
                    url: "/time-table/delete/" + id,
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: 'Time table entry deleted successfully'
                            });
                            loadTimetable();
                        }
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Error deleting time table entry'
                        });
                    }
                });
            }
        });
    }
});
</script>

@endpush