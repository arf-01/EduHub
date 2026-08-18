<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Quiz Violation Alert</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 600px;
            background-color: #ffffff;
            margin: 40px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e1e4e8;
        }

        .header h2 {
            margin: 0;
            color: #0056b3;
            font-size: 26px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        h1 {
            font-size: 22px;
            color: #dc3545;
        }

        .content {
            font-size: 16px;
            line-height: 1.6;
        }

        .highlight {
            background-color: #fff3cd;
            padding: 12px 18px;
            border-left: 5px solid #ffc107;
            margin: 20px 0;
            border-radius: 5px;
            font-weight: 600;
        }

        .footer {
            margin-top: 40px;
            font-size: 13px;
            color: #888;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        .button {
            display: inline-block;
            background-color: #007bff;
            color: white !important;
            padding: 10px 20px;
            margin-top: 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>QUIZ APP</h2>
        </div>

        <h1>⚠️ Violation Alert</h1>

        <div class="content">
            <p>Dear Administrator,</p>

            <p>We have detected a potential violation during an online quiz session. The following student has been flagged:</p>

            <div class="highlight">
                Student ID: {{ $studentId }}
            </div>

            <p>Please investigate this issue through the admin panel and take appropriate action based on the guidelines.</p>

            <a href="#" class="button">View Violation Report</a>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Quiz App | All rights reserved<br/>
            1234 Quiz St, Knowledge City, Country
        </div>
    </div>
</body>
</html>
