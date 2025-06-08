<?php
session_start();
include "db_connect.php"; // Ensure this path is correct

$username_value = ""; // To pre-fill username if there's an error
$message = ""; // To display login messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $username_value = htmlspecialchars($username); // Keep username for re-display

    // Basic input validation for non-empty fields
    if (empty($username) || empty($password)) {
        $message = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        if ($stmt === false) {
            $message = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $db_username, $hashed_password);
            $stmt->fetch();

            if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
                // Login successful
                $_SESSION["loggedin"] = true;
                $_SESSION["user_id"] = $id;
                $_SESSION["username"] = $db_username; // Store username in session
                
                // Redirect to index.php after successful login
                header("Location: index.php");
                exit();
            } else {
                // Login failed
                $message = "Incorrect username or password.";
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
    <title>Login - G's Finance Tracker</title>
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

        .login-container {
            max-width: 450px;
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

        .login-container::before {
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
            font-size: 1.6rem;
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
            font-size: 1.8rem;
        }

        .brand-subtitle {
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

        .login-btn {
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

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 15px 35px rgba(212, 175, 55, 0.4),
                0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        .login-btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .login-btn.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: var(--primary-black);
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

        .register-link {
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.95rem;
            padding-top: 20px;
            border-top: 1px solid var(--border-subtle);
        }

        .register-link a {
            color: var(--gold-primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .register-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient-gold);
            transition: width 0.3s ease;
        }

        .register-link a:hover {
            color: var(--gold-light);
        }

        .register-link a:hover::after {
            width: 100%;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .login-container {
                padding: 40px 30px;
                margin: 10px;
                border-radius: 20px;
            }

            .brand-name {
                font-size: 1.6rem;
            }

            .brand-welcome {
                font-size: 1.4rem;
            }

            .form-group input[type="text"],
            .form-group input[type="password"] {
                padding: 14px 16px;
            }

            .login-btn {
                padding: 16px 20px;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            .brand-name {
                font-size: 1.4rem;
            }

            .brand-welcome {
                font-size: 1.2rem;
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
        .login-btn:focus,
        .password-toggle:focus {
            outline: 2px solid var(--gold-primary);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="brand-header">
            <h1 class="brand-welcome">Welcome Back to <span class="brand-name">G's Finance Tracker</span></h1>
            <p class="brand-subtitle">Sign in to continue managing your finances</p>
        </div>

        <form action="login.php" method="POST" class="login-form" id="loginForm">
            <h2 class="form-title">Sign In</h2>

            <?php if (!empty($message)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Username
                </label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" placeholder="Enter your username" value="<?php echo $username_value; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i>
                Sign In
            </button>

            <div class="register-link">
                Don't have an account? <a href="register.php">Create one here</a>
            </div>
        </form>
    </div>

    <script>
        // Enhanced JavaScript with improved UX
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const usernameInput = document.getElementById('username');

            // Password visibility toggle
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
                
                // Brief focus animation
                this.style.transform = 'translateY(-50%) scale(0.9)';
                setTimeout(() => {
                    this.style.transform = 'translateY(-50%) scale(1)';
                }, 150);
            });

            // Form submission with loading state
            loginForm.addEventListener('submit', function() {
                loginBtn.classList.add('loading');
                loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
            });

            // Auto-focus on first empty field
            if (usernameInput.value === '') {
                usernameInput.focus();
            } else {
                password.focus();
            }

            // Enhanced input interactions
            const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.01)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'Enter') {
                    loginForm.submit();
                }
            });
        });
    </script>
</body>
</html>