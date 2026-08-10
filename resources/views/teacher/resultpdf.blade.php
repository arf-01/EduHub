<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 8px; text-align: left; }
        th { background-color: #ddd; }
    </style>
</head>
<body>
    <h2>Leaderboard for Quiz: {{ $quiz->title }}</h2>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Student ID</th>
                <th>Score</th>
               
            </tr>
        </thead>
        <tbody>
            @foreach($results as $index => $result)
                <tr>
                    {<td>{{ $result->rank }}</td>}
                    <td>{{ $result->student_id }}</td>
                    <td>{{ $result->score }}</td>
                   
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
