@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h3 class="card-title mb-0 text-white">
                            <i class="fas fa-file-export"></i> Time Table Report
                        </h3>
                    </div>
                </div>

                <div class="card-body">
                    <form id="reportForm">
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
                                </div>
                            </div>
                            @endif
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="session_id">Academic Session <span class="text-danger">*</span></label>
                                    <select name="session_id" id="session_id" class="form-control" required>
                                        <option value="">Select Session</option>
                                    </select>
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
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="button" id="generateReportBtn" class="btn btn-primary">
                                    <i class="fas fa-sync"></i> Generate Report
                                </button>
                                
                                <div class="btn-group ml-2">
                                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">

                                        <i class="fas fa-download"></i> Export
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item export-btn" href="#" data-format="csv">CSV</a>
                                        <a class="dropdown-item export-btn" href="#" data-format="xlsx">Excel</a>
                                        <a class="dropdown-item export-btn" href="#" data-format="pdf">PDF</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive mt-4">
                        <div id="reportContainer">
                            <!-- Report will be loaded here -->
                            <div class="alert alert-info">
                                Please select institute, session and week number, then click "Generate Report"
                            </div>
                        </div>
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
    // Initialize toaster
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
        } else {
            $('#session_id').empty().append('<option value="">Select Session</option>');
        }
    });
    @endif
    
    // When session changes
    $('#session_id').change(function() {
        var sessionId = $(this).val();
        if (!sessionId) {
            $('#week_number').val('').trigger('change');
        }
    });
    
    // Generate report button click
    $('#generateReportBtn').click(function() {
        generateReport();
    });
    
    // Export button click
    $('.export-btn').click(function(e) {
        e.preventDefault();
        var format = $(this).data('format');
        exportReport(format);
    });
    
    function generateReport() {
        var instituteId = $('#institute_id').val();
        var sessionId = $('#session_id').val();
        var weekNumber = $('#week_number').val();

        if (!instituteId || !sessionId || !weekNumber) {
            Toast.fire({
                icon: 'error',
                title: 'Please select institute, session and week number'
            });
            return;
        }

        $.ajax({
            url: "{{ route('time-table.generate-report') }}",
            type: "GET",
            data: {
                institute_id: instituteId,
                session_id: sessionId,
                week_number: weekNumber
            },
            beforeSend: function() {
                $('#generateReportBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating...');
            },
            complete: function() {
                $('#generateReportBtn').prop('disabled', false).html('<i class="fas fa-sync"></i> Generate Report');
            },
            success: function(response) {
                if (response.success) {
                    displayReport(response.data);
                    Toast.fire({
                        icon: 'success',
                        title: 'Report generated successfully'
                    });
                } else {
                    $('#reportContainer').html('<div class="alert alert-danger">' + (response.error || 'Error generating report') + '</div>');
                    Toast.fire({
                        icon: 'error',
                        title: response.error || 'Error generating report'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error generating report:', xhr.responseText);
                $('#reportContainer').html('<div class="alert alert-danger">Error generating report</div>');
                Toast.fire({
                    icon: 'error',
                    title: 'Error generating report. Please check console for details.'
                });
            }
        });
    }
    
    function displayReport(data) {
        try {
            var html = '<h4 class="mb-3">Time Table Report - Week ' + data.timeSlot.week_number + '</h4>';
            
            // Create a table for each class-section
            data.timetableData.forEach(function(classData) {
                html += '<div class="card mb-4">';
                html += '<div class="card-header bg-secondary text-white">';
                html += '<h5>' + classData.class + ' - ' + classData.section + '</h5>';
                html += '</div>';
                html += '<div class="card-body p-0">';
                html += '<table class="table table-bordered mb-0">';
                html += '<thead><tr><th>Date</th><th>Day</th>';
                
                // Add time slots as headers
                data.slots.forEach(function(slot) {
                    html += '<th>' + slot + '</th>';
                });
                
                html += '</tr></thead><tbody>';
                
                // Add rows for each date
                data.dates.forEach(function(dateInfo) {
                    html += '<tr>';
                    html += '<td>' + dateInfo.date + '</td>';
                    html += '<td>' + dateInfo.day + '</td>';
                    
                    // Add cells for each time slot
                    data.slots.forEach(function(slot) {
                        var matchingEntries = classData.entries.filter(function(entry) {
                            return entry.date === dateInfo.date && entry.slot_time === slot;
                        });
                        
                        if (matchingEntries.length > 0) {
                            var cellContent = '';
                            matchingEntries.forEach(function(entry) {
                                cellContent += '<div class="bg-success text-white p-1 small">';
                                cellContent += '<strong>' + entry.course + '</strong><br>';
                                cellContent += entry.teacher;
                                cellContent += '</div>';
                            });
                            html += '<td>' + cellContent + '</td>';
                        } else {
                            html += '<td></td>';
                        }
                    });
                    
                    html += '</tr>';
                });
                
                html += '</tbody></table></div></div>';
            });
            
            $('#reportContainer').html(html);
        } catch (error) {
            console.error('Error displaying report:', error);
            $('#reportContainer').html('<div class="alert alert-danger">Error displaying report data</div>');
            Toast.fire({
                icon: 'error',
                title: 'Error displaying report data'
            });
        }
    }
    
    function exportReport(format) {
        try {
            var instituteId = $('#institute_id').val();
            var sessionId = $('#session_id').val();
            var weekNumber = $('#week_number').val();

            if (!instituteId || !sessionId || !weekNumber) {
                Toast.fire({
                    icon: 'error',
                    title: 'Please select institute, session and week number'
                });
                return;
            }

            // Show loading indicator
            var exportBtn = $('.export-btn[data-format="' + format + '"]');
            var originalHtml = exportBtn.html();
            exportBtn.html('<i class="fas fa-spinner fa-spin"></i> Exporting...');

            // Prepare the data
            var formData = {
                institute_id: instituteId,
                session_id: sessionId,
                week_number: weekNumber,
                format: format,
                _token: "{{ csrf_token() }}"
            };

            // Different handling for PDF vs Excel/CSV
            if (format === 'pdf') {
                // For PDF, we'll submit a form to open in new tab
                var form = $('<form>', {
                    action: "{{ route('time-table.export-report') }}",
                    method: "POST",
                    target: "_blank"
                });

                $.each(formData, function(key, value) {
                    form.append($('<input>', {
                        type: 'hidden',
                        name: key,
                        value: value
                    }));
                });

                $('body').append(form);
                form.submit();
                form.remove();
            } else {
                // For Excel/CSV, we'll use AJAX to download
                $.ajax({
                    url: "{{ route('time-table.export-report') }}",
                    type: "POST",
                    data: formData,
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(response) {
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = 'timetable_week_' + weekNumber + '.' + format;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    },
                    error: function(xhr) {
                        console.error('Export error:', xhr.responseText);
                        Toast.fire({
                            icon: 'error',
                            title: 'Export failed. Please check console for details.'
                        });
                    },
                    complete: function() {
                        exportBtn.html(originalHtml);
                    }
                });
            }

            // Reset button after a delay (fallback)
            setTimeout(function() {
                exportBtn.html(originalHtml);
            }, 5000);

        } catch (error) {
            console.error('Export error:', error);
            Toast.fire({
                icon: 'error',
                title: 'Export failed: ' + error.message
            });
            $('.export-btn').html(originalHtml);
        }
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
    
    // Function to load initial data for Admin
    function loadInitialDataForAdmin() {
        var instituteId = $('#institute_id').val();
        if (instituteId) {
            loadSessions(instituteId);
        }
    }
});
</script>
@endpush