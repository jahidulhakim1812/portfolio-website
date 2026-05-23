<?php
// index.php - Complete dynamic homepage with perfect dark mode, circle-only Bangladesh map
require_once 'config.php';

// Fetch all dynamic content
$sliders = $pdo->query("SELECT * FROM sliders WHERE status = 1 ORDER BY order_position ASC")->fetchAll();
$services = $pdo->query("SELECT * FROM services ORDER BY id LIMIT 4")->fetchAll();
$featuredProjects = $pdo->query("SELECT * FROM portfolios WHERE featured = 1 ORDER BY id DESC LIMIT 3")->fetchAll();
$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY id LIMIT 3")->fetchAll();
$chairmanSpeech = $pdo->query("SELECT * FROM chairman_speech WHERE is_active = 1 LIMIT 1")->fetch();

// Counters
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers WHERE is_active = 1")->fetchColumn();
$totalCountries = $pdo->query("SELECT COUNT(DISTINCT country) FROM customers WHERE is_active = 1")->fetchColumn();
$activeDistricts = $pdo->query("SELECT COUNT(DISTINCT district) FROM customers WHERE district IS NOT NULL AND country = 'Bangladesh' AND district != ''")->fetchColumn();

// Customer logos
$allCustomers = $pdo->query("SELECT customer_name, logo_url, country FROM customers WHERE is_active = 1 ORDER BY customer_name")->fetchAll();

// Active districts list (for map markers)
$activeDistrictList = $pdo->query("SELECT DISTINCT district FROM customers WHERE country = 'Bangladesh' AND district IS NOT NULL AND district != ''")->fetchAll(PDO::FETCH_COLUMN);

