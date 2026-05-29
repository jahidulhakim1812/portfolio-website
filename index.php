<?php
// index.php - Complete dynamic homepage with all cards and testimonial slider, read-more toggle, and distinct description color
require_once 'config.php';

// Fetch all dynamic content
$sliders = $pdo->query("SELECT * FROM sliders WHERE status = 1 ORDER BY order_position ASC")->fetchAll();
$services = $pdo->query("SELECT * FROM services WHERE status = 1 ORDER BY order_position ASC, id ASC")->fetchAll();
$allPortfolios = $pdo->query("SELECT * FROM portfolios WHERE status = 1 ORDER BY order_position ASC, id DESC")->fetchAll();
$testimonials = $pdo->query("SELECT * FROM testimonials WHERE status = 1 ORDER BY order_position ASC, id DESC")->fetchAll();
$chairmanSpeech = $pdo->query("SELECT * FROM chairman_speech WHERE is_active = 1 LIMIT 1")->fetch();
$courses = $pdo->query("SELECT * FROM courses WHERE status = 1 ORDER BY order_position ASC, id ASC")->fetchAll();

// Counters
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers WHERE is_active = 1")->fetchColumn();
$totalCountries = $pdo->query("SELECT COUNT(DISTINCT country) FROM customers WHERE is_active = 1")->fetchColumn();
$activeDistricts = $pdo->query("SELECT COUNT(DISTINCT district) FROM customers WHERE district IS NOT NULL AND country = 'Bangladesh' AND district != ''")->fetchColumn();
$allCustomers = $pdo->query("SELECT customer_name, logo_url, country FROM customers WHERE is_active = 1 ORDER BY customer_name")->fetchAll();

// Active districts for map
$activeDistrictList = $pdo->query("SELECT DISTINCT district FROM customers WHERE country = 'Bangladesh' AND district IS NOT NULL AND district != ''")->fetchAll(PDO::FETCH_COLUMN);

// District coordinates (all 64 districts)
$districtCoordinates = [
    'Bagerhat' => [22.6515, 89.7855], 'Bandarban' => [22.1953, 92.2183], 'Barguna' => [22.1543, 90.1265],
    'Barishal' => [22.7000, 90.3500], 'Bhola' => [22.6859, 90.6513], 'Bogra' => [24.8467, 89.3667],
    'Brahmanbaria' => [23.9571, 91.1091], 'Chandpur' => [23.2199, 90.6498], 'Chattogram' => [22.3384, 91.8317],
    'Chuadanga' => [23.6420, 88.8473], 'Cox\'s Bazar' => [21.4272, 92.0058], 'Cumilla' => [23.4610, 91.1865],
    'Dhaka' => [23.8103, 90.4125], 'Dinajpur' => [25.6217, 88.6350], 'Faridpur' => [23.5937, 89.8376],
    'Feni' => [23.0154, 91.3982], 'Gaibandha' => [25.3276, 89.5361], 'Gazipur' => [24.0023, 90.4264],
    'Gopalganj' => [23.0126, 89.8332], 'Habiganj' => [24.3742, 91.4071], 'Jamalpur' => [24.9233, 89.9393],
    'Jashore' => [23.1664, 89.2088], 'Jhalokathi' => [22.6420, 90.1997], 'Jhenaidah' => [23.5536, 89.1677],
    'Joypurhat' => [25.0956, 89.0247], 'Khagrachhari' => [23.0447, 91.9723], 'Khulna' => [22.8456, 89.5403],
    'Kishoreganj' => [24.4347, 90.7825], 'Kurigram' => [25.8054, 89.6400], 'Kushtia' => [23.9016, 89.1219],
    'Lakshmipur' => [22.9445, 90.8302], 'Lalmonirhat' => [25.9192, 89.4457], 'Madaripur' => [23.1702, 90.2058],
    'Magura' => [23.4893, 89.4138], 'Manikganj' => [23.8500, 89.9833], 'Meherpur' => [23.7578, 88.6326],
    'Moulvibazar' => [24.4770, 91.7710], 'Munshiganj' => [23.5423, 90.5313], 'Mymensingh' => [24.7471, 90.4073],
    'Naogaon' => [24.8324, 88.9248], 'Narail' => [23.1656, 89.5144], 'Narayanganj' => [23.6225, 90.5000],
    'Narsingdi' => [23.9208, 90.7196], 'Natore' => [24.4101, 88.9329], 'Netrokona' => [24.8842, 90.7271],
    'Nilphamari' => [25.9315, 88.8540], 'Noakhali' => [22.8097, 91.0987], 'Pabna' => [24.0105, 89.2719],
    'Panchagarh' => [26.2606, 88.5630], 'Patuakhali' => [22.3394, 90.3241], 'Pirojpur' => [22.5635, 89.9922],
    'Rajbari' => [23.7550, 89.6549], 'Rajshahi' => [24.3745, 88.6042], 'Rangamati' => [22.6436, 92.1624],
    'Rangpur' => [25.7439, 89.2752], 'Satkhira' => [22.7185, 89.0728], 'Shariatpur' => [23.2100, 90.3500],
    'Sherpur' => [25.0169, 90.0106], 'Sirajganj' => [24.4524, 89.7032], 'Sunamganj' => [25.0649, 91.3920],
    'Sylhet' => [24.8993, 91.8719], 'Tangail' => [24.2646, 89.9239], 'Thakurgaon' => [26.0333, 88.4667]
];

