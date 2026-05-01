<?php
// index.php - Homepage with fullscreen slider, services, projects, chairman preview, counters, Bangladesh map, client logos, testimonials
require_once 'config.php';

// Fetch slider images from DB
$sliders = $pdo->query("SELECT * FROM sliders WHERE status = 1 ORDER BY order_position ASC")->fetchAll();

// Fetch services
$services = $pdo->query("SELECT * FROM services ORDER BY id LIMIT 4")->fetchAll();

// Fetch featured projects
$featuredProjects = $pdo->query("SELECT * FROM portfolios WHERE featured = 1 ORDER BY id DESC LIMIT 3")->fetchAll();

// Fetch testimonials
$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY id LIMIT 3")->fetchAll();

// Fetch chairman speech (active)
$chairmanSpeech = $pdo->query("SELECT * FROM chairman_speech WHERE is_active = 1 LIMIT 1")->fetch();

// Counters
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers WHERE is_active = 1")->fetchColumn();
$totalCountries = $pdo->query("SELECT COUNT(DISTINCT country) FROM customers WHERE is_active = 1")->fetchColumn();
$activeDistricts = $pdo->query("SELECT COUNT(DISTINCT district) FROM customers WHERE district IS NOT NULL AND country = 'Bangladesh' AND district != ''")->fetchColumn();

// Fetch all customers for logos
$allCustomers = $pdo->query("SELECT customer_name, logo_url, country FROM customers WHERE is_active = 1 ORDER BY customer_name")->fetchAll();

