<?php
// about.php - 3D Tech Background with About Us content (matches other pages)
require_once 'config.php';

// Fetch stats
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers WHERE is_active = 1")->fetchColumn();
$totalProjects = $pdo->query("SELECT COUNT(*) FROM portfolios WHERE status = 1")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses WHERE status = 1")->fetchColumn();

// Fetch a few team members for preview (using is_active = 1 as per team_members table)
$teamPreview = $pdo->query("SELECT name, position, image_url FROM team_members WHERE is_active = 1 ORDER BY order_position ASC LIMIT 3")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>About Us | AR Tech Solutions</title>
    <!-- Fonts (matching other 3D pages) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&family=Space+Grotesk:wght@400;500;600;700&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ---------- GLOBAL RESET & DARK MODE (DEFAULT DARK) ---------- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #050b17;
            color: #eef5ff;
            transition: background 0.3s ease, color 0.2s ease;
            overflow-x: hidden;
        }
        /* LIGHT MODE - DARK TEXT FIX */
        body.light {
            background: #f0f4fc;
            color: #1a1a2e;
        }
        /* Force all light mode text to be dark */
        body.light h1, body.light h2, body.light h3, body.light h4, body.light h5, body.light h6,
        body.light p, body.light span, body.light div:not(.special), body.light .glass-card,
        body.light .stat-box, body.light .team-preview-card,
        body.light .glass-card p, body.light .stat-box p, body.light .team-preview-card p {
            color: #1a1a2e !important;
        }
        body.light .text-muted-custom, body.light .text-white-50 {
            color: #475569 !important;
        }
        body.light .glass-card {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(123, 47, 255, 0.3);
        }
        body.light .stat-box, body.light .team-preview-card {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(123, 47, 255, 0.2);
        }
        /* 3D Canvas Background */
        #three-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }
        .main-content {
            position: relative;
            z-index: 2;
        }
        /* Navbar – glass style (identical to other pages) */
        .glass-nav {
            position: relative;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1030;
            background: rgba(5, 11, 23, 0.7);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0, 212, 255, 0.3);
            transition: all 0.3s;
            padding: 0.5rem 1rem;
        }
        body.light .glass-nav {
            background: rgba(240, 244, 252, 0.85);
            border-bottom: 1px solid rgba(123, 47, 255, 0.3);
        }
        .glass-nav.scrolled {
            padding: 0.3rem 1rem;
            background: rgba(5, 11, 23, 0.95);
        }
        body.light .glass-nav.scrolled {
            background: rgba(240, 244, 252, 0.98);
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-family: 'Orbitron', monospace;
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, #7b2fff, #00d4ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .navbar-brand img {
            height: 44px;
            width: auto;
            filter: brightness(1.1);
        }
        body.light .navbar-brand img {
            filter: brightness(0.9);
        }
        @media (max-width: 576px) {
            .navbar-brand img { height: 34px; }
            .navbar-brand { font-size: 1.3rem; }
        }
        .nav-link {
            font-family: 'Orbitron', monospace;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(238, 245, 255, 0.8) !important;
            margin: 0 0.5rem;
            position: relative;
        }
        body.light .nav-link {
            color: rgba(26, 26, 46, 0.8) !important;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: #00d4ff;
            transition: 0.3s;
        }
        body.light .nav-link::after {
            background: #7b2fff;
        }
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        .dark-toggle {
            background: rgba(0,212,255,0.15);
            border: 1px solid #00d4ff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: #00d4ff;
            transition: 0.2s;
            margin-left: 0.5rem;
        }
        body.light .dark-toggle {
            background: rgba(123, 47, 255, 0.1);
            border-color: #7b2fff;
            color: #7b2fff;
        }
        .dark-toggle:hover {
            background: rgba(0,212,255,0.3);
        }
        /* Page Hero (transparent) */
        .page-hero {
            text-align: center;
            padding: 2rem 0 1rem;
        }
        .page-hero h1 {
            font-family: 'Orbitron', monospace;
            font-size: 2.5rem;
            background: linear-gradient(135deg, #fff, #00d4ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        body.light .page-hero h1 {
            background: linear-gradient(135deg, #1a1a2e, #7b2fff);
            -webkit-background-clip: text;
        }
        .page-hero p {
            color: rgba(238,245,255,0.7);
        }
        /* Glass Cards */
        .glass-card {
            background: rgba(8, 16, 32, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 28px;
            padding: 1.8rem;
            height: 100%;
            transition: transform 0.3s ease, border-color 0.3s;
        }
        body.light .glass-card {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(123, 47, 255, 0.3);
        }
        .glass-card:hover {
            transform: translateY(-5px);
            border-color: #00d4ff;
        }
        /* Stats Cards */
        .stat-box {
            background: rgba(8, 16, 32, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 24px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
            height: 100%;
        }
        body.light .stat-box {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(123, 47, 255, 0.2);
        }
        .stat-box:hover {
            transform: translateY(-5px);
            border-color: #00d4ff;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(90deg, #7b2fff, #00d4ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        /* Team Preview Cards */
        .team-preview-card {
            background: rgba(8, 16, 32, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 24px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
            height: 100%;
        }
        body.light .team-preview-card {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(123, 47, 255, 0.2);
        }
        .team-preview-card:hover {
            transform: translateY(-5px);
            border-color: #00d4ff;
        }
        .team-preview-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 1rem;
            border: 3px solid #00d4ff;
        }
        body.light .team-preview-img {
            border-color: #7b2fff;
        }
        /* CTA Modern */
        .cta-modern {
            background: linear-gradient(135deg, rgba(123,47,255,0.2), rgba(0,212,255,0.2));
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0,212,255,0.4);
            border-radius: 48px;
            padding: 3rem;
            text-align: center;
            color: white;
            transition: all 0.3s;
        }
        body.light .cta-modern {
            background: linear-gradient(135deg, rgba(123,47,255,0.15), rgba(0,212,255,0.15));
            border-color: rgba(123,47,255,0.4);
        }
        .cta-modern:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,212,255,0.2);
        }
        /* Buttons */
        .btn-primary-custom {
            background: linear-gradient(95deg, #7b2fff, #00d4ff);
            border: none;
            padding: 12px 32px;
            border-radius: 40px;
            font-weight: 600;
            color: white;
            transition: 0.3s;
        }
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,212,255,0.3);
            color: white;
        }
        .btn-outline-custom {
            border: 2px solid #00d4ff;
            background: transparent;
            border-radius: 40px;
            padding: 8px 24px;
            color: #00d4ff;
            font-weight: 500;
            transition: 0.2s;
        }
        body.light .btn-outline-custom {
            border-color: #7b2fff;
            color: #7b2fff;
        }
        .btn-outline-custom:hover {
            background: rgba(0,212,255,0.2);
            color: white;
        }
        /* Footer */
        footer {
            background: rgba(3, 6, 18, 0.9);
            border-top: 1px solid #00d4ff;
            color: #cbd5e1;
            padding: 3rem 0 1.5rem;
            margin-top: 3rem;
        }
        body.light footer {
            background: rgba(240, 244, 252, 0.95);
            border-top-color: #7b2fff;
            color: #2a2a48;
        }
        footer a {
            color: #94a3b8;
            text-decoration: none;
        }
        footer a:hover { color: #00d4ff; }
        body.light footer a:hover { color: #7b2fff; }
        /* Floating elements */
        .floating-msg {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: rgba(0,212,255,0.2);
            backdrop-filter: blur(8px);
            border: 1px solid #00d4ff;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 99;
            transition: 0.2s;
            color: #00d4ff;
            font-size: 1.4rem;
        }
        body.light .floating-msg {
            background: rgba(123,47,255,0.1);
            border-color: #7b2fff;
            color: #7b2fff;
        }
        .floating-msg:hover {
            transform: scale(1.1);
            background: rgba(0,212,255,0.4);
        }
        .back-to-top {
            position: fixed;
            bottom: 100px;
            right: 30px;
            background: rgba(0,212,255,0.25);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: 0.3s;
            z-index: 99;
            color: #00d4ff;
        }
        body.light .back-to-top {
            background: rgba(123,47,255,0.2);
            color: #7b2fff;
        }
        .back-to-top.show { opacity: 1; }
        @media (max-width: 768px) {
            .navbar-collapse {
                background: rgba(5,11,23,0.95);
                border-radius: 20px;
                padding: 1rem;
                margin-top: 0.5rem;
            }
            body.light .navbar-collapse {
                background: rgba(240,244,252,0.95);
            }
            .cta-modern { padding: 2rem; }
        }
    </style>
</head>
<body>

<!-- 3D Canvas Background (Neural Network) -->
<canvas id="three-canvas"></canvas>

<div class="main-content">
    <!-- Navbar -->
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
                    <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
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
        <!-- Hero Title -->
        <div class="page-hero">
            <div class="container">
                <h1>About Us</h1>
                <p>Innovating the future of immersive reality</p>
            </div>
        </div>

        <!-- Company Story & Mission/Vision -->
        <div class="container py-4">
            <div class="row g-5 align-items-stretch">
                <div class="col-lg-6">
                    <div class="glass-card h-100">
                        <h2 class="mb-3">Our Story</h2>
                        <p>Founded in 2018, AR Tech Solutions has grown from a small startup into a leading provider of immersive reality solutions. Our journey began with a simple mission: to make AR/VR technology accessible and impactful for businesses of all sizes.</p>
                        <p>Today, we collaborate with enterprises across the globe, delivering custom AR applications, VR training modules, and spatial computing solutions that drive real results. Our team of passionate engineers, designers, and visionaries works tirelessly to push the boundaries of what's possible.</p>
                        <p>We believe that immersive technology will redefine how we work, learn, and connect – and we're here to lead that transformation.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="glass-card h-100">
                        <h2 class="mb-3">Our Mission</h2>
                        <p class="lead">Empower businesses with cutting-edge AR/VR solutions that enhance productivity, engagement, and innovation.</p>
                        <hr class="my-4" style="border-color: rgba(0,212,255,0.3);">
                        <h2 class="mb-3">Our Vision</h2>
                        <p class="lead">To become the global benchmark for immersive technology, shaping a future where digital and physical realities seamlessly blend.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <section class="py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">By the Numbers</span>
                    <h2 class="display-5 fw-bold mt-2">Our Impact</h2>
                    <p class="text-white-50">Measurable results that speak for themselves</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="stat-box">
                            <i class="fas fa-users fa-3x text-info mb-3"></i>
                            <div class="stat-number"><?php echo number_format($totalCustomers); ?>+</div>
                            <p class="mb-0">Happy Customers</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box">
                            <i class="fas fa-briefcase fa-3x text-info mb-3"></i>
                            <div class="stat-number"><?php echo number_format($totalProjects); ?>+</div>
                            <p class="mb-0">Projects Delivered</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box">
                            <i class="fas fa-graduation-cap fa-3x text-info mb-3"></i>
                            <div class="stat-number"><?php echo number_format($totalCourses); ?>+</div>
                            <p class="mb-0">Courses & Workshops</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Preview -->
        <?php if($teamPreview && count($teamPreview) > 0): ?>
        <section class="py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">Leadership</span>
                    <h2 class="display-5 fw-bold mt-2">Meet Our Leaders</h2>
                    <p class="text-white-50">The brilliant minds driving our innovation</p>
                </div>
                <div class="row g-4 justify-content-center">
                    <?php foreach($teamPreview as $member): ?>
                    <div class="col-md-4">
                        <div class="team-preview-card">
                            <img src="<?php echo htmlspecialchars($member['image_url']); ?>" class="team-preview-img" alt="<?php echo htmlspecialchars($member['name']); ?>">
                            <h4 class="mb-1"><?php echo htmlspecialchars($member['name']); ?></h4>
                            <p class="text-info"><?php echo htmlspecialchars($member['position']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-5">
                    <a href="chairman-speech.php" class="btn btn-primary-custom">View Full Team <i class="fas fa-arrow-right ms-2"></i></a>
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

    <!-- Footer (inline, consistent with other pages) -->
    <?php include "footer.php"?>

<!-- Floating Message & Back to Top -->
<div class="floating-msg" id="floatingMsg">
    <i class="fas fa-comment-dots"></i>
</div>
<div class="back-to-top" id="backToTop">
    <i class="fas fa-arrow-up"></i>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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
    
    // Connect close nodes
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
    
    // Particles
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
    
    // Rings
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
</script>

<script>
    // Dark Mode Toggle (default dark, toggle adds/removes 'light' class)
    function initDarkMode() {
        const toggleBtn = document.getElementById('darkModeToggle');
        const isLightMode = localStorage.getItem('lightMode') === 'enabled';
        if (isLightMode) {
            document.body.classList.add('light');
            toggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        } else {
            toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        }
        toggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('light');
            const lightEnabled = document.body.classList.contains('light');
            localStorage.setItem('lightMode', lightEnabled ? 'enabled' : 'disabled');
            toggleBtn.innerHTML = lightEnabled ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
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