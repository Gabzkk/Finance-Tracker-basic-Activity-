<?php
// register.php
session_start(); // Start session to store messages

include "db_connect.php"; // Include your database connection

// Initialize variables for error and success messages
$username_error = $password_error = $confirm_password_error = $general_error = $success_message = "";
$username_value = ""; // To pre-fill username if there's an error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize inputs
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role = "user"; // Default role for new registrations

    $username_value = htmlspecialchars($username); // Keep username for re-display

    // --- Input Validation ---

    // Validate Username
    if (empty($username)) {
        $username_error = "Username is required.";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $username_error = "Username must be between 3 and 50 characters.";
    } elseif (!preg_match("/^[a-zA-Z0-9_.-]*$/", $username)) {
        $username_error = "Username can only contain letters, numbers, dots, dashes, and underscores.";
    }

    // Validate Password
    if (empty($password)) {
        $password_error = "Password is required.";
    } elseif (strlen($password) < 8) {
        $password_error = "Password must be at least 8 characters long.";
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/", $password)) {
        $password_error = "Password must include uppercase, lowercase, number, and special character.";
    }

    // Validate Password Confirmation
    if (empty($confirm_password)) {
        $confirm_password_error = "Please confirm your password.";
    } elseif ($password !== $confirm_password) {
        $confirm_password_error = "Passwords do not match.";
    }

    // --- If no validation errors, proceed with database checks and insertion ---
    if (empty($username_error) && empty($password_error) && empty($confirm_password_error)) {
        // Check if username already exists
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        if ($stmt_check === false) {
            $general_error = "Database error: " . $conn->error;
        } else {
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $username_error = "Username already exists. Please choose a different one.";
            } else {
                // Hash password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user into the database
                $stmt_insert = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                if ($stmt_insert === false) {
                    $general_error = "Database error: " . $conn->error;
                } else {
                    $stmt_insert->bind_param("sss", $username, $hashed_password, $role);

                    if ($stmt_insert->execute()) {
                        $_SESSION['success_message'] = "Welcome to G's Finance Tracker! Registration successful - you can now log in.";
                        // Redirect to clear POST data and prevent re-submission on refresh
                        header("Location: register.php");
                        exit();
                    } else {
                        $general_error = "Error: " . $stmt_insert->error;
                    }
                    $stmt_insert->close();
                }
            }
            $stmt_check->close();
        }
    }
}

