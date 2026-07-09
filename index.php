<?php
// index.php - Ultimate 3D Tech Homepage with Perfect Image Display and Rounded Navbar Container
require_once 'config.php';

// Fetch all dynamic content
$sliders = $pdo->query("SELECT * FROM sliders WHERE status = 1 ORDER BY order_position ASC")->fetchAll();
$services = $pdo->query("SELECT * FROM services WHERE status = 1 ORDER BY order_position ASC, id ASC")->fetchAll();
$allPortfolios = $pdo->query("SELECT * FROM portfolios WHERE status = 1 ORDER BY order_position ASC, id DESC")->fetchAll();
$testimonials = $pdo->query("SELECT * FROM testimonials WHERE status = 1 ORDER BY order_position ASC, id DESC")->fetchAll();
$chairmanSpeech = $pdo->query("SELECT * FROM chairman_speech WHERE is_active = 1 LIMIT 1")->fetch();
$courses = $pdo->query("SELECT * FROM courses WHERE status = 1 ORDER BY order_position ASC, id ASC")->fetchAll();

$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers WHERE is_active = 1")->fetchColumn();
$totalCountries = $pdo->query("SELECT COUNT(DISTINCT country) FROM customers WHERE is_active = 1")->fetchColumn();
$activeDistricts = $pdo->query("SELECT COUNT(DISTINCT district) FROM customers WHERE district IS NOT NULL AND country = 'Bangladesh' AND district != ''")->fetchColumn();
$allCustomers = $pdo->query("SELECT customer_name, logo_url, country FROM customers WHERE is_active = 1 ORDER BY customer_name")->fetchAll();

$activeDistrictList = $pdo->query("SELECT DISTINCT district FROM customers WHERE country = 'Bangladesh' AND district IS NOT NULL AND district != ''")->fetchAll(PDO::FETCH_COLUMN);

