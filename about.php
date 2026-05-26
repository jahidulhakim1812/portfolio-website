<?php
// about.php - About Us page with company info, mission, vision, and team preview
require_once 'config.php';

// Fetch some stats for display (optional)
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers WHERE is_active = 1")->fetchColumn();
$totalProjects = $pdo->query("SELECT COUNT(*) FROM portfolios WHERE status = 1")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses WHERE status = 1")->fetchColumn();
$teamCount = $pdo->query("SELECT COUNT(*) FROM team_members WHERE status = 1")->fetchColumn();

// Fetch a few team members for preview
$teamPreview = $pdo->query("SELECT name, position, image_url FROM team_members WHERE status = 1 ORDER BY order_position ASC LIMIT 3")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>About Us | AR Tech Solutions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ========== IDENTICAL TO INDEX.PHP STYLES ========== */
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
        /* Navbar */
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
        /* Glass Card */
        .glass-card {
            background: var(--surface-light);
            border-radius: 28px;
            border: 1px solid var(--border-light);
            padding: 1.8rem;
            height: 100%;
            box-shadow: var(--shadow);
        }
        body.dark .glass-card {
            background: var(--surface-dark);
            border-color: var(--border-dark);
        }
        /* Stats Cards */
        .stat-box {
            background: var(--surface-light);
            border-radius: 1.5rem;
            padding: 1.2rem;
            text-align: center;
            border: 1px solid var(--border-light);
            transition: 0.2s;
        }
        body.dark .stat-box {
            background: var(--surface-dark);
        }
        .stat-box:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
        }
        /* Team Preview Card */
        .team-preview-card {
            background: var(--surface-light);
            border-radius: 28px;
            border: 1px solid var(--border-light);
            padding: 1.5rem;
            text-align: center;
            transition: 0.3s;
        }
        body.dark .team-preview-card {
            background: var(--surface-dark);
        }
        .team-preview-card:hover {
            transform: translateY(-5px);
        }
        .team-preview-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 1rem;
            border: 3px solid var(--primary);
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
        /* Force the muted text inside footer to white */
        footer .text-muted {
            color: white !important;
        }
        /* Floating message icon */
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
        /* Back to top */
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
        .text-muted-custom {
            color: var(--text-muted-light);
        }
        body.dark .text-muted-custom {
            color: var(--text-muted-dark);
        }
        .btn-primary-custom {
            background: linear-gradient(95deg, var(--primary), var(--secondary));
            border: none;
            padding: 12px 32px;
            border-radius: 40px;
            font-weight: 600;
            color: white;
            transition: 0.3s;
        }
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(124, 58, 237, 0.3);
        }
        .btn-outline-custom {
            border: 2px solid var(--primary);
            background: transparent;
            border-radius: 40px;
            padding: 8px 24px;
            color: var(--primary);
            font-weight: 500;
        }
        body.dark .btn-outline-custom {
            color: var(--secondary);
            border-color: var(--secondary);
        }
        .cta-modern {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 48px;
            padding: 3rem;
            text-align: center;
            color: white;
        }
    </style>
</head>
<body>

<!-- Navbar (identical to index.php) -->
<nav class="navbar navbar-expand-lg glass-nav" id="mainNavbar">
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
                <li class="nav-item"><a class="nav-link active" href="about.php">About</a></li>
            </ul>
            <button id="darkModeToggle" class="dark-toggle ms-2"><i class="fas fa-moon"></i></button>
        </div>
    </div>
</nav>

<main>
    <!-- Hero Section -->
    <section class="page-hero">
        <div class="container">
            <h1 class="display-4 fw-bold">About Us</h1>
            <p class="lead text-muted-custom">Pioneering the future of augmented and virtual reality</p>
        </div>
    </section>

    <!-- Company Story -->
    <div class="container py-5">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <div class="glass-card">
                    <h2 class="mb-3">Our Story</h2>
                    <p>Founded in 2018, AR Tech Solutions has grown from a small startup into a leading provider of immersive reality solutions. Our journey began with a simple mission: to make AR/VR technology accessible and impactful for businesses of all sizes.</p>
                    <p>Today, we collaborate with enterprises across the globe, delivering custom AR applications, VR training modules, and spatial computing solutions that drive real results. Our team of passionate engineers, designers, and visionaries works tirelessly to push the boundaries of what's possible.</p>
                    <p>We believe that immersive technology will redefine how we work, learn, and connect – and we're here to lead that transformation.</p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="glass-card">
                    <h2 class="mb-3">Our Mission</h2>
                    <p class="lead">Empower businesses with cutting-edge AR/VR solutions that enhance productivity, engagement, and innovation.</p>
                    <hr class="my-4">
                    <h2 class="mb-3">Our Vision</h2>
                    <p class="lead">To become the global benchmark for immersive technology, shaping a future where digital and physical realities seamlessly blend.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <section class="py-5" style="background: rgba(124,58,237,0.03);">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">By the Numbers</span>
                <h2 class="display-5 fw-bold mt-2">Our Impact</h2>
                <p class="text-muted-custom">Measurable results that speak for themselves</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stat-box">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <div class="stat-number"><?php echo number_format($totalCustomers); ?>+</div>
                        <p class="mb-0">Happy Customers</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <i class="fas fa-briefcase fa-3x text-primary mb-3"></i>
                        <div class="stat-number"><?php echo number_format($totalProjects); ?>+</div>
                        <p class="mb-0">Projects Delivered</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                        <div class="stat-number"><?php echo number_format($totalCourses); ?>+</div>
                        <p class="mb-0">Courses & Workshops</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Preview -->
    <?php if($teamPreview): ?>
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">Leadership</span>
                <h2 class="display-5 fw-bold mt-2">Meet Our Leaders</h2>
                <p class="text-muted-custom">The brilliant minds driving our innovation</p>
            </div>
            <div class="row g-4 justify-content-center">
                <?php foreach($teamPreview as $member): ?>
                <div class="col-md-4">
                    <div class="team-preview-card">
                        <img src="<?php echo htmlspecialchars($member['image_url']); ?>" class="team-preview-img" alt="<?php echo htmlspecialchars($member['name']); ?>">
                        <h4><?php echo htmlspecialchars($member['name']); ?></h4>
                        <p class="text-primary"><?php echo htmlspecialchars($member['position']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5">
                <a href="chairman-speech.php" class="btn btn-primary-custom">View Full Team <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Call to Action -->
    <section class="container py-4">
        <div class="cta-modern">
            <h2 class="fw-bold">Ready to innovate with us?</h2>
            <p class="mb-4 fs-5">Let's start a conversation about your next project.</p>
            <a href="contact.php" class="btn btn-light btn-lg rounded-pill px-5">Get in Touch <i class="fas fa-paper-plane ms-2"></i></a>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>

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

    // Floating Message Alert
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