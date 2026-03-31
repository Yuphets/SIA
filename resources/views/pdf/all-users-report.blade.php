<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Users Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #0B2B4F; }
        h2 { margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #0B2B4F; color: white; }
    </style>
</head>
<body>
    <h1>All Users Expense Report</h1>
    @foreach($users as $user)
        <h2>{{ $user->name }} ({{ $user->email }})</h2>
        <p>Budget: ₱{{ number_format($user->budget_limit, 2) }} | Total Spent: ₱{{ number_format($user->getTotalExpenses(), 2) }}</p>
        <table>
            <thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th></tr></thead>
            <tbody>
                @foreach($user->expenses as $expense)
                <tr>
                    <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                    <td>{{ $expense->category }}</td>
                    <td>{{ $expense->description }}</td>
                    <td>₱{{ number_format($expense->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>
