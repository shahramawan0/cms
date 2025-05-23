<!DOCTYPE html>
<html>
<head>
    <title>Time Table Report - Week {{ $weekNumber }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; margin-bottom: 20px; font-size: 18px; }
        h2 { margin-top: 20px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .class-header { background-color: #e9ecef; padding: 8px; margin-top: 15px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <h1>Time Table Report - Week {{ $weekNumber }}</h1>
    <p style="text-align: center;">Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>

    @foreach($groupedData as $className => $sections)
        @foreach($sections as $sectionName => $entries)
            <div class="class-header">
                <h2>{{ $className }} - {{ $sectionName }}</h2>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Time Slot</th>
                        <th>Course</th>
                        <th>Teacher</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $entry)
                        <tr>
                            <td>{{ $entry['date'] }}</td>
                            <td>{{ $entry['day'] }}</td>
                            <td>{{ $entry['slot'] }}</td>
                            <td>{{ $entry['course'] }}</td>
                            <td>{{ $entry['teacher'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            @if(!$loop->last)
                <div class="page-break"></div>
            @endif
        @endforeach
    @endforeach
</body>
</html>