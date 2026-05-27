<?php
// course-details.php - Supports both inline JSON and uploaded JSON file for curriculum
require_once 'config.php';

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND status = 1");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: index.php');
    exit;
}

/**
 * Get curriculum data from either JSON string or uploaded JSON file
 * @param string|null $curriculum DB value (JSON string or file path)
 * @return array|null Decoded curriculum array or null if invalid
 */
function getCurriculumData($curriculum) {
    if (empty($curriculum)) {
        return null;
    }
    
    // Check if it's a file path (contains .json and file exists)
    if (strpos($curriculum, '.json') !== false && file_exists('../' . $curriculum)) {
        $fileContent = file_get_contents('../' . $curriculum);
        $decoded = json_decode($fileContent, true);
        if (is_array($decoded) && isset($decoded['modules'])) {
            return $decoded;
        }
        return null;
    }
    
    // Otherwise treat as JSON string
    $decoded = json_decode($curriculum, true);
    if (is_array($decoded) && isset($decoded['modules'])) {
        return $decoded;
    }
    
    return null;
}

// Get curriculum data from file or JSON string
$curriculum = getCurriculumData($course['curriculum']);

// Calculate real enrollment
$totalEnrolled = (int)$course['enrolled_students'];
try {
    $enrollStmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND payment_status = 'completed'");
    $enrollStmt->execute([$course_id]);
    $completedEnrollments = (int)$enrollStmt->fetchColumn();
    $totalEnrolled += $completedEnrollments;
} catch (PDOException $e) {
    // Table may not exist – ignore
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="<?php echo htmlspecialchars(substr(strip_tags($course['short_description'] ?: $course['description']), 0, 160)); ?>">
    <title><?php echo htmlspecialchars($course['title']); ?> | AR Tech Solutions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* [All the CSS from the previous version – kept exactly the same] */
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
        .glass-nav {
            position: relative;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1030;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(124, 58, 237, 0.2);
            box-shadow: var(--shadow);
            transition: all 0.3s;
            padding: 0.5rem 1rem;
        }
        body.dark .glass-nav {
            background: rgba(15, 15, 18, 0.85);
            border-color: rgba(124, 58, 237, 0.4);
        }
        .glass-nav.scrolled {
            padding: 0.3rem 1rem;
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
        .nav-link:hover::after, .nav-link.active::after {
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
            margin-left: 0.5rem;
        }
        body.dark .dark-toggle {
            background: rgba(124, 58, 237, 0.3);
            color: var(--secondary);
        }
        .glass-card {
            background: var(--surface-light);
            border-radius: 28px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
            padding: 1.5rem;
            height: 100%;
            box-shadow: var(--shadow);
        }
        body.dark .glass-card {
            background: var(--surface-dark);
            border-color: var(--border-dark);
        }
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
        .curriculum-table {
            width: 100%;
            border-collapse: collapse;
        }
        .curriculum-table th, .curriculum-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
        }
        body.dark .curriculum-table th, body.dark .curriculum-table td {
            border-bottom-color: var(--border-dark);
        }
        .instructor-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid var(--primary);
        }
        .fee-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 60px;
            padding: 16px 32px;
            font-size: 1.5rem;
            font-weight: 800;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .fee-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 25px rgba(124, 58, 237, 0.4);
            color: white;
        }
        @media (max-width: 768px) {
            .instructor-img { width: 80px; height: 80px; }
            .navbar-collapse {
                background: rgba(255,255,255,0.95);
                border-radius: 28px;
                padding: 1rem;
                margin-top: 1rem;
            }
            body.dark .navbar-collapse {
                background: rgba(20,20,30,0.95);
            }
            .fee-btn {
                padding: 10px 20px;
                font-size: 1.2rem;
            }
        }
        .breadcrumb {
            background: transparent;
            padding: 0;
        }
        .breadcrumb-item a {
            color: var(--text-muted-light);
            text-decoration: none;
        }
        body.dark .breadcrumb-item a {
            color: var(--text-muted-dark);
        }
        .breadcrumb-item.active {
            color: var(--primary);
        }
        footer {
            background: #0f172a;
            color: #cbd5e1;
            padding: 3rem 0 1.5rem;
            margin-top: 3rem;
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
        .glass-card h2, .glass-card h3, .glass-card h4, .glass-card p {
            color: inherit;
        }
        .table {
            color: inherit;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg glass-nav" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-vr-cardboard me-2"></i>ARTECH</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
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
    <nav aria-label="breadcrumb">
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
                <div class="bg-primary bg-opacity-25 rounded-4 d-flex align-items-center justify-content-center" style="height: 250px;">
                    <i class="<?php echo htmlspecialchars($course['icon_class']); ?> fa-5x"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-8">
            <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($course['title']); ?></h1>
            <p class="lead"><?php echo nl2br(htmlspecialchars($course['short_description'] ?: $course['description'])); ?></p>
            <div class="row mt-4">
                <div class="col-6 col-md-3 mb-3">
                    <div class="glass-card text-center p-3">
                        <i class="fas fa-clock fa-2x text-primary"></i>
                        <h4><?php echo htmlspecialchars($course['duration']); ?></h4>
                        <small>Duration</small>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="glass-card text-center p-3">
                        <i class="fas fa-signal fa-2x text-primary"></i>
                        <h4><?php echo htmlspecialchars($course['level']); ?></h4>
                        <small>Level</small>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="glass-card text-center p-3">
                        <i class="fas fa-users fa-2x text-primary"></i>
                        <h4><?php echo number_format($totalEnrolled); ?>+</h4>
                        <small>Enrolled</small>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="glass-card text-center p-3">
                        <i class="fas fa-project-diagram fa-2x text-primary"></i>
                        <h4><?php echo (int)$course['total_projects']; ?>+</h4>
                        <small>Projects</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee as prominent button -->
    <div class="glass-card p-4 mb-5 text-center">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3>Course Fee</h3>
                <div class="d-flex justify-content-center gap-4 flex-wrap">
                    <div>
                        <small class="text-muted-custom">Online</small>
                        <div class="fee-btn mt-2">$<?php echo number_format($course['price'], 2); ?></div>
                    </div>
                    <?php if($course['price_offline'] > 0): ?>
                    <div>
                        <small class="text-muted-custom">Offline</small>
                        <div class="fee-btn mt-2" style="background: linear-gradient(135deg, var(--secondary), var(--primary));">$<?php echo number_format($course['price_offline'], 2); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6 mt-4 mt-md-0">
                <a href="enroll.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary-custom btn-lg px-5">Enroll Now <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Course Curriculum – supports both inline JSON and uploaded JSON file -->
    <?php if($curriculum && !empty($curriculum['modules'])): ?>
    <div class="glass-card p-4 mb-4">
        <h2 class="mb-4">Course Curriculum</h2>
        <?php foreach($curriculum['modules'] as $moduleIndex => $module): ?>
            <?php if(empty($module['classes'])) continue; ?>
            <div class="mb-4">
                <h4 class="mb-3">Module <?php echo $moduleIndex + 1; ?>: <?php echo htmlspecialchars($module['title'] ?? 'Untitled Module'); ?></h4>
                <div class="table-responsive">
                    <table class="curriculum-table">
                        <thead>
                            <tr><th>Class</th><th>Topic</th><th>Type</th><th>Resources</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($module['classes'] as $class): ?>
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
                <?php if(!empty($module['projects'])): ?>
                <div class="mt-2"><strong>Projects:</strong> <?php echo htmlspecialchars($module['projects']); ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="glass-card p-4 mb-4 text-center">
        <i class="fas fa-book-open fa-3x text-primary mb-3"></i>
        <h4>Curriculum Coming Soon</h4>
        <p class="text-muted-custom">We're preparing an amazing learning path for you.</p>
    </div>
    <?php endif; ?>

    <!-- Career Outcomes -->
    <?php if(!empty($course['career_outcomes'])): ?>
    <div class="glass-card p-4 mb-4">
        <h2>Career Outcomes</h2>
        <p><?php echo nl2br(htmlspecialchars($course['career_outcomes'])); ?></p>
    </div>
    <?php endif; ?>

    <!-- Prerequisites -->
    <?php if(!empty($course['prerequisites'])): ?>
    <div class="glass-card p-4 mb-4">
        <h2>Prerequisites</h2>
        <p><?php echo nl2br(htmlspecialchars($course['prerequisites'])); ?></p>
    </div>
    <?php endif; ?>

    <!-- Software You'll Learn -->
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

<!-- Floating elements -->
<div class="floating-msg" id="floatingMsg"><i class="fas fa-comment-dots"></i></div>
<div class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
    document.addEventListener('DOMContentLoaded', () => { initDarkMode(); initNavbarScroll(); initBackToTop(); });
</script>
</body>
</html>