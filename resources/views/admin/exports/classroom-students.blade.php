<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Classroom {{ $classroom->code }} Students</title>
    <style>table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:8px;text-align:left}</style>
</head>
<body>
    <h2>Classroom {{ $classroom->name }} ({{ $classroom->code }})</h2>
    <table>
        <thead>
            <tr>
                <th>Student Number</th>
                <th>Full Name</th>
                <th>Academic Level</th>
                <th>Enrollment Status</th>
                <th>Email</th>
                <th>Enrolled At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $s)
                <tr>
                    <td>{{ $s->student_number ?? '' }}</td>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->academic_level ?? '' }}</td>
                    <td>{{ $s->pivot->enrollment_status ?? '' }}</td>
                    <td>{{ $s->email }}</td>
                    <td>{{ $s->pivot->enrolled_at ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
