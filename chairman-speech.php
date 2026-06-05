<?php
// chairman-speech.php - 3D Tech Background with Chairman Speech & Team Members (Fixed Light Mode Text Dark)
require_once 'config.php';

// Fetch active chairman speech
$speech = $pdo->query("SELECT * FROM chairman_speech WHERE is_active = 1 LIMIT 1")->fetch();
if (!$speech) {
    die("Chairman speech not found.");
}

// Fetch active team members ordered by position
$teamMembers = $pdo->query("SELECT * FROM team_members WHERE is_active = 1 ORDER BY order_position ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>AR Tech Solutions | Chairman's Speech</title>
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
        body.light p, body.light span, body.light div:not(.special), body.light .speech-text,
        body.light .team-card p, body.light .page-hero p, body.light .text-muted-custom,
        body.light .team-card .text-white-50, body.light .team-card .text-info,
        body.light .team-card h4, body.light .speech-card .text-white,
        body.light .speech-card .text-info, body.light .speech-text {
            color: #1a1a2e !important;
        }
        /* Keep muted text slightly lighter but still readable */
        body.light .text-muted-custom, body.light .text-white-50 {
            color: #475569 !important;
        }
        /* Keep badges and special elements with original light colors */
        body.light .team-card .text-info, body.light .speech-card .text-info {
            color: #7b2fff !important;
        }
        /* Ensure speech card text is dark */
        body.light .speech-card {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(123, 47, 255, 0.3);
        }
        body.light .speech-text {
            color: #1a1a2e !important;
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
        /* Speech Card (glassmorphic) */
        .speech-card {
            background: rgba(8, 16, 32, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 32px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }
        body.light .speech-card {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(123, 47, 255, 0.3);
        }
        .speech-card:hover {
            transform: translateY(-5px);
            border-color: #00d4ff;
        }
        .speech-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: rgba(238,245,255, 0.85);
        }
        /* Search Box (glass) */
        .search-container {
            max-width: 400px;
            margin: 0 auto 2rem auto;
            position: relative;
        }
        .search-input {
            border-radius: 60px;
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            border: 1px solid rgba(0, 212, 255, 0.3);
            background: rgba(8, 16, 32, 0.7);
            backdrop-filter: blur(8px);
            color: #eef5ff;
            width: 100%;
            font-size: 0.95rem;
        }
        body.light .search-input {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(123, 47, 255, 0.3);
            color: #1a1a2e;
        }
        .search-input:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.2);
        }
        body.light .search-input:focus {
            border-color: #7b2fff;
            box-shadow: 0 0 0 3px rgba(123, 47, 255, 0.2);
        }
        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #00d4ff;
        }
        body.light .search-icon {
            color: #7b2fff;
        }
        .clear-search {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #00d4ff;
            display: none;
        }
        body.light .clear-search {
            color: #7b2fff;
        }
        .clear-search:hover {
            opacity: 0.7;
        }
        /* Team Cards (glassmorphic) */
        .team-card {
            background: rgba(8, 16, 32, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 24px;
            transition: all 0.3s;
            height: 100%;
            text-align: center;
            padding: 1.8rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        body.light .team-card {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(123, 47, 255, 0.2);
        }
        .team-card:hover {
            transform: translateY(-8px);
            border-color: #00d4ff;
            box-shadow: 0 0 30px rgba(0,212,255,0.2);
        }
        body.light .team-card:hover {
            border-color: #7b2fff;
            box-shadow: 0 0 30px rgba(123,47,255,0.2);
        }
        .team-img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 3px solid #00d4ff;
        }
        body.light .team-img {
            border-color: #7b2fff;
        }
        .social-icons a {
            color: #00d4ff;
            margin: 0 0.5rem;
            font-size: 1.2rem;
            transition: 0.2s;
        }
        body.light .social-icons a {
            color: #7b2fff;
        }
        .social-icons a:hover {
            opacity: 0.7;
            transform: translateY(-2px);
        }
        .no-results {
            text-align: center;
            padding: 3rem;
            color: rgba(238,245,255,0.7);
        }
        body.light .no-results {
            color: #64748b;
        }
        /* Footer – matching other pages */
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
            .team-img { width: 100px; height: 100px; }
            .team-card { padding: 1rem; }
            .speech-card { padding: 1.5rem; }
            .navbar-collapse {
                background: rgba(5,11,23,0.95);
                border-radius: 20px;
                padding: 1rem;
                margin-top: 0.5rem;
            }
            body.light .navbar-collapse {
                background: rgba(240,244,252,0.95);
            }
        }
    </style>
</head>
<body>

