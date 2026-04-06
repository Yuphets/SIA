@extends('layouts.app')

@section('content')
<div id="adminPanel">
    <div class="admin-panel">
        <div class="card-title"><i class="fas fa-user-shield"></i> Admin Dashboard</div>

        <!-- User Table (clickable rows) -->
        <div class="overflow-x-auto mb-6">
            <table class="w-full expense-table">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">Name</th>
                        <th class="text-left py-2">Email</th>
                        <th class="text-left py-2">Role</th>
                        <th class="text-right py-2">Budget</th>
                        <th class="text-right py-2">Total Spent</th>
                        <th class="text-right py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="user-row cursor-pointer hover:bg-gray-50 transition-colors" data-user-id="{{ $user->id }}">
                        <td class="py-2">{{ $user->name }}</td>
                        <td class="py-2">{{ $user->email }}</td>
                        <td class="py-2">{{ ucfirst($user->role) }}</td>
                        <td class="py-2 text-right">₱{{ number_format($user->budget_limit, 2) }}</td>
                        <td class="py-2 text-right">₱{{ number_format($user->getTotalExpenses(), 2) }}</td>
                        <td class="py-2 text-right">
                            @php $percent = $user->getBudgetPercentage(); @endphp
                            @if($percent >= 100)
                                <span class="text-red-600">Over budget</span>
                            @elseif($percent >= 80)
                                <span class="text-orange-600">Near limit</span>
                            @else
                                <span class="text-green-600">Good</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pdf-buttons mb-4">
            <button id="downloadAllUsersPdfBtn" class="btn-primary"><i class="fas fa-download"></i> Download All Users Data (PDF)</button>
            <button id="downloadSingleUserPdfBtn" class="btn-primary"><i class="fas fa-download"></i> Download Current User's Data (PDF)</button>
            <button id="refreshAdminViewBtn" class="btn-outline"><i class="fas fa-sync-alt"></i> Refresh</button>
        </div>
    </div>

    <!-- Dashboard content will be loaded here for selected user -->
    <div id="userDashboardContainer"></div>

    <!-- Logs Section -->
    <div class="admin-panel" style="margin-top: 20px;">
        <div class="card-title"><i class="fas fa-history"></i> System Activity Logs (Digital Footprint)</div>
        <div class="log-filters">
            <select id="logUserFilter">
                <option value="all">All Users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            <input type="date" id="logDateFilter" placeholder="Filter by date">
            <button id="filterLogsBtn" class="btn-outline">Filter</button>
            <button id="resetLogsFilterBtn" class="btn-outline">Reset</button>
            <button id="downloadLogsPdfBtn" class="btn-primary"><i class="fas fa-download"></i> Download Logs (PDF)</button>
        </div>
        <div class="logs-container">
            <table class="expense-table" id="logsTable">
                <thead>
                    <tr><th>Timestamp</th><th>User</th><th>Action</th><th>Details</th></tr>
                </thead>
                <tbody id="logsTbody"></tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .user-row {
        cursor: pointer;
        transition: background 0.2s;
    }
    .user-row:hover {
        background: #f8fafc;
    }
    .user-row.active {
        background: #eef2ff;
        border-left: 3px solid var(--md-gold);
    }
</style>

<script>
    // Use the selected user from the controller (passed as $selectedUser)
    let currentUserId = {{ $selectedUser->id }};
    const users = @json($users);

    // Highlight active row
    function highlightActiveUser() {
        document.querySelectorAll('.user-row').forEach(row => {
            if (row.dataset.userId == currentUserId) {
                row.classList.add('active');
            } else {
                row.classList.remove('active');
            }
        });
    }

    // Load user dashboard via AJAX
    function loadUserDashboard(userId) {
        fetch(`/admin/user/${userId}/dashboard`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                const container = document.getElementById('userDashboardContainer');
                container.innerHTML = html;
                // Execute any <script> tags inside the loaded content
                const scripts = container.querySelectorAll('script');
                scripts.forEach(script => {
                    const newScript = document.createElement('script');
                    if (script.src) {
                        newScript.src = script.src;
                    } else {
                        newScript.textContent = script.textContent;
                    }
                    document.body.appendChild(newScript);
                    script.remove();
                });
            })
            .catch(error => console.error('Error loading dashboard:', error));
    }

    // Load logs via AJAX
    function loadLogs() {
        let filters = {
            user_id: document.getElementById('logUserFilter').value,
            date: document.getElementById('logDateFilter').value
        };
        let url = '{{ route('admin.logs.json') }}?' + new URLSearchParams(filters).toString();
        fetch(url)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('logsTbody');
                tbody.innerHTML = '';
                data.forEach(log => {
                    const row = tbody.insertRow();
                    row.insertCell(0).innerHTML = new Date(log.created_at).toLocaleString();
                    row.insertCell(1).innerHTML = log.username;
                    row.insertCell(2).innerHTML = `<span class="px-2 py-1 rounded text-xs 
                        ${log.action == 'ADD_EXPENSE' ? 'bg-green-100 text-green-700' : ''}
                        ${log.action == 'DELETE_EXPENSE' ? 'bg-red-100 text-red-700' : ''}
                        ${log.action == 'BUDGET_CHANGE' ? 'bg-blue-100 text-blue-700' : ''}
                        ${log.action == 'ADMIN_BUDGET_CHANGE' ? 'bg-purple-100 text-purple-700' : ''}">
                        ${log.action}</span>`;
                    row.insertCell(3).innerHTML = log.details;
                });
            })
            .catch(error => console.error('Error loading logs:', error));
    }

    // Attach click handlers to user rows (using event delegation in case rows are re-rendered, but they are static)
    document.querySelectorAll('.user-row').forEach(row => {
        row.addEventListener('click', () => {
            const userId = row.dataset.userId;
            if (userId) {
                currentUserId = parseInt(userId);
                loadUserDashboard(currentUserId);
                highlightActiveUser();
            }
        });
    });

    // Refresh button
    document.getElementById('refreshAdminViewBtn').onclick = () => loadUserDashboard(currentUserId);

    // Download all users PDF
    document.getElementById('downloadAllUsersPdfBtn').onclick = () => window.location.href = '{{ route('admin.download.all.users') }}';

    // Download single user PDF
    // Store the route pattern
const downloadUserPdfUrl = '{{ route("admin.user.download.pdf", ":id") }}';

// Download single user PDF
document.getElementById('downloadSingleUserPdfBtn').onclick = () => {
    const url = downloadUserPdfUrl.replace(':id', currentUserId);
    window.location.href = url;
};

    // Log filters
    document.getElementById('filterLogsBtn').onclick = loadLogs;
    document.getElementById('resetLogsFilterBtn').onclick = () => {
        document.getElementById('logUserFilter').value = 'all';
        document.getElementById('logDateFilter').value = '';
        loadLogs();
    };
    document.getElementById('downloadLogsPdfBtn').onclick = () => {
        let params = new URLSearchParams();
        if (document.getElementById('logUserFilter').value !== 'all') params.append('user_id', document.getElementById('logUserFilter').value);
        if (document.getElementById('logDateFilter').value) params.append('date', document.getElementById('logDateFilter').value);
        window.location.href = '{{ route('admin.download.logs') }}?' + params.toString();
    };

    // Initial load
    highlightActiveUser();
    loadUserDashboard(currentUserId);
    loadLogs();
</script>
@endsection