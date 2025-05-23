<!DOCTYPE html>
<html>
<head>
    <title>Result - {{ $student_name }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f9f9f9; 
            margin: 10px 20px 20px 20px; 
            padding: 0; 
            color: #333;
            font-size: 14px;
            line-height: 1.3;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .header h2 { 
            margin: 0; 
            color: #222; 
            font-weight: 700;
            font-size: 26px;
        }
        .header h3 { 
            margin: 6px 0 15px; 
            color: #555; 
            font-weight: 600;
            font-size: 20px;
        }

        .student-info {
            display: table;
            width: 100%;
            background-color: #fff; 
            padding: 15px 20px; 
            border-radius: 10px; 
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
            margin-bottom: 20px;
            border-collapse: separate;
            border-spacing: 15px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .info-column {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        .info-row {
            margin-bottom: 10px;
            font-size: 13.5px;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
            display: flex;
            justify-content: space-between;
        }
        .info-row strong {
            font-weight: 600;
            color: #444;
            width: 140px;
            flex-shrink: 0;
        }
        .info-row span {
            flex-grow: 1;
            text-align: right;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
            background-color: #fff; 
            border-radius: 10px; 
            overflow: hidden; 
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12); 
            page-break-inside: avoid;
            break-inside: avoid;
            font-size: 13.5px;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 10px 9px; 
            text-align: left; 
        }
        th { 
            background-color: #007bff; 
            color: white; 
            font-weight: 600;
        }
        tr:nth-child(even) td {
            background-color: #fafafa;
        }
        .summary { 
            display: table;
            width: 100%;
            margin-bottom: 20px; 
            background-color: #fff; 
            padding: 15px 20px; 
            border-radius: 10px; 
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
            font-size: 14px;
            font-weight: 600;
            border-collapse: separate;
            border-spacing: 15px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .summary > div {
            display: table-cell;
            width: 33%;
            text-align: center;
        }
        .badge { 
            padding: 6px 14px; 
            border-radius: 4px; 
            color: white; 
            font-weight: 700;
            letter-spacing: 0.03em;
            min-width: 70px;
            text-align: center;
            display: inline-block;
        }
        .badge-primary { background-color: #007bff; }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .footer { 
            text-align: right; 
            font-size: 12px; 
            margin-top: 25px; 
            color: #666; 
            font-style: italic;
        }

        @media print {
            body {
                margin: 10px 0 20px 0;
            }
            .student-info, table, .summary {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $institute_name }}</h2>
        <h3>Result Card - {{ $course_name }}</h3>
    </div>

    <div class="student-info">
        <div class="info-column">
            <div class="info-row"><strong>Student Name:</strong> <span>{{ $student_name }}</span></div>
            <div class="info-row"><strong>Session:</strong> <span>{{ $session_name }}</span></div>
            <div class="info-row"><strong>Teacher:</strong> <span>{{ $teacher_name }}</span></div>
        </div>
        <div class="info-column">
            <div class="info-row"><strong>Roll No:</strong> <span>{{ $roll_number }}</span></div>
            <div class="info-row"><strong>Class:</strong> <span>{{ $class_name }} ({{ $section_name }})</span></div>
            <div class="info-row"><strong>Date:</strong> <span>{{ $date }}</span></div>
        </div>
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
                <th>{{ $obtained_marks }}</th>
                <th>{{ $total_marks }}</th>
                <th>{{ $total_weightage }}%</th>
                <th>
                    <span class="badge badge-{{ $status == 'Pass' ? 'success' : 'danger' }}">
                        {{ $status }}
                    </span>
                </th>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        <div><strong>Percentage:</strong> <span class="badge badge-primary">{{ $percentage }}%</span></div>
        <div><strong>Grade:</strong> <span class="badge badge-primary">{{ $grade }}</span></div>
        <div><strong>Status:</strong> <span class="badge badge-{{ $status == 'Pass' ? 'success' : 'danger' }}">{{ $status }}</span></div>
    </div>

    <div class="footer">
        Generated on: {{ $date }}
    </div>
</body>
</html>

