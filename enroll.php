<?php
// enroll.php - Collect student info, store enrollment, then initiate payment (demo mode)
require_once 'config.php';

// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND status = 1");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $payment_type = $_POST['payment_type'] ?? 'online';
    
    $amount = ($payment_type == 'online') ? $course['price'] : $course['price_offline'];
    
    if (empty($name) || empty($email) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO enrollments (course_id, student_name, student_email, student_phone, student_address, payment_method, amount, payment_status) VALUES (?, ?, ?, ?, ?, 'demo', ?, 'pending')");
        $stmt->execute([$course_id, $name, $email, $phone, $address, $amount]);
        $enrollment_id = $pdo->lastInsertId();
        
        // Demo mode: redirect to success page
        header('Location: payment-success.php?enrollment_id=' . $enrollment_id . '&demo=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll in <?php echo htmlspecialchars($course['title']); ?> | AR Tech Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #7c3aed;
            --primary-dark: #5b21b6;
            --secondary: #06b6d4;
            --bg-light: #ffffff;
            --bg-dark: #0f0f12;
            --surface-light: #f8fafc;
            --surface-dark: #1e1e2a;
            --text-light: #1e293b;
            --text-dark: #e2e8f0;
            --border-light: #e2e8f0;
            --border-dark: #2d3a4e;
            --shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--text-light);
        }
        body.dark {
            background: var(--bg-dark);
            color: var(--text-dark);
        }
        .glass-card {
            background: var(--surface-light);
            border-radius: 28px;
            border: 1px solid var(--border-light);
            padding: 2rem;
            box-shadow: var(--shadow);
        }
        body.dark .glass-card {
            background: var(--surface-dark);
            border-color: var(--border-dark);
        }
        .btn-primary-custom {
            background: linear-gradient(95deg, var(--primary), var(--secondary));
            border: none;
            padding: 12px 32px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(124, 58, 237, 0.3);
        }
        .glass-nav {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(124,58,237,0.2);
            padding: 0.5rem 1rem;
        }
        body.dark .glass-nav {
            background: rgba(15,15,18,0.85);
        }
        .navbar-brand {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .dark-toggle {
            background: rgba(124,58,237,0.15);
            border: none;
            border-radius: 40px;
            width: 44px;
            height: 44px;
            color: var(--primary);
        }
        .form-control, .form-select {
            background: rgba(0,0,0,0.03);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 0.75rem;
        }
        body.dark .form-control, body.dark .form-select {
            background: rgba(255,255,255,0.05);
            border-color: var(--border-dark);
            color: var(--text-dark);
        }
        @media (max-width: 768px) {
            .navbar-collapse {
                background: rgba(255,255,255,0.95);
                border-radius: 28px;
                padding: 1rem;
                margin-top: 1rem;
            }
            body.dark .navbar-collapse {
                background: rgba(20,20,30,0.95);
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg glass-nav">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-vr-cardboard me-2"></i>ARTECH</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="portfolio.php">Portfolio</a></li>
                <li class="nav-item"><a class="nav-link" href="chairman-speech.php">Chairman</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
            </ul>
            <button id="darkModeToggle" class="dark-toggle ms-2"><i class="fas fa-moon"></i></button>
        </div>
    </div>
</nav>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="glass-card">
                <h2 class="text-center mb-4">Enroll in <?php echo htmlspecialchars($course['title']); ?></h2>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4>Course Details</h4>
                        <p><strong>Course:</strong> <?php echo htmlspecialchars($course['title']); ?></p>
                        <p><strong>Duration:</strong> <?php echo htmlspecialchars($course['duration']); ?></p>
                        <p><strong>Level:</strong> <?php echo htmlspecialchars($course['level']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h4>Fee Structure</h4>
                        <p><strong>Online Course:</strong> $<?php echo number_format($course['price'], 2); ?></p>
                        <?php if($course['price_offline'] > 0): ?>
                        <p><strong>Offline Course:</strong> $<?php echo number_format($course['price_offline'], 2); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" name="phone" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Type *</label>
                            <select name="payment_type" class="form-select" required>
                                <option value="online">Online Course ($<?php echo number_format($course['price'], 2); ?>)</option>
                                <?php if($course['price_offline'] > 0): ?>
                                <option value="offline">Offline Course ($<?php echo number_format($course['price_offline'], 2); ?>)</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary-custom w-100">Proceed to Payment (Demo)</button>
                    </div>
                </form>
                <p class="text-muted mt-3 small text-center">* Demo mode: After submission, enrollment will be saved and you will be redirected to a success page.</p>
            </div>
        </div>
    </div>
</main>

<footer class="text-center py-4 text-muted small">© <?php echo date('Y'); ?> AR Tech Solutions</footer>

<script>
    const toggle = document.getElementById('darkModeToggle');
    if (localStorage.getItem('darkMode') === 'enabled') document.body.classList.add('dark');
    toggle.addEventListener('click', () => {
        document.body.classList.toggle('dark');
        localStorage.setItem('darkMode', document.body.classList.contains('dark') ? 'enabled' : 'disabled');
        toggle.innerHTML = document.body.classList.contains('dark') ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
    });
    if(document.body.classList.contains('dark')) toggle.innerHTML = '<i class="fas fa-sun"></i>';
</script>
</body>
</html>