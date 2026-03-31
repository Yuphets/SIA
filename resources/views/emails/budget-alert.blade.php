<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Budget Alert</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .header {
            background-color: #0B2B4F;
            padding: 20px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
            color: #1A2C3E;
        }
        .alert-box {
            background-color: {{ $percentage >= 100 ? '#FCE4E4' : '#FFF2DF' }};
            border-left: 4px solid {{ $percentage >= 100 ? '#D9534F' : '#F7B84D' }};
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .alert-box h2 {
            margin: 0 0 10px 0;
            color: {{ $percentage >= 100 ? '#D9534F' : '#E67E22' }};
        }
        .stats {
            background: #F8FAFE;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .stats p {
            margin: 5px 0;
        }
        .button {
            display: inline-block;
            background-color: #0B2B4F;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 40px;
            margin-top: 20px;
        }
        .footer {
            background-color: #F4F7FC;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #6C86A3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Expense Tracker</h1>
            <p>Mater Dei College</p>
        </div>
        <div class="content">
            <p>Dear {{ $user->name }},</p>
            <div class="alert-box">
                <h2>{{ $percentage >= 100 ? '⚠️ BUDGET EXCEEDED!' : '⚠️ BUDGET WARNING!' }}</h2>
                <p>
                    @if($percentage >= 100)
                        You have exceeded your budget limit by <strong>₱{{ number_format($total - $limit, 2) }}</strong>.
                    @else
                        You have used <strong>{{ number_format($percentage, 1) }}%</strong> of your budget.
                    @endif
                </p>
            </div>

            <div class="stats">
                <p><strong>Budget Limit:</strong> ₱{{ number_format($limit, 2) }}</p>
                <p><strong>Total Spent:</strong> ₱{{ number_format($total, 2) }}</p>
                <p><strong>Remaining:</strong> ₱{{ number_format(max(0, $limit - $total), 2) }}</p>
            </div>

            <p>Please log in to review your expenses and adjust your spending.</p>
            <a href="{{ url('/expenses') }}" class="button">View Dashboard</a>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Expense Tracker – Systems Integration & Architecture<br>
            Mater Dei College, Cabulijan, Tubigon, Bohol
        </div>
    </div>
</body>
</html>
