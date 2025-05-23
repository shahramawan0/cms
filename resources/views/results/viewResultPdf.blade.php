<!DOCTYPE html>
<html>
<head>
    <title>Results - {{ $institute_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            padding: 20px;
        }
        .result-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
            padding: 30px;
        }
        .institute-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .institute-header h2 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .institute-header h3 {
            color: #3498db;
            font-weight: 600;
        }
        .info-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .info-item {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            color: #212529;
        }
        .result-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .result-table thead th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
            padding: 12px 15px;
        }
        .result-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .result-table tbody tr:hover {
            background-color: #e9ecef;
        }
        .result-table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        .status-pass {
            color: #28a745;
            font-weight: 600;
        }
        .status-fail {
            color: #dc3545;
            font-weight: 600;
        }
        .footer {
            text-align: right;
            font-size: 14px;
            color: #6c757d;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }
        .print-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <div class="institute-header">
            <h2>{{ $institute_name }}</h2>
            <h3>Course Results - {{ $course_name }}</h3>
        </div>

        <div class="info-card">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-calendar-alt me-2"></i>Session:</span>
                        <span class="info-value">{{ $session_name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-users me-2"></i>Section:</span>
                        <span class="info-value">{{ $section_name }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-graduation-cap me-2"></i>Class:</span>
                        <span class="info-value">{{ $class_name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-chalkboard-teacher me-2"></i>Teacher:</span>
                        <span class="info-value">{{ $teacher_name }}</span>
                    </div>
                </div>
            </div>
        </div>

        <table class="result-table table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Roll No</th>
                    <th>Total Marks</th>
                    <th>Obtained Marks</th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $index => $result)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $result['name'] }}</td>
                    <td>{{ $result['roll_number'] }}</td>
                    <td>{{ $result['total_marks'] }}</td>
                    <td>{{ $result['obtained_marks'] }}</td>
                    <td>{{ $result['percentage'] }}%</td>
                    <td>{{ $result['grade'] }}</td>
                    <td class="{{ $result['status'] == 'Pass' ? 'status-pass' : 'status-fail' }}">
                        {{ $result['status'] }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <i class="fas fa-calendar me-1"></i> Generated on: {{ $date }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
