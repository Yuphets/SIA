@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:underline">← Back to Dashboard</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="stat-card">
        <h3 class="text-gray-500 text-sm mb-2">User: {{ $user->name }}</h3>
        <div class="text-sm text-gray-600">{{ $user->email }}</div>
    </div>
    <div class="stat-card">
        <h3 class="text-gray-500 text-sm mb-2">Total Expenses</h3>
        <div class="text-3xl font-bold">₱{{ number_format($totalExpenses, 2) }}</div>
    </div>
    <div class="stat-card">
        <h3 class="text-gray-500 text-sm mb-2">Budget Limit</h3>
        <div class="text-3xl font-bold">₱{{ number_format($user->budget_limit, 2) }}</div>
        <form method="POST" action="{{ route('admin.user.budget.update', $user) }}" class="mt-2">
            @csrf
            @method('PUT')
            <div class="flex gap-2">
                <input type="number" name="budget_limit" value="{{ $user->budget_limit }}"
                       class="border rounded-lg px-2 py-1 text-sm w-32" step="500">
                <button type="submit" class="btn-outline text-sm">Update</button>
            </div>
        </form>
    </div>
    <div class="stat-card">
        <h3 class="text-gray-500 text-sm mb-2">Alert Status</h3>
        <div class="text-lg font-semibold">
            @if($budgetPercentage >= 100)
                <span class="text-red-600">🔴 OVER BUDGET</span>
            @elseif($budgetPercentage >= 80)
                <span class="text-orange-600">⚠️ Near Limit ({{ number_format($budgetPercentage, 1) }}%)</span>
            @else
                <span class="text-green-600">✅ Within Limit</span>
            @endif
        </div>
    </div>
</div>

<div class="card mb-8">
    <div class="progress-bar">
        <div class="progress-fill" style="width: {{ min($budgetPercentage, 100) }}%;
             background: {{ $budgetPercentage >= 100 ? '#D9534F' : ($budgetPercentage >= 80 ? '#E67E22' : '#0B2B4F') }}">
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="card">
        <h2 class="text-xl font-bold mb-4">Expense Ledger</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">Date</th>
                        <th class="text-left py-2">Category</th>
                        <th class="text-left py-2">Description</th>
                        <th class="text-right py-2">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                    <tr class="border-b">
                        <td class="py-2">{{ $expense->expense_date->format('Y-m-d') }}</td>
                        <td class="py-2">{{ $expense->category }}</td>
                        <td class="py-2">{{ $expense->description }}</td>
                        <td class="py-2 text-right">₱{{ number_format($expense->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 flex gap-2">
            <a href="{{ route('admin.user.edit', $user) }}" class="btn-primary">Edit User</a>
            <a href="{{ route('admin.user.download.pdf', $user) }}" class="btn-primary">Download User Data (PDF)</a>
        </div>
    </div>

    <div class="card">
        <h2 class="text-xl font-bold mb-4">Spending by Category</h2>
        <canvas id="expenseChart" height="250"></canvas>
    </div>
</div>

<div class="card mt-8">
    <h2 class="text-xl font-bold mb-4">Monthly Expense History</h2>
    <div class="flex gap-2 mb-4 items-center">
        <label>Select Month:</label>
        <input type="month" id="monthPicker" value="{{ date('Y-m') }}" class="border rounded px-3 py-1">
        <button onclick="loadMonthlyExpenses()" class="btn-outline">View</button>
        <button onclick="downloadMonthlyPDF()" class="btn-primary">Download PDF</button>
    </div>
    <div id="monthlyExpensesTable">
        <table class="w-full">
            <thead>
                <tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th> \\
            </thead>
            <tbody id="monthlyExpensesBody">
                <tr><td colspan="4">Select a month to view</td> \\
            </tbody>
        </table>
    </div>
</div>

<!-- Google Calendar Integration -->
<div class="card mt-8">
    <h2 class="text-xl font-bold mb-4">
        <i class="fab fa-google mr-2"></i> Google Calendar · Bill Reminders ({{ $user->name }})
    </h2>

    <iframe src="https://calendar.google.com/calendar/embed?src=en.philippines%23holiday%40group.v.calendar.google.com&ctz=Asia%2FManila"
            style="border: 0" width="100%" height="300" frameborder="0" scrolling="no">
    </iframe>
    <p class="text-sm text-gray-500 mt-2">
        * Replace with the user's personal calendar ID if needed.
    </p>
</div>

<script>
    const ctx = document.getElementById('expenseChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Food', 'Transport', 'Utilities', 'Entertainment', 'Others'],
            datasets: [{
                label: 'Expenses (₱)',
                data: [{{ $chartData['Food'] }}, {{ $chartData['Transport'] }},
                       {{ $chartData['Utilities'] }}, {{ $chartData['Entertainment'] }},
                       {{ $chartData['Others'] }}],
                backgroundColor: '#0B2B4F',
                borderRadius: 8
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    function loadMonthlyExpenses() {
        const month = document.getElementById('monthPicker').value;
        fetch(`{{ route('admin.user.monthly.expenses', $user) }}?month=${month}`)
            .then(res => res.json())
            .then(expenses => {
                const tbody = document.getElementById('monthlyExpensesBody');
                tbody.innerHTML = '';
                if (expenses.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4">No expenses for this month</td></tr>';
                    return;
                }
                expenses.forEach(exp => {
                    const row = tbody.insertRow();
                    row.insertCell(0).innerText = exp.expense_date;
                    row.insertCell(1).innerText = exp.category;
                    row.insertCell(2).innerText = exp.description;
                    row.insertCell(3).innerText = `₱${parseFloat(exp.amount).toFixed(2)}`;
                });
            });
    }

    function downloadMonthlyPDF() {
        const month = document.getElementById('monthPicker').value;
        window.location.href = `{{ route('admin.user.download.monthly.pdf', $user) }}?month=${month}`;
    }
</script>
@endsection
