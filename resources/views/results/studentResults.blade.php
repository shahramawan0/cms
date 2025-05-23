@extends('layouts.app')

@section('content')
<div class="container">
    <div class="result-card border p-4 rounded shadow-lg bg-white">
        <div class="text-center border-bottom pb-3 mb-4">
            <h2 class="fw-bold text-primary">
                <i class="fas fa-graduation-cap"></i> Student Result Card
            </h2>
            <p class="mb-0 text-muted">{{ $summary['session'] }} - {{ $summary['class'] }} ({{ $summary['section'] }})</p>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="fw-bold text-dark border-bottom pb-2">Student Information</h5>
                <table class="table table-borderless small">
                    <tr><th>Name:</th><td>{{ $student->name }}</td></tr>
                    <tr><th>CNIC:</th><td>{{ $student->cnic ?? 'N/A' }}</td></tr>
                    <tr><th>Phone:</th><td>{{ $student->phone ?? 'N/A' }}</td></tr>
                    <tr><th>Email:</th><td>{{ $student->email ?? 'N/A' }}</td></tr>
                    <tr><th>Institute:</th><td>{{ $student->institute->name ?? 'N/A' }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5 class="fw-bold text-dark border-bottom pb-2">Academic Summary</h5>
                <table class="table table-borderless small">
                    <tr><th>Total Marks:</th><td>{{ $summary['total_marks'] }}</td></tr>
                    <tr><th>Obtained Marks:</th><td>{{ $summary['total_obtained'] }}</td></tr>
                    <tr><th>Percentage:</th><td>{{ $summary['percentage'] }}%</td></tr>
                    <tr><th>Grade:</th>
                        <td>
                            <span class="badge {{ $summary['grade'] == 'F' ? 'bg-danger' : 'bg-success' }}">
                                {{ $summary['grade'] }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Course-wise Results</h5>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Course</th>
                    <th>Teacher</th>
                    <th>Credit Hours</th>
                    <th>Obtained</th>
                    <th>Total</th>
                    <th>Percentage</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $index => $result)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $result['course_name'] }}</td>
                    <td>{{ $result['teacher_name'] }}</td>
                    <td>{{ $result['credit_hours'] }}</td>
                    <td>{{ $result['obtained_marks'] }}</td>
                    <td>{{ $result['total_marks'] }}</td>
                    <td>{{ $result['percentage'] }}%</td>
                    <td>
                        <span class="badge {{ $result['grade'] == 'F' ? 'bg-danger' : 'bg-success' }}">
                            {{ $result['grade'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="text-center mt-4">
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="fas fa-print"></i> Print Result Card
            </button>
            <a href="{{ route('results.student.pdf', $student->id) }}" class="btn btn-danger ms-2">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
        </div>

        <div class="mt-4 text-muted small text-center border-top pt-2">
            Generated on {{ now()->format('d M, Y h:i A') }}
        </div>
    </div>
</div>
@endsection
    