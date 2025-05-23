<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Enrollment - {{ $student['name'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #3490dc; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #3490dc; font-size: 18px; }
        .header p { margin: 5px 0 0; color: #666; }
        .student-info { display: flex; margin-bottom: 20px; align-items: center; }
        .student-photo { margin-right: 20px; border: 3px solid #3490dc; border-radius: 50%; width: 100px; height: 100px; overflow: hidden; }
        .student-photo img { width: 100%; height: 100%; object-fit: cover; }
        .student-details { flex: 1; }
        .student-details h2 { margin: 0 0 5px; color: #333; }
        .section { margin-bottom: 20px; }
        .section h3 { color: #3490dc; border-bottom: 1px solid #3490dc; padding-bottom: 5px; font-size: 14px; margin-bottom: 10px; }
        .row { display: flex; margin-bottom: 5px; flex-wrap: wrap; }
        .col-6 { width: 50%; padding: 2px 5px; box-sizing: border-box; }
        .col-12 { width: 100%; padding: 2px 5px; box-sizing: border-box; }
        .badge { padding: 3px 6px; border-radius: 3px; font-size: 10px; display: inline-block; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-secondary { background-color: #6c757d; color: white; }
        strong { color: #555; }
        .footer { text-align: center; margin-top: 20px; padding-top: 10px; border-top: 1px solid #eee; font-size: 10px; color: #999; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table th { background-color: #3490dc; color: white; padding: 6px; text-align: left; }
        table td { padding: 5px; border: 1px solid #ddd; }
        .session-header { background-color: #f8f9fa; padding: 8px; margin: 15px 0 5px; border-left: 4px solid #3490dc; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Academic History</h1>
        <p>Generated on: {{ now()->format('M d, Y h:i A') }}</p>
    </div>

    <div class="student-info">
        <div class="student-photo">
            @if(isset($student['profile_image_base64']))
                <img src="{{ $student['profile_image_base64'] }}" alt="Student Photo">
            @else
                <img src="{{ $default_profile_image }}" alt="Default Student Photo">
            @endif
        </div>
        <div class="student-details">
            <h2>{{ $student['name'] }}</h2>
            <p><strong>Roll Number:</strong> {{ $student['roll_number'] ?? 'N/A' }}</p>
        </div>
    </div>

    <div class="section">
        <h3>Personal Information</h3>
        <div class="row">
            <div class="col-6">
                <strong>Full Name:</strong> {{ $student['name'] }}
            </div>
            <div class="col-6">
                <strong>Email:</strong> {{ $student['email'] ?? 'N/A' }}
            </div>
            <div class="col-6">
                <strong>CNIC:</strong> {{ $student['cnic'] ?? 'N/A' }}
            </div>
            <div class="col-6">
                <strong>Phone:</strong> {{ $student['phone'] ?? 'N/A' }}
            </div>
            <div class="col-12">
                <strong>Address:</strong> {{ $student['address'] ?? 'N/A' }}
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Academic History</h3>
        
        @foreach($enrollments as $sessionEnrollments)
            @php
                $firstEnrollment = $sessionEnrollments[0] ?? null;
            @endphp
            
            @if($firstEnrollment)
            <div class="session-header">
                <strong>{{ $firstEnrollment['session']['session_name'] }}</strong> - 
                {{ $firstEnrollment['class']['name'] }} - 
                {{ $firstEnrollment['section']['section_name'] }}
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Course</th>
                        <th>Teacher</th>
                        <th>Enrollment Date</th>
                        
                        <th>Attendance %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessionEnrollments as $index => $enrollment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $enrollment['course']['course_name'] ?? 'N/A' }}</td>
                            <td>{{ $enrollment['teacher']['name'] ?? 'N/A' }}</td>
                            <td>{{ $enrollment['enrollment_date'] }}</td>
                            
                            <td>
                                <span class="badge {{ $enrollment['attendance_percentage'] >= 75 ? 'badge-success' : ($enrollment['attendance_percentage'] >= 50 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $enrollment['attendance_percentage'] }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        @endforeach
    </div>
</body>
</html>