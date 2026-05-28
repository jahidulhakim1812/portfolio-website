<?php
// portfolio-details.php - Display full portfolio project details with perfect styling
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: portfolio.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM portfolios WHERE id = ? AND status = 1");
$stmt->execute([$id]);
$portfolio = $stmt->fetch();

if (!$portfolio) {
    header("Location: portfolio.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo htmlspecialchars($portfolio['title']); ?> | AR Tech Solutions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #7c3aed;
            --primary-dark: #5b21b6;
            --primary-light: #a78bfa;
            --secondary: #06b6d4;
            --accent: #f43f5e;
            --bg-light: #ffffff;
            --bg-dark: #0f0f12;
            --surface-light: #f8fafc;
            --surface-dark: #1e1e2a;
            --text-light: #1e293b;
            --text-dark: #e2e8f0;
            --text-muted-light: #64748b;
            --text-muted-dark: #94a3b8;
            --border-light: #e2e8f0;
            --border-dark: #2d3a4e;
            --shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.02);
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--text-light);
            transition: background 0.3s ease, color 0.2s ease;
            overflow-x: hidden;
        }
        body.dark {
            background: var(--bg-dark);
            color: var(--text-dark);
        }
        * {
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }
        h1, h2, h3, h4, .brand {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
        }
        /* Navbar */
        .glass-nav {
            position: relative;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(124,58,237,0.2);
            box-shadow: var(--shadow);
            padding: 0.5rem 1rem;
        }
        body.dark .glass-nav {
            background: rgba(15,15,18,0.85);
            border-color: rgba(124,58,237,0.4);
        }
        .glass-nav.scrolled {
            padding: 0.3rem 1rem;
            background: rgba(255,255,255,0.98);
        }
        body.dark .glass-nav.scrolled {
            background: rgba(10,10,15,0.98);
        }
        .navbar-brand {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .nav-link {
            font-weight: 600;
            color: var(--text-light) !important;
            margin: 0 0.5rem;
            position: relative;
        }
        body.dark .nav-link {
            color: var(--text-dark) !important;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: 0.3s;
        }
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        .dark-toggle {
            background: rgba(124,58,237,0.15);
            border: none;
            border-radius: 40px;
            width: 44px;
            height: 44px;
            color: var(--primary);
            transition: 0.2s;
            margin-left: 0.5rem;
        }
        body.dark .dark-toggle {
            background: rgba(124,58,237,0.3);
            color: var(--secondary);
        }
        /* Detail Card */
        .detail-card {
            background: var(--surface-light);
            border-radius: 32px;
            border: 1px solid var(--border-light);
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-top: 2rem;
        }
        body.dark .detail-card {
            background: var(--surface-dark);
            border-color: var(--border-dark);
        }
        /* Full image - no cropping */
        .detail-img {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain;
            background: var(--surface-light);
        }
        .detail-body {
            padding: 2rem;
        }
        .client-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 6px 18px;
            border-radius: 40px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .btn-back {
            border: 2px solid var(--primary);
            border-radius: 40px;
            padding: 10px 28px;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }
        body.dark .btn-back {
            color: var(--secondary);
            border-color: var(--secondary);
        }
        .btn-back:hover {
            background: var(--primary);
            color: white;
        }
        body.dark .btn-back:hover {
            background: var(--secondary);
            color: var(--bg-dark);
        }
        .btn-primary-custom {
            background: linear-gradient(95deg, var(--primary), var(--secondary));
            border: none;
            padding: 12px 32px;
            border-radius: 40px;
            font-weight: 600;
            color: white;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(124,58,237,0.3);
            color: white;
        }
        footer {
            background: #0f172a;
            color: #cbd5e1;
            padding: 2rem 0;
            margin-top: 3rem;
        }
        body.dark footer {
            background: #020617;
        }
        footer a {
            color: #94a3b8;
            text-decoration: none;
        }
        footer a:hover {
            color: var(--secondary);
        }
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary-dark);
            width: 44px;
            height: 44px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: 0.3s;
            z-index: 99;
            color: white;
        }
        .back-to-top.show { opacity: 1; }
        @media (max-width: 768px) {
            .detail-img { max-height: 300px; }
            .detail-body { padding: 1.5rem; }
            .client-badge { font-size: 0.8rem; }
            .btn-back, .btn-primary-custom { padding: 8px 20px; font-size: 0.9rem; }
        }
        .text-muted-custom {
            color: var(--text-muted-light);
        }
        body.dark .text-muted-custom {
            color: var(--text-muted-dark);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg glass-nav" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand" href="index.php">ARTECH</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
                <li class="nav-item"><a class="nav-link active" href="portfolio.php">Portfolio</a></li>
                <li class="nav-item"><a class="nav-link" href="chairman-speech.php">Chairman</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
            </ul>
            <button id="darkModeToggle" class="dark-toggle ms-2"><i class="fas fa-moon"></i></button>
        </div>
    </div>
</nav>

<main>
    <div class="container py-5">
        <div class="detail-card">
            <?php if(!empty($portfolio['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($portfolio['image_url']); ?>" class="detail-img" alt="<?php echo htmlspecialchars($portfolio['title']); ?>">
            <?php else: ?>
                <div class="detail-img d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); min-height: 300px;">
                    <i class="fas fa-image fa-5x text-white opacity-75"></i>
                </div>
            <?php endif; ?>
            <div class="detail-body">
                <div class="client-badge">
                    <i class="fas fa-building"></i> <?php echo htmlspecialchars($portfolio['client']); ?>
                </div>
                <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($portfolio['title']); ?></h1>
                
                <div class="description-text" style="font-size: 1.1rem; line-height: 1.7;">
                    <?php echo nl2br(htmlspecialchars($portfolio['description'])); ?>
                </div>

                <?php if(!empty($portfolio['project_url'])): ?>
                    <div class="mt-4">
                        <a href="<?php echo htmlspecialchars($portfolio['project_url']); ?>" target="_blank" class="btn-primary-custom">
                            View Live Project <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="mt-5">
                    <a href="portfolio.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Portfolio
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold">ARTECH</h5>
                <p class="text-muted">Augmenting reality with precision and innovation.</p>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="portfolio.php">Portfolio</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Connect</h5>
                <p><i class="fas fa-envelope me-2"></i> hello@artechsolutions.com</p>
                <p><i class="fas fa-phone me-2"></i> +1 (823) 456-5588</p>
                <p><i class="fas fa-map-marker-alt me-2"></i> 123 AR Avenue, Tech Valley</p>
            </div>
        </div>
        <hr class="opacity-25">
        <div class="text-center small">&copy; <?php echo date('Y'); ?> AR Tech Solutions. All rights reserved.</div>
    </div>
</footer>

<div class="back-to-top" id="backToTop">
    <i class="fas fa-arrow-up"></i>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Dark Mode Toggle
    function initDarkMode() {
        const toggle = document.getElementById('darkModeToggle');
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark');
            toggle.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            toggle.innerHTML = '<i class="fas fa-moon"></i>';
        }
        toggle.addEventListener('click', () => {
            document.body.classList.toggle('dark');
            const isDark = document.body.classList.contains('dark');
            localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
            toggle.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        });
    }

    // Navbar Scroll Effect
    function initNavbarScroll() {
        const navbar = document.querySelector('.glass-nav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) navbar.classList.add('scrolled');
            else navbar.classList.remove('scrolled');
        });
    }

    // Back to Top
    function initBackToTop() {
        const btn = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) btn.classList.add('show');
            else btn.classList.remove('show');
        });
        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    document.addEventListener('DOMContentLoaded', () => {
        initDarkMode();
        initNavbarScroll();
        initBackToTop();
    });
</script>
</body>
</html>