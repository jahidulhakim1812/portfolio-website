<?php
// course-details.php - 3D Tech Background with Perfect Text Colors (Light/Dark Mode)
require_once 'config.php';

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND status = 1");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: index.php');
    exit;
}

// Parse JSON curriculum
$curriculum = null;
if (!empty($course['curriculum'])) {
    $decoded = json_decode($course['curriculum'], true);
    if (is_array($decoded) && isset($decoded['modules'])) {
        $curriculum = $decoded;
    }
}

// Calculate real enrollment
$totalEnrolled = (int)$course['enrolled_students'];
try {
    $enrollStmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND payment_status = 'completed'");
    $enrollStmt->execute([$course_id]);
    $completedEnrollments = (int)$enrollStmt->fetchColumn();
    $totalEnrolled += $completedEnrollments;
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="<?php echo htmlspecialchars(substr(strip_tags($course['short_description'] ?: $course['description']), 0, 160)); ?>">
    <title><?php echo htmlspecialchars($course['title']); ?> | AR Tech Solutions</title>
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
        /* Force all common text elements to have correct colors in light mode */
        body.light h1, body.light h2, body.light h3, body.light h4, body.light h5,
        body.light p, body.light span, body.light li, body.light a:not(.nav-link):not(.btn),
        body.light .breadcrumb-item a, body.light .breadcrumb-item.active,
        body.light .glass-card, body.light .curriculum-table th, body.light .curriculum-table td,
        body.light .text-white-50, body.light .lead, body.light small, body.light strong,
        body.light .table, body.light .text-muted-custom {
            color: #1a1a2e !important;
        }
        /* Keep specific elements with their intended colors */
        body.light .glass-card {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(123, 47, 255, 0.3);
        }
        body.light .btn-primary-custom, body.light .fee-btn {
            color: white !important;
        }
        body.light .text-info {
            color: #7b2fff !important;
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
        /* Glass Cards */
        .glass-card {
            background: rgba(8, 16, 32, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 28px;
            padding: 1.5rem;
            transition: 0.3s;
        }
        .glass-card:hover {
            transform: translateY(-5px);
            border-color: #00d4ff;
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
        .fee-btn {
            background: linear-gradient(135deg, #7b2fff, #00d4ff);
            color: white;
            border: none;
            border-radius: 60px;
            padding: 16px 32px;
            font-size: 1.5rem;
            font-weight: 800;
            transition: 0.3s;
            display: inline-block;
        }
        .fee-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 25px rgba(123,47,255,0.4);
            color: white;
        }
        .curriculum-table {
            width: 100%;
            border-collapse: collapse;
        }
        .curriculum-table th, .curriculum-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(0,212,255,0.2);
        }
        .instructor-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #00d4ff;
        }
        /* Breadcrumb */
        .breadcrumb {
            background: transparent;
            padding: 0;
        }
        .breadcrumb-item a {
            color: rgba(238,245,255,0.6);
            text-decoration: none;
        }
        .breadcrumb-item.active {
            color: #00d4ff;
        }
        /* Footer (inline, consistent) */
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
        .floating-msg:hover { transform: scale(1.1); background: rgba(0,212,255,0.4); }
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
            .instructor-img { width: 80px; height: 80px; }
            .fee-btn { padding: 10px 20px; font-size: 1.2rem; }
        }
    </style>
</head>
<body>

<!-- 3D Canvas Background (Neural Network) -->
<canvas id="three-canvas"></canvas>

<div class="main-content">
    <!-- Navbar – standard glass-nav (like services.php) -->
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

    <main class="container py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mt-2">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="courses.php">Courses</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($course['title']); ?></li>
            </ol>
        </nav>

        <!-- Course Header -->
        <div class="row mb-5">
            <div class="col-md-4 mb-4">
                <?php if(!empty($course['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($course['image_url']); ?>" class="img-fluid rounded-4 shadow w-100" alt="<?php echo htmlspecialchars($course['title']); ?>" style="object-fit: cover; height: 250px;">
                <?php else: ?>
                    <div class="glass-card d-flex align-items-center justify-content-center" style="height: 250px;">
                        <i class="<?php echo htmlspecialchars($course['icon_class']); ?> fa-5x" style="color:#00d4ff;"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-8">
                <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($course['title']); ?></h1>
                <p class="lead"><?php echo nl2br(htmlspecialchars($course['short_description'] ?: $course['description'])); ?></p>
                <div class="row mt-4">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="glass-card text-center p-3">
                            <i class="fas fa-clock fa-2x text-info"></i>
                            <h4><?php echo htmlspecialchars($course['duration']); ?></h4>
                            <small>Duration</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="glass-card text-center p-3">
                            <i class="fas fa-signal fa-2x text-info"></i>
                            <h4><?php echo htmlspecialchars($course['level']); ?></h4>
                            <small>Level</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="glass-card text-center p-3">
                            <i class="fas fa-users fa-2x text-info"></i>
                            <h4><?php echo number_format($totalEnrolled); ?>+</h4>
                            <small>Enrolled</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="glass-card text-center p-3">
                            <i class="fas fa-project-diagram fa-2x text-info"></i>
                            <h4><?php echo (int)$course['total_projects']; ?>+</h4>
                            <small>Projects</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fee & Enrollment -->
        <div class="glass-card p-4 mb-5 text-center">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3>Course Fee</h3>
                    <div class="d-flex justify-content-center gap-4 flex-wrap">
                        <div>
                            <small>Online</small>
                            <div class="fee-btn mt-2">$<?php echo number_format($course['price'], 2); ?></div>
                        </div>
                        <?php if($course['price_offline'] > 0): ?>
                        <div>
                            <small>Offline</small>
                            <div class="fee-btn mt-2" style="background: linear-gradient(135deg, #00d4ff, #7b2fff);">$<?php echo number_format($course['price_offline'], 2); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6 mt-4 mt-md-0">
                    <a href="enroll.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary-custom btn-lg px-5">Enroll Now <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Curriculum Table -->
        <?php if ($curriculum && !empty($curriculum['modules'])): ?>
        <div class="glass-card p-4 mb-4">
            <h2 class="mb-4">Course Curriculum</h2>
            <?php foreach ($curriculum['modules'] as $moduleIndex => $module): ?>
                <div class="mb-4">
                    <h4 class="mb-3">Module <?php echo $moduleIndex + 1; ?>: <?php echo htmlspecialchars($module['title'] ?? 'Untitled Module'); ?></h4>
                    <?php if (!empty($module['classes'])): ?>
                    <div class="table-responsive">
                        <table class="curriculum-table">
                            <thead>
                                <tr><th>Class</th><th>Topic</th><th>Type</th><th>Resources</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($module['classes'] as $class): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($class['class_number'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($class['topic'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($class['type'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($class['resource'] ?? '—'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($module['projects'])): ?>
                    <div class="mt-2"><strong>Projects:</strong> <?php echo htmlspecialchars($module['projects']); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="glass-card p-4 mb-4 text-center">
            <i class="fas fa-book-open fa-3x text-info mb-3"></i>
            <h4>Curriculum Coming Soon</h4>
            <p>We're preparing an amazing learning path for you.</p>
        </div>
        <?php endif; ?>

        <!-- Additional Sections -->
        <?php if(!empty($course['career_outcomes'])): ?>
        <div class="glass-card p-4 mb-4">
            <h2>Career Outcomes</h2>
            <p><?php echo nl2br(htmlspecialchars($course['career_outcomes'])); ?></p>
        </div>
        <?php endif; ?>

        <?php if(!empty($course['prerequisites'])): ?>
        <div class="glass-card p-4 mb-4">
            <h2>Prerequisites</h2>
            <p><?php echo nl2br(htmlspecialchars($course['prerequisites'])); ?></p>
        </div>
        <?php endif; ?>

        <?php if(!empty($course['software_learned'])): ?>
        <div class="glass-card p-4 mb-4">
            <h2>Software You'll Learn</h2>
            <p><?php echo nl2br(htmlspecialchars($course['software_learned'])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Instructor Info -->
        <?php if(!empty($course['instructor_name'])): ?>
        <div class="glass-card p-4 mb-4">
            <h2>Your Instructor</h2>
            <div class="row align-items-center">
                <div class="col-md-2 text-center mb-3 mb-md-0">
                    <img src="<?php echo !empty($course['instructor_image']) ? htmlspecialchars($course['instructor_image']) : 'assets/default-avatar.png'; ?>" class="instructor-img" alt="<?php echo htmlspecialchars($course['instructor_name']); ?>">
                </div>
                <div class="col-md-10">
                    <h3><?php echo htmlspecialchars($course['instructor_name']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($course['instructor_bio'])); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
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
        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // Floating Message
    document.getElementById('floatingMsg')?.addEventListener('click', () => alert('Live chat support coming soon! 📱'));

    document.addEventListener('DOMContentLoaded', () => {
        initDarkMode();
        initNavbarScroll();
        initBackToTop();
    });
</script>
</body>
</html>