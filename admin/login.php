<?php
// admin/login.php - Updated with white subtitle and forgot password option
require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Direct string comparison (local testing only)
        if ($user && $password === $user['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Admin Access | AR Tech Solutions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* -------------------- NEW COLOR SYSTEM -------------------- */
        :root {
            /* Light mode – sunset coral & amber */
            --primary-l: #f97316;
            --primary-dark-l: #ea580c;
            --secondary-l: #f59e0b;
            --bg-gradient-l: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);
            --card-bg-l: rgba(255, 255, 255, 0.92);
            --text-l: #431407;
            --text-muted-l: #9a3412;
            --border-l: rgba(249, 115, 22, 0.3);
            --shadow-l: 0 20px 35px -10px rgba(0, 0, 0, 0.15);
            --glow-l: 0 0 15px rgba(249, 115, 22, 0.4);
            --white-text: #ffffff;
        }
        /* Dark mode – deep indigo + neon cyan */
        body.dark {
            --bg-gradient-l: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            --card-bg-l: rgba(15, 23, 42, 0.9);
            --text-l: #e2e8f0;
            --text-muted-l: #94a3b8;
            --border-l: rgba(6, 182, 212, 0.3);
            --shadow-l: 0 20px 35px -10px rgba(0, 0, 0, 0.4);
            --glow-l: 0 0 20px rgba(6, 182, 212, 0.5);
            --primary-l: #06b6d4;
            --primary-dark-l: #0891b2;
            --secondary-l: #22d3ee;
            --white-text: #e2e8f0;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-gradient-l);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
            transition: background 0.3s ease;
        }
        /* Animated floating card with neon glow */
        .login-card {
            background: var(--card-bg-l);
            backdrop-filter: blur(20px);
            border-radius: 3rem;
            padding: 2.2rem;
            box-shadow: var(--shadow-l), var(--glow-l);
            border: 1px solid var(--border-l);
            max-width: 460px;
            width: 100%;
            animation: float 5s ease-in-out infinite;
            transition: all 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-l), 0 0 25px var(--primary-l);
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        /* Logo pop animation */
        .logo-wrapper {
            width: 90px;
            height: 90px;
            background: linear-gradient(145deg, var(--primary-l), var(--secondary-l));
            border-radius: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            animation: popBounce 0.7s cubic-bezier(0.34, 1.2, 0.64, 1) forwards;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
        }
        .logo-wrapper i {
            font-size: 2.8rem;
            color: white;
            transition: 0.2s;
        }
        .logo-wrapper:hover {
            transform: scale(1.05);
        }
        @keyframes popBounce {
            0% { transform: scale(0) rotate(-10deg); opacity: 0; }
            60% { transform: scale(1.05) rotate(2deg); opacity: 1; }
            100% { transform: scale(1) rotate(0); opacity: 1; }
        }
        h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            color: var(--primary-l);
            letter-spacing: -0.5px;
        }
        /* White color for subtitle */
        .admin-subtitle {
            color: var(--white-text);
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: 600;
            color: var(--text-l);
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 3rem;
            padding: 0.8rem 1.2rem;
            border: 1px solid var(--border-l);
            background: rgba(255,255,255,0.2);
            color: var(--text-l);
            transition: 0.2s;
        }
        body.dark .form-control {
            background: rgba(15, 23, 42, 0.6);
            border-color: var(--border-l);
            color: var(--text-l);
        }
        .form-control:focus {
            border-color: var(--primary-l);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.2);
            outline: none;
        }
        body.dark .form-control:focus {
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.3);
        }
        .btn-login {
            background: linear-gradient(95deg, var(--primary-l), var(--secondary-l));
            border: none;
            border-radius: 3rem;
            padding: 0.8rem;
            font-weight: 700;
            color: white;
            width: 100%;
            transition: 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            filter: brightness(1.05);
        }
        .forgot-link {
            text-align: center;
            margin-top: 1rem;
        }
        .forgot-link a {
            color: var(--text-muted-l);
            text-decoration: none;
            font-size: 0.85rem;
            transition: 0.2s;
        }
        .forgot-link a:hover {
            color: var(--primary-l);
            text-decoration: underline;
        }
        .back-to-site a {
            color: var(--text-muted-l);
            text-decoration: none;
            transition: 0.2s;
            font-weight: 500;
        }
        .back-to-site a:hover {
            color: var(--primary-l);
        }
        .dark-mode-toggle {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: var(--card-bg-l);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-l);
            border-radius: 3rem;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--primary-l);
            font-size: 1.3rem;
            transition: 0.2s;
            z-index: 100;
            box-shadow: var(--shadow-l);
        }
        .dark-mode-toggle:hover {
            transform: scale(1.08);
            background: var(--primary-l);
            color: white;
        }
        .alert {
            border-radius: 3rem;
            background: rgba(249, 115, 22, 0.1);
            border-left: 4px solid var(--primary-l);
            color: var(--text-l);
        }
        body.dark .alert {
            background: rgba(6, 182, 212, 0.1);
            border-left-color: var(--primary-l);
        }
        /* Small responsive */
        @media (max-width: 480px) {
            .login-card { padding: 1.5rem; }
            .logo-wrapper { width: 70px; height: 70px; }
            .logo-wrapper i { font-size: 2rem; }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center">
            <div class="logo-wrapper">
           
            </div>
            <h3 class="mt-3">Admin Portal</h3>
            <p class="admin-subtitle">Secure access to dashboard</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-dismissible fade show mt-3">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-user me-2"></i>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter your username" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-login"><i class="fas fa-arrow-right-to-bracket me-2"></i>Login</button>
            <div class="forgot-link">
                <a href="#" id="forgotPasswordLink"><i class="fas fa-question-circle me-1"></i> Forgot Password?</a>
            </div>
        </form>

        <div class="back-to-site mt-4 text-center">
            <a href="../index.php"><i class="fas fa-globe me-1"></i> Return to AR Tech Solutions</a>
        </div>
    </div>

    <button id="darkModeToggle" class="dark-mode-toggle">
        <i class="fas fa-moon"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Perfect dark mode with local storage
        function initDarkMode() {
            const toggle = document.getElementById('darkModeToggle');
            const isDark = localStorage.getItem('adminDarkMode') === 'enabled';
            if (isDark) {
                document.body.classList.add('dark');
                toggle.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                document.body.classList.remove('dark');
                toggle.innerHTML = '<i class="fas fa-moon"></i>';
            }
            toggle.addEventListener('click', () => {
                document.body.classList.toggle('dark');
                const nowDark = document.body.classList.contains('dark');
                localStorage.setItem('adminDarkMode', nowDark ? 'enabled' : 'disabled');
                toggle.innerHTML = nowDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
            });
        }
        // Forgot password demo alert
        document.getElementById('forgotPasswordLink')?.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Please contact the system administrator to reset your password.\n\nDemo mode: For local testing, use username: admin, password: admin123');
        });
        document.addEventListener('DOMContentLoaded', initDarkMode);
    </script>
</body>
</html>