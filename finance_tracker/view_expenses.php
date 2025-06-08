<?php
session_start();
include "db_connect.php"; // Ensure this path is correct

// Authentication Check: Redirect to login if user is not authenticated
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$expenses = []; // Array to store fetched expenses
$message = "";   // Message for user, e.g., if no expenses found

// Fetch expenses for the logged-in user, ordered by most recent transaction date
$sql = "SELECT id, category, amount, payment_method, transaction_date, created_at FROM expenses WHERE user_id = ? ORDER BY transaction_date DESC, created_at DESC";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Handle database query preparation error
    $message = "Database query error: " . $conn->error;
} else {
    $stmt->bind_param("i", $user_id); // Bind user_id parameter
    $stmt->execute();                 // Execute the prepared statement
    $result = $stmt->get_result();   // Get the result set

    if ($result->num_rows > 0) {
        // Fetch all expenses into an array
        while ($row = $result->fetch_assoc()) {
            $expenses[] = $row;
        }
    } else {
        // Message if no expenses are found for the user
        $message = "You haven't logged any expenses yet. Go ahead and add your first one!";
    }
    $stmt->close(); // Close the statement
}
$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Expenses - G's Finance Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Consistent Black and Gold Theme CSS */
        :root {
            --primary-black: #0a0a0a;
            --secondary-black: #1a1a1a;
            --tertiary-black: #2a2a2a;
            --card-black: #1e1e1e;
            --gold-primary: #d4af37;
            --gold-secondary: #b8860b;
            --gold-light: #f4e4a1;
            --gold-dark: #9a7209;
            --text-primary: #ffffff;
            --text-secondary: #cccccc;
            --text-muted: #999999;
            --border-subtle: #333333;
            --border-accent: var(--gold-primary);
            --error-red: #ff6b6b;
            --success-green: #51cf66;
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
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 25% 25%, rgba(212, 175, 55, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(212, 175, 55, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        .container {
            max-width: 900px; /* Wider for table */
            width: 100%;
            background: var(--card-black);
            border-radius: 24px;
            padding: 50px 40px;
            box-shadow:
                0 25px 60px var(--shadow-strong),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border: 1px solid var(--border-subtle);
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-gold);
            border-radius: 24px 24px 0 0;
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
            margin-bottom: 40px;
        }

        .header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 600;
            color: var(--gold-primary);
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }

        .message {
            margin-bottom: 25px;
            padding: 16px 20px;
            border-radius: 12px;
            font-size: 0.95rem;
            text-align: center;
            animation: slideInDown 0.5s ease-out;
            border: 1px solid;
            position: relative;
            overflow: hidden;
        }

        .message::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .message:hover::before {
            left: 100%;
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.info {
            background: rgba(81, 150, 207, 0.15); /* Light blue background */
            color: #87CEEB; /* Sky blue text */
            border-color: #87CEEB;
        }

        /* Table styling */
        .expenses-table-container {
            overflow-x: auto; /* Enable horizontal scrolling on small screens */
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .expenses-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: var(--tertiary-black);
            border-radius: 12px;
            overflow: hidden; /* Ensures rounded corners apply to content */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            min-width: 600px; /* Ensure minimum width for desktop view */
        }

        .expenses-table th,
        .expenses-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid var(--border-subtle);
        }

        .expenses-table th {
            background-color: var(--secondary-black);
            color: var(--gold-primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0; /* Keeps header visible during scroll */
            z-index: 1;
        }

        .expenses-table tbody tr:last-child td {
            border-bottom: none;
        }

        .expenses-table tbody tr:nth-child(even) {
            background-color: #222; /* Slightly darker even rows */
        }

        .expenses-table tbody tr:hover {
            background-color: #333;
            transform: scale(1.005);
            transition: all 0.2s ease-in-out;
            box-shadow: inset 0 0 0 1px var(--gold-secondary);
        }

        .expenses-table td {
            color: var(--text-primary);
            font-size: 0.95rem;
        }

        .expenses-table td:first-child { /* Date column */
            color: var(--text-secondary);
        }

        .expenses-table td:nth-child(3) { /* Amount column */
            font-weight: 600;
            color: var(--success-green);
        }

        .expenses-table td:nth-child(4) { /* Payment method */
            color: var(--gold-light);
        }

        /* Buttons */
        .btn-group {
            display: flex;
            justify-content: center; /* Center buttons */
            gap: 20px;
            margin-top: 30px;
        }

        .btn {
            padding: 15px 25px;
            background: var(--gradient-gold);
            color: var(--primary-black);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            text-align: center;
            letter-spacing: 0.5px;
            font-family: 'Inter', sans-serif;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn i {
            margin-right: 10px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow:
                0 15px 35px rgba(212, 175, 55, 0.4),
                0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .btn.secondary {
            background: var(--tertiary-black);
            color: var(--gold-primary);
            border: 1px solid var(--border-subtle);
        }

        .btn.secondary:hover {
            background: var(--secondary-black);
            color: var(--gold-light);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 40px 30px;
                margin: 10px;
            }
            .header h1 {
                font-size: 1.8rem;
            }
            .expenses-table th, .expenses-table td {
                padding: 12px 15px;
            }
            .btn-group {
                flex-direction: column;
                gap: 15px;
            }
            .btn {
                padding: 14px 20px;
                font-size: 0.95rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }
            .header h1 {
                font-size: 1.6rem;
            }
            .expenses-table {
                min-width: unset;
            }
            .expenses-table th, .expenses-table td {
                font-size: 0.85rem;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Your Expenses</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message info">
                <i class="fas fa-info-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($expenses)): ?>
            <div class="expenses-table-container">
                <table class="expenses-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Logged On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($expense['transaction_date']); ?></td>
                                <td><?php echo htmlspecialchars($expense['category']); ?></td>
                                <td>₱<?php echo number_format($expense['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($expense['payment_method']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($expense['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="btn-group">
            <a href="add_expense.php" class="btn">
                <i class="fas fa-plus-circle"></i> Add New Expense
            </a>
            <a href="index.php" class="btn secondary">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</body>
</html>