// Coordinates for divisions
$divisionCoordinates = [
    'Dhaka'       => [23.8103, 90.4125],
    'Chattogram'  => [22.3569, 91.7832],
    'Rajshahi'    => [24.3745, 88.6042],
    'Khulna'      => [22.8456, 89.5403],
    'Sylhet'      => [24.8993, 91.8719],
    'Barishal'    => [22.7010, 90.3535],
    'Rangpur'     => [25.7439, 89.2752],
    'Mymensingh'  => [24.7471, 90.4073]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AR Tech Solutions - Immersive Reality</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* ========== CSS VARIABLES (Light + Dark) ========== */
        :root {
            --primary: #5e2ced;
            --primary-dark: #4a1fdb;
            --secondary: #00c2d1;
            --bg-body: #ffffff;
            --bg-surface: #f8f9ff;
            --text-primary: #0a0b10;
            --text-muted: #5a5a6e;
            --card-shadow: 0 15px 35px rgba(0,0,0,0.05);
            --border-light: rgba(94,44,237,0.1);
            --navbar-bg: rgba(10,11,16,0.85);
            --footer-bg: #0a0b10;
            --map-tile: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
            --input-bg: #ffffff;
            --input-border: #ddd;
        }
        body.dark {
            --bg-body: #121212;
            --bg-surface: #1e1e2a;
            --text-primary: #f0f0f0;
            --text-muted: #b0b0c0;
            --card-shadow: 0 15px 35px rgba(0,0,0,0.3);
            --border-light: rgba(94,44,237,0.3);
            --navbar-bg: rgba(0,0,0,0.95);
            --footer-bg: #0a0a0f;
            --map-tile: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
            --input-bg: #2a2a2a;
            --input-border: #444;
        }
        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.2s ease;
            overflow-x: hidden;
        }
        /* Navbar */
        .main-navbar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1030;
            background: var(--navbar-bg);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            padding: 1rem 0;
        }
        .main-navbar .navbar-brand {
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
        }
        .main-navbar .navbar-brand i {
            color: var(--secondary);
        }
        .main-navbar .nav-link {
            color: rgba(255,255,255,0.85);
            font-weight: 500;
            margin: 0 0.5rem;
        }
        .main-navbar .nav-link:hover, .main-navbar .nav-link.active {
            color: var(--secondary);
        }
        /* Dark mode toggle - icon only, perfect */
        .dark-toggle {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            border-radius: 50%;
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.3s;
            font-size: 1.3rem;
        }
        .dark-toggle:hover {
            background: var(--secondary);
            transform: rotate(15deg);
        }
        /* Fullscreen Slider */
        .fullscreen-slider {
            width: 100%;
            height: 100vh;
            position: relative;
        }
        .heroSwiper {
            width: 100%;
            height: 100%;
        }
        .swiper-slide {
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .slide-content {
            color: white;
            text-shadow: 0 2px 15px rgba(0,0,0,0.3);
        }
        .slide-title {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease;
        }
        .btn-primary {
            background: var(--primary);
            border: none;
            padding: 12px 32px;
            border-radius: 40px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary);
        }
        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
        }
        /* Cards */
        .service-card, .project-card, .testimonial-card, .counter-card, .logo-card {
            background: var(--bg-surface);
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--border-light);
        }
        .service-card:hover, .project-card:hover, .logo-card:hover {
            transform: translateY(-8px);
        }
        .service-icon i {
            font-size: 3rem;
            color: var(--primary);
        }
        .client-name {
            color: var(--primary);
        }
        .testimonial-icon i {
            font-size: 2.5rem;
            color: var(--secondary);
        }
        .counter-num {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary);
        }
        .logo-card img {
            max-height: 80px;
            object-fit: contain;
            filter: grayscale(20%);
        }
        .logo-card:hover img {
            filter: grayscale(0%);
        }
        /* Map container */
        #bangladeshMap {
            height: 500px;
            width: 100%;
            border-radius: 20px;
            z-index: 1;
            background: #eef2f5;
        }
        body.dark #bangladeshMap {
            background: #1a1a2a;
        }
        .map-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 15px;
            background: var(--bg-surface);
            padding: 8px 20px;
            border-radius: 40px;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .map-legend span {
            display: inline-block;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            margin-right: 8px;
            vertical-align: middle;
        }
        .map-legend .active-marker {
            background-color: #2ecc71;
            box-shadow: 0 0 6px #2ecc71;
        }
        .cta-section {
            background: linear-gradient(135deg, var(--primary), #7a4af5);
        }
        footer {
            background: var(--footer-bg);
            color: #ccc;
            padding: 2rem 0;
        }
        footer a {
            color: #aaa;
            transition: 0.2s;
        }
        footer a:hover {
            color: var(--secondary);
        }
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary);
            color: white;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: 0.3s;
            z-index: 99;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .back-to-top.show {
            opacity: 1;
        }
        /* Additional dark mode fixes for text and inputs */
        .text-muted {
            color: var(--text-muted) !important;
        }
        .bg-light {
            background-color: var(--bg-surface) !important;
        }
        .form-control, .form-control:focus {
            background-color: var(--input-bg);
            border-color: var(--input-border);
            color: var(--text-primary);
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 768px) {
            .slide-title { font-size: 2rem; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg main-navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-vr-cardboard"></i> AR Tech Solutions</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="portfolio.php">Portfolio</a></li>
                <li class="nav-item"><a class="nav-link" href="chairman-speech.php">Chairman's Speech</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="location.php">Location</a></li>
            </ul>
            <button id="darkModeToggle" class="dark-toggle ms-2"><i class="fas fa-moon"></i></button>
        </div>
    </div>
</nav>

<main>
    <!-- 1. Fullscreen Slider -->
    <section class="fullscreen-slider">
        <div class="swiper heroSwiper">
            <div class="swiper-wrapper">
                <?php foreach($sliders as $slide): ?>
                <div class="swiper-slide" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo htmlspecialchars($slide['image_url']); ?>');">
                    <div class="slide-content container">
                        <h1 class="slide-title"><?php echo htmlspecialchars($slide['title']); ?></h1>
                        <p class="slide-subtitle"><?php echo htmlspecialchars($slide['subtitle']); ?></p>
                        <?php if($slide['button_text']): ?>
                        <a href="<?php echo htmlspecialchars($slide['button_link']); ?>" class="btn btn-primary btn-lg"><?php echo htmlspecialchars($slide['button_text']); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
    </section>

    <!-- 2. Our Services -->
    <section class="services-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Our Services</h2>
                <p class="text-muted">Cutting-edge AR solutions for modern enterprises</p>
            </div>
            <div class="row g-4">
                <?php foreach($services as $service): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="service-card text-center p-4 h-100">
                        <div class="service-icon"><i class="<?php echo htmlspecialchars($service['icon_class']); ?>"></i></div>
                        <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                        <a href="<?php echo htmlspecialchars($service['link_url']); ?>" class="btn btn-outline-primary btn-sm">Learn More →</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 3. Featured Projects -->
    <section class="projects-section py-5" style="background: var(--bg-surface);">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Featured Projects</h2>
                <p class="text-muted">Real-world impact with immersive technology</p>
            </div>
            <div class="row g-4">
                <?php foreach($featuredProjects as $project): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="project-card h-100">
                        <div class="project-img" style="height: 220px; background-size: cover; background-position: center; background-image: url('<?php echo htmlspecialchars($project['image_url']); ?>');"></div>
                        <div class="p-3">
                            <h4><?php echo htmlspecialchars($project['title']); ?></h4>
                            <p class="client-name"><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($project['client']); ?></p>
                            <p><?php echo htmlspecialchars(substr($project['description'], 0, 100)) . '...'; ?></p>
                            <a href="portfolio.php" class="btn btn-outline-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="portfolio.php" class="btn btn-primary">View All Projects</a>
            </div>
        </div>
    </section>

    <!-- 4. Chairman Speech Preview -->
    <?php if($chairmanSpeech): ?>
    <section class="chairman-preview py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-center">
                    <img src="<?php echo htmlspecialchars($chairmanSpeech['image_url']); ?>" class="rounded-circle img-fluid shadow" style="width: 200px; height: 200px; object-fit: cover; border: 4px solid var(--primary);">
                </div>
                <div class="col-md-8">
                    <h3>A Word from Our Chairman</h3>
                    <p class="lead"><?php echo htmlspecialchars(substr($chairmanSpeech['speech_text'], 0, 200)) . '...'; ?></p>
                    <a href="chairman-speech.php" class="btn btn-outline-primary">Read Full Speech <i class="fas fa-microphone-alt"></i></a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- 5. Dynamic Counters -->
    <section class="counters-section py-5 text-center" style="background: var(--bg-surface);">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="counter-card p-4">
                        <i class="fas fa-users fa-3x text-primary"></i>
                        <h2 class="counter-num" data-target="<?php echo $totalCustomers; ?>">0</h2>
                        <p>Happy Customers</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="counter-card p-4">
                        <i class="fas fa-globe fa-3x text-primary"></i>
                        <h2 class="counter-num" data-target="<?php echo $totalCountries; ?>">0</h2>
                        <p>Countries Served</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="counter-card p-4">
                        <i class="fas fa-map-marker-alt fa-3x text-primary"></i>
                        <h2 class="counter-num" data-target="<?php echo $activeDistricts; ?>">0</h2>
                        <p>Bangladeshi Districts Covered</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Bangladesh Map - Only Circles for Active Areas -->
    <section class="map-section py-5">
        <div class="container">
            <div class="text-center mb-4">
                <h2>Our Reach in Bangladesh</h2>
                <p>Green circles show divisions with active AR deployments.</p>
            </div>
            <div class="map-legend">
                <div><span class="active-marker"></span> Active Service Area</div>
            </div>
            <div id="bangladeshMap"></div>
        </div>
    </section>

    <!-- 7. Client Logos -->
    <section class="customers-logos py-5" style="background: var(--bg-surface);">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Trusted by Industry Leaders</h2>
                <p class="text-muted">Our global and local partners</p>
            </div>
            <div class="row g-4 justify-content-center align-items-center">
                <?php foreach($allCustomers as $cust): ?>
                <div class="col-6 col-md-3 col-lg-2 text-center">
                    <div class="logo-card p-3">
                        <img src="<?php echo htmlspecialchars($cust['logo_url']); ?>" alt="<?php echo htmlspecialchars($cust['customer_name']); ?>" class="img-fluid">
                        <p class="mt-2 small text-muted"><?php echo htmlspecialchars($cust['customer_name']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 8. Testimonials -->
    <section class="testimonials-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Client Testimonials</h2>
                <p class="text-muted">What our partners say about us</p>
            </div>
            <div class="row g-4">
                <?php foreach($testimonials as $testimonial): ?>
                <div class="col-md-4">
                    <div class="testimonial-card p-4 h-100">
                        <div class="testimonial-icon"><i class="fas fa-quote-left"></i></div>
                        <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['testimonial_text']); ?>"</p>
                        <div class="testimonial-author">
                            <h5><?php echo htmlspecialchars($testimonial['client_name']); ?></h5>
                            <span><?php echo htmlspecialchars($testimonial['client_title'] . ', ' . $testimonial['company']); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 9. CTA -->
    <section class="cta-section py-5 text-center text-white">
        <div class="container">
            <h3 class="mb-3">Ready to Transform Your Business with AR?</h3>
            <p class="mb-4">Let's discuss your next immersive project.</p>
            <a href="contact.php" class="btn btn-light btn-lg">Get in Touch <i class="fas fa-paper-plane"></i></a>
        </div>
    </section>
</main>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5><i class="fas fa-vr-cardboard"></i> AR Tech Solutions</h5>
                <p>Augmenting reality with precision and innovation.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="portfolio.php">Portfolio</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Connect</h5>
                <p><i class="fas fa-envelope"></i> hello@artechsolutions.com</p>
                <p><i class="fas fa-phone"></i> +1 (823) 456-5588</p>
            </div>
        </div>
        <hr class="bg-secondary">
        <div class="text-center small">&copy; <?php echo date('Y'); ?> AR Tech Solutions</div>
    </div>
</footer>

<!-- Back to Top Button -->
<div class="back-to-top" id="backToTop">
    <i class="fas fa-arrow-up"></i>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Initialize Swiper
    new Swiper('.heroSwiper', {
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
        effect: 'fade',
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
    });

    // Counters with Intersection Observer
    function initCounters() {
        const counters = document.querySelectorAll('.counter-num');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.getAttribute('data-target'));
                    let current = 0;
                    const increment = target / 50;
                    const update = () => {
                        current += increment;
                        if (current < target) {
                            counter.innerText = Math.ceil(current);
                            requestAnimationFrame(update);
                        } else {
                            counter.innerText = target;
                        }
                    };
                    update();
                    observer.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(c => observer.observe(c));
    }

    // Bangladesh Map: only green circles, dynamic tile layer based on dark mode
    let map;
    function initMap() {
        const activeDivisions = <?php echo json_encode($activeDistrictList); ?>;
        const coords = <?php echo json_encode($divisionCoordinates); ?>;
        const isDark = document.body.classList.contains('dark');
        const tileUrl = isDark ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png' : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
        
        map = L.map('bangladeshMap').setView([23.8, 90.3], 7.2);
        L.tileLayer(tileUrl, {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors'
        }).addTo(map);

        activeDivisions.forEach(division => {
            if (coords[division]) {
                const [lat, lng] = coords[division];
                L.circleMarker([lat, lng], {
                    radius: 14,
                    fillColor: "#2ecc71",
                    color: "#ffffff",
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.85
                }).addTo(map)
                  .bindPopup(`<b>${division} Division</b><br>✅ Active AR Service Area`);
            }
        });
    }

    // Update map tiles when dark mode toggles
    function updateMapTiles() {
        if (!map) return;
        const isDark = document.body.classList.contains('dark');
        const newTileUrl = isDark ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png' : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
        map.eachLayer(layer => {
            if (layer instanceof L.TileLayer) {
                map.removeLayer(layer);
            }
        });
        L.tileLayer(newTileUrl, {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors'
        }).addTo(map);
    }

    // Perfect Dark Mode (icon only, full styling)
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
            if (document.body.classList.contains('dark')) {
                localStorage.setItem('darkMode', 'enabled');
                toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                localStorage.setItem('darkMode', 'disabled');
                toggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
            }
            updateMapTiles();  // refresh map tiles
        });
    }

    // Back to top
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

    // Navbar scroll effect
    function initNavbarScroll() {
        const navbar = document.querySelector('.main-navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(0,0,0,0.95)';
            } else {
                navbar.style.background = 'var(--navbar-bg)';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initCounters();
        initMap();
        initDarkMode();
        initBackToTop();
        initNavbarScroll();
    });
</script>
</body>
</html>