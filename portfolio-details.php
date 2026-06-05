<?php
// portfolio-details.php - 3D Tech Background with Portfolio Details (matches other pages)
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: portfolio.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM portfolios WHERE id = ? AND status = 1");
$stmt->execute([$id]);
$portfolio = $stmt->fetch();

if (!$portfolio) {
    header("Location: portfolio.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo htmlspecialchars($portfolio['title']); ?> | AR Tech Solutions</title>
    <!-- Fonts (matching other 3D pages) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&family=Space+Grotesk:wght@400;500;600;700&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ========== GLOBAL RESET & DARK MODE (DEFAULT DARK) ========== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #050b17;
            color: #eef5ff;
            transition: background 0.3s ease, color 0.2s ease;
            overflow-x: hidden;
        }
        /* LIGHT MODE - DARK TEXT (Perfect contrast) */
        body.light {
            background: #f0f4fc;
            color: #1a1a2e;
        }
        /* Force all text elements to have correct colors in light mode */
        body.light h1, body.light h2, body.light h3, body.light h4, body.light h5,
        body.light p, body.light span, body.light li, body.light a:not(.nav-link):not(.btn),
        body.light .detail-card, body.light .detail-details-col,
        body.light .client-badge, body.light .description-text,
        body.light .text-muted-custom {
            color: #1a1a2e !important;
        }
        body.light .glass-card, body.light .detail-card {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(123, 47, 255, 0.3);
        }
        body.light .client-badge {
            background: linear-gradient(135deg, #7b2fff, #00d4ff);
            color: white !important;
        }
        body.light .btn-back {
            border-color: #7b2fff;
            color: #7b2fff;
        }
        body.light .btn-back:hover {
            background: #7b2fff;
            color: white;
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
        /* Navbar – standard glass-nav (like services.php) */
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
        /* Detail Card - split layout (glassmorphic) */
        .detail-card {
            background: rgba(8, 16, 32, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            margin-top: 2rem;
            display: flex;
            flex-wrap: wrap;
        }
        body.light .detail-card {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(123, 47, 255, 0.3);
        }
        /* Left side: image (no cropping) */
        .detail-image-col {
            flex: 1;
            min-width: 280px;
            background: rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        body.light .detail-image-col {
            background: rgba(0, 0, 0, 0.03);
        }
        .detail-img {
            width: 100%;
            height: auto;
            max-height: 450px;
            object-fit: contain;
            border-radius: 24px;
        }
        .fallback-img {
            width: 100%;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #7b2fff, #00d4ff);
            border-radius: 24px;
            color: white;
            font-size: 4rem;
        }
        /* Right side: details */
        .detail-details-col {
            flex: 1;
            min-width: 280px;
            padding: 2rem;
        }
        .client-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #7b2fff, #00d4ff);
            color: white;
            padding: 6px 18px;
            border-radius: 40px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .description-text {
            font-size: 1.05rem;
            line-height: 1.7;
            margin: 1.5rem 0;
            color: rgba(238,245,255,0.85);
        }
        body.light .description-text {
            color: #1a1a2e;
        }
        .btn-primary-custom {
            background: linear-gradient(95deg, #7b2fff, #00d4ff);
            border: none;
            padding: 12px 32px;
            border-radius: 40px;
            font-weight: 600;
            color: white;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,212,255,0.3);
            color: white;
        }
        .btn-back {
            border: 2px solid #00d4ff;
            border-radius: 40px;
            padding: 10px 28px;
            font-weight: 600;
            color: #00d4ff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
            background: transparent;
        }
        body.light .btn-back {
            border-color: #7b2fff;
            color: #7b2fff;
        }
        .btn-back:hover {
            background: rgba(0,212,255,0.2);
            color: white;
            transform: translateX(-3px);
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
            .detail-image-col, .detail-details-col {
                flex: 0 0 100%;
            }
            .detail-image-col {
                padding: 1rem 1rem 0 1rem;
            }
            .detail-details-col {
                padding: 1.5rem;
            }
            .detail-img {
                max-height: 280px;
            }
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

<!-- 3D Canvas Background (Neural Network, same as other pages) -->
<canvas id="three-canvas"></canvas>

<div class="main-content">
    <!-- Navbar – standard glass-nav (like services.php, not floating rounded) -->
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
                <!-- Left Column: Image (no cropping) -->
                <div class="detail-image-col">
                    <?php if(!empty($portfolio['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($portfolio['image_url']); ?>" class="detail-img" alt="<?php echo htmlspecialchars($portfolio['title']); ?>">
                    <?php else: ?>
                        <div class="fallback-img">
                            <i class="fas fa-image fa-5x"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Right Column: Details -->
                <div class="detail-details-col">
                    <div class="client-badge">
                        <i class="fas fa-building"></i> <?php echo htmlspecialchars($portfolio['client']); ?>
                    </div>
                    <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($portfolio['title']); ?></h1>
                    
                    <div class="description-text">
                        <?php echo nl2br(htmlspecialchars($portfolio['description'])); ?>
                    </div>

                    <?php if(!empty($portfolio['project_url'])): ?>
                        <div class="mt-4">
                            <a href="<?php echo htmlspecialchars($portfolio['project_url']); ?>" target="_blank" class="btn-primary-custom">
                                View Live Project <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="mt-5">
                        <a href="portfolio.php" class="btn-back">
                            <i class="fas fa-arrow-left"></i> Back to Portfolio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'footer.php'; ?>
</div>

<!-- Floating Message & Back to Top -->
<div class="floating-msg" id="floatingMsg"><i class="fas fa-comment-dots"></i></div>
<div class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script type="importmap">
    { "imports": { "three": "https://unpkg.com/three@0.128.0/build/three.module.js" } }
</script>
<script type="module">
    import * as THREE from 'three';
    
    // --- 3D BACKGROUND: Neural Network / Connected Nodes (same as other pages) ---
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