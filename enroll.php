<?php
// enroll.php - Student enrollment with bKash payment instructions and transaction ID
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND status = 1");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $transaction_id = trim($_POST['transaction_id']);
    $payment_type = $_POST['payment_type'] ?? 'online';
    
    $amount = ($payment_type == 'online') ? $course['price'] : $course['price_offline'];
    
    // Handle photo upload
    $photo_path = null;
    if (isset($_FILES['student_photo']) && $_FILES['student_photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_ext = strtolower(pathinfo($_FILES['student_photo']['name'], PATHINFO_EXTENSION));
        if (in_array($file_ext, $allowed)) {
            $upload_dir = 'uploads/students/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $photo_path = $upload_dir . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['student_photo']['name']);
            move_uploaded_file($_FILES['student_photo']['tmp_name'], $photo_path);
        } else {
            $error = 'Invalid photo format. Allowed: jpg, jpeg, png, gif, webp.';
        }
    }
    
    if (empty($name) || empty($email) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (empty($transaction_id)) {
        $error = 'Please enter your bKash transaction ID.';
    }
    
    if (empty($error)) {
        $stmt = $pdo->prepare("INSERT INTO enrollments (course_id, student_name, student_email, student_phone, student_address, student_photo, payment_method, amount, transaction_id, payment_status) VALUES (?, ?, ?, ?, ?, ?, 'bkash', ?, ?, 'pending')");
        $stmt->execute([$course_id, $name, $email, $phone, $address, $photo_path, $amount, $transaction_id]);
        $enrollment_id = $pdo->lastInsertId();
        
        $success = 'Enrollment submitted successfully! Your bKash payment is pending verification. We will notify you once confirmed.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Enroll in <?php echo htmlspecialchars($course['title']); ?> | AR Tech Solutions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&family=Space+Grotesk:wght@400;500;600;700&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
        body.light .glass-card {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(123, 47, 255, 0.3);
        }
        body.light .glass-card .text-muted {
            color: #475569 !important;
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
        .main-content {
            position: relative;
            z-index: 2;
        }
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
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }
        .dark-toggle {
            background: rgba(0,212,255,0.15);
            border: 1px solid #00d4ff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: #00d4ff;
            margin-left: 0.5rem;
        }
        .glass-card {
            background: rgba(8, 16, 32, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 28px;
            padding: 2rem;
            transition: 0.3s;
        }
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
        }
        .form-control, .form-select {
            background: rgba(5, 11, 23, 0.5);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 16px;
            padding: 0.75rem 1rem;
            color: #eef5ff;
        }
        body.light .form-control, body.light .form-select {
            background: rgba(255, 255, 255, 0.8);
            border-color: rgba(123, 47, 255, 0.3);
            color: #1a1a2e;
        }
        .form-control:focus, .form-select:focus {
            border-color: #00d4ff;
            box-shadow: 0 0 0 3px rgba(0,212,255,0.2);
            outline: none;
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        /* bKash payment box */
        .payment-info {
            background: rgba(0, 212, 255, 0.1);
            border-left: 4px solid #00d4ff;
            border-radius: 16px;
            padding: 1.2rem;
            margin-bottom: 1.5rem;
        }
        .payment-number {
            font-size: 1.4rem;
            font-weight: 800;
            font-family: monospace;
            background: rgba(0,0,0,0.3);
            display: inline-block;
            padding: 0.2rem 1rem;
            border-radius: 40px;
            letter-spacing: 2px;
            color: #00d4ff;
        }
        body.light .payment-number {
            color: #7b2fff;
            background: rgba(0,0,0,0.05);
        }
        footer {
            background: rgba(3, 6, 18, 0.9);
            border-top: 1px solid #00d4ff;
            padding: 1.5rem 0;
            margin-top: 3rem;
            text-align: center;
        }
        @media (max-width: 768px) {
            .navbar-collapse {
                background: rgba(5,11,23,0.95);
                border-radius: 20px;
                padding: 1rem;
                margin-top: 0.5rem;
            }
            .glass-card { padding: 1.5rem; }
            .payment-number { font-size: 1.1rem; }
        }
        /* Ensure all text colors are perfect in light mode */
        body.light .text-muted {
            color: #6c757d !important;
        }
        body.light .payment-info {
            background: rgba(123, 47, 255, 0.08);
            border-left-color: #7b2fff;
        }
    </style>
</head>
<body>

<!-- 3D Canvas Background -->
<canvas id="three-canvas"></canvas>

<div class="main-content">
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
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                </ul>
                <button id="darkModeToggle" class="dark-toggle ms-2"><i class="fas fa-moon"></i></button>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="glass-card">
                    <h2 class="text-center mb-4">Enroll in <?php echo htmlspecialchars($course['title']); ?></h2>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <!-- bKash Payment Instructions (only bKash) -->
                    <div class="payment-info">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fab fa-bkash fa-2x me-2 text-info"></i>
                            <h5 class="mb-0">bKash Payment Instructions</h5>
                        </div>
                        <p><strong>Send payment to this bKash number:</strong></p>
                        <div class="payment-number">01837090666 (bKash Merchant)</div>
                        <div class="mt-3">
                            <p><strong>Follow these steps to complete payment:</strong></p>
                            <ol class="small">
                                <li>Open your bKash mobile app.</li>
                                <li>Select "Send Money" option.</li>
                                <li>Enter the bKash number: <strong>01837090666</strong></li>
                                <li>Enter the exact amount: 
                                    <strong>$<?php echo number_format($course['price'], 2); ?></strong> (Online) or 
                                    <strong>$<?php echo number_format($course['price_offline'], 2); ?></strong> (Offline) based on your selection below.
                                </li>
                                <li>Complete the transaction and save the <strong>Transaction ID (TrxID)</strong> from bKash.</li>
                                <li>Fill out the form below with your details and the bKash Transaction ID.</li>
                            </ol>
                            <p class="text-muted mb-0">⚠️ Your enrollment will be confirmed only after payment verification.</p>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4><i class="fas fa-info-circle me-2"></i>Course Details</h4>
                            <p><strong>Course:</strong> <?php echo htmlspecialchars($course['title']); ?></p>
                            <p><strong>Duration:</strong> <?php echo htmlspecialchars($course['duration']); ?></p>
                            <p><strong>Level:</strong> <?php echo htmlspecialchars($course['level']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h4><i class="fas fa-dollar-sign me-2"></i>Fee Structure</h4>
                            <p><strong>Online Course:</strong> $<?php echo number_format($course['price'], 2); ?></p>
                            <?php if($course['price_offline'] > 0): ?>
                            <p><strong>Offline Course:</strong> $<?php echo number_format($course['price_offline'], 2); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" name="phone" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Type *</label>
                                <select name="payment_type" class="form-select" required>
                                    <option value="online">Online Course ($<?php echo number_format($course['price'], 2); ?>)</option>
                                    <?php if($course['price_offline'] > 0): ?>
                                    <option value="offline">Offline Course ($<?php echo number_format($course['price_offline'], 2); ?>)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">bKash Transaction ID (TrxID) *</label>
                                <input type="text" name="transaction_id" class="form-control" placeholder="Enter your bKash transaction ID (e.g., 8Y7Z3K5P)" required>
                                <small class="text-muted">This is the 8-10 character alphanumeric code from your bKash payment receipt.</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Upload Your Photo (Optional)</label>
                                <input type="file" name="student_photo" class="form-control" accept="image/*">
                                <small class="text-muted">Upload a clear photo for identification (JPG, PNG, GIF, WebP).</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary-custom w-100">Submit Enrollment <i class="fas fa-paper-plane ms-2"></i></button>
                        </div>
                    </form>
                    <p class="text-muted mt-3 small text-center">* After submission, your enrollment will be pending verification. Once your bKash payment is confirmed, you will receive course access.</p>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> AR Tech Solutions. All rights reserved.</p>
        </div>
    </footer>
</div>

<!-- Floating Message & Back to Top -->
<div class="floating-msg" id="floatingMsg" style="position:fixed; bottom:30px; right:30px; background:rgba(0,212,255,0.2); backdrop-filter:blur(8px); border:1px solid #00d4ff; width:52px; height:52px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; color:#00d4ff; font-size:1.4rem; z-index:99;"><i class="fas fa-comment-dots"></i></div>
<div class="back-to-top" id="backToTop" style="position:fixed; bottom:100px; right:30px; background:rgba(0,212,255,0.25); width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; opacity:0; transition:0.3s; z-index:99; color:#00d4ff;"><i class="fas fa-arrow-up"></i></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script type="importmap">
    { "imports": { "three": "https://unpkg.com/three@0.128.0/build/three.module.js" } }
</script>
<script type="module">
    import * as THREE from 'three';
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
    const wireframeSphere = new THREE.Mesh(new THREE.SphereGeometry(0.9, 24, 18), new THREE.MeshBasicMaterial({ color: 0x00d4ff, wireframe: true, transparent: true, opacity: 0.3 }));
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
    function initNavbarScroll() {
        const navbar = document.querySelector('.glass-nav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) navbar.classList.add('scrolled');
            else navbar.classList.remove('scrolled');
        });
    }
    function initBackToTop() {
        const btn = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) btn.classList.add('show');
            else btn.classList.remove('show');
        });
        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }
    document.getElementById('floatingMsg')?.addEventListener('click', () => alert('Live chat support coming soon! 📱'));
    document.addEventListener('DOMContentLoaded', () => {
        initDarkMode();
        initNavbarScroll();
        initBackToTop();
    });
</script>
</body>
</html>