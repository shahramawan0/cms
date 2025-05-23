<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Enrollment Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #3490dc; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #3490dc; }
        .info-section { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th { background-color: #3490dc; color: white; padding: 8px; text-align: left; }
        table td { padding: 6px; border: 1px solid #ddd; }
        .badge { padding: 3px 6px; border-radius: 3px; font-size: 10px; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-secondary { background-color: #6c757d; color: white; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $institute }} - Enrollment Report</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <div class="info-section">
        <div class="row">
            <div class="col-6">
                <strong>Session:</strong> {{ $session }}
            </div>
            <div class="col-6">
                <strong>Class:</strong> {{ $class }}
            </div>
            <div class="col-6">
                <strong>Section:</strong> {{ $section }}
            </div>
            <div class="col-6">
                <strong>Total Students:</strong> {{ count($students) }}
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Roll Number</th>
                <th>Courses</th>
                <th>Teachers</th>
                <th>Enrollment Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student['name'] }}</td>
                <td>{{ $student['roll_number'] }}</td>
                <td>{{ $student['courses'] }}</td>
                <td>{{ $student['teachers'] }}</td>
                <td>{{ $student['enrollment_date'] }}</td>
                <td>
                    <span class="badge {{ $student['status'] === 'active' ? 'badge-success' : ($student['status'] === 'inactive' ? 'badge-warning' : 'badge-secondary') }}">
                        {{ ucfirst($student['status']) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Report generated on {{ now()->format('M d, Y h:i A') }} | © {{ date('Y') }} {{ $institute }}
    </div>
</body>
</html>