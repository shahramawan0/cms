<!DOCTYPE html>
<html>
<head>
    <title>Result Details - {{ $student['name'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .student-info { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary { margin-top: 20px; }
        .passed { color: green; }
        .failed { color: red; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Result Details</h2>
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
                <th>Component</th>
                <th>Obtained Marks</th>
                <th>Total Marks</th>
                <th>Weight %</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assessments as $assessment)
            <tr>
                <td>{{ $assessment['name'] }}</td>
                <td>{{ $assessment['obtained'] }}</td>
                <td>{{ $assessment['total'] }}</td>
                <td>{{ $assessment['weightage'] }}%</td>
                <td>{{ $assessment['remarks'] }}</td>
            </tr>
            @endforeach
            <tr style="background-color: #f2f2f2;">
                <th>Total</th>
                <th>{{ $result['obtained_marks'] }}</th>
                <th>{{ $result['total_marks'] }}</th>
                <th>100%</th>
                <th>
                    <span class="{{ $result['status'] === 'Pass' ? 'passed' : 'failed' }}">
                        {{ $result['status'] }}
                    </span>
                </th>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Percentage:</strong> {{ $percentage }}%</p>
        <p><strong>Grade:</strong> {{ $grade }}</p>
        <p><strong>Status:</strong> 
            <span class="{{ $result['status'] === 'Pass' ? 'passed' : 'failed' }}">
                {{ $result['status'] }}
            </span>
        </p>
    </div>
</body>
</html>