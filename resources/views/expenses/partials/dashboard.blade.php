@php
    if (!isset($events)) {
        $events = [];
    }
@endphp
<div class="stats-grid">
    <div class="stat-card"><h3><i class="fas fa-coins"></i> Total Expenses</h3><div class="stat-value">₱{{ number_format($totalExpenses, 2) }}</div></div>
    <div class="stat-card"><h3><i class="fas fa-bullhorn"></i> Recommended Limit</h3><div class="stat-value">₱{{ number_format($user->budget_limit, 2) }}</div></div>
    <div class="stat-card"><h3><i class="fas fa-chart-line"></i> Remaining</h3><div class="stat-value">₱{{ number_format($remainingBudget, 2) }}</div></div>
    <div class="stat-card"><h3><i class="fas fa-bell"></i> Alert Status</h3><div id="alertBadgeStatic">
        @if($budgetPercentage >= 100)
            🔴 OVER BUDGET
        @elseif($budgetPercentage >= 80)
            ⚠️ Near Limit ({{ number_format($budgetPercentage, 1) }}%)
        @else
            ✅ Within Limit
        @endif
    </div></div>
</div>

<div class="progress-section">
    <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
        <span><strong>Monthly budget utilization</strong></span>
        <div>
            <label><i class="fas fa-edit"></i> Set limit (₱):</label>
            <input type="number" id="limitInput" value="{{ $user->budget_limit }}" step="500" style="width:120px; margin:0 8px;">
            <button id="updateLimitBtn" class="btn-outline">Update</button>
        </div>
    </div>
    @if($cooldownDays > 0 && !$user->isAdmin())
        <div class="cooldown-info"><i class="fas fa-hourglass-half"></i> Budget change cooldown: You can change your budget again in {{ $cooldownDays }} day(s).</div>
    @endif
    <div class="progress-wrapper" style="margin-top:12px;">
        <div style="background:#E3EAF1; border-radius:30px; height:12px;"><div id="progressFill" style="width: {{ min($budgetPercentage, 100) }}%; height:100%; background: {{ $budgetPercentage >= 100 ? '#D9534F' : ($budgetPercentage >= 80 ? '#E67E22' : '#0B2B4F') }}; border-radius:30px;"></div></div>
    </div>
    <div id="dynamicAlertArea" class="alert-message" style="background:#F1F6FD;"><i class="fas fa-info-circle"></i> Monitoring budget...</div>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-title"><i class="fas fa-table"></i> Google Sheets · Expense Ledger</div>
        <div style="overflow-x: auto;">
            <table class="expense-table">
                <thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Amount (₱)</th><th></th></tr></thead>
                <tbody id="expenseTbody">
                    @foreach($expenses as $expense)
                    <tr>
                        <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                        <td><span style="background:#F0F3F9; padding:4px 8px; border-radius:30px;">{{ $expense->category }}</span></td>
                        <td>{{ $expense->description }}</td>
                        <td class="text-right">₱{{ number_format($expense->amount, 2) }}</td>
                        <td>
                            @if(!$isAdminView || ($isAdminView && $user->id == auth()->id()))
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="delete-btn"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(!$isAdminView || ($isAdminView && $user->id == auth()->id()))
            <form method="POST" action="{{ route('expenses.store') }}" class="add-form">
                @csrf
                <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required>
                <select name="category" required><option>Food</option><option>Transport</option><option>Utilities</option><option>Entertainment</option><option>Others</option></select>
                <input type="text" name="description" placeholder="Description" required>
                <input type="number" name="amount" placeholder="Amount" step="0.01" required>
                <button type="submit" class="btn-primary"><i class="fas fa-plus-circle"></i> Add</button>
            </form>
        @endif
        <div class="pdf-buttons">
            <a href="{{ $isAdminView ? route('admin.user.download.pdf', $user) : route('expenses.download.pdf') }}" class="btn-primary"><i class="fas fa-file-pdf"></i> Download Data (PDF)</a>
        </div>
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-chart-pie"></i> Looker Studio · Live Dashboard</div>
        <canvas id="categoryChart" style="max-height: 260px; width: 100%; margin-bottom: 16px;"></canvas>
    </div>
