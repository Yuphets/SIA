<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expense Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #0B2B4F; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #0B2B4F; color: white; }
        .total { margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Expense Report - {{ $user->name }}</h1>
    <p>Email: {{ $user->email }}</p>
    <p>Budget Limit: ₱{{ number_format($user->budget_limit, 2) }}</p>
    <p>Total Expenses: ₱{{ number_format($expenses->sum('amount'), 2) }}</p>

    <table>
        <thead>
            <tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th></tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                <td>{{ $expense->category }}</td>
                <td>{{ $expense->description }}</td>
                <td>Php{{ number_format($expense->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="total">Total: Php{{ number_format($expenses->sum('amount'), 2) }}</div>
</body>
</html>