$districtCoordinates = [
    'Bagerhat' => [22.6515, 89.7855], 'Bandarban' => [22.1953, 92.2183], 'Barguna' => [22.1543, 90.1265],
    'Barishal' => [22.7000, 90.3500], 'Bhola' => [22.6859, 90.6513], 'Bogra' => [24.8467, 89.3667],
    'Brahmanbaria' => [23.9571, 91.1091], 'Chandpur' => [23.2199, 90.6498], 'Chattogram' => [22.3384, 91.8317],
    'Chuadanga' => [23.6420, 88.8473], "Cox's Bazar" => [21.4272, 92.0058], 'Cumilla' => [23.4610, 91.1865],
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
    <meta name="description" content="AR Tech Solutions - 3D Immersive Reality Experiences">
    <title>AR Tech Solutions | 3D Immersive Reality</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        /* ========== ALL CSS (PERFECT IMAGE DISPLAY) ========== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Space Grotesk', sans-serif;
            background: #050b17;
            color: #eef5ff;
            overflow-x: hidden;
            transition: background 0.3s, color 0.3s;
        }
        #three-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }
        .main-content { position: relative; z-index: 2; }
        
        /* ========== ROUNDED CONTAINER NAVBAR (NEW DESIGN) ========== */
        .glass-nav {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 92%;
            max-width: 1400px;
            z-index: 1030;
            background: rgba(5, 11, 23, 0.85);
            backdrop-filter: blur(16px);
            border-radius: 60px;
            border: 1px solid rgba(0, 212, 255, 0.4);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2), 0 0 20px rgba(0, 212, 255, 0.1);
            padding: 0.5rem 1.5rem;
            transition: all 0.3s;
        }
        .glass-nav.scrolled {
            top: 12px;
            background: rgba(5, 11, 23, 0.95);
            border-color: rgba(0, 212, 255, 0.6);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), 0 0 30px rgba(0, 212, 255, 0.2);
        }
        .glass-nav .container {
            padding: 0;
            max-width: 100%;
        }
        .navbar-brand {
            font-family: 'Orbitron', monospace;
            font-size: 1.7rem;
            font-weight: 800;
            background: linear-gradient(135deg, #7b2fff, #00d4ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .navbar-brand img {
            height: 40px;
            filter: brightness(1.1);
            display: inline-block;
            margin-right: 8px;
        }
        .nav-link {
            font-family: 'Orbitron', monospace;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(238, 245, 255, 0.8) !important;
            border-radius: 40px;
            transition: 0.25s;
            padding: 0.5rem 1rem !important;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(0, 212, 255, 0.25);
            color: #fff !important;
            box-shadow: 0 0 12px rgba(0,212,255,0.3);
        }
        #darkModeToggle {
            background: rgba(0,212,255,0.15);
            border: 1px solid cyan;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: cyan;
            transition: 0.2s;
        }
        .btn-nav {
            background: linear-gradient(90deg, #7b2fff, #00d4ff);
            border: none;
            padding: 0.5rem 1.4rem;
            border-radius: 40px;
            font-family: 'Orbitron', monospace;
            font-weight: 700;
            color: white;
            font-size: 0.75rem;
            box-shadow: 0 0 12px rgba(123,47,255,0.5);
            transition: 0.2s;
        }
        .btn-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(123,47,255,0.7);
        }
        /* Rest of the CSS remains exactly as before */
        .hero-section {
            position: relative;
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .heroSwiper, .heroSwiper .swiper-wrapper, .heroSwiper .swiper-slide { height: 100%; }
        .slide-bg {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            object-fit: cover;
        }
        .slide-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(5,11,23,0.85) 0%, rgba(5,11,23,0.5) 60%, rgba(5,11,23,0.8) 100%);
            z-index: 1;
        }
        .hero-content {
            position: relative; z-index: 2;
            text-align: center;
            padding: 0 1rem;
        }
        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: 0.5rem;
            font-family: 'Orbitron', monospace;
            font-size: 0.7rem;
            letter-spacing: 0.2em;
            background: rgba(0,212,255,0.2);
            border: 1px solid #00d4ff;
            border-radius: 40px;
            padding: 0.3rem 1rem;
            color: #00d4ff;
            margin-bottom: 1.5rem;
            animation: fadeInDown 0.8s ease both;
        }
        .hero-eyebrow::before {
            content: ''; width: 6px; height: 6px; border-radius: 50%;
            background: #00d4ff;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.4;transform:scale(0.6)} }
        @keyframes fadeInDown { from{opacity:0;transform:translateY(-20px)} to{opacity:1;transform:translateY(0)} }
        @keyframes fadeInUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
        .hero-title {
            font-family: 'Orbitron', monospace;
            font-size: clamp(2.5rem, 7vw, 6rem);
            font-weight: 800;
            background: linear-gradient(135deg, #fff, #00d4ff, #7b2fff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: fadeInUp 0.9s ease 0.2s both;
            margin-bottom: 1.2rem;
        }
        .hero-subtitle {
            font-size: clamp(1rem, 2vw, 1.3rem);
            color: rgba(238,245,255,0.8);
            animation: fadeInUp 0.9s ease 0.35s both;
            max-width: 600px; margin: 0 auto 2.5rem;
        }
        .hero-actions {
            display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;
            animation: fadeInUp 0.9s ease 0.5s both;
        }
        .heroSwiper .swiper-button-next, .heroSwiper .swiper-button-prev {
            color: #00d4ff;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(8px);
            width: 44px; height: 44px; border-radius: 50%;
        }
        .btn-3d-primary, .btn-3d-secondary, .btn-card, .btn-nav {
            font-family: 'Orbitron', monospace;
            font-weight: 700;
            border-radius: 40px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.25s ease;
            cursor: pointer;
            border: none;
        }
        .btn-3d-primary {
            background: linear-gradient(90deg, #7b2fff, #00c3ff);
            color: white;
            padding: 0.8rem 2rem;
            box-shadow: 0 8px 20px rgba(123,47,255,0.4);
        }
        .btn-3d-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(123,47,255,0.6);
            color: white;
        }
        .btn-3d-secondary {
            border: 2px solid #00d4ff;
            color: #00d4ff;
            background: transparent;
            padding: 0.75rem 1.9rem;
        }
        .btn-3d-secondary:hover {
            background: rgba(0,212,255,0.15);
            transform: translateY(-3px);
            color: #00d4ff;
        }
        .btn-card {
            background: rgba(0,212,255,0.15);
            border: 1px solid rgba(0,212,255,0.4);
            color: #00d4ff;
            padding: 0.4rem 1.2rem;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        .btn-card:hover {
            background: rgba(0,212,255,0.3);
            transform: translateX(3px);
            color: white;
        }
        .card-3d, .counter-3d-card, .chairman-card, .testimonial-3d, .cta-3d-card {
            background: rgba(8, 16, 32, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 24px;
            transition: 0.3s;
        }
        .card-3d:hover, .counter-3d-card:hover, .testimonial-3d:hover {
            transform: translateY(-8px);
            border-color: #00d4ff;
            box-shadow: 0 0 30px rgba(0,212,255,0.2);
        }
        .section-title {
            font-family: 'Orbitron', monospace;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            background: linear-gradient(120deg, #fff, #00d4ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .section-label {
            background: rgba(0,212,255,0.15);
            border: 1px solid #00d4ff;
            border-radius: 40px;
            padding: 0.2rem 1rem;
            display: inline-block;
            font-family: 'Orbitron', monospace;
            font-size: 0.7rem;
            letter-spacing: 0.1em;
        }
        .bg-grid {
            background-image: linear-gradient(rgba(0,212,255,0.05) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(0,212,255,0.05) 1px, transparent 1px);
            background-size: 60px 60px;
        }
        .bg-dot {
            background-image: radial-gradient(rgba(0,212,255,0.12) 1px, transparent 1px);
            background-size: 30px 30px;
        }
        .logo-card {
            background: rgba(8,16,32,0.5);
            border: 1px solid rgba(0,212,255,0.3);
            border-radius: 16px;
            padding: 1rem;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 80px;
        }
        /* ===== FIX: Trusted by Innovators logos display fully ===== */
        .logo-card img {
            max-height: 70px;   /* increased from 50px to show full logos */
            max-width: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: grayscale(30%) brightness(1.1);
            transition: 0.3s;
        }
        .logo-card:hover {
            border-color: #00d4ff;
            box-shadow: 0 0 20px rgba(0,212,255,0.2);
        }
        .logo-card:hover img {
            filter: grayscale(0%) brightness(1.2);
        }
        #bangladeshMap {
            height: 500px;
            border-radius: 24px;
            border: 2px solid #00d4ff;
            box-shadow: 0 0 30px rgba(0,212,255,0.3);
        }
        footer {
            background: rgba(3, 6, 18, 0.95);
            border-top: 1px solid #00d4ff;
            padding: 3rem 0 1.5rem;
            margin-top: 3rem;
        }
        .footer-brand {
            font-family: 'Orbitron', monospace;
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #7b2fff, #00d4ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .footer-heading {
            font-family: 'Orbitron', monospace;
            font-size: 0.75rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #00d4ff;
            margin-bottom: 1rem;
        }
        footer a {
            color: rgba(238,245,255,0.6);
            text-decoration: none;
            transition: 0.2s;
        }
        footer a:hover { color: #00d4ff; }
        .footer-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(0,212,255,0.1);
            border: 1px solid rgba(0,212,255,0.3);
            color: #00d4ff;
            transition: 0.2s;
            font-size: 1.2rem;
        }
        .footer-social a:hover {
            background: #00d4ff;
            color: #050b17;
            transform: translateY(-3px);
        }
        .fab {
            position: fixed;
            z-index: 1050;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(0,212,255,0.2);
            backdrop-filter: blur(8px);
            border: 1px solid cyan;
            color: cyan;
            transition: 0.2s;
        }
        .fab-star { bottom: 30px; right: 30px; }
        .fab-chat { bottom: 30px; left: 30px; }
        .fab:hover { transform: scale(1.1); background: rgba(0,212,255,0.4); }
        .back-to-top {
            position: fixed;
            bottom: 100px;
            right: 30px;
            background: rgba(0,212,255,0.25);
            border-radius: 20px;
            padding: 8px 14px;
            cursor: pointer;
            opacity: 0;
            transition: 0.2s;
            z-index: 1050;
        }
        body.light {
            background: #f0f4fc;
            color: #1a1a2e;
        }
        body.light .glass-nav {
            background: rgba(240, 244, 252, 0.9);
            border-bottom-color: rgba(123, 47, 255, 0.3);
        }
        body.light .glass-nav.scrolled { background: rgba(240, 244, 252, 0.98); }
        body.light .nav-link { color: rgba(26, 26, 46, 0.8) !important; }
        body.light .nav-link:hover { background: rgba(123, 47, 255, 0.1); color: #7b2fff !important; }
        body.light .btn-nav { background: linear-gradient(90deg, #7b2fff, #00c3ff); color: white; }
        body.light .card-3d, body.light .counter-3d-card, body.light .chairman-card, 
        body.light .testimonial-3d, body.light .cta-3d-card, body.light .logo-card {
            background: rgba(255, 255, 255, 0.8);
            border-color: rgba(123, 47, 255, 0.2);
        }
        body.light .section-title { background: linear-gradient(120deg, #1a1a2e, #7b2fff); -webkit-background-clip: text; }
        body.light .hero-title { background: linear-gradient(135deg, #1a1a2e, #7b2fff, #00d4ff); -webkit-background-clip: text; }
        body.light .hero-subtitle, body.light .card-desc, body.light .text-white-50 { color: #2a2a48 !important; }
        body.light footer { background: rgba(240, 244, 252, 0.95); border-top-color: #7b2fff; }
        body.light footer a { color: #2a2a48; }
        body.light .footer-social a { background: rgba(123,47,255,0.1); border-color: #7b2fff; color: #7b2fff; }
        body.light .btn-3d-secondary { border-color: #7b2fff; color: #7b2fff; }
        body.light .btn-3d-secondary:hover { background: rgba(123,47,255,0.1); }
        body.light .section-label { border-color: #7b2fff; color: #7b2fff; background: rgba(123,47,255,0.1); }
        body.light .btn-card { background: rgba(123,47,255,0.1); border-color: #7b2fff; color: #7b2fff; }
        .card-img-top, .card-3d img, .chairman-avatar, .logo-card img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 12px;
        }
        .card-3d img { height: 180px; object-fit: cover; }
        @media (max-width: 768px) {
            .hero-title { font-size: 2rem; }
            .glass-nav { width: 95%; top: 12px; padding: 0.3rem 1rem; }
            .navbar-collapse { background: rgba(5,11,23,0.95); border-radius: 20px; padding: 1rem; margin-top: 0.8rem; }
            body.light .navbar-collapse { background: rgba(240,244,252,0.95); }
            .card-3d img { height: 140px; }
            .logo-card img { max-height: 50px; } /* adapt for mobile */
        }
    </style>
</head>
<body>

<!-- 3D Canvas Background - Neural Network / Connected Nodes Tech Design -->
<canvas id="three-canvas"></canvas>

<div class="main-content">
    <!-- Navigation - Rounded Floating Container -->
    <nav class="navbar navbar-expand-lg glass-nav" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="uploads/logo.png" alt="ARTECH" style="height:40px; filter:brightness(1.1); display:inline-block; margin-right:8px;">
                ARTECH
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars text-white"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="portfolio.php">Portfolio</a></li>
                    <li class="nav-item"><a class="nav-link" href="chairman-speech.php">Chairman</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                </ul>
                <button id="darkModeToggle" class="ms-3"><i class="fas fa-moon"></i></button>
            </div>
        </div>
    </nav>

    <!-- Hero Slider (unchanged) -->
    <section class="hero-section">
        <div class="swiper heroSwiper" style="position:absolute; inset:0;">
            <div class="swiper-wrapper">
                <?php foreach($sliders as $slide): ?>
                <div class="swiper-slide">
                    <img src="<?php echo htmlspecialchars($slide['image_url']); ?>" class="slide-bg" alt="<?php echo htmlspecialchars($slide['title']); ?>">
                    <div class="slide-overlay"></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
        <div class="hero-content container">
            <span class="hero-eyebrow">Immersive Reality Technology</span>
            <?php $firstSlide = $sliders[0] ?? null; ?>
            <h1 class="hero-title">
                <?php if($firstSlide): ?>
                    <span class="accent-word"><?php echo htmlspecialchars($firstSlide['title']); ?></span>
                <?php else: ?>
                    The Future <span class="accent-word">Starts Here</span>
                <?php endif; ?>
            </h1>
            <p class="hero-subtitle">
                <?php echo $firstSlide ? htmlspecialchars($firstSlide['subtitle']) : 'Cutting-edge AR/VR solutions transforming industries worldwide.'; ?>
            </p>
            <div class="hero-actions">
                <?php if($firstSlide && $firstSlide['button_text']): ?>
                <a href="<?php echo htmlspecialchars($firstSlide['button_link']); ?>" class="btn-3d-primary">
                    <i class="fas fa-rocket"></i> <?php echo htmlspecialchars($firstSlide['button_text']); ?>
                </a>
                <?php else: ?>
                <a href="services.php" class="btn-3d-primary"><i class="fas fa-rocket"></i> Explore Services</a>
                <?php endif; ?>
                <a href="portfolio.php" class="btn-3d-secondary"><i class="fas fa-play-circle"></i> View Portfolio</a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section bg-grid py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label"><i class="fas fa-cube"></i> What We Do</span>
                <h2 class="section-title mt-2">Our <span class="gradient-text">Services</span></h2>
                <div style="width:60px; height:3px; background:linear-gradient(90deg, #7b2fff, #00d4ff); margin:1rem auto;"></div>
            </div>
            <div class="row g-4">
                <?php foreach($services as $service): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="service-details.php?id=<?php echo $service['id']; ?>" class="text-decoration-none">
                        <div class="card-3d p-3">
                            <div class="text-center">
                                <?php if(!empty($service['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($service['image_url']); ?>" class="img-fluid rounded-3" style="height:160px; width:100%; object-fit:cover;" alt="<?php echo htmlspecialchars($service['title']); ?>">
                                <?php else: ?>
                                    <div style="height:160px; display:flex; align-items:center; justify-content:center; background:rgba(0,212,255,0.1); border-radius:12px;">
                                        <i class="<?php echo htmlspecialchars($service['icon_class'] ?? 'fas fa-vr-cardboard'); ?> fa-3x" style="color:#00d4ff;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mt-3">
                                <h4 class="text-white fs-6 fw-bold"><?php echo htmlspecialchars($service['title']); ?></h4>
                                <p class="small text-white-50"><?php echo htmlspecialchars(substr($service['description'], 0, 80)); ?>...</p>
                                <span class="btn-card">View Details <i class="fas fa-arrow-right"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Courses Section -->
    <section class="courses-section bg-dot py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label"><i class="fas fa-graduation-cap"></i> Academy</span>
                <h2 class="section-title">Master Immersive Tech</h2>
                <div style="width:60px; height:3px; background:linear-gradient(90deg, #7b2fff, #00d4ff); margin:1rem auto;"></div>
            </div>
            <div class="row g-4">
                <?php foreach($courses as $course): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="course-details.php?id=<?php echo $course['id']; ?>" class="text-decoration-none">
                        <div class="card-3d p-3">
                            <?php if(!empty($course['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($course['image_url']); ?>" class="img-fluid rounded-3" style="height:140px; width:100%; object-fit:cover;" alt="<?php echo htmlspecialchars($course['title']); ?>">
                            <?php else: ?>
                                <div style="height:140px; display:flex; align-items:center; justify-content:center; background:rgba(0,212,255,0.1); border-radius:12px;">
                                    <i class="<?php echo htmlspecialchars($course['icon_class'] ?? 'fas fa-book'); ?> fa-3x" style="color:#00d4ff;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="mt-2">
                                <h5 class="text-white fs-6"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="small text-white-50"><?php echo htmlspecialchars(substr($course['description'], 0, 60)); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="badge bg-info">$<?php echo number_format($course['price'], 0); ?></span>
                                    <span class="btn-card" style="padding:0.2rem 0.8rem;">Details <i class="fas fa-arrow-right"></i></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5">
                <a href="courses.php" class="btn-3d-primary"><i class="fas fa-graduation-cap"></i> All Courses</a>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section class="portfolio-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label"><i class="fas fa-layer-group"></i> Our Work</span>
                <h2 class="section-title">Portfolio Projects</h2>
                <div style="width:60px; height:3px; background:linear-gradient(90deg, #7b2fff, #00d4ff); margin:1rem auto;"></div>
            </div>
            <div class="row g-4">
                <?php foreach($allPortfolios as $project): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card-3d p-2">
                        <img src="<?php echo htmlspecialchars($project['image_url']); ?>" class="img-fluid rounded-3" style="height:180px; width:100%; object-fit:cover;" alt="<?php echo htmlspecialchars($project['title']); ?>">
                        <div class="p-2">
                            <h6 class="text-white"><?php echo htmlspecialchars($project['title']); ?></h6>
                            <p class="small text-white-50"><i class="fas fa-building"></i> <?php echo htmlspecialchars($project['client']); ?></p>
                            <a href="portfolio-details.php?id=<?php echo $project['id']; ?>" class="btn-card">View Details <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5">
                <a href="portfolio.php" class="btn-3d-secondary"><i class="fas fa-th-large"></i> Browse All Projects</a>
            </div>
        </div>
    </section>

    <!-- Chairman Speech -->
    <?php if($chairmanSpeech): ?>
    <section class="chairman-section bg-grid py-5">
        <div class="container">
            <div class="chairman-card p-4 position-relative">
                <div class="quote-mark" style="font-size:5rem; opacity:0.1; position:absolute; top:10px; left:20px;">"</div>
                <div class="row align-items-center g-4">
                    <div class="col-md-3 text-center">
                        <img src="<?php echo htmlspecialchars($chairmanSpeech['image_url']); ?>" class="chairman-avatar" style="width:140px; height:140px; border-radius:50%; border:2px solid #00d4ff; object-fit:cover;">
                    </div>
                    <div class="col-md-9">
                        <span class="section-label"><i class="fas fa-microphone-alt"></i> Visionary Words</span>
                        <h3 class="text-white mt-2" style="font-family:'Orbitron';">Chairman's Message</h3>
                        <p class="text-white-50"><?php echo htmlspecialchars(substr($chairmanSpeech['speech_text'], 0, 240)); ?>…</p>
                        <a href="chairman-speech.php" class="btn-3d-primary btn-sm">Read Full Speech <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- COUNTERS SECTION -->
    <section class="counter-section py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="counter-3d-card p-4 text-center">
                        <i class="fas fa-map-marked-alt fa-2x" style="color:#00ff9d;"></i>
                        <div class="counter-num fs-1 fw-bold" data-target="<?php echo $activeDistricts; ?>">0</div>
                        <div class="counter-label">Districts Reached in Bangladesh</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="counter-3d-card p-4 text-center">
                        <i class="fas fa-smile-wink fa-2x" style="color:#ffcc44;"></i>
                        <div class="counter-num fs-1 fw-bold" data-target="<?php echo $totalCustomers; ?>">0</div>
                        <div class="counter-label">Happy Customers</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="counter-3d-card p-4 text-center">
                        <i class="fas fa-globe fa-2x" style="color:#00d4ff;"></i>
                        <div class="counter-num fs-1 fw-bold" data-target="<?php echo $totalCountries; ?>">0</div>
                        <div class="counter-label">Countries Served Worldwide</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bangladesh Map -->
    <section class="map-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label"><i class="fas fa-map-marked-alt"></i> Coverage</span>
                <h2 class="section-title">Our Reach in Bangladesh</h2>
                <div style="width:60px; height:3px; background:linear-gradient(90deg, #7b2fff, #00d4ff); margin:1rem auto;"></div>
            </div>
            <div id="bangladeshMap"></div>
        </div>
    </section>

    <!-- Trusted Partners -->
    <section class="trusted-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label"><i class="fas fa-shield-alt"></i> Partners</span>
                <h2 class="section-title">Trusted by Innovators</h2>
            </div>
            <div class="swiper logoSlider">
                <div class="swiper-wrapper">
                    <?php foreach($allCustomers as $cust): ?>
                    <div class="swiper-slide">
                        <div class="logo-card text-center">
                            <img src="<?php echo htmlspecialchars($cust['logo_url']); ?>" alt="<?php echo htmlspecialchars($cust['customer_name']); ?>">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials-section bg-dot py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label"><i class="fas fa-comments"></i> Reviews</span>
                <h2 class="section-title">What Clients Say</h2>
                <div style="width:60px; height:3px; background:linear-gradient(90deg, #7b2fff, #00d4ff); margin:1rem auto;"></div>
            </div>
            <div class="swiper testimonialSwiper">
                <div class="swiper-wrapper">
                    <?php foreach($testimonials as $t): ?>
                    <div class="swiper-slide">
                        <div class="testimonial-3d p-4">
                            <div class="stars text-warning mb-2">
                                <?php for($i=1;$i<=5;$i++): ?><i class="fas fa-star<?php echo $i<=$t['rating']?'':'-o'; ?>"></i><?php endfor; ?>
                            </div>
                            <p class="small"><?php echo htmlspecialchars(substr($t['testimonial_text'], 0, 120)); ?>…</p>
                            <div class="d-flex align-items-center gap-3 mt-3">
                                <div class="bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px; background:linear-gradient(135deg,#7b2fff,#00d4ff);">
                                    <span class="fw-bold text-white"><?php echo strtoupper(substr($t['client_name'],0,1)); ?></span>
                                </div>
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($t['client_name']); ?></div>
                                    <div class="small text-white-50"><?php echo htmlspecialchars($t['client_title'] ?? ''); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination testimonial-pagination mt-4"></div>
                <div class="swiper-button-next testimonial-next"></div>
                <div class="swiper-button-prev testimonial-prev"></div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section py-5">
        <div class="container">
            <div class="cta-3d-card p-5 text-center position-relative overflow-hidden">
                <div class="cta-orb position-absolute" style="width:300px;height:300px;background:#7b2fff;filter:blur(80px);top:-100px;right:-100px;opacity:0.3;"></div>
                <div class="cta-orb position-absolute" style="width:250px;height:250px;background:#00d4ff;filter:blur(80px);bottom:-80px;left:-80px;opacity:0.3;"></div>
                <h2 class="text-white">Ready to Transform<br>Your Business?</h2>
                <p class="text-white-50">Let's discuss your next immersive reality project.</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="contact.php" class="btn-3d-primary"><i class="fas fa-paper-plane"></i> Get in Touch</a>
                    <a href="services.php" class="btn-3d-secondary"><i class="fas fa-eye"></i> Our Services</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include "footer.php"; ?>
    <!-- Floating Buttons -->
    <button class="fab fab-star" id="testimonialBtn"><i class="fas fa-star"></i></button>
    <button class="fab fab-chat" id="chatBtn"><i class="fas fa-comment-dots"></i></button>
    <div class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></div>

    <!-- Modals -->
    <div class="modal fade" id="registerModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content bg-dark text-white"><div class="modal-header"><h5>Register</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><form id="registerForm"><input type="text" id="regName" class="form-control mb-2" placeholder="Name"><input type="email" id="regEmail" class="form-control mb-2" placeholder="Email"><button class="btn btn-info w-100">Continue</button></form></div></div></div></div>
    <div class="modal fade" id="testimonialModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content bg-dark text-white"><div class="modal-header"><h5>Leave Review</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><form id="testimonialForm"><input type="text" id="testimonialName" class="form-control mb-2" readonly><input type="email" id="testimonialEmail" class="form-control mb-2" readonly><select id="testimonialRating" class="form-select mb-2"><option value="5">★★★★★</option><option value="4">★★★★☆</option></select><textarea id="testimonialText" class="form-control" rows="3"></textarea><button class="btn btn-info mt-3 w-100">Submit</button></form><div id="testimonialMessage"></div></div></div></div></div>
    <div class="modal fade" id="chatModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content bg-dark text-white"><div class="modal-header"><h5>Chat</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><form id="chatForm"><input type="text" id="chatName" class="form-control mb-2" readonly><input type="email" id="chatEmail" class="form-control mb-2" readonly><textarea id="chatMessage" class="form-control" rows="3"></textarea><button class="btn btn-info mt-3 w-100">Send</button></form><div id="chatMessageDiv"></div></div></div></div></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script type="importmap">
        { "imports": { "three": "https://unpkg.com/three@0.128.0/build/three.module.js" } }
    </script>
    <script type="module">
        import * as THREE from 'three';
        
        // --- 3D BACKGROUND: Neural Network / Connected Nodes ---
        const canvas = document.getElementById('three-canvas');
        const renderer = new THREE.WebGLRenderer({ canvas, alpha: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0x050b17);
        scene.fog = new THREE.FogExp2(0x050b17, 0.004);
        
        const camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.set(0, 1.5, 14);
        
        // Lighting
        const ambientLight = new THREE.AmbientLight(0x111122);
        scene.add(ambientLight);
        const mainLight = new THREE.DirectionalLight(0xffffff, 1);
        mainLight.position.set(2, 3, 4);
        scene.add(mainLight);
        const fillLight = new THREE.PointLight(0x2266ff, 0.4);
        fillLight.position.set(-2, 1, 3);
        scene.add(fillLight);
        const backLight = new THREE.PointLight(0xff44aa, 0.3);
        backLight.position.set(0, 2, -5);
        scene.add(backLight);
        
        // Central core
        const coreGeo = new THREE.SphereGeometry(0.65, 32, 32);
        const coreMat = new THREE.MeshStandardMaterial({ color: 0x00aaff, emissive: 0x0088ff, emissiveIntensity: 0.8, metalness: 0.9, roughness: 0.2 });
        const core = new THREE.Mesh(coreGeo, coreMat);
        scene.add(core);
        
        const wireframeSphere = new THREE.Mesh(
            new THREE.SphereGeometry(0.9, 24, 18),
            new THREE.MeshBasicMaterial({ color: 0x00d4ff, wireframe: true, transparent: true, opacity: 0.3 })
        );
        scene.add(wireframeSphere);
        
        // Network nodes
        const nodeCount = 180;
        const nodePositions = [];
        const nodeGroup = new THREE.Group();
        for (let i = 0; i < nodeCount; i++) {
            const radius = 2.2 + Math.random() * 1.8;
            const theta = Math.random() * Math.PI * 2;
            const phi = Math.acos(2 * Math.random() - 1);
            const x = radius * Math.sin(phi) * Math.cos(theta);
            const y = radius * Math.sin(phi) * Math.sin(theta) * 0.8;
            const z = radius * Math.cos(phi);
            nodePositions.push(new THREE.Vector3(x, y, z));
            const sphereGeo = new THREE.SphereGeometry(0.045, 8, 8);
            const sphereMat = new THREE.MeshStandardMaterial({ color: 0x44ccff, emissive: 0x0088aa, emissiveIntensity: 0.3 });
            const nodeSphere = new THREE.Mesh(sphereGeo, sphereMat);
            nodeSphere.position.set(x, y, z);
            nodeGroup.add(nodeSphere);
        }
        scene.add(nodeGroup);
        
        // Connect close nodes with lines
        const lineMaterial = new THREE.LineBasicMaterial({ color: 0x00d4ff, transparent: true, opacity: 0.35 });
        for (let i = 0; i < nodePositions.length; i++) {
            for (let j = i + 1; j < nodePositions.length; j++) {
                if (nodePositions[i].distanceTo(nodePositions[j]) < 1.8) {
                    const points = [nodePositions[i], nodePositions[j]];
                    const geometry = new THREE.BufferGeometry().setFromPoints(points);
                    const line = new THREE.Line(geometry, lineMaterial);
                    scene.add(line);
                }
            }
        }
        
        // Floating particles
        const particleCount = 2500;
        const particlesGeo = new THREE.BufferGeometry();
        const positions = new Float32Array(particleCount * 3);
        for (let i = 0; i < particleCount; i++) {
            positions[i*3] = (Math.random() - 0.5) * 40;
            positions[i*3+1] = (Math.random() - 0.5) * 25;
            positions[i*3+2] = (Math.random() - 0.5) * 35 - 10;
        }
        particlesGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        const particleMat = new THREE.PointsMaterial({ color: 0x44aaff, size: 0.045, transparent: true, opacity: 0.5 });
        const particleSystem = new THREE.Points(particlesGeo, particleMat);
        scene.add(particleSystem);
        
        // Rotating rings
        const ringGeo = new THREE.TorusGeometry(2.1, 0.04, 64, 200);
        const ringMat = new THREE.MeshStandardMaterial({ color: 0x7b2fff, emissive: 0x3300aa, emissiveIntensity: 0.4 });
        const ring1 = new THREE.Mesh(ringGeo, ringMat);
        ring1.rotation.x = Math.PI / 2;
        scene.add(ring1);
        const ring2 = new THREE.Mesh(ringGeo, ringMat);
        ring2.rotation.z = Math.PI / 2;
        scene.add(ring2);
        const ring3 = new THREE.Mesh(ringGeo, ringMat);
        ring3.rotation.x = Math.PI / 3;
        ring3.rotation.z = Math.PI / 4;
        scene.add(ring3);
        
        const gridHelper = new THREE.GridHelper(30, 40, 0x00ccff, 0x3366aa);
        gridHelper.position.y = -2.8;
        gridHelper.material.transparent = true;
        gridHelper.material.opacity = 0.2;
        scene.add(gridHelper);
        
        let time = 0;
        function animate() {
            requestAnimationFrame(animate);
            time += 0.008;
            core.rotation.y = time * 0.3;
            core.rotation.x = Math.sin(time * 0.5) * 0.1;
            wireframeSphere.rotation.y = time * 0.2;
            wireframeSphere.rotation.x = time * 0.15;
            ring1.rotation.z = time * 0.1;
            ring2.rotation.x = time * 0.12;
            ring3.rotation.y = time * 0.08;
            nodeGroup.rotation.y = time * 0.05;
            nodeGroup.rotation.x = Math.sin(time * 0.2) * 0.1;
            particleSystem.rotation.y = time * 0.02;
            particleSystem.rotation.x = Math.sin(time * 0.15) * 0.05;
            camera.position.x = Math.sin(time * 0.1) * 0.4;
            camera.position.y = 1.5 + Math.sin(time * 0.2) * 0.1;
            camera.lookAt(0, 0, 0);
            renderer.render(scene, camera);
        }
        animate();
        
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
        
        // Leaflet Map
        let map;
        function initMap() {
            const data = <?php echo json_encode($activeDistrictsData); ?>;
            const isLight = document.body.classList.contains('light');
            const tileUrl = isLight 
                ? 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png'
                : 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
            map = L.map('bangladeshMap').setView([23.8, 90.3], 7);
            L.tileLayer(tileUrl, { attribution: '© OSM' }).addTo(map);
            data.forEach(d => {
                L.circleMarker([d.lat, d.lng], { radius: 8, fillColor: '#0cf', color: '#fff', weight: 1.5, fillOpacity: 0.8 }).addTo(map).bindPopup(`<b>${d.name}</b><br>Active AR Hub`);
            });
            window.map = map;
        }
        initMap();
        
        function updateMapTiles() {
            if (!window.map) return;
            const isLight = document.body.classList.contains('light');
            const newTileUrl = isLight 
                ? 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png'
                : 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
            window.map.eachLayer(layer => {
                if (layer instanceof L.TileLayer) window.map.removeLayer(layer);
            });
            L.tileLayer(newTileUrl, { attribution: '© OSM' }).addTo(window.map);
        }
        
        // Swipers
        new Swiper('.heroSwiper', { loop: true, autoplay: { delay: 5500 }, effect: 'fade', navigation: { nextEl: '.heroSwiper .swiper-button-next', prevEl: '.heroSwiper .swiper-button-prev' } });
        new Swiper('.logoSlider', { slidesPerView: 2, spaceBetween: 16, loop: true, autoplay: { delay: 2200 }, breakpoints: { 576:{slidesPerView:3}, 768:{slidesPerView:4}, 1024:{slidesPerView:6} } });
        new Swiper('.testimonialSwiper', { slidesPerView: 1, spaceBetween: 24, loop: true, autoplay: { delay: 4200 }, pagination: { el: '.testimonial-pagination', clickable: true }, navigation: { nextEl: '.testimonial-next', prevEl: '.testimonial-prev' }, breakpoints: { 640:{slidesPerView:2}, 992:{slidesPerView:3} } });
        
        // Counters Animation
        const counters = document.querySelectorAll('.counter-num');
        const obs = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    const el = e.target;
                    const target = parseInt(el.dataset.target);
                    let curr = 0, step = target / 60;
                    const update = () => { curr += step; if (curr < target) { el.innerText = Math.ceil(curr); requestAnimationFrame(update); } else el.innerText = target; };
                    update();
                    obs.unobserve(el);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(c => obs.observe(c));
        
        // Navbar scroll
        window.addEventListener('scroll', () => {
            document.getElementById('mainNavbar').classList.toggle('scrolled', window.scrollY > 60);
            document.getElementById('backToTop').classList.toggle('show', window.scrollY > 300);
        });
        document.getElementById('backToTop').addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        
        // Dark Mode Toggle
        const toggleBtn = document.getElementById('darkModeToggle');
        const storedTheme = localStorage.getItem('darkMode');
        if (storedTheme === 'light') {
            document.body.classList.add('light');
            toggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        } else {
            toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        }
        toggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('light');
            const isLight = document.body.classList.contains('light');
            localStorage.setItem('darkMode', isLight ? 'light' : 'dark');
            toggleBtn.innerHTML = isLight ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
            updateMapTiles();
        });
        
        // Newsletter subscription
        document.getElementById('newsletterSubscribe')?.addEventListener('click', () => {
            const email = document.getElementById('newsletterEmail').value;
            if (email && email.includes('@')) {
                alert(`Thank you for subscribing! ${email} will receive updates.`);
                document.getElementById('newsletterEmail').value = '';
            } else {
                alert('Please enter a valid email address.');
            }
        });
        
        // Registration & Testimonial/Chat logic
        let userRegistered = sessionStorage.getItem('userRegistered') === 'true';
        let userName = sessionStorage.getItem('userName') || '';
        let userEmail = sessionStorage.getItem('userEmail') || '';
        const registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
        const testimonialModal = new bootstrap.Modal(document.getElementById('testimonialModal'));
        const chatModal = new bootstrap.Modal(document.getElementById('chatModal'));
        function openTestimonialModal() { document.getElementById('testimonialName').value = userName; document.getElementById('testimonialEmail').value = userEmail; testimonialModal.show(); }
        function openChatModal() { document.getElementById('chatName').value = userName; document.getElementById('chatEmail').value = userEmail; chatModal.show(); }
        document.getElementById('testimonialBtn').addEventListener('click', () => { if (!userRegistered) { sessionStorage.setItem('pendingAction','testimonial'); registerModal.show(); } else openTestimonialModal(); });
        document.getElementById('chatBtn').addEventListener('click', () => { if (!userRegistered) { sessionStorage.setItem('pendingAction','chat'); registerModal.show(); } else openChatModal(); });
        document.getElementById('registerForm').addEventListener('submit', e => {
            e.preventDefault();
            const n = document.getElementById('regName').value.trim();
            const em = document.getElementById('regEmail').value.trim();
            if (n && em) {
                sessionStorage.setItem('userRegistered','true');
                sessionStorage.setItem('userName', n);
                sessionStorage.setItem('userEmail', em);
                userRegistered=true; userName=n; userEmail=em;
                registerModal.hide();
                const action = sessionStorage.getItem('pendingAction');
                if (action==='testimonial') openTestimonialModal();
                else if (action==='chat') openChatModal();
                sessionStorage.removeItem('pendingAction');
            }
        });
        document.getElementById('testimonialForm')?.addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData();
            fd.append('client_name', userName); fd.append('email', userEmail);
            fd.append('testimonial_text', document.getElementById('testimonialText').value);
            fd.append('rating', document.getElementById('testimonialRating').value);
            const res = await fetch('submit_testimonial.php', { method:'POST', body:fd });
            const data = await res.json();
            document.getElementById('testimonialMessage').innerHTML = `<div class="alert alert-${data.success?'success':'danger'}">${data.message}</div>`;
            if (data.success) setTimeout(() => testimonialModal.hide(), 2000);
        });
        document.getElementById('chatForm')?.addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData();
            fd.append('name', userName); fd.append('email', userEmail);
            fd.append('message', document.getElementById('chatMessage').value);
            const res = await fetch('submit_chat.php', { method:'POST', body:fd });
            const data = await res.json();
            document.getElementById('chatMessageDiv').innerHTML = `<div class="alert alert-${data.success?'success':'danger'}">${data.message}</div>`;
            if (data.success) setTimeout(() => chatModal.hide(), 2000);
        });
    </script>
</body>
</html>