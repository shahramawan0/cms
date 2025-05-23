<!DOCTYPE html>
<html>
<head>
    <title>Attendance Details - {{ $student['name'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .student-info { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary { margin-top: 20px; padding: 10px; border-radius: 5px; }
        .eligible { background-color: #d4edda; color: #155724; }
        .not-eligible { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Attendance Details</h2>
    </div>

    <div class="student-info">
        <p><strong>Student:</strong> {{ $student['name'] }}</p>
        <p><strong>Roll No:</strong> {{ $student['roll_number'] ?? 'N/A' }}</p>
        <p><strong>Course:</strong> {{ $course['course_name'] }}</p>
        <p><strong>Session:</strong> {{ $session['session_name'] }}</p>
        <p><strong>Class:</strong> {{ $class['name'] }} - {{ $section['section_name'] }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time Slot</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendance as $record)
            <tr>
                <td>{{ $record['date'] }}</td>
                <td>{{ $record['slot_times'] }}</td>
                <td>
                    <span style="color: {{ $record['status'] === 'Present' ? 'green' : 'red' }}">
                        {{ $record['status'] }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary {{ $percentage >= 65 ? 'eligible' : 'not-eligible' }}">
        <p><strong>Total Days:</strong> {{ $total_days }}</p>
        <p><strong>Present Days:</strong> {{ $present_days }}</p>
        <p><strong>Attendance Percentage:</strong> {{ $percentage }}%</p>
        <p><strong>Status:</strong> {{ $percentage >= 65 ? 'Eligible' : 'Not Eligible' }}</p>
    </div>
</body>
</html>