$activeDistrictsData = [];
foreach ($activeDistrictList as $district) {
    if (isset($districtCoordinates[$district])) {
        $activeDistrictsData[] = ['name' => $district, 'lat' => $districtCoordinates[$district][0], 'lng' => $districtCoordinates[$district][1]];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="AR Tech Solutions - Immersive Reality experiences, AR/VR development, and digital transformation.">
    <title>AR Tech Solutions | Immersive Reality</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
        footer .text-muted {
            color: white !important;
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
        /* Hero Slider - perfect mobile & desktop */
        .hero-slider {
            width: 100%;
            height: 100vh;
            height: 100dvh;
            min-height: -webkit-fill-available;
            position: relative;
            overflow: hidden;
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
            object-position: center center;
        }
        /* Dark overlay for better text contrast */
        .slide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.55);
            z-index: 1;
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
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title { font-size: 2.2rem; line-height: 1.2; }
            .hero-content .fs-4 { font-size: 1rem !important; margin-top: 0.5rem; }
            .hero-content .btn-primary-custom { padding: 8px 20px; font-size: 0.9rem; }
            .glass-nav { top: 10px; width: 94%; left: 3%; }
            .navbar-collapse { background: rgba(255,255,255,0.95); border-radius: 28px; padding: 1rem; margin-top: 1rem; }
            body.dark .navbar-collapse { background: rgba(20,20,30,0.95); }
            .service-col, .course-col, .project-col { flex: 0 0 50%; max-width: 50%; }
            .card-img-top { height: 170px !important; object-fit: cover; object-position: center; }
            .glass-card .card-body { padding: 1rem; }
            .glass-card h4, .glass-card h5 { font-size: 1rem; margin-bottom: 0.5rem; }
            .description-text { font-size: 0.8rem; }
            .btn-outline-custom { padding: 4px 16px; font-size: 0.75rem; }
            .counter-num { font-size: 2rem; }
            .counter-card { padding: 1rem; }
            .cta-modern { padding: 1.5rem; }
            .cta-modern h2 { font-size: 1.5rem; }
            #bangladeshMap { height: 320px; }
            .logo-card-fixed img { max-height: 45px; }
        }
        @media (max-width: 480px) {
            .hero-title { font-size: 1.8rem; }
            .hero-content .fs-4 { font-size: 0.9rem !important; }
            .card-img-top { height: 150px !important; }
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
            object-position: center;
        }
        .card-body {
            padding: 1.5rem;
        }
        .description-text {
            color: var(--text-muted-light);
        }
        body.dark .description-text {
            color: var(--text-muted-dark);
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
        /* Trusted section */
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
        .testimonial-swiper .swiper-pagination-bullet {
            background: var(--primary);
        }
        .testimonial-swiper .swiper-button-next,
        .testimonial-swiper .swiper-button-prev {
            color: var(--primary);
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
        /* Floating buttons */
        .floating-testimonial, .floating-chat {
            position: fixed;
            z-index: 99;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
        }
        .floating-testimonial {
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            width: 60px;
            height: 60px;
            border-radius: 30px;
            font-size: 1.8rem;
        }
        .floating-chat {
            bottom: 30px;
            left: 30px;
            background: var(--primary);
            width: 56px;
            height: 56px;
            border-radius: 30px;
            font-size: 1.6rem;
        }
        .floating-testimonial:hover, .floating-chat:hover { transform: scale(1.1); }
        @media (max-width: 768px) {
            .floating-testimonial { width: 50px; height: 50px; font-size: 1.4rem; bottom: 100px; }
            .floating-chat { bottom: 30px; left: 30px; }
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
        .text-muted-custom {
            color: var(--text-muted-light);
        }
        body.dark .text-muted-custom {
            color: var(--text-muted-dark);
        }
        /* Modal styles */
        .modal-content {
            background: var(--surface-dark);
            color: var(--text-dark);
            border-radius: 1.5rem;
        }
        body.light .modal-content {
            background: var(--surface-light);
            color: var(--text-light);
        }
        .form-control {
            background: rgba(255,255,255,0.1);
            border: 1px solid var(--border-light);
            border-radius: 1rem;
            color: var(--text-dark);
        }
        body.light .form-control {
            background: var(--bg-light);
            color: var(--text-light);
        }
        /* Read-more */
        .truncated-text { display: inline; }
        .full-text { display: none; }
        .read-more-btn {
            color: var(--primary);
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: 5px;
            text-decoration: none;
        }
        .read-more-btn:hover { text-decoration: underline; }
        .card-text-wrapper { margin-bottom: 0.5rem; }
    </style>
</head>
<body>

<!-- Navbar with Logo (path: uploads/logo.png) -->
<nav class="navbar navbar-expand-lg glass-nav" id="mainNavbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="uploads/logo.png" alt="ARTECH Logo">
            ARTECH
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
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
    <!-- Hero Slider with Overlay -->
    <section class="hero-slider">
        <div class="swiper heroSwiper">
            <div class="swiper-wrapper">
                <?php foreach($sliders as $slide): ?>
                <div class="swiper-slide">
                    <img src="<?php echo htmlspecialchars($slide['image_url']); ?>" class="slide-bg" alt="<?php echo htmlspecialchars($slide['title']); ?>">
                    <div class="slide-overlay"></div>
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

    <!-- Our Services -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">What We Do</span>
                <h2 class="display-5 fw-bold mt-2">Our Services</h2>
                <p class="text-muted-custom">Cutting-edge AR/VR solutions for modern enterprises</p>
            </div>
            <div class="row g-4">
                <?php foreach($services as $service): ?>
                <div class="col-6 col-md-4 col-lg-3 service-col">
                    <a href="service-details.php?id=<?php echo $service['id']; ?>" class="text-decoration-none">
                        <div class="glass-card h-100 d-flex flex-column">
                            <?php if(!empty($service['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($service['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($service['title']); ?>">
                            <?php else: ?>
                                <div class="card-img-top d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); height: 200px;">
                                    <i class="<?php echo htmlspecialchars($service['icon_class']); ?> fa-4x text-white"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                                <div class="card-text-wrapper description-text">
                                    <span class="truncated-text"><?php echo htmlspecialchars(substr($service['description'], 0, 100)); ?><?php echo strlen($service['description']) > 100 ? '...' : ''; ?></span>
                                    <span class="full-text"><?php echo htmlspecialchars($service['description']); ?></span>
                                    <?php if(strlen($service['description']) > 100): ?>
                                    <span class="read-more-btn" onclick="toggleReadMore(this)">Read more</span>
                                    <?php endif; ?>
                                </div>
                                <?php if(!empty($service['price']) && $service['price'] > 0): ?>
                                    <div class="mt-2"><span class="badge bg-success fs-6">$<?php echo number_format($service['price'], 2); ?></span></div>
                                <?php endif; ?>
                                <span class="btn btn-outline-custom btn-sm mt-2">View Details →</span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Our Courses -->
    <section class="py-5" style="background: rgba(124,58,237,0.03);">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">Academy</span>
                <h2 class="display-5 fw-bold">Master Immersive Tech</h2>
                <p class="text-muted-custom">Expert-led courses for the future</p>
            </div>
            <div class="row g-4">
                <?php foreach($courses as $course): ?>
                <div class="col-6 col-md-4 col-lg-3 course-col">
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
                                <div class="card-text-wrapper description-text">
                                    <span class="truncated-text"><?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?><?php echo strlen($course['description']) > 100 ? '...' : ''; ?></span>
                                    <span class="full-text"><?php echo htmlspecialchars($course['description']); ?></span>
                                    <?php if(strlen($course['description']) > 100): ?>
                                    <span class="read-more-btn" onclick="toggleReadMore(this)">Read more</span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
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

    <!-- Portfolio Projects -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Our Work</span>
                <h2 class="display-5 fw-bold">Portfolio Projects</h2>
                <p class="text-muted-custom">Real-world impact with immersive technology</p>
            </div>
            <div class="row g-4">
                <?php foreach($allPortfolios as $project): ?>
                <div class="col-6 col-md-4 col-lg-3 project-col">
                    <div class="glass-card h-100 d-flex flex-column">
                        <img src="<?php echo htmlspecialchars($project['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($project['title']); ?>">
                        <div class="card-body">
                            <h4 class="fs-5"><?php echo htmlspecialchars($project['title']); ?></h4>
                            <p class="text-muted-custom small"><i class="fas fa-building"></i> <?php echo htmlspecialchars($project['client']); ?></p>
                            <div class="card-text-wrapper description-text">
                                <span class="truncated-text"><?php echo htmlspecialchars(substr($project['description'], 0, 70)); ?><?php echo strlen($project['description']) > 70 ? '...' : ''; ?></span>
                                <span class="full-text"><?php echo htmlspecialchars($project['description']); ?></span>
                                <?php if(strlen($project['description']) > 70): ?>
                                <span class="read-more-btn" onclick="toggleReadMore(this)">Read more</span>
                                <?php endif; ?>
                            </div>
                            <a href="portfolio-details.php?id=<?php echo $project['id']; ?>" class="btn btn-outline-custom btn-sm mt-2">View Details</a>
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

    <!-- Counters -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="counter-card">
                        <i class="fas fa-users fa-3x mb-2 text-primary"></i>
                        <h2 class="counter-num" data-target="<?php echo $totalCustomers; ?>">0</h2>
                        <p>Happy Customers</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="counter-card">
                        <i class="fas fa-globe fa-3x mb-2 text-primary"></i>
                        <h2 class="counter-num" data-target="<?php echo $totalCountries; ?>">0</h2>
                        <p>Countries Served</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="counter-card">
                        <i class="fas fa-map-marker-alt fa-3x mb-2 text-primary"></i>
                        <h2 class="counter-num" data-target="<?php echo $activeDistricts; ?>">0</h2>
                        <p>Bangladeshi Districts</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bangladesh Map -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-4">
                <h2>Our Reach in Bangladesh</h2>
                <p><i class="fas fa-circle text-success"></i> Active AR Service Areas (Districts)</p>
            </div>
            <div id="bangladeshMap"></div>
        </div>
    </section>

    <!-- Trusted by Innovators -->
    <section class="trusted-section py-5">
        <div class="container">
            <div class="text-center mb-5"><h2>Trusted by Innovators</h2><p class="text-muted-custom">Global leaders who trust our expertise</p></div>
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

    <!-- Testimonials -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>What Our Clients Say</h2>
                <p class="text-muted-custom">Real feedback from real partners</p>
            </div>
            <div class="swiper testimonialSwiper">
                <div class="swiper-wrapper">
                    <?php foreach($testimonials as $testimonial): ?>
                    <div class="swiper-slide">
                        <div class="testimonial-card h-100">
                            <i class="fas fa-quote-left fa-2x text-primary mb-3 opacity-50"></i>
                            <div class="card-text-wrapper description-text">
                                <span class="truncated-text"><?php echo htmlspecialchars(substr($testimonial['testimonial_text'], 0, 120)); ?><?php echo strlen($testimonial['testimonial_text']) > 120 ? '...' : ''; ?></span>
                                <span class="full-text"><?php echo htmlspecialchars($testimonial['testimonial_text']); ?></span>
                                <?php if(strlen($testimonial['testimonial_text']) > 120): ?>
                                <span class="read-more-btn" onclick="toggleReadMore(this)">Read more</span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-3">
                                <h5 class="mb-0"><?php echo htmlspecialchars($testimonial['client_name']); ?></h5>
                                <small class="text-muted-custom">
                                    <?php echo htmlspecialchars($testimonial['client_title'] ?? '') . (!empty($testimonial['company']) ? ', ' . htmlspecialchars($testimonial['company']) : ''); ?>
                                </small>
                                <div class="mt-2">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="fas fa-star<?php echo ($i <= $testimonial['rating']) ? '' : '-o'; ?>" style="color: #f59e0b;"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination testimonial-pagination"></div>
                <div class="swiper-button-next testimonial-next"></div>
                <div class="swiper-button-prev testimonial-prev"></div>
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
<?php include 'footer.php'; ?>

<!-- Registration Modal -->
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Register to Continue</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="registerForm">
                    <div class="mb-3"><label class="form-label">Your Name *</label><input type="text" id="regName" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email Address *</label><input type="email" id="regEmail" class="form-control" required></div>
                    <button type="submit" class="btn btn-primary-custom w-100">Register & Continue</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Testimonial Submit Modal -->
<div class="modal fade" id="testimonialModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-star me-2"></i>Share Your Experience</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="testimonialForm">
                    <div class="mb-3"><label>Your Name</label><input type="text" id="testimonialName" class="form-control" readonly></div>
                    <div class="mb-3"><label>Email</label><input type="email" id="testimonialEmail" class="form-control" readonly></div>
                    <div class="mb-3"><label>Rating</label><select id="testimonialRating" class="form-select">
                        <option value="5">★★★★★ (5)</option><option value="4">★★★★☆ (4)</option>
                        <option value="3">★★★☆☆ (3)</option><option value="2">★★☆☆☆ (2)</option>
                        <option value="1">★☆☆☆☆ (1)</option>
                    </select></div>
                    <div class="mb-3"><label>Your Testimonial *</label><textarea id="testimonialText" rows="3" class="form-control" required></textarea></div>
                    <button type="submit" class="btn btn-primary-custom w-100">Submit Testimonial</button>
                </form>
                <div id="testimonialMessage" class="mt-3 text-center"></div>
            </div>
        </div>
    </div>
</div>

<!-- Chat Modal -->
<div class="modal fade" id="chatModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-comment-dots me-2"></i>Live Chat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="chatForm">
                    <div class="mb-3"><label>Your Name</label><input type="text" id="chatName" class="form-control" readonly></div>
                    <div class="mb-3"><label>Email</label><input type="email" id="chatEmail" class="form-control" readonly></div>
                    <div class="mb-3"><label>Message *</label><textarea id="chatMessage" rows="3" class="form-control" required></textarea></div>
                    <button type="submit" class="btn btn-primary-custom w-100">Send Message</button>
                </form>
                <div id="chatMessageDiv" class="mt-3 text-center"></div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Buttons -->
<div class="floating-testimonial" id="testimonialBtn"><i class="fas fa-star"></i></div>
<div class="floating-chat" id="chatBtn"><i class="fas fa-comment-dots"></i></div>
<div class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Full height for hero slider
    function setFullHeight() { 
        const hero = document.querySelector('.hero-slider'); 
        if(hero) hero.style.height = window.innerHeight + 'px';
    }
    window.addEventListener('resize', setFullHeight);
    setFullHeight();

    // Hero Swiper
    new Swiper('.heroSwiper', { 
        loop: true, 
        autoplay: { delay: 5000, disableOnInteraction: false }, 
        effect: 'fade', 
        pagination: { el: '.swiper-pagination', clickable: true }, 
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' } 
    });

    // Logo Swiper
    new Swiper('.logoSlider', { 
        slidesPerView: 2, 
        spaceBetween: 15, 
        loop: true, 
        autoplay: { delay: 2500, disableOnInteraction: false }, 
        breakpoints: { 576: { slidesPerView: 3 }, 768: { slidesPerView: 4 }, 1024: { slidesPerView: 6 } }, 
        pagination: { el: '.logo-pagination', clickable: true }, 
        navigation: { nextEl: '.logo-next', prevEl: '.logo-prev' } 
    });

    // Testimonial Swiper
    new Swiper('.testimonialSwiper', {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        autoplay: { delay: 4000, disableOnInteraction: false },
        pagination: { el: '.testimonial-pagination', clickable: true },
        navigation: { nextEl: '.testimonial-next', prevEl: '.testimonial-prev' },
        breakpoints: { 640: { slidesPerView: 2 }, 992: { slidesPerView: 3 } }
    });

    // Counters animation
    function initCounters() {
        const counters = document.querySelectorAll('.counter-num');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const c = entry.target;
                    const target = +c.dataset.target;
                    let curr = 0;
                    const inc = target / 50;
                    const upd = () => {
                        curr += inc;
                        if (curr < target) {
                            c.innerText = Math.ceil(curr);
                            requestAnimationFrame(upd);
                        } else {
                            c.innerText = target;
                        }
                    };
                    upd();
                    observer.unobserve(c);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(c => observer.observe(c));
    }

    // Bangladesh Map
    let map;
    function initMap() {
        const activeDistricts = <?php echo json_encode($activeDistrictsData); ?>;
        const isDark = document.body.classList.contains('dark');
        const tileUrl = isDark ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png' : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
        map = L.map('bangladeshMap').setView([23.8, 90.3], 7.2);
        L.tileLayer(tileUrl, { attribution: '&copy; OSM' }).addTo(map);
        activeDistricts.forEach(d => {
            L.circleMarker([d.lat, d.lng], { radius: 12, fillColor: "#10b981", color: "#fff", weight: 2, fillOpacity: 0.85 })
                .addTo(map)
                .bindPopup(`<b>${d.name} District</b><br>✅ Active AR Service Area`);
        });
    }
    function updateMapTiles() {
        if (!map) return;
        const isDark = document.body.classList.contains('dark');
        const newTile = isDark ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png' : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
        map.eachLayer(layer => { if (layer instanceof L.TileLayer) map.removeLayer(layer); });
        L.tileLayer(newTile, { attribution: '&copy; OSM' }).addTo(map);
    }

    // Dark Mode
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
            updateMapTiles();
        });
    }

    // Navbar scroll effect
    function initNavbarScroll() {
        const navbar = document.querySelector('.glass-nav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) navbar.classList.add('scrolled');
            else navbar.classList.remove('scrolled');
        });
    }

    // Back to top
    function initBackToTop() {
        const btn = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) btn.classList.add('show');
            else btn.classList.remove('show');
        });
        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // Read more toggle
    function toggleReadMore(btn) {
        const wrapper = btn.parentNode;
        const truncated = wrapper.querySelector('.truncated-text');
        const full = wrapper.querySelector('.full-text');
        if (full.style.display === 'none' || getComputedStyle(full).display === 'none') {
            truncated.style.display = 'none';
            full.style.display = 'inline';
            btn.innerText = 'Read less';
        } else {
            truncated.style.display = 'inline';
            full.style.display = 'none';
            btn.innerText = 'Read more';
        }
    }

    // Registration & modals logic
    let userRegistered = sessionStorage.getItem('userRegistered') === 'true';
    let userName = sessionStorage.getItem('userName') || '';
    let userEmail = sessionStorage.getItem('userEmail') || '';

    const registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
    const testimonialModal = new bootstrap.Modal(document.getElementById('testimonialModal'));
    const chatModal = new bootstrap.Modal(document.getElementById('chatModal'));

    function openTestimonialModal() {
        if (userRegistered) {
            document.getElementById('testimonialName').value = userName;
            document.getElementById('testimonialEmail').value = userEmail;
            testimonialModal.show();
        } else {
            registerModal.show();
            sessionStorage.setItem('pendingAction', 'testimonial');
        }
    }
    function openChatModal() {
        if (userRegistered) {
            document.getElementById('chatName').value = userName;
            document.getElementById('chatEmail').value = userEmail;
            chatModal.show();
        } else {
            registerModal.show();
            sessionStorage.setItem('pendingAction', 'chat');
        }
    }

    document.getElementById('registerForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const name = document.getElementById('regName').value.trim();
        const email = document.getElementById('regEmail').value.trim();
        if (name && email) {
            sessionStorage.setItem('userRegistered', 'true');
            sessionStorage.setItem('userName', name);
            sessionStorage.setItem('userEmail', email);
            userRegistered = true; userName = name; userEmail = email;
            registerModal.hide();
            const action = sessionStorage.getItem('pendingAction');
            if (action === 'testimonial') openTestimonialModal();
            else if (action === 'chat') openChatModal();
            sessionStorage.removeItem('pendingAction');
        }
    });

    document.getElementById('testimonialBtn').addEventListener('click', () => {
        if (!userRegistered) {
            sessionStorage.setItem('pendingAction', 'testimonial');
            registerModal.show();
        } else openTestimonialModal();
    });
    document.getElementById('chatBtn').addEventListener('click', () => {
        if (!userRegistered) {
            sessionStorage.setItem('pendingAction', 'chat');
            registerModal.show();
        } else openChatModal();
    });

    // Submit testimonial
    document.getElementById('testimonialForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData();
        fd.append('client_name', userName);
        fd.append('email', userEmail);
        fd.append('testimonial_text', document.getElementById('testimonialText').value);
        fd.append('rating', document.getElementById('testimonialRating').value);
        const res = await fetch('submit_testimonial.php', { method: 'POST', body: fd });
        const data = await res.json();
        const msgDiv = document.getElementById('testimonialMessage');
        if (data.success) {
            msgDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
            setTimeout(() => { testimonialModal.hide(); msgDiv.innerHTML = ''; document.getElementById('testimonialForm').reset(); }, 3000);
        } else {
            msgDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    });

    // Submit chat
    document.getElementById('chatForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData();
        fd.append('name', userName);
        fd.append('email', userEmail);
        fd.append('message', document.getElementById('chatMessage').value);
        const res = await fetch('submit_chat.php', { method: 'POST', body: fd });
        const data = await res.json();
        const msgDiv = document.getElementById('chatMessageDiv');
        if (data.success) {
            msgDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
            setTimeout(() => { chatModal.hide(); msgDiv.innerHTML = ''; document.getElementById('chatForm').reset(); }, 3000);
        } else {
            msgDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
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