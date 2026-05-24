<?php
// services.php - Display all services from database
require_once 'config.php';

// Fetch all services ordered by ID
$services = $pdo->query("SELECT * FROM services ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>AR Tech Solutions | Our Services</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ---------- SAME DESIGN SYSTEM AS OTHER PAGES ---------- */
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
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
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
        /* Navbar exactly as reference */
        .glass-nav {
            position: relative;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1030;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(124, 58, 237, 0.2);
            box-shadow: var(--shadow);
            transition: all 0.3s;
            padding: 0.5rem 1rem;
        }
        body.dark .glass-nav {
            background: rgba(15, 15, 18, 0.85);
            border-color: rgba(124, 58, 237, 0.4);
        }
        .glass-nav.scrolled {
            padding: 0.3rem 1rem;
            background: rgba(255, 255, 255, 0.98);
        }
        body.dark .glass-nav.scrolled {
            background: rgba(10, 10, 15, 0.98);
        }
        .navbar-brand {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.5px;
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
            background: rgba(124, 58, 237, 0.15);
            border: none;
            border-radius: 40px;
            width: 44px;
            height: 44px;
            color: var(--primary);
            transition: 0.2s;
            margin-left: 0.5rem;
        }
        body.dark .dark-toggle {
            background: rgba(124, 58, 237, 0.3);
            color: var(--secondary);
        }
        /* Page Hero */
        .page-hero {
            background: var(--surface-light);
            padding: 3rem 0 2rem;
            text-align: center;
            border-bottom: 1px solid var(--border-light);
        }
        body.dark .page-hero {
            background: var(--surface-dark);
        }
        /* Service Cards (identical to homepage) */
        .service-card {
            background: var(--surface-light);
            border-radius: 28px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
            padding: 1.8rem;
            height: 100%;
            text-align: center;
            box-shadow: var(--shadow);
        }
        body.dark .service-card {
            background: var(--surface-dark);
            border-color: var(--border-dark);
        }
        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.2);
        }
        .service-icon i {
            font-size: 2.8rem;
            color: var(--primary);
        }
        /* Footer */
        footer {
            background: #0f172a;
            color: #cbd5e1;
            padding: 3rem 0 1.5rem;
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
        /* Floating message & back to top */
        .floating-msg {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary);
            width: 56px;
            height: 56px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 99;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: 0.2s;
            color: white;
            font-size: 1.6rem;
        }
        .floating-msg:hover {
            transform: scale(1.1);
            background: var(--secondary);
        }
        .back-to-top {
            position: fixed;
            bottom: 100px;
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
        /* Responsive Grid */
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
            .service-col {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }
        .text-muted-custom {
            color: var(--text-muted-light);
        }
        body.dark .text-muted-custom {
            color: var(--text-muted-dark);
        }
        .btn-outline-custom {
            border: 2px solid var(--primary);
            background: transparent;
            border-radius: 40px;
            padding: 8px 24px;
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: 0.2s;
        }
        body.dark .btn-outline-custom {
            color: var(--secondary);
            border-color: var(--secondary);
        }
        .btn-outline-custom:hover {
            background: var(--primary);
            color: white;
        }
        body.dark .btn-outline-custom:hover {
            background: var(--secondary);
            color: var(--bg-dark);
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
                <li class="nav-item"><a class="nav-link" href="portfolio.php">Portfolio</a></li>
                <li class="nav-item"><a class="nav-link" href="chairman-speech.php">Chairman</a></li>
                <li class="nav-item"><a class="nav-link active" href="services.php">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
            </ul>
            <button id="darkModeToggle" class="dark-toggle ms-2"><i class="fas fa-moon"></i></button>
        </div>
    </div>
</nav>

<main>
    <!-- Hero Section -->
    <section class="page-hero">
        <div class="container">
            <h1 class="display-4 fw-bold">Our Services</h1>
            <p class="lead text-muted-custom">Cutting-edge AR/VR solutions for modern enterprises</p>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <?php if(count($services) > 0): ?>
                    <?php foreach($services as $service): ?>
                    <div class="col-6 col-md-6 col-lg-3 service-col">
                        <div class="service-card">
                            <div class="service-icon mb-3">
                                <i class="<?php echo htmlspecialchars($service['icon_class']); ?> fa-3x"></i>
                            </div>
                            <h4 class="fs-5 fw-bold"><?php echo htmlspecialchars($service['title']); ?></h4>
                            <p class="text-muted-custom small"><?php echo htmlspecialchars($service['description']); ?></p>
                            <?php if(!empty($service['link_url'])): ?>
                            <a href="<?php echo htmlspecialchars($service['link_url']); ?>" class="btn-outline-custom btn-sm mt-2">Learn More →</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-cogs fa-4x text-muted-custom mb-3"></i>
                        <h3>No services available</h3>
                        <p>Check back soon for our service offerings.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
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

<!-- Floating Message Icon -->
<div class="floating-msg" id="floatingMsg">
    <i class="fas fa-comment-dots"></i>
</div>

<!-- Back to Top -->
<div class="back-to-top" id="backToTop">
    <i class="fas fa-arrow-up"></i>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Dark Mode Toggle
    function initDarkMode() {
        const toggleBtn = document.getElementById('darkModeToggle');
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark');
            toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            toggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        }
        toggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark');
            const isDark = document.body.classList.contains('dark');
            localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
            toggleBtn.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        });
    }

    // Navbar Scroll Effect
    function initNavbarScroll() {
        const navbar = document.querySelector('.glass-nav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // Back to Top
    function initBackToTop() {
        const btn = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) btn.classList.add('show');
            else btn.classList.remove('show');
        });
        btn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Floating Message
    document.getElementById('floatingMsg')?.addEventListener('click', () => {
        alert('Live chat support coming soon! 📱');
    });

    document.addEventListener('DOMContentLoaded', () => {
        initDarkMode();
        initNavbarScroll();
        initBackToTop();
    });
</script>
</body>
</html>