// Fetch list of district names that have service (for map coloring)
$activeDistrictList = $pdo->query("SELECT DISTINCT district FROM customers WHERE country = 'Bangladesh' AND district IS NOT NULL AND district != ''")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>AR Tech Solutions - Immersive Reality Experiences</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Leaflet CSS for map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Custom Styles -->
    <style>
        :root {
            --primary: #5e2ced;
            --primary-dark: #4a1fdb;
            --secondary: #00c2d1;
            --dark: #0a0b10;
            --light: #f8f9ff;
        }
        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            overflow-x: hidden;
            background-color: #fff;
        }
        /* Navbar */
        .main-navbar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1030;
            background: rgba(10, 11, 16, 0.85);
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
            margin-right: 8px;
        }
        .main-navbar .nav-link {
            color: rgba(255,255,255,0.85);
            font-weight: 500;
            margin: 0 0.5rem;
            transition: 0.3s;
        }
        .main-navbar .nav-link:hover,
        .main-navbar .nav-link.active {
            color: var(--secondary);
        }
        .navbar-toggler {
            background-color: white;
        }
        /* Fullscreen Slider */
        .fullscreen-slider {
            width: 100%;
            height: 100vh;
            position: relative;
            overflow: hidden;
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
            z-index: 2;
            text-shadow: 0 2px 15px rgba(0,0,0,0.3);
        }
        .slide-title {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease;
        }
        .slide-subtitle {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 1.5rem;
            opacity: 0.9;
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
        .swiper-button-next, .swiper-button-prev {
            color: white;
            background: rgba(0,0,0,0.3);
            border-radius: 50%;
            width: 45px;
            height: 45px;
        }
        .swiper-pagination-bullet-active {
            background: var(--secondary);
        }
        /* Service Cards */
        .service-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(94,44,237,0.1);
        }
        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 40px rgba(94,44,237,0.15);
        }
        .service-icon i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        .service-link {
            text-decoration: none;
            font-weight: 600;
            color: var(--primary);
        }
        /* Project Cards */
        .project-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: 0.3s;
            height: 100%;
        }
        .project-card:hover {
            transform: scale(1.02);
        }
        .project-img {
            height: 220px;
            background-size: cover;
            background-position: center;
        }
        .client-name {
            font-size: 0.9rem;
            color: var(--primary);
        }
        /* Testimonials */
        .testimonial-card {
            background: #f9f9ff;
            border-radius: 30px;
            position: relative;
            transition: 0.3s;
        }
        .testimonial-icon i {
            font-size: 2.5rem;
            color: var(--secondary);
            opacity: 0.7;
        }
        .testimonial-text {
            font-size: 1rem;
            line-height: 1.6;
            color: #333;
        }
        /* Counters */
        .counter-card {
            background: white;
            border-radius: 30px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .counter-card:hover { transform: translateY(-5px); }
        .counter-num {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary);
            margin: 1rem 0;
        }
        /* Logo cards */
        .logo-card {
            background: white;
            border-radius: 16px;
            padding: 1rem;
            transition: all 0.3s;
            border: 1px solid #eee;
        }
        .logo-card:hover {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transform: scale(1.02);
        }
        .logo-card img {
            filter: grayscale(20%);
            transition: 0.3s;
            max-height: 80px;
            object-fit: contain;
        }
        .logo-card:hover img { filter: grayscale(0%); }
        /* Chairman preview */
        .chairman-preview .rounded-circle {
            border: 4px solid var(--primary);
        }
        /* Map container */
        #bangladeshMap {
            height: 500px;
            width: 100%;
            border-radius: 20px;
            z-index: 1;
        }
        .map-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 15px;
            background: white;
            padding: 8px 20px;
            border-radius: 40px;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .map-legend span {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-right: 6px;
            vertical-align: middle;
        }
        .map-legend .active {
            background-color: #2ecc71;
            border: 1px solid #27ae60;
        }
        .map-legend .inactive {
            background-color: #d3d3d3;
            border: 1px solid #aaa;
        }
        .cta-section {
            background: linear-gradient(135deg, var(--primary), #7a4af5);
        }
        footer {
            background: #0a0b10;
            color: #ccc;
            padding: 2rem 0;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 768px) {
            .slide-title { font-size: 2rem; }
            .main-navbar { background: rgba(10,11,16,0.95); }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg main-navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-vr-cardboard"></i> AR Tech Solutions
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
        </div>
    </div>
</nav>

<main>
    <!-- 1. Fullscreen Slider Section -->
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

    <!-- 2. Our Services Section (directly below slider) -->
    <section class="services-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Our Services</h2>
                <p class="section-subtitle">Cutting-edge AR solutions for modern enterprises</p>
            </div>
            <div class="row g-4">
                <?php foreach($services as $service): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="service-card text-center p-4 h-100">
                        <div class="service-icon">
                            <i class="<?php echo htmlspecialchars($service['icon_class']); ?>"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                        <a href="<?php echo htmlspecialchars($service['link_url']); ?>" class="service-link">Learn More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 3. Featured Projects Section -->
    <section class="projects-section bg-light py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Featured Projects</h2>
                <p class="section-subtitle">Real-world impact with immersive technology</p>
            </div>
            <div class="row g-4">
                <?php foreach($featuredProjects as $project): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="project-card">
                        <div class="project-img" style="background-image: url('<?php echo htmlspecialchars($project['image_url']); ?>');"></div>
                        <div class="project-body p-3">
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
                <a href="portfolio.php" class="btn btn-primary">View All Projects <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- 4. Chairman Speech Preview (placed just before the Bangladesh map) -->
    <?php if($chairmanSpeech): ?>
    <section class="chairman-preview py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-center">
                    <img src="<?php echo htmlspecialchars($chairmanSpeech['image_url']); ?>" class="rounded-circle img-fluid shadow" style="width: 200px; height: 200px; object-fit: cover;" alt="Chairman">
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

    <!-- 5. Dynamic Counters Section -->
    <section class="counters-section py-5 text-center bg-light">
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

    <!-- 6. Bangladesh Map Section (Our Reach in Bangladesh) -->
    <section class="map-section py-5">
        <div class="container">
            <div class="text-center mb-4">
                <h2>Our Reach in Bangladesh</h2>
                <p>Divisions with active AR deployments are highlighted in <span class="text-success">green</span>.</p>
            </div>
            <div class="map-legend">
                <div><span class="active"></span> Active Service Area</div>
                <div><span class="inactive"></span> Expansion Planned</div>
            </div>
            <div id="bangladeshMap"></div>
        </div>
    </section>

    <!-- 7. Our Customers Logos Section -->
    <section class="customers-logos py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Trusted by Industry Leaders</h2>
                <p>Our global and local partners</p>
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

    <!-- 8. Testimonials Section -->
    <section class="testimonials-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Client Testimonials</h2>
                <p class="section-subtitle">What our partners say about us</p>
            </div>
            <div class="row g-4">
                <?php foreach($testimonials as $testimonial): ?>
                <div class="col-md-4">
                    <div class="testimonial-card p-4 h-100">
                        <div class="testimonial-icon">
                            <i class="fas fa-quote-left"></i>
                        </div>
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

    <!-- 9. Quick Contact Call to Action -->
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
                <p>Augmenting reality with precision and innovation. Transforming industries with immersive tech.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="text-white-50 text-decoration-none">Home</a></li>
                    <li><a href="portfolio.php" class="text-white-50 text-decoration-none">Portfolio</a></li>
                    <li><a href="chairman-speech.php" class="text-white-50 text-decoration-none">Chairman's Speech</a></li>
                    <li><a href="contact.php" class="text-white-50 text-decoration-none">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Connect</h5>
                <p><i class="fas fa-envelope"></i> hello@artechsolutions.com</p>
                <p><i class="fas fa-phone"></i> +1 (823) 456-5588</p>
                <div class="social-icons">
                    <a href="#" class="text-white me-3"><i class="fab fa-linkedin fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-github fa-lg"></i></a>
                </div>
            </div>
        </div>
        <hr class="bg-secondary">
        <div class="text-center">
            <small>&copy; <?php echo date('Y'); ?> AR Tech Solutions. All rights reserved.</small>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Initialize Swiper (fullscreen slider)
    const swiper = new Swiper('.heroSwiper', {
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
        effect: 'fade',
        fadeEffect: { crossFade: true },
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
    });

    // Animate counters when they come into view
    function animateCounters() {
        const counters = document.querySelectorAll('.counter-num');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.getAttribute('data-target'));
                    let current = 0;
                    const increment = target / 50;
                    const updateCounter = () => {
                        current += increment;
                        if (current < target) {
                            counter.innerText = Math.ceil(current);
                            requestAnimationFrame(updateCounter);
                        } else {
                            counter.innerText = target;
                        }
                    };
                    updateCounter();
                    observer.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(counter => observer.observe(counter));
    }

    // Bangladesh Map with Leaflet (only Bangladesh map)
    function initBangladeshMap() {
        // Active divisions from PHP (list of districts with customers)
        const activeDivisions = <?php echo json_encode($activeDistrictList); ?>;

        // GeoJSON for Bangladesh divisions (simplified but visually coherent)
        const bangladeshGeoJSON = {
            "type": "FeatureCollection",
            "features": [
                {"type":"Feature","properties":{"name":"Dhaka"},"geometry":{"type":"Polygon","coordinates":[[[89.8,24.8],[90.9,24.8],[91.0,23.8],[89.9,23.5],[89.5,24.0],[89.8,24.8]]]}},
                {"type":"Feature","properties":{"name":"Chattogram"},"geometry":{"type":"Polygon","coordinates":[[[91.2,23.4],[92.4,23.1],[92.6,21.8],[91.4,21.6],[90.8,22.3],[91.2,23.4]]]}},
                {"type":"Feature","properties":{"name":"Rajshahi"},"geometry":{"type":"Polygon","coordinates":[[[88.0,25.1],[89.2,25.2],[89.5,24.5],[88.5,24.2],[87.9,24.6],[88.0,25.1]]]}},
                {"type":"Feature","properties":{"name":"Khulna"},"geometry":{"type":"Polygon","coordinates":[[[88.8,22.9],[89.7,22.8],[89.9,22.0],[88.9,21.9],[88.2,22.5],[88.8,22.9]]]}},
                {"type":"Feature","properties":{"name":"Sylhet"},"geometry":{"type":"Polygon","coordinates":[[[91.3,25.1],[92.4,25.2],[92.6,24.4],[91.7,24.2],[91.0,24.5],[91.3,25.1]]]}},
                {"type":"Feature","properties":{"name":"Barishal"},"geometry":{"type":"Polygon","coordinates":[[[89.9,22.8],[90.7,22.7],[90.9,21.9],[89.8,21.9],[89.4,22.3],[89.9,22.8]]]}},
                {"type":"Feature","properties":{"name":"Rangpur"},"geometry":{"type":"Polygon","coordinates":[[[88.3,26.3],[89.6,26.4],[89.8,25.6],[88.6,25.5],[88.0,25.9],[88.3,26.3]]]}},
                {"type":"Feature","properties":{"name":"Mymensingh"},"geometry":{"type":"Polygon","coordinates":[[[89.9,25.2],[91.0,25.1],[91.1,24.3],[90.0,24.2],[89.5,24.7],[89.9,25.2]]]}}
            ]
        };

        const map = L.map('bangladeshMap').setView([23.8, 90.3], 7.2);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors &copy; CARTO'
        }).addTo(map);

        function getFeatureStyle(feature) {
            const isActive = activeDivisions.includes(feature.properties.name);
            return {
                fillColor: isActive ? '#2ecc71' : '#d3d3d3',
                weight: 1.5,
                color: '#ffffff',
                fillOpacity: 0.75
            };
        }

        function onEachFeature(feature, layer) {
            const isActive = activeDivisions.includes(feature.properties.name);
            const status = isActive ? '✅ Active (AR services deployed)' : '⏳ Coming soon';
            layer.bindPopup(`<b>${feature.properties.name} Division</b><br>${status}`);
        }

        L.geoJSON(bangladeshGeoJSON, {
            style: getFeatureStyle,
            onEachFeature: onEachFeature
        }).addTo(map);

        // Marker for capital city
        L.circleMarker([23.8103, 90.4125], { radius: 5, fillColor: "#e67e22", color: "#fff", weight: 1.5, fillOpacity: 0.9 })
            .addTo(map)
            .bindPopup("<b>Dhaka</b><br>Head Office");

        map.invalidateSize();
    }

    // Run after DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        animateCounters();
        initBangladeshMap();

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.main-navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(10, 11, 16, 0.98)';
            } else {
                navbar.style.background = 'rgba(10, 11, 16, 0.85)';
            }
        });
    });
</script>
</body>
</html>