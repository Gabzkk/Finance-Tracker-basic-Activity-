<?php
// index.php
session_start(); // Start the session to access session variables

// Include the database connection (optional for this page, but good practice if you fetch user-specific data)
include "db_connect.php";

// Check if the user is NOT logged in.
// We assume that a successful login/signup will set $_SESSION['loggedin'] to true
// and $_SESSION['username'] to the logged-in user's username.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit; // Stop further script execution
}

$username = $_SESSION['username'] ?? 'Guest'; // Get username from session, default to Guest if not set
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Tracker - Dashboard</title>
    <!-- Google Fonts for enhanced typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Enhanced Black and Gold Theme */

        :root {
            --primary-black: #0a0a0a;
            --secondary-black: #1a1a1a;
            --tertiary-black: #2a2a2a;
            --gold-primary: #d4af37;
            --gold-secondary: #b8860b;
            --gold-light: #f4e4a1;
            --gold-dark: #9a7209;
            --text-primary: #ffffff;
            --text-secondary: #cccccc;
            --text-muted: #999999;
            --border-subtle: #333333;
            --border-accent: var(--gold-primary);
            --shadow-soft: rgba(0, 0, 0, 0.3);
            --shadow-strong: rgba(0, 0, 0, 0.6);
            --gradient-gold: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary));
            --gradient-dark: linear-gradient(135deg, var(--primary-black), var(--secondary-black));
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gradient-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-primary);
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(212, 175, 55, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(212, 175, 55, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        .dashboard-container {
            max-width: 800px;
            width: 100%;
            background: var(--secondary-black);
            border-radius: 20px;
            padding: 60px 50px;
            box-shadow: 
                0 25px 60px var(--shadow-strong),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border: 1px solid var(--border-subtle);
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .dashboard-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-gold);
            border-radius: 20px 20px 0 0;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 50px;
        }

        .welcome-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--gold-primary);
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }

        .username-highlight {
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }

        .subtitle {
            font-size: 1.1rem;
            color: var(--text-secondary);
            line-height: 1.6;
            max-width: 500px;
            margin: 0 auto;
            font-weight: 400;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .action-card {
            background: var(--tertiary-black);
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            padding: 35px 25px;
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
            text-align: center;
            group: action-card;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .action-card:hover::before {
            left: 100%;
        }

        .action-card:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: var(--gold-primary);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(212, 175, 55, 0.2);
        }

        .card-icon {
            font-size: 2.5rem;
            color: var(--gold-primary);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .action-card:hover .card-icon {
            color: var(--gold-light);
            transform: scale(1.1);
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .card-description {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .logout-section {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid var(--border-subtle);
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 30px;
            background: transparent;
            border: 2px solid var(--border-subtle);
            border-radius: 50px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .logout-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .logout-btn:hover::before {
            left: 100%;
        }

        .logout-btn:hover {
            border-color: var(--gold-primary);
            color: var(--gold-primary);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .logout-btn i {
            font-size: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 40px 30px;
                margin: 10px;
                border-radius: 16px;
            }

            .welcome-title {
                font-size: 2rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .action-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .action-card {
                padding: 30px 20px;
            }

            .card-icon {
                font-size: 2rem;
            }

            .card-title {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 30px 20px;
            }

            .welcome-title {
                font-size: 1.8rem;
            }

            .action-card {
                padding: 25px 15px;
            }
        }

        /* Accessibility improvements */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        .action-card:focus,
        .logout-btn:focus {
            outline: 2px solid var(--gold-primary);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h1 class="welcome-title">
                Welcome back, <span class="username-highlight"><?php echo htmlspecialchars($username); ?></span>!
            </h1>
            <p class="subtitle">
                Take control of your financial journey with our comprehensive expense tracking tools and insights.
            </p>
        </div>

        <div class="action-grid">
            <a href="add_expense.php" class="action-card">
                <div class="card-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h3 class="card-title">Add New Expense</h3>
                <p class="card-description">
                    Record your latest expenses quickly and categorize them for better tracking.
                </p>
            </a>

            <a href="view_expenses.php" class="action-card">
                <div class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="card-title">View All Expenses</h3>
                <p class="card-description">
                    Analyze your spending patterns and get insights into your financial habits.
                </p>
            </a>
        </div>

        <div class="logout-section">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</body>
</html>