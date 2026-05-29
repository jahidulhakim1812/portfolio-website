<?php
// service-details.php - Display full service information with split layout: image left (full, no cropping), description right
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: services.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND status = 1");
$stmt->execute([$id]);
$service = $stmt->fetch();

if (!$service) {
    header("Location: services.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($service['title']); ?> | AR Tech Solutions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
            --text-muted-light: #64748b;
            --text-muted-dark: #94a3b8;
            --border-light: #e2e8f0;
            --border-dark: #2d3a4e;
            --shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--text-light);
            transition: all 0.3s ease;
        }
        body.dark {
            background: var(--bg-dark);
            color: var(--text-dark);
        }
        .glass-nav {
            position: relative;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(124,58,237,0.2);
            padding: 0.5rem 1rem;
        }
        body.dark .glass-nav {
            background: rgba(15,15,18,0.85);
        }
        /* Navbar brand with logo and text */
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            transition: opacity 0.2s;
        }
        .navbar-brand img {
            height: 44px;
            width: auto;
            max-width: 180px;
            display: inline-block;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
        }
        body.dark .navbar-brand img {
            filter: brightness(0.9);
        }
        @media (max-width: 576px) {
            .navbar-brand img {
                height: 34px;
            }
            .navbar-brand {
                font-size: 1.3rem;
                gap: 0.4rem;
            }
        }
        .nav-link {
            font-weight: 600;
            color: var(--text-light) !important;
            margin: 0 0.5rem;
        }
        body.dark .nav-link {
            color: var(--text-dark) !important;
        }
        .dark-toggle {
            background: rgba(124,58,237,0.15);
            border: none;
            border-radius: 40px;
            width: 44px;
            height: 44px;
            color: var(--primary);
        }
        /* Detail card split layout */
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
        /* Image column: full image visible, no cropping */
        .service-image-col {
            background: var(--surface-light);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        body.dark .service-image-col {
            background: var(--surface-dark);
        }
        .service-full-img {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain;  /* ensures the entire image is visible without cropping */
            display: block;
            border-radius: 24px;
            background: var(--bg-light);
            transition: all 0.2s;
        }
        body.dark .service-full-img {
            background: var(--bg-dark);
        }
        /* Fallback icon container (when no image) */
        .service-icon-fallback {
            width: 100%;
            min-height: 380px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-size: 5rem;
            border-radius: 24px;
        }
        /* Right side content styling */
        .detail-body {
            padding: 2rem 2rem 2rem 1rem;
        }
        @media (max-width: 768px) {
            .detail-body {
                padding: 1.8rem;
            }
            .service-image-col {
                padding: 1rem;
            }
            .service-full-img {
                max-height: 320px;
            }
            .service-icon-fallback {
                min-height: 280px;
                font-size: 3.5rem;
            }
        }
        .price-tag {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
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
            transition: all 0.2s;
        }
        .btn-back:hover {
            background: var(--primary);
            color: white;
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
            display: inline-block;
        }
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(124, 58, 237, 0.3);
        }
        .description-text {
            font-size: 1.1rem;
            line-height: 1.7;
            margin: 1.5rem 0;
        }
        /* Footer Styles */
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
    </style>
</head>
<body>

<!-- Navbar with Logo -->
<nav class="navbar navbar-expand-lg glass-nav" id="mainNavbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="uploads/logo.png" alt="ARTECH Logo">
            ARTECH
        </a>
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
            <div class="row g-0">
                <!-- LEFT COLUMN: Service Image (full, no cropping) -->
                <div class="col-md-6 service-image-col">
                    <?php if(!empty($service['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($service['image_url']); ?>" 
                             class="service-full-img" 
                             alt="<?php echo htmlspecialchars($service['title']); ?>">
                    <?php else: ?>
                        <div class="service-icon-fallback">
                            <i class="<?php echo htmlspecialchars($service['icon_class']); ?> fa-5x"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- RIGHT COLUMN: Description, Price, Buttons -->
                <div class="col-md-6">
                    <div class="detail-body">
                        <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($service['title']); ?></h1>
                        
                        <?php if(!empty($service['price']) && $service['price'] > 0): ?>
                            <div class="price-tag my-3">$<?php echo number_format($service['price'], 2); ?></div>
                        <?php else: ?>
                            <div class="text-muted my-3">Price on request</div>
                        <?php endif; ?>

                        <div class="description-text">
                            <?php echo nl2br(htmlspecialchars($service['description'])); ?>
                        </div>

                        <?php if(!empty($service['link_url'])): ?>
                            <div class="mt-4">
                                <a href="<?php echo htmlspecialchars($service['link_url']); ?>" class="btn-primary-custom">Get Started →</a>
                            </div>
                        <?php endif; ?>

                        <div class="mt-5">
                            <a href="services.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Services</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- ========== FULL FOOTER ========== -->
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
                    <li><a href="services.php">Services</a></li>
                    <li><a href="portfolio.php">Portfolio</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="about.php">About</a></li>
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

<div class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
    function initBackToTop() {
        const btn = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) btn.classList.add('show');
            else btn.classList.remove('show');
        });
        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }
    document.getElementById('floatingMsg')?.addEventListener('click', () => {
        alert('Live chat support coming soon! 📱');
    });

    document.addEventListener('DOMContentLoaded', () => {
        initDarkMode();
        initBackToTop();
    });
</script>
</body>
</html>