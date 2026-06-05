<?php
// courses.php - 3D Tech Background with Courses Listing (matching other pages)
require_once 'config.php';

// Fetch all active courses ordered by position
$courses = $pdo->query("SELECT * FROM courses WHERE status = 1 ORDER BY order_position ASC, id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>AR Tech Solutions | Our Courses</title>
    <!-- Fonts (same as index/services) -->
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
        body.light {
            background: #f0f4fc;
            color: #1a1a2e;
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
        /* Navbar – glass style same as other pages */
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
        /* Page Hero (transparent overlay) */
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
        body.light .page-hero p {
            color: #2a2a48;
        }
        /* Search Box (glass) */
        .search-container {
            max-width: 500px;
            margin: 0 auto;
        }
        .search-input {
            border-radius: 60px;
            padding: 0.8rem 1.5rem;
            border: 1px solid rgba(0, 212, 255, 0.3);
            background: rgba(8, 16, 32, 0.7);
            backdrop-filter: blur(8px);
            color: #eef5ff;
            width: 100%;
            font-size: 1rem;
        }
        body.light .search-input {
            background: rgba(255, 255, 255, 0.8);
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
        /* Course Cards (glassmorphic, same as service cards) */
        .course-card {
            background: rgba(8, 16, 32, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 24px;
            transition: all 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        body.light .course-card {
            background: rgba(255, 255, 255, 0.85);
            border-color: rgba(123, 47, 255, 0.2);
        }
        .course-card:hover {
            transform: translateY(-8px);
            border-color: #00d4ff;
            box-shadow: 0 0 30px rgba(0,212,255,0.2);
        }
        body.light .course-card:hover {
            border-color: #7b2fff;
            box-shadow: 0 0 30px rgba(123,47,255,0.2);
        }
        .course-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            object-position: center;
        }
        .course-icon-fallback {
            width: 100%;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #7b2fff, #00d4ff);
            color: white;
            font-size: 3.5rem;
        }
        .course-body {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .course-title {
            font-family: 'Orbitron', monospace;
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.75rem;
        }
        body.light .course-title {
            color: #1a1a2e;
        }
        .course-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .level-badge {
            background: #7b2fff;
            color: white;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .duration-badge {
            background: #00d4ff;
            color: #050b17;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .price-badge {
            display: inline-block;
            background: linear-gradient(90deg, #7b2fff, #00d4ff);
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 4px 12px;
            border-radius: 30px;
            margin-bottom: 0.75rem;
            width: fit-content;
        }
        .enrolled-info {
            font-size: 0.75rem;
            color: rgba(200,200,232,0.6);
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }
        body.light .enrolled-info {
            color: #64748b;
        }
        .course-description {
            color: rgba(200,200,232,0.7);
            font-size: 0.85rem;
            line-height: 1.5;
            margin-bottom: 1.25rem;
            flex-grow: 1;
        }
        body.light .course-description {
            color: #475569;
        }
        .truncated-text { display: inline; }
        .full-text { display: none; }
        .read-more-btn {
            color: #00d4ff;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 5px;
            text-decoration: none;
        }
        body.light .read-more-btn {
            color: #7b2fff;
        }
        .read-more-btn:hover { text-decoration: underline; }
        .btn-outline-custom {
            border: 2px solid #00d4ff;
            background: transparent;
            border-radius: 40px;
            padding: 6px 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #00d4ff;
            text-decoration: none;
            display: inline-block;
            transition: 0.2s;
            text-align: center;
            align-self: flex-start;
            margin-top: 0.5rem;
        }
        body.light .btn-outline-custom {
            border-color: #7b2fff;
            color: #7b2fff;
        }
        .btn-outline-custom:hover {
            background: rgba(0,212,255,0.2);
            color: white;
        }
        body.light .btn-outline-custom:hover {
            background: rgba(123,47,255,0.2);
            color: #1a1a2e;
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
            .course-img, .course-icon-fallback { height: 150px; }
            .course-body { padding: 1rem; }
            .course-title { font-size: 1rem; }
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

<!-- 3D Canvas Background (Neural Network Style, same as other pages) -->
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
                    <li class="nav-item"><a class="nav-link active" href="courses.php">Courses</a></li>
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
        <!-- Hero Title -->
        <div class="page-hero">
            <div class="container">
                <h1>Our Courses</h1>
                
            </div>
        </div>

        <!-- Search Bar -->
        <section class="py-3">
            <div class="container">
                <div class="search-container">
                    <input type="text" id="searchInput" class="search-input" placeholder="🔍 Search courses by title, level, or description...">
                </div>
            </div>
        </section>

        <!-- Courses Grid -->
        <section class="py-4">
            <div class="container">
                <div class="row g-4" id="coursesGrid">
                    <?php if(count($courses) > 0): ?>
                        <?php foreach($courses as $course): ?>
                        <div class="col-6 col-md-6 col-lg-3 course-col" 
                             data-title="<?php echo strtolower(htmlspecialchars($course['title'])); ?>" 
                             data-level="<?php echo strtolower(htmlspecialchars($course['level'])); ?>"
                             data-desc="<?php echo strtolower(htmlspecialchars($course['description'])); ?>">
                            <a href="course-details.php?id=<?php echo $course['id']; ?>" class="text-decoration-none">
                                <div class="course-card">
                                    <?php if(!empty($course['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($course['image_url']); ?>" class="course-img" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                    <?php else: ?>
                                        <div class="course-icon-fallback">
                                            <i class="<?php echo htmlspecialchars($course['icon_class'] ?? 'fas fa-graduation-cap'); ?> fa-3x"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="course-body">
                                        <h4 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h4>
                                        <div class="course-meta">
                                            <span class="level-badge"><?php echo htmlspecialchars($course['level']); ?></span>
                                            <span class="duration-badge"><?php echo htmlspecialchars($course['duration']); ?></span>
                                        </div>
                                        <?php if(!empty($course['price']) && $course['price'] > 0): ?>
                                            <div class="price-badge">$<?php echo number_format($course['price'], 2); ?></div>
                                        <?php endif; ?>
                                        <div class="course-description">
                                            <?php 
                                            $desc = htmlspecialchars($course['description']);
                                            $maxLen = 100;
                                            if(strlen($desc) > $maxLen): 
                                            ?>
                                                <span class="truncated-text"><?php echo substr($desc, 0, $maxLen); ?>...</span>
                                                <span class="full-text"><?php echo $desc; ?></span>
                                                <span class="read-more-btn" onclick="event.preventDefault();toggleReadMore(this)">Read more</span>
                                            <?php else: ?>
                                                <span><?php echo $desc; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="enrolled-info">
                                            <i class="fas fa-users"></i> <?php echo number_format($course['enrolled_students']); ?>+ students enrolled
                                        </div>
                                        <span class="btn-outline-custom">View Course →</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-graduation-cap fa-4x mb-3" style="color:rgba(238,245,255,0.4);"></i>
                            <h3>No courses available</h3>
                            <p>Check back soon for our upcoming courses.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div id="noResultsMsg" class="no-results" style="display: none;">
                    <i class="fas fa-search fa-3x mb-3"></i>
                    <h4>No courses found</h4>
                    <p>Try adjusting your search term.</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer (full inline, consistent with other pages) -->
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
    
    // --- 3D BACKGROUND: Neural Network / Connected Nodes (same as index/services) ---
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
    // Toggle read more / read less
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

    // Search / Filter functionality
    function initSearch() {
        const searchInput = document.getElementById('searchInput');
        const courseCards = document.querySelectorAll('.course-col');
        const noResultsMsg = document.getElementById('noResultsMsg');
        if (!searchInput) return;

        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.trim().toLowerCase();
            let visibleCount = 0;

            courseCards.forEach(card => {
                const title = card.getAttribute('data-title') || '';
                const level = card.getAttribute('data-level') || '';
                const desc = card.getAttribute('data-desc') || '';
                const matches = title.includes(searchTerm) || level.includes(searchTerm) || desc.includes(searchTerm);

                if (searchTerm === '' || matches) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (visibleCount === 0 && noResultsMsg) {
                noResultsMsg.style.display = 'block';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
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
        initSearch();
    });
</script>
</body>
</html>