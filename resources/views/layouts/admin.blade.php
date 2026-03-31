<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard | Expense Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #F4F7FC;
            font-family: 'Inter', sans-serif;
            color: #1A2C3E;
        }
        :root {
            --md-blue: #0B2B4F;
            --md-gold: #C6A43B;
            --md-gold-light: #F5E7C8;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem;
        }
        .btn-primary {
            background: var(--md-blue);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--md-blue);
            color: var(--md-blue);
            padding: 0.5rem 1.5rem;
            border-radius: 40px;
            cursor: pointer;
        }
        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .card {
            background: white;
            border-radius: 24px;
            padding: 1.2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        .progress-bar {
            background: #E3EAF1;
            border-radius: 30px;
            height: 8px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 30px;
            transition: width 0.3s;
        }
        .logout-btn {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: var(--md-blue);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 40px;
            text-decoration: none;
            font-size: 0.85rem;
            z-index: 100;
        }
        .logout-btn:hover {
            background: #1a4a7a;
        }
    </style>
</head>
<body>
    <a href="{{ route('logout') }}" class="logout-btn"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <div class="container">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </div>
</body>
</html>
