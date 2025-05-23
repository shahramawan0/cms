<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <style>
        @page {
            margin: 20px;
            size: A4 landscape;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h1 {
            color: #2c3e50;
            margin-bottom: 2px;
            font-size: 16px;
        }
        .header p {
            color: #7f8c8d;
            margin-top: 0;
            font-size: 12px;
        }
        .info-section {
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 3px;
            font-size: 10px;
        }
        .info-section p {
            margin: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        table th {
            background-color: #3490dc;
            color: white;
            padding: 5px;
            text-align: left;
            font-size: 9px;
        }
        table td {
            padding: 4px;
            border-bottom: 1px solid #ddd;
            font-size: 8px;
            word-wrap: break-word;
        }
        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            color: white;
            display: inline-block;
        }
        .badge-present {
            background-color: #28a745;
        }
        .badge-absent {
            background-color: #dc3545;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            color: #7f8c8d;
            font-size: 8px;
        }
        .info-column {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            box-sizing: border-box;
        }
        .student-name {
            min-width: 80px;
            max-width: 80px;
        }
        .email-cell {
            min-width: 100px;
            max-width: 100px;
        }
        .cnic-cell {
            min-width: 80px;
            max-width: 80px;
        }
        .phone-cell {
            min-width: 80px;
            max-width: 80px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $institute }} - Attendance Report</h1>
        <p>Generated on {{ now()->format('F j, Y') }}</p>
    </div>

    <div class="info-section">
        <div class="info-column">
            <p><strong>Session:</strong> {{ $session }}</p>
            <p><strong>Class:</strong> {{ $class }}</p>
            <p><strong>Section:</strong> {{ $section }}</p>
        </div>
        <div class="info-column">
            <p><strong>Course:</strong> {{ $course }}</p>
            <p><strong>Teacher:</strong> {{ $teacher }}</p>
            <p><strong>Date Range:</strong> {{ $start_date }} to {{ $end_date }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 12%;">Student Name</th>
                <th style="width: 8%;">Roll No</th>
                <th style="width: 15%;">Email</th>
                <th style="width: 10%;">CNIC</th>
                <th style="width: 10%;">Phone</th>
                <th style="width: 5%;">Present</th>
                <th style="width: 5%;">Total Lecture</th>
                <th style="width: 7%;">Percentage</th>
                <th style="width: 5%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="student-name">{{ $student['name'] }}</td>
                <td>{{ $student['roll_number'] ?? 'N/A' }}</td>
                <td class="email-cell">{{ $student['email'] ?? 'N/A' }}</td>
                <td class="cnic-cell">{{ $student['cnic'] ?? 'N/A' }}</td>
                <td class="phone-cell">{{ $student['phone'] ?? 'N/A' }}</td>
                <td>{{ $student['present_count'] }}</td>
                <td>{{ $student['total_classes'] }}</td>
                <td>{{ $student['percentage'] }}%</td>
                <td>
                    <span class="badge badge-{{ strtolower($student['status']) }}">
                        {{ $student['status'] }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} {{ $institute }}. All rights reserved.
    </div>
</body>
</html>