<?php
// index.php - Complete dynamic homepage with perfect dark mode, circle-only Bangladesh map, floating message icon
require_once 'config.php';

// Fetch all dynamic content
$sliders = $pdo->query("SELECT * FROM sliders WHERE status = 1 ORDER BY order_position ASC")->fetchAll();
$services = $pdo->query("SELECT * FROM services ORDER BY id LIMIT 4")->fetchAll();
$featuredProjects = $pdo->query("SELECT * FROM portfolios WHERE featured = 1 ORDER BY id DESC LIMIT 3")->fetchAll();
$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY id LIMIT 3")->fetchAll();
$chairmanSpeech = $pdo->query("SELECT * FROM chairman_speech WHERE is_active = 1 LIMIT 1")->fetch();

// Fetch courses from database (with image support)
$courses = $pdo->query("SELECT * FROM courses WHERE status = 1 ORDER BY order_position ASC, id ASC LIMIT 4")->fetchAll();

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

// Map districts to divisions
$districtToDivision = [
    'Dhaka' => 'Dhaka', 'Gazipur' => 'Dhaka', 'Narayanganj' => 'Dhaka', 'Tangail' => 'Dhaka', 'Kishoreganj' => 'Dhaka',
    'Chittagong' => 'Chattogram', 'Cox\'s Bazar' => 'Chattogram', 'Comilla' => 'Chattogram', 'Noakhali' => 'Chattogram', 'Feni' => 'Chattogram',
    'Rajshahi' => 'Rajshahi', 'Natore' => 'Rajshahi', 'Pabna' => 'Rajshahi', 'Bogra' => 'Rajshahi',
    'Khulna' => 'Khulna', 'Jessore' => 'Khulna', 'Satkhira' => 'Khulna', 'Kushtia' => 'Khulna',
    'Sylhet' => 'Sylhet', 'Moulvibazar' => 'Sylhet', 'Habiganj' => 'Sylhet',
    'Barishal' => 'Barishal', 'Patuakhali' => 'Barishal', 'Bhola' => 'Barishal',
    'Rangpur' => 'Rangpur', 'Dinajpur' => 'Rangpur', 'Thakurgaon' => 'Rangpur',
    'Mymensingh' => 'Mymensingh', 'Jamalpur' => 'Mymensingh', 'Netrokona' => 'Mymensingh'
];