<!-- 3D Canvas Background -->
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
                    <li class="nav-item"><a class="nav-link active" href="chairman-speech.php">Chairman</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                </ul>
                <button id="darkModeToggle" class="dark-toggle ms-2"><i class="fas fa-moon"></i></button>
            </div>
        </div>
    </nav>

    <main>
        <!-- Hero Title -->
        <div class="page-hero">
            <div class="container">
                <h1>Chairman's Vision</h1>
                <p>Words that inspire our journey</p>
            </div>
        </div>

        <!-- Chairman Speech Content -->
        <section class="py-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="speech-card">
                            <div class="text-center mb-4">
                                <img src="<?php echo htmlspecialchars($speech['image_url']); ?>" class="rounded-circle shadow-lg" style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #00d4ff;">
                                <h3 class="mt-3" style="color: #fff;"><?php echo htmlspecialchars($speech['chairman_name'] ?? 'Chairman'); ?></h3>
                                <p class="text-info"><?php echo htmlspecialchars($speech['title'] ?? 'Chairman & Founder'); ?></p>
                            </div>
                            <div class="speech-text">
                                <?php echo nl2br(htmlspecialchars($speech['speech_text'])); ?>
                            </div>
                            <?php if(!empty($speech['signature_url'])): ?>
                            <div class="text-end mt-5">
                                <img src="<?php echo htmlspecialchars($speech['signature_url']); ?>" style="max-width: 200px;" alt="Signature">
                                <p class="mt-2 mb-0 text-white-50"><?php echo htmlspecialchars($speech['chairman_name'] ?? 'Chairman'); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section (if any) -->
        <?php if(count($teamMembers) > 0): ?>
        <section class="py-5">
            <div class="container">
                <div class="text-center mb-4">
                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">Leadership & Team</span>
                    <h2 class="display-5 fw-bold mt-2">Meet Our Team</h2>
                    <p class="text-white-50">The brilliant minds behind our immersive innovations</p>
                </div>
                
                <!-- Search Input -->
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="teamSearchInput" class="search-input" placeholder="Search by name, position or bio...">
                    <i class="fas fa-times-circle clear-search" id="clearTeamSearch"></i>
                </div>

                <!-- Team Grid -->
                <div class="row g-4" id="teamGrid">
                    <?php foreach($teamMembers as $member): ?>
                    <div class="col-6 col-md-4 col-lg-3 team-col" 
                         data-name="<?php echo strtolower(htmlspecialchars($member['name'])); ?>" 
                         data-position="<?php echo strtolower(htmlspecialchars($member['position'])); ?>" 
                         data-bio="<?php echo strtolower(htmlspecialchars($member['bio'])); ?>">
                        <div class="team-card">
                            <img src="<?php echo htmlspecialchars($member['image_url']); ?>" class="team-img" alt="<?php echo htmlspecialchars($member['name']); ?>">
                            <h4 class="fs-5 mb-1" style="color: #fff;"><?php echo htmlspecialchars($member['name']); ?></h4>
                            <p class="text-info small"><?php echo htmlspecialchars($member['position']); ?></p>
                            <p class="small" style="color: rgba(238,245,255,0.7);"><?php echo htmlspecialchars($member['bio']); ?></p>
                            <div class="social-icons mt-3">
                                <?php if(!empty($member['social_facebook'])): ?>
                                <a href="<?php echo htmlspecialchars($member['social_facebook']); ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                                <?php endif; ?>
                                <?php if(!empty($member['social_twitter'])): ?>
                                <a href="<?php echo htmlspecialchars($member['social_twitter']); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                                <?php endif; ?>
                                <?php if(!empty($member['social_linkedin'])): ?>
                                <a href="<?php echo htmlspecialchars($member['social_linkedin']); ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div id="teamNoResults" class="no-results" style="display: none;">
                    <i class="fas fa-user-friends fa-3x mb-3"></i>
                    <h4>No team members found</h4>
                    <p>Try a different search term.</p>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- Footer -->
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
    // Team Search Functionality
    const teamSearchInput = document.getElementById('teamSearchInput');
    const clearTeamSearch = document.getElementById('clearTeamSearch');
    const teamCards = document.querySelectorAll('.team-col');
    const teamNoResults = document.getElementById('teamNoResults');

    function filterTeam() {
        const query = teamSearchInput.value.toLowerCase().trim();
        let hasResults = false;

        teamCards.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const position = card.getAttribute('data-position') || '';
            const bio = card.getAttribute('data-bio') || '';
            if (name.includes(query) || position.includes(query) || bio.includes(query)) {
                card.style.display = '';
                hasResults = true;
            } else {
                card.style.display = 'none';
            }
        });

        if (hasResults) {
            teamNoResults.style.display = 'none';
        } else {
            teamNoResults.style.display = 'block';
        }

        clearTeamSearch.style.display = query.length > 0 ? 'block' : 'none';
    }

    if (teamSearchInput) {
        teamSearchInput.addEventListener('input', filterTeam);
        clearTeamSearch.addEventListener('click', () => {
            teamSearchInput.value = '';
            filterTeam();
            teamSearchInput.focus();
        });
    }

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