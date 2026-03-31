<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Expense Tracker | Mater Dei College</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Copy the entire <style> from the provided HTML */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #F4F7FC;
            font-family: 'Inter', sans-serif;
            padding: 32px 24px;
            color: #1A2C3E;
        }
        :root {
            --md-blue: #0B2B4F;
            --md-gold: #C6A43B;
            --md-gold-light: #F5E7C8;
            --md-white: #FFFFFF;
            --md-shadow: 0 8px 20px rgba(0, 0, 0, 0.03), 0 2px 6px rgba(0, 0, 0, 0.05);
            --danger: #D9534F;
            --success: #2C7A4D;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 28px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--md-gold-light);
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
            background: var(--md-white);
            padding: 8px 18px;
            border-radius: 40px;
        }
        .logout-btn, .btn-outline {
            background: transparent;
            border: 1px solid var(--md-blue);
            padding: 6px 14px;
            border-radius: 30px;
            cursor: pointer;
        }
        .btn-primary {
            background: var(--md-blue);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--md-white);
            border-radius: 24px;
            padding: 20px 18px;
            box-shadow: var(--md-shadow);
        }
        .progress-section {
            background: var(--md-white);
            border-radius: 24px;
            padding: 20px 24px;
            margin-bottom: 28px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }
        .card {
            background: var(--md-white);
            border-radius: 28px;
            padding: 20px 24px;
            box-shadow: var(--md-shadow);
        }
        .card-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 18px;
            border-left: 4px solid var(--md-gold);
            padding-left: 14px;
        }
        .expense-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .expense-table th, .expense-table td {
            padding: 10px 6px;
            text-align: left;
            border-bottom: 1px solid #EDF2F7;
        }
        .delete-btn {
            background: none;
            border: none;
            color: #B22234;
            cursor: pointer;
        }
        .add-form {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            background: #F9FCFE;
            padding: 14px;
            border-radius: 20px;
        }
        .add-form input, .add-form select {
            padding: 10px 12px;
            border-radius: 40px;
            border: 1px solid #CDDFEC;
            flex: 1;
        }
        .alert-message {
            margin-top: 16px;
            padding: 12px 16px;
            border-radius: 18px;
            font-weight: 500;
        }
        .modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999; /* increased */
}
        .modal-content {
            background: white;
            padding: 24px 32px;
            border-radius: 32px;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 20px 35px rgba(0,0,0,0.2);
        }
        .month-selector {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .cooldown-info {
            font-size: 0.75rem;
            color: #E67E22;
            margin-top: 8px;
        }
        @media (max-width: 900px) { .dashboard-grid { grid-template-columns: 1fr; } }
        footer {
            margin-top: 32px;
            text-align: center;
            font-size: 0.75rem;
            color: #6D8AAC;
        }
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #2C3E50;
            color: white;
            padding: 12px 24px;
            border-radius: 40px;
            z-index: 2000;
            animation: fadeOut 4s forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; transform: translateY(0); }
            70% { opacity: 1; }
            100% { opacity: 0; transform: translateY(20px); visibility: hidden; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title-section">
                <h1>Expense Tracking System</h1>
                <p>Google Sheets · Forms · Looker · Calendar + Alerts</p>
            </div>
            <div class="user-info">
                <i class="fas fa-user-circle"></i> {{ Auth::user()->name }} ({{ Auth::user()->role }})
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif
        @if(session('budget_alert'))
            <div class="fixed bottom-4 right-4 alert-toast">
                <div class="bg-{{ session('budget_alert.type') === 'danger' ? 'red' : 'orange' }}-100 border-l-4 border-{{ session('budget_alert.type') === 'danger' ? 'red' : 'orange' }}-500 text-{{ session('budget_alert.type') === 'danger' ? 'red' : 'orange' }}-700 p-4 rounded shadow-lg">
                    <strong>{{ session('budget_alert.title') }}</strong>
                    <p>{{ session('budget_alert.message') }}</p>
                </div>
            </div>
            <script>
                setTimeout(() => { document.querySelector('.alert-toast')?.remove(); }, 8000);
                new Audio('/siren.mp3').play().catch(e => console.log('Audio not supported'));
            </script>
        @endif

        @yield('content')
    </div>
</body>
</html>