// Display messages from session (after redirect)
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear message after displaying
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - G's Finance Tracker</title>
    <!-- Enhanced Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Enhanced Black and Gold Theme */
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

        .registration-container {
            max-width: 480px;
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

        .registration-container::before {
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

        .brand-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .brand-welcome {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--gold-primary);
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }

        .brand-name {
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            font-size: 2rem;
        }

        .brand-subtitle {
            font-size: 1rem;
            color: var(--text-secondary);
            margin-bottom: 15px;
        }

        .form-title {
            font-family: 'Inter', sans-serif;
            font-size: 1.5rem;
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

        .message.success {
            background: rgba(81, 207, 102, 0.15);
            color: var(--success-green);
            border-color: var(--success-green);
        }

        .message.error {
            background: rgba(255, 107, 107, 0.15);
            color: var(--error-red);
            border-color: var(--error-red);
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

        .input-wrapper {
            position: relative;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--border-subtle);
            border-radius: 12px;
            font-size: 1rem;
            background-color: var(--tertiary-black);
            color: var(--text-primary);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            font-family: 'Inter', sans-serif;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
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

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gold-primary);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .password-toggle:hover {
            color: var(--gold-light);
            background: rgba(212, 175, 55, 0.1);
        }

        .password-toggle:focus {
            outline: 2px solid var(--gold-primary);
            outline-offset: 2px;
        }

        .form-group.has-error input {
            border-color: var(--error-red);
            background: rgba(255, 107, 107, 0.05);
        }

        .help-block {
            color: var(--error-red);
            font-size: 0.85rem;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
            opacity: 0;
            transform: translateY(-10px);
            animation: errorSlideIn 0.3s ease-out forwards;
        }

        @keyframes errorSlideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .help-block::before {
            content: '⚠️';
            font-size: 0.9rem;
        }

        .register-btn {
            width: 100%;
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
            margin-bottom: 25px;
        }

        .register-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .register-btn:hover::before {
            left: 100%;
        }

        .register-btn:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 15px 35px rgba(212, 175, 55, 0.4),
                0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .register-btn:active {
            transform: translateY(-1px);
        }

        .login-link {
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.95rem;
            padding-top: 20px;
            border-top: 1px solid var(--border-subtle);
        }

        .login-link a {
            color: var(--gold-primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .login-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient-gold);
            transition: width 0.3s ease;
        }

        .login-link a:hover {
            color: var(--gold-light);
        }

        .login-link a:hover::after {
            width: 100%;
        }

        /* Password strength indicator */
        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: var(--border-subtle);
            border-radius: 2px;
            overflow: hidden;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .password-strength.show {
            opacity: 1;
        }

        .strength-bar {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background: var(--error-red); width: 25%; }
        .strength-fair { background: #ffa500; width: 50%; }
        .strength-good { background: #ffeb3b; width: 75%; }
        .strength-strong { background: var(--success-green); width: 100%; }

        /* Responsive Design */
        @media (max-width: 600px) {
            .registration-container {
                padding: 40px 30px;
                margin: 10px;
                border-radius: 20px;
            }

            .brand-name {
                font-size: 1.8rem;
            }

            .brand-welcome {
                font-size: 1.6rem;
            }

            .form-group input[type="text"],
            .form-group input[type="password"] {
                padding: 14px 16px;
            }

            .register-btn {
                padding: 16px 20px;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .registration-container {
                padding: 30px 20px;
            }

            .brand-name {
                font-size: 1.6rem;
            }

            .brand-welcome {
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
        .register-btn:focus,
        .password-toggle:focus {
            outline: 2px solid var(--gold-primary);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="brand-header">
            <h1 class="brand-welcome">Welcome to <span class="brand-name">G's Finance Tracker</span></h1>
            <p class="brand-subtitle">Take control of your financial future</p>
        </div>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="registration-form">
            <h2 class="form-title">Create Your Account</h2>

            <?php if (!empty($success_message)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($general_error)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $general_error; ?>
                </div>
            <?php endif; ?>

            <div class="form-group <?php echo (!empty($username_error)) ? 'has-error' : ''; ?>">
                <label for="username">
                    <i class="fas fa-user"></i> Username
                </label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" value="<?php echo $username_value; ?>" placeholder="Choose a unique username">
                </div>
                <?php if (!empty($username_error)): ?>
                    <span class="help-block"><?php echo $username_error; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group <?php echo (!empty($password_error)) ? 'has-error' : ''; ?>">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Create a strong password">
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <?php if (!empty($password_error)): ?>
                    <span class="help-block"><?php echo $password_error; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group <?php echo (!empty($confirm_password_error)) ? 'has-error' : ''; ?>">
                <label for="confirm_password">
                    <i class="fas fa-lock"></i> Confirm Password
                </label>
                <div class="input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password">
                    <button type="button" class="password-toggle" id="toggleConfirmPassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <?php if (!empty($confirm_password_error)): ?>
                    <span class="help-block"><?php echo $confirm_password_error; ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="register-btn">
                <i class="fas fa-user-plus"></i>
                Create Account
            </button>

            <div class="login-link">
                Already have an account? <a href="login.php">Sign in here</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggles
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const confirmPassword = document.getElementById('confirm_password');

            function togglePasswordVisibility(toggleBtn, inputField) {
                toggleBtn.addEventListener('click', function() {
                    const type = inputField.getAttribute('type') === 'password' ? 'text' : 'password';
                    inputField.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
                });
            }

            togglePasswordVisibility(togglePassword, password);
            togglePasswordVisibility(toggleConfirmPassword, confirmPassword);

            // Password strength indicator
            const passwordStrength = document.getElementById('passwordStrength');
            const strengthBar = document.getElementById('strengthBar');

            function checkPasswordStrength(password) {
                let strength = 0;
                let className = '';

                if (password.length >= 8) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;

                switch (strength) {
                    case 0:
                    case 1:
                    case 2:
                        className = 'strength-weak';
                        break;
                    case 3:
                        className = 'strength-fair';
                        break;
                    case 4:
                        className = 'strength-good';
                        break;
                    case 5:
                        className = 'strength-strong';
                        break;
                }

                return className;
            }

            password.addEventListener('input', function() {
                const value = this.value;
                if (value.length > 0) {
                    passwordStrength.classList.add('show');
                    const strengthClass = checkPasswordStrength(value);
                    strengthBar.className = 'strength-bar ' + strengthClass;
                } else {
                    passwordStrength.classList.remove('show');
                }
            });

            // Enhanced form validation feedback
            const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.parentElement.parentElement.classList.add('has-error');
                    } else {
                        this.parentElement.parentElement.classList.remove('has-error');
                    }
                });
            });
        });
    </script>
</body>
</html>