$activeDivisions = [];
foreach ($activeDistrictList as $district) {
    if (isset($districtToDivision[$district])) {
        $activeDivisions[$districtToDivision[$district]] = true;
    }
}
$activeDivisions = array_keys($activeDivisions);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>AR Tech Solutions | Immersive Reality</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* ---------- UNIQUE DESIGN SYSTEM: PERFECT DARK/LIGHT CONTRAST ---------- */
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
            position: absolute;
            top: 20px;
            left: 5%;
            width: 90%;
            z-index: 1030;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-radius: 60px;
            border: 1px solid rgba(124, 58, 237, 0.2);
            box-shadow: var(--shadow);
            transition: all 0.3s;
            padding: 0.5rem 1rem;
        }
        body.dark .glass-nav {
            background: rgba(15, 15, 18, 0.85);
            border-color: rgba(124, 58, 237, 0.4);
        }
        .glass-nav.scrolled {
            top: 5px;
            width: 96%;
            left: 2%;
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
        .nav-link:hover::after {
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
        }
        body.dark .dark-toggle {
            background: rgba(124, 58, 237, 0.3);
            color: var(--secondary);
        }
        /* Hero Slider - full screen on mobile */
        .hero-slider {
            width: 100%;
            height: 100dvh;
            min-height: -webkit-fill-available;
            position: relative;
        }
        .heroSwiper, .heroSwiper .swiper-wrapper, .heroSwiper .swiper-slide {
            height: 100%;
        }
        .swiper-slide {
            position: relative;
        }
        .slide-bg {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.6);
        }
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            padding: 0 1rem;
        }
        .hero-title {
            font-size: 4.5rem;
            font-weight: 800;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        /* Mobile responsive: 2 columns for services, courses, projects, testimonials */
        @media (max-width: 768px) {
            .hero-title { 
                font-size: 2.5rem; 
            }
            .hero-content .fs-4 {
                font-size: 1.1rem !important;
            }
            .glass-nav {
                top: 10px;
                width: 94%;
                left: 3%;
            }
            .navbar-collapse {
                background: rgba(255,255,255,0.95);
                border-radius: 28px;
                padding: 1rem;
                margin-top: 1rem;
            }
            body.dark .navbar-collapse {
                background: rgba(20,20,30,0.95);
            }
            /* 2 columns on mobile */
            .service-col, .course-col, .project-col, .testimonial-col {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }
        /* Buttons */
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
        /* Cards */
        .glass-card {
            background: var(--surface-light);
            border-radius: 28px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
            height: 100%;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        body.dark .glass-card {
            background: var(--surface-dark);
            border-color: var(--border-dark);
        }
        .glass-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.2);
        }
        .card-img-top {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .card-body {
            padding: 1.5rem;
        }
        .service-icon i, .course-icon i {
            font-size: 2.5rem;
            color: var(--primary);
        }
        /* Counters */
        .counter-card {
            background: var(--surface-light);
            border-radius: 32px;
            padding: 2rem;
            text-align: center;
            border: 1px solid var(--border-light);
        }
        body.dark .counter-card {
            background: var(--surface-dark);
        }
        .counter-num {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary);
        }
        /* Map */
        #bangladeshMap {
            height: 480px;
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow);
        }
        /* Trusted by Innovators - Swiper slider with fixed size */
        .trusted-section {
            background: #ffffff;
        }
        body.dark .trusted-section {
            background: var(--surface-dark);
        }
        .logo-swiper-slide {
            text-align: center;
            padding: 0.5rem;
        }
        .logo-card-fixed {
            background: transparent;
            padding: 1rem;
            border-radius: 20px;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100px;
        }
        .logo-card-fixed img {
            max-height: 60px;
            max-width: 100%;
            object-fit: contain;
            filter: grayscale(20%);
            transition: 0.2s;
        }
        .logo-card-fixed:hover img {
            filter: grayscale(0%);
            transform: scale(1.02);
        }
        /* Testimonials */
        .testimonial-card {
            background: var(--surface-light);
            border-radius: 24px;
            padding: 1.8rem;
            border: 1px solid var(--border-light);
            height: 100%;
        }
        body.dark .testimonial-card {
            background: var(--surface-dark);
        }
        /* CTA */
        .cta-modern {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 48px;
            padding: 3rem;
            text-align: center;
            color: white;
        }
        /* Footer */
        footer {
            background: #0f172a;
            color: #cbd5e1;
            padding: 3rem 0 1.5rem;
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
        /* Text adjustments */
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
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><i class="fas fa-vr-cardboard me-2"></i>ARTECH</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="portfolio.php">Portfolio</a></li>
                <li class="nav-item"><a class="nav-link" href="chairman-speech.php">Chairman</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
            </ul>
            <button id="darkModeToggle" class="dark-toggle ms-2"><i class="fas fa-moon"></i></button>
        </div>
    </div>
</nav>

<main>
    <!-- Hero Slider - full screen on mobile -->
    <section class="hero-slider">
        <div class="swiper heroSwiper">
            <div class="swiper-wrapper">
                <?php foreach($sliders as $slide): ?>
                <div class="swiper-slide">
                    <img src="<?php echo htmlspecialchars($slide['image_url']); ?>" class="slide-bg" alt="slide">
                    <div class="hero-content container d-flex flex-column justify-content-center h-100">
                        <h1 class="hero-title"><?php echo htmlspecialchars($slide['title']); ?></h1>
                        <p class="fs-4"><?php echo htmlspecialchars($slide['subtitle']); ?></p>
                        <?php if($slide['button_text']): ?>
                        <div><a href="<?php echo htmlspecialchars($slide['button_link']); ?>" class="btn btn-primary-custom mt-3"><?php echo htmlspecialchars($slide['button_text']); ?> →</a></div>
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

    <!-- Our Services (2 columns on mobile) -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">What We Do</span>
                <h2 class="display-5 fw-bold mt-2">Our Services</h2>
                <p class="text-muted-custom">Cutting-edge AR/VR solutions for modern enterprises</p>
            </div>
            <div class="row g-4">
                <?php foreach($services as $service): ?>
                <div class="col-6 col-md-6 col-lg-3 service-col">
                    <div class="glass-card text-center">
                        <div class="card-body">
                            <div class="service-icon mb-3"><i class="<?php echo htmlspecialchars($service['icon_class']); ?> fa-3x"></i></div>
                            <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                            <p class="text-muted-custom"><?php echo htmlspecialchars($service['description']); ?></p>
                            <a href="<?php echo htmlspecialchars($service['link_url']); ?>" class="btn btn-outline-custom btn-sm">Learn More</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Our Courses (2 columns on mobile) – Clickable cards linking to course-details.php -->
    <section class="py-5" style="background: rgba(124,58,237,0.03);">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">Academy</span>
                <h2 class="display-5 fw-bold">Master Immersive Tech</h2>
                <p class="text-muted-custom">Expert-led courses for the future</p>
            </div>
            <div class="row g-4">
                <?php foreach($courses as $course): ?>
                <div class="col-6 col-md-6 col-lg-3 course-col">
                    <a href="course-details.php?id=<?php echo $course['id']; ?>" class="text-decoration-none">
                        <div class="glass-card h-100 d-flex flex-column">
                            <?php if(!empty($course['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($course['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>">
                            <?php else: ?>
                                <div class="card-img-top d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); height: 200px;">
                                    <i class="<?php echo htmlspecialchars($course['icon_class']); ?> fa-4x text-white"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5><?php echo htmlspecialchars($course['title']); ?></h5>
                                <div class="d-flex gap-2 my-2">
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($course['level']); ?></span>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($course['duration']); ?></span>
                                </div>
                                <p class="small text-muted-custom"><?php echo htmlspecialchars($course['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-primary">$<?php echo number_format($course['price'], 2); ?></span>
                                    <span class="badge bg-info"><?php echo $course['enrolled_students']; ?>+ enrolled</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5">
                <a href="courses.php" class="btn btn-primary-custom">All Courses <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Featured Projects (2 columns on mobile) -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Success Stories</span>
                <h2 class="display-5 fw-bold">Featured Projects</h2>
                <p class="text-muted-custom">Real-world impact with immersive technology</p>
            </div>
            <div class="row g-4">
                <?php foreach($featuredProjects as $project): ?>
                <div class="col-6 col-md-6 col-lg-4 project-col">
                    <div class="glass-card h-100 d-flex flex-column">
                        <img src="<?php echo htmlspecialchars($project['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($project['title']); ?>">
                        <div class="card-body">
                            <h4 class="fs-5"><?php echo htmlspecialchars($project['title']); ?></h4>
                            <p class="text-muted-custom small"><i class="fas fa-building"></i> <?php echo htmlspecialchars($project['client']); ?></p>
                            <p class="text-muted-custom small"><?php echo substr($project['description'], 0, 70); ?>...</p>
                            <a href="portfolio.php" class="btn btn-outline-custom btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="portfolio.php" class="btn btn-primary-custom">Browse All Projects</a>
            </div>
        </div>
    </section>

    <!-- Chairman Speech -->
    <?php if($chairmanSpeech): ?>
    <section class="py-5" style="background: rgba(6, 182, 212, 0.03);">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-md-4 text-center">
                    <img src="<?php echo htmlspecialchars($chairmanSpeech['image_url']); ?>" class="rounded-circle shadow-lg border border-3 border-primary" style="width: 180px; height: 180px; object-fit: cover;">
                </div>
                <div class="col-md-8">
                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">Visionary Words</span>
                    <h3 class="mt-2">Chairman's Message</h3>
                    <p class="lead"><?php echo htmlspecialchars(substr($chairmanSpeech['speech_text'], 0, 220)); ?>...</p>
                    <a href="chairman-speech.php" class="btn btn-primary-custom">Read Full Speech <i class="fas fa-microphone-alt"></i></a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Happy Customers Counters -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="counter-card">
                        <i class="fas fa-users fa-3x mb-2 text-primary"></i>
                        <h2 class="counter-num" data-target="<?php echo $totalCustomers; ?>">0</h2>
                        <p class="mb-0 fw-semibold">Happy Customers</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="counter-card">
                        <i class="fas fa-globe fa-3x mb-2 text-primary"></i>
                        <h2 class="counter-num" data-target="<?php echo $totalCountries; ?>">0</h2>
                        <p class="mb-0 fw-semibold">Countries Served</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="counter-card">
                        <i class="fas fa-map-marker-alt fa-3x mb-2 text-primary"></i>
                        <h2 class="counter-num" data-target="<?php echo $activeDistricts; ?>">0</h2>
                        <p class="mb-0 fw-semibold">Bangladeshi Districts</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bangladesh Map (Circle only) -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-4">
                <h2>Our Reach in Bangladesh</h2>
                <p><i class="fas fa-circle text-success"></i> Active AR Service Areas (Divisions)</p>
            </div>
            <div id="bangladeshMap"></div>
        </div>
    </section>

    <!-- Trusted by Innovators - Swiper Logo Slider Fixed Size -->
    <section class="trusted-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Trusted by Innovators</h2>
                <p class="text-muted-custom">Global leaders who trust our expertise</p>
            </div>
            <div class="swiper logoSlider">
                <div class="swiper-wrapper">
                    <?php foreach($allCustomers as $cust): ?>
                    <div class="swiper-slide logo-swiper-slide">
                        <div class="logo-card-fixed">
                            <img src="<?php echo htmlspecialchars($cust['logo_url']); ?>" alt="<?php echo htmlspecialchars($cust['customer_name']); ?>">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination logo-pagination"></div>
                <div class="swiper-button-next logo-next"></div>
                <div class="swiper-button-prev logo-prev"></div>
            </div>
        </div>
    </section>

    <!-- Client Testimonials (2 columns on mobile) -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>What Our Clients Say</h2>
                <p class="text-muted-custom">Real feedback from real partners</p>
            </div>
            <div class="row g-4">
                <?php foreach($testimonials as $testimonial): ?>
                <div class="col-6 col-md-4 testimonial-col">
                    <div class="testimonial-card h-100">
                        <i class="fas fa-quote-left fa-2x text-primary mb-3 opacity-50"></i>
                        <p class="fst-italic small">"<?php echo htmlspecialchars($testimonial['testimonial_text']); ?>"</p>
                        <h5 class="mt-3 mb-0 fs-6"><?php echo htmlspecialchars($testimonial['client_name']); ?></h5>
                        <small class="text-muted-custom"><?php echo htmlspecialchars($testimonial['client_title'] . ', ' . $testimonial['company']); ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="container py-4">
        <div class="cta-modern">
            <h2 class="fw-bold">Ready to Transform Your Business?</h2>
            <p class="mb-4 fs-5">Let's discuss your next immersive project.</p>
            <a href="contact.php" class="btn btn-light btn-lg rounded-pill px-5">Get in Touch <i class="fas fa-paper-plane ms-2"></i></a>
        </div>
    </section>
</main>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold"><i class="fas fa-vr-cardboard me-2"></i>ARTECH</h5>
                <p class="text-muted">Augmenting reality with precision and innovation.</p>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="portfolio.php">Portfolio</a></li>
                    <li><a href="courses.php">Courses</a></li>
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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Set full screen height for hero on mobile
    function setFullHeight() {
        const hero = document.querySelector('.hero-slider');
        if (hero) {
            if (window.innerHeight) {
                hero.style.height = window.innerHeight + 'px';
            }
        }
    }
    window.addEventListener('resize', setFullHeight);
    setFullHeight();

    // Hero Slider
    new Swiper('.heroSwiper', {
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
        effect: 'fade',
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
    });

    // Logo Slider (Trusted by Innovators)
    new Swiper('.logoSlider', {
        slidesPerView: 2,
        spaceBetween: 15,
        loop: true,
        autoplay: { delay: 2500, disableOnInteraction: false },
        breakpoints: {
            576: { slidesPerView: 3 },
            768: { slidesPerView: 4 },
            1024: { slidesPerView: 6 }
        },
        pagination: { el: '.logo-pagination', clickable: true },
        navigation: { nextEl: '.logo-next', prevEl: '.logo-prev' }
    });

    // Counters Observer
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

    // Bangladesh Map
    let map;
    function initMap() {
        const activeDivisions = <?php echo json_encode($activeDivisions); ?>;
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
                    radius: 15,
                    fillColor: "#10b981",
                    color: "#ffffff",
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.85
                }).addTo(map)
                  .bindPopup(`<b>${division} Division</b><br>✅ Active AR Service Area`);
            }
        });
    }

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
            updateMapTiles();
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
        initCounters();
        initMap();
        initDarkMode();
        initNavbarScroll();
        initBackToTop();
        setFullHeight();
    });
</script>
</body>
</html>