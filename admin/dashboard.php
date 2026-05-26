<?php
require_once 'auth.php';
require_once '../config.php';

/* =========================================
   FETCH ALL DYNAMIC DATA (Courses + All Content)
========================================= */

// Check if courses table exists
$tableExists = false;
try {
    $check = $pdo->query("SHOW TABLES LIKE 'courses'")->rowCount();
    $tableExists = ($check > 0);
} catch (Exception $e) {
    $tableExists = false;
}

// COURSE DATA
if ($tableExists) {
    $totalCourses = $pdo->query("SELECT COUNT(*) FROM courses WHERE status = 1")->fetchColumn();
    $totalEnrollments = $pdo->query("SELECT SUM(enrolled_students) FROM courses WHERE status = 1")->fetchColumn() ?: 0;
    $totalRevenue = $pdo->query("SELECT SUM(price * enrolled_students) FROM courses WHERE status = 1")->fetchColumn() ?: 0;
    $avgPrice = $pdo->query("SELECT AVG(price) FROM courses WHERE status = 1")->fetchColumn() ?: 0;
    $mostEnrolled = $pdo->query("SELECT title, enrolled_students, price FROM courses WHERE status = 1 ORDER BY enrolled_students DESC LIMIT 1")->fetch();
    $highestRevenue = $pdo->query("SELECT title, price * enrolled_students as revenue FROM courses WHERE status = 1 ORDER BY revenue DESC LIMIT 1")->fetch();
    $recentCourses = $pdo->query("SELECT id, title, price, enrolled_students, status FROM courses ORDER BY id DESC LIMIT 5")->fetchAll();
    $allCourses = $pdo->query("SELECT id, title, price, enrolled_students, status FROM courses WHERE status = 1 ORDER BY id DESC LIMIT 10")->fetchAll();
    $courseNames = [];
    $courseEnrollments = [];
    $courseData = $pdo->query("SELECT title, enrolled_students FROM courses WHERE status = 1 ORDER BY enrolled_students DESC LIMIT 5")->fetchAll();
    foreach ($courseData as $c) {
        $courseNames[] = $c['title'];
        $courseEnrollments[] = $c['enrolled_students'];
    }
} else {
    $totalCourses = $totalEnrollments = $totalRevenue = $avgPrice = 0;
    $mostEnrolled = $highestRevenue = null;
    $recentCourses = $allCourses = [];
    $courseNames = $courseEnrollments = [];
}

// OTHER CONTENT COUNTS
$totalSliders = $pdo->query("SELECT COUNT(*) FROM sliders")->fetchColumn();
$totalServices = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$totalPortfolios = $pdo->query("SELECT COUNT(*) FROM portfolios")->fetchColumn();
$totalTestimonials = $pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$totalLocations = 8; // demo, adjust as needed

// RECENT ITEMS FOR BOXES (no testimonials)
$recentSliders = $pdo->query("SELECT id, title, status FROM sliders ORDER BY id DESC LIMIT 4")->fetchAll();
$recentServices = $pdo->query("SELECT id, title FROM services ORDER BY id DESC LIMIT 4")->fetchAll();
$recentPortfolios = $pdo->query("SELECT id, title, client FROM portfolios ORDER BY id DESC LIMIT 4")->fetchAll();

// Month labels for chart
$months = [];
for ($i = 5; $i >= 0; $i--) $months[] = date('M', strtotime("-$i months"));

