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
                       
                        <div class="row">
                            @if(auth()->user()->hasRole('Super Admin'))
                            <div class="col-md-4">
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
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="session_id">Academic Session <span class="text-danger">*</span></label>
                                    <select name="session_id" id="session_id" class="form-control" required>
                                        <option value="">Select Session</option>
                                    </select>
                                    <div class="invalid-feedback" id="session_id_error"></div>
                                </div>
                            </div>
                        
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="class_id">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" id="class_id" class="form-control" required>
                                        <option value="">Select Class</option>
                                    </select>
                                    <div class="invalid-feedback" id="class_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="section_id">Section <span class="text-danger">*</span></label>
                                    <select name="section_id" id="section_id" class="form-control" required>
                                        <option value="">Select Section</option>
                                    </select>
                                    <div class="invalid-feedback" id="section_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="course_id">Course <span class="text-danger">*</span></label>
                                    <select name="course_id" id="course_id" class="form-control" required>
                                        <option value="">Select Course</option>
                                    </select>
                                    <div class="invalid-feedback" id="course_id_error"></div>
                                </div>
                            </div>
                            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin'))
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="teacher_id">Teacher <span class="text-danger">*</span></label>
                                    <select name="teacher_id" id="teacher_id" class="form-control" required>
                                        <option value="">Select Teacher</option>
                                    </select>
                                    <div class="invalid-feedback" id="teacher_id_error"></div>
                                </div>
                            </div>
                            @else
                                <input type="hidden" name="teacher_id" id="teacher_id" value="{{ auth()->user()->id }}">
                            @endif
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" id="date" class="form-control" required>
                                    <div class="invalid-feedback" id="date_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="time_slot_id">Time Slot Template <span class="text-danger">*</span></label>
                                    <select name="time_slot_id" id="time_slot_id" class="form-control" required>
                                        <option value="">Select Time Slot</option>
                                    </select>
                                    <div class="invalid-feedback" id="time_slot_id_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end mt-2 ">
                                <button type="button" id="checkAvailabilityBtn" class="btn btn-info btn-sm mr-2 me-1">
                                    <i class="fas fa-check"></i> Check Availability
                                </button>
                                <button type="button" id="addSlotBtn" class="btn btn-primary btn-sm mt-2" disabled>
                                    <i class="fas fa-plus"></i> Add Slot
                                </button>
                            </div>
                        </div>
                        <div id="slotContainer" class="mt-3">
                            <!-- Time slots will be displayed here dynamically -->
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
            loadSessions(instituteId);
            $('#class_id, #section_id, #course_id, #teacher_id').empty().append('<option value="">Select</option>');
        } else {
            clearAllDropdowns();
        }
    });
    @endif
    
    // When session changes, load classes and time slot templates
    $('#session_id').change(function() {
        var sessionId = $(this).val();
        var instituteId = $('#institute_id').val();

        if (sessionId && instituteId) {
            loadClasses(instituteId, sessionId);
            loadTimeSlotTemplates(sessionId);
        } else {
            $('#class_id').empty().append('<option value="">Select Class</option>');
            $('#time_slot_id').empty().append('<option value="">Select Time Slot</option>');
        }
    });

    // When class changes, load sections
    $('#class_id').change(function() {
        var classId = $(this).val();
        var instituteId = $('#institute_id').val();
        var sessionId = $('#session_id').val();

        if (classId && instituteId && sessionId) {
            loadSections(instituteId, sessionId, classId);
        } else {
            $('#section_id').empty().append('<option value="">Select Section</option>');
        }
    });

    // When section changes, load courses
    $('#section_id').change(function() {
        var sectionId = $(this).val();
        var instituteId = $('#institute_id').val();
        var sessionId = $('#session_id').val();
        var classId = $('#class_id').val();

        if (sectionId && instituteId && sessionId && classId) {
            loadCourses(instituteId, sessionId, classId, sectionId);
        } else {
            $('#course_id').empty().append('<option value="">Select Course</option>');
        }
    });

    // When course changes, load teachers
    $('#course_id').change(function() {
        var courseId = $(this).val();
        var instituteId = $('#institute_id').val();
        var sessionId = $('#session_id').val();
        var classId = $('#class_id').val();
        var sectionId = $('#section_id').val();

        if (courseId && instituteId && sessionId && classId && sectionId) {
            loadTeachers(instituteId, sessionId, classId, sectionId, courseId);
        } else {
            $('#teacher_id').empty().append('<option value="">Select Teacher</option>');
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

function editTimeTableEntry(id) {
    $.ajax({
        url: "/time-table/edit/" + id,  // Fixed URL
        type: "GET",
        success: function(response) {
            if (response.success) {
                // Populate the form fields
                $('#institute_id').val(response.data.timetable.institute_id).trigger('change');
                $('#session_id').val(response.data.timetable.session_id).trigger('change');
                $('#class_id').val(response.data.timetable.class_id).trigger('change');
                $('#section_id').val(response.data.timetable.section_id).trigger('change');
                $('#course_id').val(response.data.timetable.course_id).trigger('change');
                $('#teacher_id').val(response.data.timetable.teacher_id).trigger('change');
                $('#date').val(response.data.timetable.date);
                $('#week_number').val(response.data.timetable.week_number).trigger('change');
                $('#time_slot_id').val(response.data.timetable.time_slot_id).trigger('change');
                
                // Create a hidden field for the ID
                if (!$('#timetable_id').length) {
                    $('<input>').attr({
                        type: 'hidden',
                        id: 'timetable_id',
                        name: 'timetable_id'
                    }).appendTo('#filterForm');
                }
                $('#timetable_id').val(id);
                
                // Change the add button to update button
                $('#addSlotBtn').html('<i class="fas fa-save"></i> Update Slot')
                    .removeClass('btn-primary')
                    .addClass('btn-warning')
                    .off('click')
                    .on('click', function() {
                        updateTimeTableEntry();
                    });
                
                // Display the slots with the selected ones checked
                var container = $('<div class="slot-container"></div>');
                var header = $('<h5 class="mb-3">Available Slots</h5>');
                var slotList = $('<div class="row slot-list"></div>');
                
                $.each(response.data.slots, function(index, slot) {
                    var isChecked = response.data.selected_slots.includes(slot.formatted);
                    
                    var slotCol = $('<div class="col-md-3 mb-2"></div>');
                    var slotDiv = $('<div class="form-check"></div>');
                    var input = $(`<input type="checkbox" class="form-check-input slot-checkbox" 
                                    id="slot_${index}" value="${slot.formatted}" ${isChecked ? 'checked' : ''}>`);
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
                            ${response.data.selected_slots.join(', ')}
                        </div>
                        <input type="hidden" name="selected_slots" id="selected_slots" value="${response.data.timetable.slot_times}">
                        <input type="hidden" name="total_slots" id="total_slots" value="${response.data.selected_slots.length}">
                    </div>
                `);
                
                container.append(previewContainer);
                
                $('#slotContainer').html(container);
                
                // Handle checkbox changes
                $('.slot-checkbox').change(function() {
                    updateSelectedSlotsPreview();
                });
                
                // Scroll to the form
                $('html, body').animate({
                    scrollTop: $('#filterForm').offset().top
                }, 500);
            }
        },
        error: function(xhr) {
            console.error('Error loading timetable for edit:', xhr.responseText);
            Toast.fire({
                icon: 'error',
                title: 'Error loading timetable for edit'
            });
        }
    });
}

function updateTimeTableEntry() {
    var selectedSlots = $('#selected_slots').val();
    var totalSlots = $('#total_slots').val();
    var id = $('#timetable_id').val();
    
    if (!selectedSlots || !totalSlots || !id) {
        Toast.fire({
            icon: 'error',
            title: 'Please select at least one time slot'
        });
        return;
    }
    
    // Collect all form data
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
    
    // Submit the form data to the server
    $.ajax({
        url: "/time-table/update/" + id,  // Fixed URL
        type: "POST", // Using POST with _method=PUT for Laravel
        data: formData,
        beforeSend: function() {
            $('#addSlotBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        },
        complete: function() {
            $('#addSlotBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Update Slot');
        },
        success: function(response) {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: 'Time table entry updated successfully'
                });
                
                // Reset the form
                $('#slotContainer').html('');
                $('#selected_slots').val('');
                $('#total_slots').val('');
                $('#timetable_id').remove();
                
                // Change the button back to add
                $('#addSlotBtn').html('<i class="fas fa-plus"></i> Add Slot')
                    .removeClass('btn-warning')
                    .addClass('btn-primary')
                    .off('click')
                    .on('click', function() {
                        addTimeTableEntry();
                    });
                
                // Refresh the timetable
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
                url: "/time-table/delete/" + id,  // Fixed URL
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

// Update the displayTimetable function to include edit and delete buttons
function displayTimetable(data) {
    var $table = $('#timeTable');
    var $thead = $table.find('thead');
    var $tbody = $table.find('tbody');

    $thead.empty();
    $tbody.empty();

    // Create header row
    var $headerRow = $('<tr></tr>');
    $headerRow.append('<th>Days</th>');

    if (data.slots && data.slots.length > 0) {
        data.slots.forEach(function(slot) {
            $headerRow.append('<th>' + slot + '</th>');
        });
    }

    // Add actions column
    $headerRow.append('<th>Actions</th>');

    $thead.append($headerRow);

    // Create table body
    if (data.dates && data.dates.length > 0) {
        data.dates.forEach(function(dateInfo) {
            var $row = $('<tr></tr>');
            $row.append('<td>' + dateInfo.date + '<br>' + dateInfo.day + '</td>');

            var hasEntries = false;
            var entryIds = [];

            if (data.slots && data.slots.length > 0) {
                data.slots.forEach(function(slot) {
                    var matchingEntries = data.entries.filter(function(entry) {
                        return entry.date === dateInfo.date && entry.slot_time === slot;
                    });

                    if (matchingEntries.length > 0) {
                        var cellContent = '';
                        matchingEntries.forEach(function(entry) {
                            cellContent += '<div class="bg-success text-white p-1">' +
                                          entry.course + '<br>' +
                                          entry.teacher + '<br>' +
                                          entry.class + '-' + entry.section + '</div>';
                            if (entry.id) {
                                entryIds.push(entry.id);
                            }
                        });
                        $row.append('<td>' + cellContent + '</td>');
                        hasEntries = true;
                    } else {
                        $row.append('<td></td>');
                    }
                });
            }

            // Add action buttons if there are entries for this date
            var $actionCell = $('<td></td>');
            
            if (hasEntries) {
                $actionCell.append(
                    $('<button>').addClass('btn btn-sm btn-warning mr-1 edit-btn')
                        .html('<i class="fas fa-edit"></i>')
                        .attr('title', 'Edit')
                        .data('ids', entryIds.join(','))
                        .on('click', function() {
                            var ids = $(this).data('ids').split(',');
                            if (ids.length > 0) {
                                editTimeTableEntry(ids[0]);
                            }
                        })
                ).append(
                    $('<button>').addClass('btn btn-sm btn-danger delete-btn')
                        .html('<i class="fas fa-trash"></i>')
                        .attr('title', 'Delete')
                        .data('ids', entryIds.join(','))
                        .on('click', function() {
                            var ids = $(this).data('ids').split(',');
                            if (ids.length > 0) {
                                deleteTimeTableEntry(ids[0]);
                            }
                        })
                );
            } else {
                $actionCell.append('<span>-</span>');
            }
            
            $row.append($actionCell);
            $tbody.append($row);
        });
    }
}

    
    // Helper function to find timetable entry for a specific date and slot
    function findTimetableEntry(date, slot, entries) {
        if (!entries) return null;
        
        for (var i = 0; i < entries.length; i++) {
            var entry = entries[i];
            if (entry.date === date && entry.slot_times.includes(slot)) {
                return entry;
            }
        }
        
        return null;
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
        
        if (!selectedSlots || !totalSlots) {
            Toast.fire({
                icon: 'error',
                title: 'Please select at least one time slot'
            });
            return;
        }
        
        // Collect all form data
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
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        // Validate required fields
        var requiredFields = ['institute_id', 'session_id', 'class_id', 'section_id', 'course_id', 'teacher_id', 'date', 'week_number', 'time_slot_id'];
        var isValid = true;
        
        requiredFields.forEach(function(field) {
            if (!formData[field]) {
                $(`#${field}`).addClass('is-invalid');
                $(`#${field}_error`).text('This field is required');
                isValid = false;
            } else {
                $(`#${field}`).removeClass('is-invalid');
                $(`#${field}_error`).text('');
            }
        });
        
        if (!isValid) {
            return;
        }
        
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
                        title: response.message
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
                        title: 'An error occurred while adding the time table entry'
                    });
                }
            }
        });
    }
    
    // Function to load sessions
    function loadSessions(instituteId, callback) {
        $.ajax({
            url: "{{ route('time-table.dropdowns') }}",
            type: "GET",
            data: { institute_id: instituteId },
            success: function(data) {
                $('#session_id').empty().append('<option value="">Select Session</option>');
                if(data.sessions && data.sessions.length > 0) {
                    $.each(data.sessions, function(key, value) {
                        $('#session_id').append(`<option value="${value.id}">${value.name}</option>`);
                    });
                }
                if (callback) callback();
            },
            error: function(xhr) {
                console.error('Error loading sessions:', xhr.responseText);
                Toast.fire({
                    icon: 'error',
                    title: 'Error loading sessions'
                });
                if (callback) callback();
            }
        });
    }
    
    // Function to load classes
    function loadClasses(instituteId, sessionId) {
        $.ajax({
            url: "{{ route('time-table.dropdowns') }}",
            type: "GET",
            data: { 
                institute_id: instituteId,
                session_id: sessionId
            },
            success: function(data) {
                $('#class_id').empty().append('<option value="">Select Class</option>');
                if (data.classes && data.classes.length > 0) {
                    $.each(data.classes, function(key, value) {
                        $('#class_id').append(`<option value="${value.id}">${value.name}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Error loading classes:', xhr.responseText);
                Toast.fire({
                    icon: 'error',
                    title: 'Error loading classes'
                });
            }
        });
    }
    
    // Function to load sections
    function loadSections(instituteId, sessionId, classId) {
        $.ajax({
            url: "{{ route('time-table.dropdowns') }}",
            type: "GET",
            data: {
                institute_id: instituteId,
                session_id: sessionId,
                class_id: classId
            },
            success: function(data) {
                $('#section_id').empty().append('<option value="">Select Section</option>');
                if (data.sections && data.sections.length > 0) {
                    $.each(data.sections, function(key, value) {
                        $('#section_id').append(`<option value="${value.id}">${value.name}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Error loading sections:', xhr.responseText);
                Toast.fire({
                    icon: 'error',
                    title: 'Error loading sections'
                });
            }
        });
    }
    
    // Function to load courses
    function loadCourses(instituteId, sessionId, classId, sectionId) {
        $.ajax({
            url: "{{ route('time-table.dropdowns') }}",
            type: "GET",
            data: {
                institute_id: instituteId,
                session_id: sessionId,
                class_id: classId,
                section_id: sectionId
            },
            success: function(data) {
                $('#course_id').empty().append('<option value="">Select Course</option>');
                if (data.courses && data.courses.length > 0) {
                    $.each(data.courses, function(key, value) {
                        $('#course_id').append(`<option value="${value.id}">${value.name}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Error loading courses:', xhr.responseText);
                Toast.fire({
                    icon: 'error',
                    title: 'Error loading courses'
                });
            }
        });
    }
    
    // Function to load teachers
    function loadTeachers(instituteId, sessionId, classId, sectionId, courseId) {
        $.ajax({
            url: "{{ route('time-table.dropdowns') }}",
            type: "GET",
            data: {
                institute_id: instituteId,
                session_id: sessionId,
                class_id: classId,
                section_id: sectionId,
                course_id: courseId
            },
            success: function(data) {
                $('#teacher_id').empty().append('<option value="">Select Teacher</option>');
                if (data.teachers && data.teachers.length > 0) {
                    $.each(data.teachers, function(key, value) {
                        $('#teacher_id').append(`<option value="${value.id}">${value.name}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Error loading teachers:', xhr.responseText);
                Toast.fire({
                    icon: 'error',
                    title: 'Error loading teachers'
                });
            }
        });
    }
    
    // Function to load initial data for Admin
    function loadInitialDataForAdmin() {
        var instituteId = $('#institute_id').val();
        if (instituteId) {
            loadSessions(instituteId);
        }
    }
    
    // Function to clear all dropdowns
    function clearAllDropdowns() {
        $('#session_id, #class_id, #section_id, #course_id, #teacher_id').empty().append('<option value="">Select</option>');
    }
});
</script>

@endpush