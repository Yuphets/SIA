<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Expense Tracker | Mater Dei College</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #F4F7FC;
            font-family: 'Inter', sans-serif;
            color: #1A2C3E;
        }

        :root {
            --md-blue: #0B2B4F;
            --md-gold: #C6A43B;
            --md-gold-light: #F5E7C8;
            --md-white: #FFFFFF;
        }

        /* Navigation */
        .navbar {
            background: var(--md-blue);
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .logo span {
            color: var(--md-gold);
        }

        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .btn-nav {
            padding: 0.5rem 1.5rem;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-login {
            color: white;
            border: 1px solid white;
        }

        .btn-login:hover {
            background: white;
            color: var(--md-blue);
        }

        .btn-register {
            background: var(--md-gold);
            color: var(--md-blue);
        }

        .btn-register:hover {
            background: #b58f2c;
        }

        /* Hero Section */
        .hero {
            padding: 120px 2rem 60px;
            background: linear-gradient(135deg, #0B2B4F 0%, #1a4a7a 100%);
            color: white;
        }

        .hero .container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .hero h1 span {
            color: var(--md-gold);
        }

        .hero p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn-primary {
            background: var(--md-gold);
            color: var(--md-blue);
            padding: 0.75rem 2rem;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s;
            display: inline-block;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .btn-outline-light {
            border: 2px solid white;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-outline-light:hover {
            background: white;
            color: var(--md-blue);
        }

        .hero-image {
            text-align: center;
        }

        .hero-image i {
            font-size: 8rem;
            color: var(--md-gold);
        }

        /* Features Section */
        .features {
            padding: 80px 2rem;
            background: var(--md-white);
        }

        .features .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 3rem;
            color: var(--md-blue);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: #F8FAFE;
            padding: 2rem;
            border-radius: 24px;
            text-align: center;
            transition: transform 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-card i {
            font-size: 3rem;
            color: var(--md-gold);
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: var(--md-blue);
        }

        /* Footer */
        .footer {
            background: var(--md-blue);
            color: white;
            text-align: center;
            padding: 2rem;
        }

        @media (max-width: 768px) {
            .hero .container {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .hero-buttons {
                justify-content: center;
            }
            .navbar .container {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <i class="fas fa-chart-line"></i> Expense<span>Tracker</span>
                <span style="font-size: 0.8rem; margin-left: 0.5rem;">Mater Dei College</span>
            </div>
            <div class="nav-links">
                <a href="{{ route('login') }}" class="btn-nav btn-login">Login</a>
                <a href="{{ route('register') }}" class="btn-nav btn-register">Register</a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <div>
                <h1>Smart <span>Expense Tracking</span> System</h1>
                <p>Take control of your finances with our integrated expense tracking system. Monitor your spending, set budgets, and get real-time alerts when you're close to your limit.</p>
                <div class="hero-buttons">
                    <a href="{{ route('register') }}" class="btn-primary">Get Started Free</a>
                    <a href="#features" class="btn-outline-light">Learn More</a>
                </div>
            </div>
            <div class="hero-image">
                <i class="fas fa-chart-pie"></i>
            </div>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <h2 class="section-title">Key Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Real-time Analytics</h3>
                    <p>Visualize your spending with interactive charts and dashboards powered by Looker Studio.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-bell"></i>
                    <h3>Smart Alerts</h3>
                    <p>Get email and sound alerts when you reach 80% of your budget limit.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-file-pdf"></i>
                    <h3>PDF Reports</h3>
                    <p>Download detailed expense reports for any month or period.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Monthly History</h3>
                    <p>View and manage expenses from previous months with ease.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-user-shield"></i>
                    <h3>Admin Controls</h3>
                    <p>Admins can monitor all users and view detailed activity logs.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-clock"></i>
                    <h3>Budget Cooldown</h3>
                    <p>7-day cooldown period to prevent frequent budget changes.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <p>© 2024 Expense Tracker | Mater Dei College, Cabulijan, Tubigon, Bohol</p>
        <p style="margin-top: 0.5rem; font-size: 0.8rem;">Systems Integration & Architecture Project</p>
    </footer>
</body>
</html>