// AJAX handlers for adding/updating courses
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    if ($_POST['action'] === 'add_course') {
        $title = trim($_POST['title'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $enrolled = intval($_POST['enrolled_students'] ?? 0);
        if ($title && $price >= 0) {
            $stmt = $pdo->prepare("INSERT INTO courses (title, price, enrolled_students, status) VALUES (?, ?, ?, 1)");
            $stmt->execute([$title, $price, $enrolled]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
        }
        exit;
    }
    if ($_POST['action'] === 'update_fee') {
        $id = intval($_POST['id']);
        $price = floatval($_POST['price']);
        if ($id && $price >= 0) {
            $stmt = $pdo->prepare("UPDATE courses SET price = ? WHERE id = ?");
            $stmt->execute([$price, $id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>NEXORA AI | Full Intelligence Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ========== UNIQUE FUTURISTIC THEME ========== */
        :root {
            --bg: #050816;
            --panel: #0f172a;
            --primary: #7c3aed;
            --primary-glow: #a855f7;
            --secondary: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --text: #ffffff;
            --muted: #94a3b8;
            --border: rgba(255,255,255,0.08);
            --shadow: 0 20px 35px -10px rgba(0,0,0,0.4);
        }
        body.light {
            --bg: #f8fafc;
            --panel: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --border: rgba(0,0,0,0.08);
            --shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--text);
            transition: all 0.3s ease;
            overflow-x: hidden;
        }
        body::before, body::after {
            content: '';
            position: fixed;
            width: 800px;
            height: 800px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(124,58,237,0.15), transparent);
            top: -300px;
            left: -300px;
            z-index: -1;
            animation: floatBg 20s infinite alternate;
        }
        body::after {
            background: radial-gradient(circle, rgba(6,182,212,0.12), transparent);
            top: auto; bottom: -200px; right: -200px; left: auto;
            animation: floatBg2 18s infinite alternate;
        }
        @keyframes floatBg { 0% { transform: translate(0,0); } 100% { transform: translate(100px, 80px); } }
        @keyframes floatBg2 { 0% { transform: translate(0,0); } 100% { transform: translate(-80px, -60px); } }
        /* Sidebar */
        .sidebar { position: fixed; left: 20px; top: 20px; bottom: 20px; width: 280px; background: rgba(15,23,42,0.9); backdrop-filter: blur(20px); border-radius: 2rem; border: 1px solid var(--border); transition: 0.3s; z-index: 1050; box-shadow: var(--shadow); }
        body.light .sidebar { background: rgba(255,255,255,0.9); }
        .sidebar.collapsed { width: 90px; }
        .logo-area { padding: 1.5rem; display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); }
        .logo { font-size: 1.8rem; font-weight: 800; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .toggle-btn { background: rgba(255,255,255,0.1); border: none; border-radius: 1rem; width: 40px; height: 40px; color: white; cursor: pointer; }
        body.light .toggle-btn { background: rgba(0,0,0,0.05); color: #0f172a; }
        .toggle-btn:hover { background: var(--primary); color: white; }
        .menu { padding: 1rem; }
        .menu-title { color: var(--muted); font-size: 0.7rem; letter-spacing: 2px; margin: 1rem 1rem 0.5rem; }
        .menu a { display: flex; align-items: center; gap: 14px; padding: 0.8rem 1rem; border-radius: 1.2rem; color: var(--muted); text-decoration: none; margin-bottom: 0.5rem; transition: 0.2s; }
        .menu a i { width: 24px; font-size: 1.2rem; }
        .menu a:hover, .menu a.active { background: rgba(124,58,237,0.2); color: var(--primary-glow); transform: translateX(5px); }
        .sidebar.collapsed .logo, .sidebar.collapsed .menu span, .sidebar.collapsed .menu-title { display: none; }
        .sidebar.collapsed .menu a { justify-content: center; }
        .main { margin-left: 310px; padding: 20px; transition: 0.3s; }
        .main.expand { margin-left: 120px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .search-box { position: relative; width: 320px; }
        .search-box input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 2rem; padding: 12px 20px 12px 45px; color: var(--text); }
        .search-box i { position: absolute; left: 18px; top: 15px; color: var(--muted); }
        .theme-toggle { background: rgba(255,255,255,0.1); border: none; border-radius: 2rem; width: 45px; height: 45px; cursor: pointer; color: var(--text); }
        .profile-img { width: 48px; height: 48px; border-radius: 1.2rem; background: var(--primary); display: flex; align-items: center; justify-content: center; }
        /* Course Intelligence Hero Card */
        .hero-card { background: linear-gradient(135deg, rgba(124,58,237,0.2), rgba(6,182,212,0.1)); border-radius: 2rem; padding: 2rem; margin-bottom: 25px; border: 1px solid var(--border); }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 25px; }
        .stat-box { background: rgba(255,255,255,0.03); border-radius: 1.5rem; padding: 1.2rem; border: 1px solid var(--border); transition: 0.2s; }
        .stat-box:hover { transform: translateY(-5px); background: rgba(124,58,237,0.1); }
        .stat-icon { width: 50px; height: 50px; border-radius: 1rem; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; }
        .stat-number { font-size: 2.2rem; font-weight: 800; }
        /* Content Summary Boxes (6 cards) */
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .summary-card { background: rgba(255,255,255,0.03); border-radius: 1.5rem; padding: 1.2rem; border: 1px solid var(--border); transition: 0.2s; text-align: center; }
        .summary-card:hover { transform: translateY(-3px); background: rgba(124,58,237,0.1); }
        .summary-icon { width: 48px; height: 48px; border-radius: 1rem; background: rgba(124,58,237,0.2); display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 0.8rem; color: var(--primary); }
        .summary-number { font-size: 1.8rem; font-weight: 700; }
        .manage-btn { background: transparent; border: 1px solid var(--primary); color: var(--primary); border-radius: 2rem; padding: 0.3rem 1rem; font-size: 0.75rem; transition: 0.2s; text-decoration: none; display: inline-block; margin-top: 0.5rem; }
        .manage-btn:hover { background: var(--primary); color: white; }
        /* Charts & Tables */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .panel { background: rgba(255,255,255,0.03); border-radius: 1.8rem; padding: 1.5rem; border: 1px solid var(--border); }
        .recent-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        .recent-table td { background: rgba(255,255,255,0.04); padding: 10px; border-radius: 1rem; }
        .status-badge { background: rgba(16,185,129,0.2); color: #10b981; padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
        @media (max-width: 1000px) { .grid-2 { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .sidebar { width: 80px; left: 10px; } .main { margin-left: 100px; } .summary-grid { grid-template-columns: repeat(2, 1fr); } }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: var(--muted); font-size: 0.8rem; }
        .btn-add { background: linear-gradient(135deg, var(--primary), var(--secondary)); border: none; border-radius: 2rem; padding: 0.6rem 1.5rem; color: white; font-weight: 600; }
        .modal-content { background: var(--panel); color: var(--text); border-radius: 1.5rem; }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="main" id="main">
    <div class="topbar">
        <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search courses..."></div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon"></i></button>
            <div class="profile-img"><i class="fas fa-user-astronaut"></i></div>
            <div><strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong><br><small style="color:var(--muted)">Super Admin</small></div>
        </div>
    </div>

    <!-- 1. Course Intelligence Box -->
    <div class="hero-card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div><h1 style="font-size:2.5rem; font-weight:800;">Course<span style="color:var(--primary);"> Intelligence</span></h1>
            <p style="color:var(--muted);">Enrollment analytics · Revenue tracking · Performance insights</p></div>
            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addCourseModal"><i class="fas fa-plus me-2"></i>Add New Course</button>
        </div>
        <div class="stats-grid">
            <div class="stat-box"><div class="stat-icon"><i class="fas fa-book-open"></i></div><div class="stat-number"><?php echo $totalCourses; ?></div><div>Active Courses</div></div>
            <div class="stat-box"><div class="stat-icon"><i class="fas fa-users"></i></div><div class="stat-number"><?php echo number_format($totalEnrollments); ?></div><div>Total Enrollments</div></div>
            <div class="stat-box"><div class="stat-icon"><i class="fas fa-dollar-sign"></i></div><div class="stat-number">$<?php echo number_format($totalRevenue, 0); ?></div><div>Gross Revenue</div></div>
            <div class="stat-box"><div class="stat-icon"><i class="fas fa-tag"></i></div><div class="stat-number">$<?php echo number_format($avgPrice, 0); ?></div><div>Avg. Course Price</div></div>
            <div class="stat-box"><div class="stat-icon"><i class="fas fa-trophy"></i></div><div class="stat-number"><?php echo $mostEnrolled ? htmlspecialchars($mostEnrolled['title']) : 'N/A'; ?></div><div>Most Enrolled</div><small><?php echo $mostEnrolled ? $mostEnrolled['enrolled_students'].' students' : ''; ?></small></div>
            <div class="stat-box"><div class="stat-icon"><i class="fas fa-chart-line"></i></div><div class="stat-number"><?php echo $highestRevenue ? '$'.number_format($highestRevenue['revenue'], 0) : '$0'; ?></div><div>Highest Revenue</div><small><?php echo $highestRevenue ? htmlspecialchars($highestRevenue['title']) : ''; ?></small></div>
        </div>
    </div>

    <!-- 2. Content Summary Boxes (6 cards with Manage buttons) -->
    <div class="summary-grid">
        <div class="summary-card"><div class="summary-icon"><i class="fas fa-images"></i></div><div class="summary-number"><?php echo $totalSliders; ?></div><div>Sliders</div><a href="manage_sliders.php" class="manage-btn">Manage</a></div>
        <div class="summary-card"><div class="summary-icon"><i class="fas fa-cogs"></i></div><div class="summary-number"><?php echo $totalServices; ?></div><div>Services</div><a href="manage_services.php" class="manage-btn">Manage</a></div>
        <div class="summary-card"><div class="summary-icon"><i class="fas fa-briefcase"></i></div><div class="summary-number"><?php echo $totalPortfolios; ?></div><div>Portfolios</div><a href="manage_portfolios.php" class="manage-btn">Manage</a></div>
        <div class="summary-card"><div class="summary-icon"><i class="fas fa-star"></i></div><div class="summary-number"><?php echo $totalTestimonials; ?></div><div>Testimonials</div><a href="manage_testimonials.php" class="manage-btn">Manage</a></div>
        <div class="summary-card"><div class="summary-icon"><i class="fas fa-users"></i></div><div class="summary-number"><?php echo $totalCustomers; ?></div><div>Customers</div><a href="manage_customers.php" class="manage-btn">Manage</a></div>
        <div class="summary-card"><div class="summary-icon"><i class="fas fa-location-dot"></i></div><div class="summary-number"><?php echo $totalLocations; ?></div><div>Locations</div><a href="manage_locations.php" class="manage-btn">Manage</a></div>
    </div>

    <!-- 3. Charts -->
    <div class="grid-2">
        <div class="panel"><h4><i class="fas fa-chart-bar me-2"></i> Enrollments by Course</h4><canvas id="courseChart" height="200"></canvas></div>
        <div class="panel"><h4><i class="fas fa-chart-line me-2"></i> Monthly Revenue Trend (Sample)</h4><canvas id="revenueChart" height="200"></canvas><small class="text-muted">*Demo data – connect to real transactions</small></div>
    </div>

    <!-- 4. All Courses Table -->
    <div class="panel mb-3">
        <h4 class="mb-3"><i class="fas fa-list me-2"></i> All Courses</h4>
        <div class="table-responsive">
            <table class="recent-table w-100" id="coursesTable">
                <thead><tr style="color:var(--muted);"><th>Title</th><th>Price</th><th>Enrolled</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($allCourses as $c): ?>
                    <tr data-id="<?php echo $c['id']; ?>">
                        <td><?php echo htmlspecialchars($c['title']); ?></td>
                        <td class="price-cell">$<?php echo number_format($c['price'], 2); ?></td>
                        <td><?php echo $c['enrolled_students']; ?></td>
                        <td><span class="status-badge"><?php echo $c['status'] ? 'Active' : 'Draft'; ?></span></td>
                        <td><button class="btn btn-sm btn-outline-primary edit-fee-btn" data-id="<?php echo $c['id']; ?>" data-price="<?php echo $c['price']; ?>"><i class="fas fa-edit"></i> Edit Fee</button>
                            <a href="manage_courses.php?edit=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary">Full Edit</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. Recent Data Panels (Sliders, Services, Portfolios) – No Customer Reviews -->
    <div class="grid-2">
        <div class="panel"><h4><i class="fas fa-images me-2"></i> Recent Sliders</h4>
            <table class="recent-table w-100">
                <thead><tr style="color:var(--muted);"><th>Title</th><th>Status</th><th></th></tr></thead>
                <tbody><?php foreach($recentSliders as $s): ?>
                <tr><td><?php echo htmlspecialchars($s['title']); ?></td><td><span class="status-badge"><?php echo $s['status'] ? 'Active' : 'Inactive'; ?></span></td><td><a href="manage_sliders.php?edit=<?php echo $s['id']; ?>" class="btn btn-sm btn-primary">Edit</a></td></tr>
                <?php endforeach; ?></tbody>
            </table>
        </div>
        <div class="panel"><h4><i class="fas fa-cogs me-2"></i> Recent Services</h4>
            <table class="recent-table w-100">
                <thead><tr><th>Title</th><th></th></tr></thead>
                <tbody><?php foreach($recentServices as $s): ?>
                <tr><td><?php echo htmlspecialchars($s['title']); ?></td><td><a href="manage_services.php?edit=<?php echo $s['id']; ?>" class="btn btn-sm btn-primary">Edit</a></td></tr>
                <?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
    <div class="panel mt-3">
        <h4><i class="fas fa-briefcase me-2"></i> Recent Portfolios</h4>
        <table class="recent-table w-100">
            <thead><tr><th>Title</th><th>Client</th><th></th></tr></thead>
            <tbody><?php foreach($recentPortfolios as $p): ?>
            <tr><td><?php echo htmlspecialchars($p['title']); ?></td><td><?php echo htmlspecialchars($p['client']); ?></td><td><a href="manage_portfolios.php?edit=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">Edit</a></td></tr>
            <?php endforeach; ?></tbody>
        </table>
    </div>

    <div class="footer">© 2025 NEXORA AI | Full Intelligence Dashboard · All modules integrated</div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Add New Course</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><form id="addCourseForm"><div class="mb-3"><label>Title</label><input type="text" id="courseTitle" class="form-control" required></div><div class="mb-3"><label>Price ($)</label><input type="number" step="0.01" id="coursePrice" class="form-control" required></div><div class="mb-3"><label>Enrolled Students</label><input type="number" id="courseEnrolled" class="form-control" value="0"></div><button type="submit" class="btn btn-primary w-100">Create</button></form></div></div></div></div>

<!-- Edit Fee Modal -->
<div class="modal fade" id="editFeeModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Update Fee</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="editCourseId"><label>New Price ($)</label><input type="number" step="0.01" id="editCoursePrice" class="form-control"><button id="updateFeeBtn" class="btn btn-primary mt-3 w-100">Update</button></div></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar toggle
    const sidebar = document.getElementById('sidebar'), main = document.getElementById('main');
    document.getElementById('toggleBtn').onclick = () => { sidebar.classList.toggle('collapsed'); main.classList.toggle('expand'); localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed')); };
    if (localStorage.getItem('sidebarCollapsed') === 'true') { sidebar.classList.add('collapsed'); main.classList.add('expand'); }

    <?php if ($tableExists): ?>
    // Charts
    new Chart(document.getElementById('courseChart'), { type: 'bar', data: { labels: <?php echo json_encode($courseNames); ?>, datasets: [{ label: 'Enrolled Students', data: <?php echo json_encode($courseEnrollments); ?>, backgroundColor: '#7c3aed', borderRadius: 8 }] }, options: { responsive: true, plugins: { legend: { labels: { color: getComputedStyle(document.body).getPropertyValue('--muted') } } }, scales: { y: { ticks: { color: 'var(--muted)' }, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { ticks: { color: 'var(--muted)' } } } } });
    new Chart(document.getElementById('revenueChart'), { type: 'line', data: { labels: <?php echo json_encode($months); ?>, datasets: [{ label: 'Monthly Revenue ($)', data: [3800, 6200, 5400, 8900, 11200, 14500], borderColor: '#06b6d4', backgroundColor: 'rgba(6,182,212,0.1)', fill: true, tension: 0.3 }] }, options: { responsive: true, plugins: { legend: { labels: { color: getComputedStyle(document.body).getPropertyValue('--muted') } } }, scales: { x: { ticks: { color: 'var(--muted)' }, grid: { color: 'rgba(255,255,255,0.05)' } }, y: { ticks: { color: 'var(--muted)' }, grid: { color: 'rgba(255,255,255,0.05)' } } } } });

    // Add Course AJAX
    document.getElementById('addCourseForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(); fd.append('action', 'add_course'); fd.append('title', document.getElementById('courseTitle').value); fd.append('price', document.getElementById('coursePrice').value); fd.append('enrolled_students', document.getElementById('courseEnrolled').value);
        const res = await fetch('dashboard.php', { method: 'POST', body: fd }); const data = await res.json();
        if (data.success) location.reload(); else alert('Error');
    });

    // Edit fee
    const editBtns = document.querySelectorAll('.edit-fee-btn');
    const editModal = new bootstrap.Modal(document.getElementById('editFeeModal'));
    editBtns.forEach(btn => btn.addEventListener('click', () => { document.getElementById('editCourseId').value = btn.dataset.id; document.getElementById('editCoursePrice').value = btn.dataset.price; editModal.show(); }));
    document.getElementById('updateFeeBtn').addEventListener('click', async () => {
        const fd = new FormData(); fd.append('action', 'update_fee'); fd.append('id', document.getElementById('editCourseId').value); fd.append('price', document.getElementById('editCoursePrice').value);
        const res = await fetch('dashboard.php', { method: 'POST', body: fd }); const data = await res.json();
        if (data.success) location.reload(); else alert('Update failed');
    });

    // Search courses
    document.getElementById('searchInput').addEventListener('keyup', function() { const filter = this.value.toLowerCase(); document.querySelectorAll('#coursesTable tbody tr').forEach(row => { row.style.display = row.cells[0].innerText.toLowerCase().includes(filter) ? '' : 'none'; }); });
    <?php endif; ?>

    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    if (localStorage.getItem('nexoraTheme') === 'light') document.body.classList.add('light');
    themeToggle.addEventListener('click', () => { document.body.classList.toggle('light'); localStorage.setItem('nexoraTheme', document.body.classList.contains('light') ? 'light' : 'dark'); themeToggle.innerHTML = document.body.classList.contains('light') ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>'; location.reload(); });
    if(document.body.classList.contains('light')) themeToggle.innerHTML = '<i class="fas fa-moon"></i>'; else themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
</script>
</body>
</html>