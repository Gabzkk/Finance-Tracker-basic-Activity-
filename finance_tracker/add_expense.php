<?php
session_start();
include "db_connect.php"; // Ensure this path is correct

$message = ""; // To store success or error messages

// 1. Authentication Check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit;
}

// Initialize variables for form pre-filling in case of validation errors
$category = ""; 
$amount = "";
$payment_method = "";
$transaction_date = date('Y-m-d'); // Default to today's date

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"]; // Get user ID from session
    $category = trim($_POST["category"]);
    $amount = trim($_POST["amount"]);
    $payment_method = trim($_POST["payment_method"]);
    $transaction_date = trim($_POST["transaction_date"]);

    // Input validation
    if (empty($category) || empty($amount) || empty($payment_method) || empty($transaction_date)) {
        $message = "Error: All fields are required.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $message = "Error: Amount must be a positive number.";
    } else {
        // Prepare and bind the statement
        $stmt = $conn->prepare("INSERT INTO expenses (user_id, category, amount, payment_method, transaction_date) VALUES (?, ?, ?, ?, ?)");
        if ($stmt === false) {
            $message = "Database error: " . $conn->error;
        } else {
            // "issss" means: integer, string, string, string, string
            $stmt->bind_param("issss", $user_id, $category, $amount, $payment_method, $transaction_date);

            if ($stmt->execute()) {
                $message = "Expense logged successfully!";
                // Optional: Clear form fields after successful submission for a fresh form
                $category = ""; 
                $amount = ""; 
                $payment_method = "";
                $transaction_date = date('Y-m-d'); // Reset date to today
            } else {
                $message = "Error adding expense: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense - G's Finance Tracker</title>
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
            max-width: 500px;
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
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--gold-primary);
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }

        .header p {
            font-size: 0.95rem;
            color: var(--text-secondary);
            margin-bottom: 15px;
        }

        .form-title {
            font-family: 'Inter', sans-serif;
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-primary);
            text-align: center;
            margin-bottom: 30px;
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

        .message.error {
            background: rgba(255, 107, 107, 0.15);
            color: var(--error-red);
            border-color: var(--error-red);
        }

        .message.success {
            background: rgba(81, 207, 102, 0.15);
            color: var(--success-green);
            border-color: var(--success-green);
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }

        .form-group label i {
            margin-right: 8px;
            color: var(--gold-secondary);
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group select {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--border-subtle);
            border-radius: 12px;
            font-size: 1rem;
            background-color: var(--tertiary-black);
            color: var(--text-primary);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            font-family: 'Inter', sans-serif;
            -webkit-appearance: none; /* Remove default styling for select */
            -moz-appearance: none;
            appearance: none;
        }

        .form-group select {
            padding-right: 40px; /* Space for custom arrow */
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23d4af37' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E"); /* Custom SVG arrow */
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
        }


        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--gold-primary);
            box-shadow: 
                0 0 0 4px rgba(212, 175, 55, 0.15),
                0 8px 25px rgba(0, 0, 0, 0.2);
            outline: none;
            transform: translateY(-1px);
        }

        .form-group input::placeholder {
            color: var(--text-muted);
            transition: opacity 0.3s ease;
        }

        .form-group input:focus::placeholder {
            opacity: 0.7;
        }

        .form-group option {
            background-color: var(--tertiary-black); /* Dark background for dropdown options */
            color: var(--text-primary);
        }

        .btn-group {
            display: flex;
            gap: 20px; /* Space between buttons */
            margin-top: 30px;
        }

        .btn {
            flex-grow: 1;
            padding: 18px 24px;
            background: var(--gradient-gold);
            color: var(--primary-black);
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            text-align: center;
            letter-spacing: 0.5px;
            font-family: 'Inter', sans-serif;
            position: relative;
            overflow: hidden;
            text-decoration: none; /* For anchor tags styled as buttons */
            display: flex; /* For centering content */
            justify-content: center; /* For centering content */
            align-items: center; /* For centering content */
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

        .btn.loading {
            pointer-events: none;
            opacity: 0.8;
            color: transparent; /* Hide text while loading */
        }

        .btn.loading i {
            display: none; /* Hide icon when loading */
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid transparent;
            border-top-color: var(--primary-black);
            border-left-color: var(--primary-black);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                padding: 40px 30px;
                margin: 10px;
                border-radius: 20px;
            }

            .header h1 {
                font-size: 1.6rem;
            }

            .form-group input, .form-group select {
                padding: 14px 16px;
            }

            .btn {
                padding: 16px 20px;
                font-size: 1rem;
            }

            .btn-group {
                flex-direction: column; /* Stack buttons on small screens */
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 1.4rem;
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

        .form-group input:focus,
        .form-group select:focus,
        .btn:focus {
            outline: 2px solid var(--gold-primary);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Add a New Expense</h1>
            <p>Log your financial outflows to keep your tracker up-to-date.</p>
        </div>

        <form action="add_expense.php" method="POST" id="expenseForm">
            <h2 class="form-title">Expense Details</h2>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo strpos($message, 'Error') === 0 ? 'error' : 'success'; ?>">
                    <i class="<?php echo strpos($message, 'Error') === 0 ? 'fas fa-exclamation-triangle' : 'fas fa-check-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="category"><i class="fas fa-tags"></i> Category</label>
                <select id="category" name="category" required>
                    <option value="">Select a Category</option>
                    <option value="Food" <?php echo ($category == 'Food') ? 'selected' : ''; ?>>Food</option>
                    <option value="Shopping" <?php echo ($category == 'Shopping') ? 'selected' : ''; ?>>Shopping</option>
                    <option value="Bills" <?php echo ($category == 'Bills') ? 'selected' : ''; ?>>Bills</option>
                    <option value="Transportation" <?php echo ($category == 'Transportation') ? 'selected' : ''; ?>>Transportation</option>
                    <option value="Others" <?php echo ($category == 'Others') ? 'selected' : ''; ?>>Others</option>
                </select>
            </div>

            <div class="form-group">
                <label for="amount"><i class="fas fa-money-bill-wave"></i> Amount</label>
                <input type="number" id="amount" name="amount" placeholder="e.g., 50.00" step="0.01" min="0.01" value="<?php echo htmlspecialchars($amount); ?>" required>
            </div>

            <div class="form-group">
                <label for="payment_method"><i class="fas fa-credit-card"></i> Payment Method</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="">Select Method</option>
                    <option value="Cash" <?php echo ($payment_method == 'Cash') ? 'selected' : ''; ?>>Cash</option>
                    <option value="Online Payment" <?php echo ($payment_method == 'Online Payment') ? 'selected' : ''; ?>>Online Payment</option>
                </select>
            </div>

            <div class="form-group">
                <label for="transaction_date"><i class="fas fa-calendar-alt"></i> Date</label>
                <input type="date" id="transaction_date" name="transaction_date" value="<?php echo htmlspecialchars($transaction_date); ?>" required>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn" id="addExpenseBtn">
                    <i class="fas fa-plus-circle"></i> Add Expense
                </button>
                <a href="index.php" class="btn secondary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const expenseForm = document.getElementById('expenseForm');
            const addExpenseBtn = document.getElementById('addExpenseBtn');
            const categorySelect = document.getElementById('category');
            const amountInput = document.getElementById('amount');
            const paymentMethodSelect = document.getElementById('payment_method');
            const transactionDateInput = document.getElementById('transaction_date');

            // Set today's date as default for the date input if not already set by PHP (handled by PHP now)
            // The PHP variable $transaction_date is already initialized to today's date
            // or populated from $_POST in case of error.

            // Form submission loading state
            expenseForm.addEventListener('submit', function() {
                addExpenseBtn.classList.add('loading');
                // The spinner and text change are handled by CSS
            });

            // Enhanced input interactions (copied from login.php for consistency)
            const inputsAndSelects = document.querySelectorAll('.form-group input, .form-group select');
            inputsAndSelects.forEach(element => {
                element.addEventListener('focus', function() {
                    this.style.transform = 'scale(1.01)';
                });
                
                element.addEventListener('blur', function() {
                    this.style.transform = 'scale(1)';
                });
            });

            // Re-select category, amount, and payment method if form submission fails (due to $message being present)
            // This ensures user input is retained on validation errors
            <?php if (!empty($message) && strpos($message, 'Error') === 0): ?>
                categorySelect.value = "<?php echo htmlspecialchars($category); ?>";
                paymentMethodSelect.value = "<?php echo htmlspecialchars($payment_method); ?>";
            <?php endif; ?>
        });
    </script>
</body>
</html>