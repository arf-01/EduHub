<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Leaderboard - {{ $quiz->title }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #6366f1;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0 0 4px 0;
            color: #0f172a;
        }
        .header .meta {
            font-size: 11px;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border-bottom: 1.5px solid #cbd5e1;
            text-align: left;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .rank {
            font-weight: bold;
            color: #6366f1;
        }
        .score {
            font-weight: bold;
            font-family: monospace;
        }
        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Quiz Leaderboard: {{ $quiz->title }}</h1>
        <div class="meta">
            Generated on {{ date('d M Y, H:i') }} · Total Submissions: {{ count($results) }} · Total Questions: {{ $totalQuestions ?? 'N/A' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Rank</th>
                <th style="width: 40%;">Student ID (Roll)</th>
                <th style="width: 25%;">Score</th>
                <th style="width: 25%;">Submission Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results as $result)
                <tr>
                    <td class="rank">#{{ $result->rank }}</td>
                    <td><strong>{{ $result->student_id }}</strong></td>
                    <td class="score">{{ $result->score }} / {{ $totalQuestions ?? '' }}</td>
                    <td>{{ $result->created_at->format('d M Y, H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px; color: #94a3b8;">
                        No submissions recorded for this quiz.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        © {{ date('Y') }} Quiz System. Confidential Grade Records.
    </div>
</body>
</html>