</div>

<div class="card" style="margin-bottom: 28px;">
    <div class="card-title"><i class="fas fa-calendar-alt"></i> Monthly Expense History</div>
    <div class="month-selector">
        <label for="monthPicker">Select Month:</label>
        <input type="month" id="monthPicker" value="{{ date('Y-m') }}">
        <button id="filterMonthBtn" class="btn-outline">View</button>
        <button id="downloadMonthPdfBtn" class="btn-primary"><i class="fas fa-download"></i> Download This Month's PDF</button>
    </div>
    <div style="overflow-x: auto;">
        <table class="expense-table"><thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Amount (₱)</th></tr></thead><tbody id="monthlyExpenseTbody"><tr><td colspan="4">Select a month to view</td></tr></tbody></table>
    </div>
</div>

<div class="dashboard-grid">
   <div class="card">
    <div class="card-title">
        <i class="fab fa-google"></i> Google Calendar · Upcoming Events
        @if($user->google_calendar_token && count($events) > 0)
            <button id="toggleEventsBtn" class="btn-outline text-sm ml-2" style="padding: 4px 12px;">Hide Events</button>
        @endif
    </div>
    @if($user->google_calendar_token)
        <div id="eventsContainer">
            @if(count($events) > 0)
                <div class="space-y-3" id="eventsList">
                    @foreach($events as $event)
                        <div class="border-b pb-2">
                            <strong>{{ $event->getSummary() }}</strong><br>
                            <span class="text-sm text-gray-600">
                                @if($event->getStart()->getDateTime())
                                    {{ \Carbon\Carbon::parse($event->getStart()->getDateTime())->format('M d, Y H:i') }}
                                    @if($event->getEnd())
                                        - {{ \Carbon\Carbon::parse($event->getEnd()->getDateTime())->format('H:i') }}
                                    @endif
                                @else
                                    All-day event: {{ $event->getStart()->getDate() }}
                                @endif
                            </span>
                            @if($event->getDescription())
                                <p class="text-sm mt-1">{{ \Illuminate\Support\Str::limit($event->getDescription(), 100) }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p>No upcoming events found.</p>
            @endif
        </div>
        <div class="mt-3">
            <a href="{{ route('google.calendar') }}" class="btn-outline text-sm">View Full Calendar</a>
        </div>
    @else
        <p class="text-gray-600">Connect your Google Calendar to see upcoming bill reminders and events.</p>
        @if(!$isAdminView)
            <a href="{{ route('google.auth') }}" class="btn-primary">Connect Google Calendar</a>
        @endif
    @endif
</div>

@if($user->google_calendar_token && count($events) > 0)
<script>
    const toggleBtn = document.getElementById('toggleEventsBtn');
    const eventsContainer = document.getElementById('eventsContainer');
    const eventsList = document.getElementById('eventsList');

    toggleBtn.addEventListener('click', () => {
        if (eventsList.style.display === 'none') {
            eventsList.style.display = 'block';
            toggleBtn.textContent = 'Hide Events';
        } else {
            eventsList.style.display = 'none';
            toggleBtn.textContent = 'Show Events';
        }
    });
</script>
@endif
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-envelope"></i> Email & Sound Alert System</div>
        <div id="warningBox" style="padding:14px; background:#FEF8E7; border-radius:24px;">
            <strong><i class="fas fa-bell"></i> Real-time alerts</strong>
            <div id="liveAlertMsg" style="margin-top:12px;">✅ No alerts.</div>
        </div>
        <div style="margin-top: 16px; font-size:0.85rem;"><i class="fas fa-microchip"></i> When budget ≥ 80%: popup + siren sound + email.</div>
    </div>
</div>

<footer><i class="fas fa-check-circle" style="color:var(--md-gold);"></i> Systems Integration & Architecture – Mater Dei College</footer>

<script>
    const chartData = @json($chartData);
    const budgetPercentage = {{ $budgetPercentage }};
    const totalExpenses = {{ $totalExpenses }};
    const remainingBudget = {{ $remainingBudget }};
    const budgetLimit = {{ $user->budget_limit }};

    // Chart initialization
    const ctx = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Food', 'Transport', 'Utilities', 'Entertainment', 'Others'],
            datasets: [{
                label: 'Expenses (₱)',
                data: [chartData.Food, chartData.Transport, chartData.Utilities, chartData.Entertainment, chartData.Others],
                backgroundColor: '#0B2B4F',
                borderRadius: 8
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Budget update
    document.getElementById('updateLimitBtn').onclick = function() {
        const newLimit = parseFloat(document.getElementById('limitInput').value);
        if (isNaN(newLimit) || newLimit <= 0) return;
        if (confirm(`Are you sure you want to change the budget limit from ₱${budgetLimit.toFixed(2)} to ₱${newLimit.toFixed(2)}? You will not be able to change it again for 7 days.`)) {
            fetch('{{ $isAdminView ? route('admin.user.budget.update', $user) : route('expenses.budget.update') }}', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ budget_limit: newLimit })
            }).then(() => location.reload());
        }
    };

    // Monthly history
    function refreshMonthlyView() {
        const month = document.getElementById('monthPicker').value;
        const url = '{{ $isAdminView ? route('admin.user.monthly.expenses', $user) : route('expenses.download.monthly') }}?month=' + month;
        fetch(url, { headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('monthlyExpenseTbody');
                tbody.innerHTML = '';
                if (data.length === 0) tbody.innerHTML = '<tr><td colspan="4">No expenses for this month</td></tr>';
                else data.forEach(exp => {
                    const row = tbody.insertRow();
                    row.insertCell(0).innerText = exp.expense_date;
                    row.insertCell(1).innerText = exp.category;
                    row.insertCell(2).innerText = exp.description;
                    row.insertCell(3).innerText = `₱${parseFloat(exp.amount).toFixed(2)}`;
                });
            });
    }

    document.getElementById('filterMonthBtn').onclick = refreshMonthlyView;
    document.getElementById('downloadMonthPdfBtn').onclick = function() {
        window.location.href = '{{ $isAdminView ? route('admin.user.download.monthly.pdf', $user) : route('expenses.download.monthly') }}?month=' + document.getElementById('monthPicker').value;
    };
    refreshMonthlyView();

    // Alert messages
    const alertArea = document.getElementById('dynamicAlertArea');
    if (budgetPercentage >= 100) {
        alertArea.innerHTML = '<i class="fas fa-bell"></i> ⚠️ CRITICAL: Exceeded budget by ₱' + (totalExpenses - budgetLimit).toFixed(2);
        alertArea.style.cssText = 'background:#FCE4E4; border-left:4px solid #D9534F;';
        document.getElementById('liveAlertMsg').innerHTML = 'Budget exceeded!';
    } else if (budgetPercentage >= 80) {
        alertArea.innerHTML = '<i class="fas fa-bell"></i> ⚠️ WARNING: Used ' + budgetPercentage.toFixed(1) + '% of budget (₱' + totalExpenses.toFixed(2) + ' / ₱' + budgetLimit.toFixed(2) + ')';
        alertArea.style.cssText = 'background:#FFF2DF; border-left:4px solid #F7B84D;';
        document.getElementById('liveAlertMsg').innerHTML = 'Near limit';
    } else {
        alertArea.innerHTML = '<i class="fas fa-bell"></i> ✅ Healthy spending: ' + budgetPercentage.toFixed(1) + '% used.';
        alertArea.style.cssText = 'background:#E9F5EB; border-left:4px solid #2C7A4D;';
        document.getElementById('liveAlertMsg').innerHTML = '✅ No alerts.';
    }
</script>
