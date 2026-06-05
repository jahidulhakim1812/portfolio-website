<?php
// admin/dashboard.php - Full Intelligence Dashboard with purchase-based revenue
require_once 'auth.php';
require_once '../config.php';

/* =========================================
   CHECK TABLES AND FETCH DYNAMIC DATA
========================================= */

$coursesExist = false;
$purchasesExist = false;

try {
    $checkCourses = $pdo->query("SHOW TABLES LIKE 'courses'")->rowCount();
    $coursesExist = ($checkCourses > 0);
    if ($coursesExist) {
        $checkPurchases = $pdo->query("SHOW TABLES LIKE 'purchases'")->rowCount();
        $purchasesExist = ($checkPurchases > 0);
    }
} catch (Exception $e) {
    $coursesExist = false;
    $purchasesExist = false;
}

// Initialize variables
$totalCourses = 0;
$totalEnrollments = 0;
$totalRevenue = 0;
$avgPrice = 0;
$mostEnrolled = null;
$highestRevenue = null;
$courseNames = [];
$courseEnrollments = [];
$allCourses = [];
$recentPurchases = [];
$months = [];
$revenueByMonth = [];

if ($coursesExist) {
    // Basic course stats
    $totalCourses = $pdo->query("SELECT COUNT(*) FROM courses WHERE status = 1")->fetchColumn();
    $avgPrice = $pdo->query("SELECT AVG(price) FROM courses WHERE status = 1")->fetchColumn() ?: 0;
    $allCourses = $pdo->query("SELECT id, title, price, enrolled_students, status FROM courses WHERE status = 1 ORDER BY id DESC LIMIT 10")->fetchAll();

    if ($purchasesExist) {
        // --- REVENUE & ENROLLMENTS FROM PURCHASES TABLE ---
        $totalEnrollments = $pdo->query("SELECT COUNT(*) FROM purchases")->fetchColumn();
        $totalRevenue = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM purchases")->fetchColumn();

        // Most purchased course (by number of purchases)
        $mostEnrolled = $pdo->query("
            SELECT c.title, COUNT(p.id) as purchases 
            FROM purchases p 
            JOIN courses c ON p.course_id = c.id 
            GROUP BY p.course_id 
            ORDER BY purchases DESC LIMIT 1
        ")->fetch();

        // Highest revenue course (by sum of amounts)
        $highestRevenue = $pdo->query("
            SELECT c.title, SUM(p.amount) as revenue 
            FROM purchases p 
            JOIN courses c ON p.course_id = c.id 
            GROUP BY p.course_id 
            ORDER BY revenue DESC LIMIT 1
        ")->fetch();

        // Data for bar chart: course name vs number of purchases (top 5)
        $courseData = $pdo->query("
            SELECT c.title, COUNT(p.id) as purchases 
            FROM purchases p 
            JOIN courses c ON p.course_id = c.id 
            GROUP BY p.course_id 
            ORDER BY purchases DESC LIMIT 5
        ")->fetchAll();
        foreach ($courseData as $c) {
            $courseNames[] = $c['title'];
            $courseEnrollments[] = $c['purchases'];
        }

        // Monthly revenue trend (last 6 months from purchases)
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-$i months"));
            $monthEnd = date('Y-m-t', strtotime("-$i months"));
            $monthRevenue = $pdo->query("
                SELECT COALESCE(SUM(amount), 0) 
                FROM purchases 
                WHERE purchase_date BETWEEN '$monthStart' AND '$monthEnd 23:59:59'
            ")->fetchColumn();
            $revenueByMonth[] = $monthRevenue;
            $months[] = date('M', strtotime("-$i months"));
        }

        // Recent purchases for display
        $recentPurchases = $pdo->query("
            SELECT p.id, c.title as course_name, p.user_name, p.amount, p.purchase_date 
            FROM purchases p 
            JOIN courses c ON p.course_id = c.id 
            ORDER BY p.purchase_date DESC LIMIT 10
        ")->fetchAll();

    } else {
        // Fallback: no purchases table – use static course data
        $totalEnrollments = $pdo->query("SELECT SUM(enrolled_students) FROM courses WHERE status = 1")->fetchColumn() ?: 0;
        $totalRevenue = $pdo->query("SELECT SUM(price * enrolled_students) FROM courses WHERE status = 1")->fetchColumn() ?: 0;
        $mostEnrolled = $pdo->query("SELECT title, enrolled_students, price FROM courses WHERE status = 1 ORDER BY enrolled_students DESC LIMIT 1")->fetch();
        $highestRevenue = $pdo->query("SELECT title, price * enrolled_students as revenue FROM courses WHERE status = 1 ORDER BY revenue DESC LIMIT 1")->fetch();
        $courseData = $pdo->query("SELECT title, enrolled_students FROM courses WHERE status = 1 ORDER BY enrolled_students DESC LIMIT 5")->fetchAll();
        foreach ($courseData as $c) {
            $courseNames[] = $c['title'];
            $courseEnrollments[] = $c['enrolled_students'];
        }
        for ($i = 5; $i >= 0; $i--) {
            $months[] = date('M', strtotime("-$i months"));
        }
        // Demo revenue data
        $revenueByMonth = [3800, 6200, 5400, 8900, 11200, 14500];
    }
}

// OTHER CONTENT COUNTS (sliders, services, etc.)
$totalSliders = $pdo->query("SELECT COUNT(*) FROM sliders")->fetchColumn();
$totalServices = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$totalPortfolios = $pdo->query("SELECT COUNT(*) FROM portfolios")->fetchColumn();
$totalTestimonials = $pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$totalLocations = 8; // demo

$recentSliders = $pdo->query("SELECT id, title, status FROM sliders ORDER BY id DESC LIMIT 4")->fetchAll();
$recentServices = $pdo->query("SELECT id, title FROM services ORDER BY id DESC LIMIT 4")->fetchAll();
$recentPortfolios = $pdo->query("SELECT id, title, client FROM portfolios ORDER BY id DESC LIMIT 4")->fetchAll();

// AJAX handlers for adding course, updating fee, and simulating purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_course') {
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
    
    if ($action === 'update_fee') {
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
    
    if ($action === 'purchase_course' && $purchasesExist) {
        $course_id = intval($_POST['course_id']);
        $user_name = trim($_POST['user_name'] ?? 'Demo User');
        $user_email = trim($_POST['user_email'] ?? 'demo@example.com');
        
        $stmt = $pdo->prepare("SELECT price FROM courses WHERE id = ? AND status = 1");
        $stmt->execute([$course_id]);
        $course = $stmt->fetch();
        if ($course) {
            $amount = $course['price'];
            $stmt = $pdo->prepare("INSERT INTO purchases (course_id, user_name, user_email, amount) VALUES (?, ?, ?, ?)");
            $stmt->execute([$course_id, $user_name, $user_email, $amount]);
            // Optionally update enrolled_students counter in courses table
            $pdo->prepare("UPDATE courses SET enrolled_students = enrolled_students + 1 WHERE id = ?")->execute([$course_id]);
            echo json_encode(['success' => true, 'amount' => $amount]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Course not found']);
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
        .main {
            margin-left: 310px;
            padding: 20px;
            transition: margin 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .main.expand { margin-left: 120px; }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .search-box {
            position: relative;
            width: 320px;
        }
        .search-box input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 2rem;
            padding: 12px 20px 12px 45px;
            color: var(--text);
        }
        .search-box i {
            position: absolute;
            left: 18px;
            top: 15px;
            color: var(--muted);
        }
        .theme-toggle {
            background: rgba(255,255,255,0.1);
            border: none;
            border-radius: 2rem;
            width: 45px;
            height: 45px;
            cursor: pointer;
            color: var(--text);
        }
        .profile-img {
            width: 48px;
            height: 48px;
            border-radius: 1.2rem;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .hero-card {
            background: linear-gradient(135deg, rgba(124,58,237,0.2), rgba(6,182,212,0.1));
            border-radius: 2rem;
            padding: 2rem;
            margin-bottom: 25px;
            border: 1px solid var(--border);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }
        .stat-box {
            background: rgba(255,255,255,0.03);
            border-radius: 1.5rem;
            padding: 1.2rem;
            border: 1px solid var(--border);
            transition: 0.2s;
        }
        .stat-box:hover {
            transform: translateY(-5px);
            background: rgba(124,58,237,0.1);
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .stat-number {
            font-size: 2.2rem;
            font-weight: 800;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: rgba(255,255,255,0.03);
            border-radius: 1.5rem;
            padding: 1.2rem;
            border: 1px solid var(--border);
            transition: 0.2s;
            text-align: center;
        }
        .summary-card:hover {
            transform: translateY(-3px);
            background: rgba(124,58,237,0.1);
        }
        .summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 1rem;
            background: rgba(124,58,237,0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 0.8rem;
            color: var(--primary);
        }
        .summary-number { font-size: 1.8rem; font-weight: 700; }
        .manage-btn {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
            border-radius: 2rem;
            padding: 0.3rem 1rem;
            font-size: 0.75rem;
            transition: 0.2s;
            text-decoration: none;
            display: inline-block;
            margin-top: 0.5rem;
        }
        .manage-btn:hover { background: var(--primary); color: white; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .panel {
            background: rgba(255,255,255,0.03);
            border-radius: 1.8rem;
            padding: 1.5rem;
            border: 1px solid var(--border);
        }
        .recent-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        .recent-table td {
            background: rgba(255,255,255,0.04);
            padding: 10px;
            border-radius: 1rem;
        }
        .status-badge {
            background: rgba(16,185,129,0.2);
            color: #10b981;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        @media (max-width: 1000px) { .grid-2 { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .main { margin-left: 100px; } .summary-grid { grid-template-columns: repeat(2, 1fr); } }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: var(--muted); font-size: 0.8rem; }
        .btn-add {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 2rem;
            padding: 0.6rem 1.5rem;
            color: white;
            font-weight: 600;
        }
        .btn-success-custom {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            border-radius: 2rem;
            padding: 0.6rem 1.5rem;
            color: white;
            font-weight: 600;
            margin-left: 10px;
        }
        .modal-content {
            background: var(--panel);
            color: var(--text);
            border-radius: 1.5rem;
        }
        .btn-sm-outline-primary {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
            border-radius: 2rem;
            padding: 0.3rem 0.8rem;
            font-size: 0.75rem;
            transition: 0.2s;
        }
        .btn-sm-outline-primary:hover { background: var(--primary); color: white; }
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

    <!-- 1. Course Intelligence Box (purchase-based stats) -->
    <div class="hero-card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div>
                <h1 style="font-size:2.5rem; font-weight:800;">Course<span style="color:var(--primary);"> Intelligence</span></h1>
                <p style="color:var(--muted);">Real purchase revenue · Enrollment analytics · Performance insights</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addCourseModal"><i class="fas fa-plus me-2"></i>Add Course</button>
                <?php if ($purchasesExist): ?>
                <button class="btn-success-custom" data-bs-toggle="modal" data-bs-target="#purchaseModal"><i class="fas fa-shopping-cart me-2"></i>Test Purchase</button>
                <?php endif; ?>
            </div>
        </div>
        <div class="stats-grid">
            <div class="stat-box"><div class="stat-icon"><i class="fas fa-book-open"></i></div><div class="stat-number"><?php echo $totalCourses; ?></div><div>Active Courses</div></div>
            <div class="stat-box"><div class="stat-icon"><i class="fas fa-users"></i></div><div class="stat-number"><?php echo number_format($totalEnrollments); ?></div><div>Total Purchases</div></div>
            <div class="stat-box"><div class="stat-icon"><i class="fas fa-dollar-sign"></i></div><div class="stat-number">$<?php echo number_format($totalRevenue, 0); ?></div><div>Gross Revenue</div></div>
            <div class="stat-box"><div class="stat-icon"><i class="fas fa-tag"></i></div><div class="stat-number">$<?php echo number_format($avgPrice, 0); ?></div><div>Avg. Course Price</div></div>
            <div class="stat-box"><div class="stat-icon"><i class="fas fa-trophy"></i></div><div class="stat-number"><?php echo $mostEnrolled ? htmlspecialchars($mostEnrolled['title']) : 'N/A'; ?></div><div>Most Purchased</div><small><?php echo $mostEnrolled ? ($purchasesExist ? $mostEnrolled['purchases'].' purchases' : $mostEnrolled['enrolled_students'].' students') : ''; ?></small></div>
            <div class="stat-box"><div class="stat-icon"><i class="fas fa-chart-line"></i></div><div class="stat-number"><?php echo $highestRevenue ? '$'.number_format($purchasesExist ? $highestRevenue['revenue'] : $highestRevenue['revenue'], 0) : '$0'; ?></div><div>Highest Revenue</div><small><?php echo $highestRevenue ? htmlspecialchars($highestRevenue['title']) : ''; ?></small></div>
        </div>
    </div>

    <!-- 2. Content Summary Boxes (Manage buttons) -->
    <div class="summary-grid">
        <div class="summary-card"><div class="summary-icon"><i class="fas fa-images"></i></div><div class="summary-number"><?php echo $totalSliders; ?></div><div>Sliders</div><a href="manage_sliders.php" class="manage-btn">Manage</a></div>
        <div class="summary-card"><div class="summary-icon"><i class="fas fa-cogs"></i></div><div class="summary-number"><?php echo $totalServices; ?></div><div>Services</div><a href="manage_services.php" class="manage-btn">Manage</a></div>
        <div class="summary-card"><div class="summary-icon"><i class="fas fa-briefcase"></i></div><div class="summary-number"><?php echo $totalPortfolios; ?></div><div>Portfolios</div><a href="manage_portfolios.php" class="manage-btn">Manage</a></div>
        <div class="summary-card"><div class="summary-icon"><i class="fas fa-star"></i></div><div class="summary-number"><?php echo $totalTestimonials; ?></div><div>Testimonials</div><a href="manage_testimonials.php" class="manage-btn">Manage</a></div>
        <div class="summary-card"><div class="summary-icon"><i class="fas fa-users"></i></div><div class="summary-number"><?php echo $totalCustomers; ?></div><div>Customers</div><a href="manage_customers.php" class="manage-btn">Manage</a></div>
        <div class="summary-card"><div class="summary-icon"><i class="fas fa-location-dot"></i></div><div class="summary-number"><?php echo $totalLocations; ?></div><div>Locations</div><a href="manage_locations.php" class="manage-btn">Manage</a></div>
    </div>

    <!-- 3. Charts (based on real purchase data) -->
    <div class="grid-2">
        <div class="panel"><h4><i class="fas fa-chart-bar me-2"></i> Most Purchased Courses</h4><canvas id="courseChart" height="200"></canvas></div>
        <div class="panel"><h4><i class="fas fa-chart-line me-2"></i> Monthly Revenue Trend (Actual Purchases)</h4><canvas id="revenueChart" height="200"></canvas></div>
    </div>

    <!-- 4. Recent Purchases Table (if purchases exist) -->
    <?php if ($purchasesExist && count($recentPurchases) > 0): ?>
    <div class="panel mb-3">
        <h4 class="mb-3"><i class="fas fa-history me-2"></i> Recent Purchases</h4>
        <div class="table-responsive">
            <table class="recent-table w-100">
                <thead><tr style="color:var(--muted);"><th>Course</th><th>Customer</th><th>Amount</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach($recentPurchases as $purchase): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($purchase['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($purchase['user_name']); ?></td>
                        <td>$<?php echo number_format($purchase['amount'], 2); ?></td>
                        <td><?php echo date('M d, Y', strtotime($purchase['purchase_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- 5. All Courses Table -->
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
                        <td>
                            <button class="edit-fee-btn btn-sm-outline-primary" data-id="<?php echo $c['id']; ?>" data-price="<?php echo $c['price']; ?>"><i class="fas fa-edit"></i> Edit Fee</button>
                            <a href="manage_courses.php?edit=<?php echo $c['id']; ?>" class="btn-sm-outline-primary">Full Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 6. Recent Sliders, Services, Portfolios -->
    <div class="grid-2">
        <div class="panel"><h4><i class="fas fa-images me-2"></i> Recent Sliders</h4>
            <table class="recent-table w-100">
                <thead><tr style="color:var(--muted);"><th>Title</th><th>Status</th><th></th></tr></thead>
                <tbody><?php foreach($recentSliders as $s): ?>
                <tr><td><?php echo htmlspecialchars($s['title']); ?></td><td><span class="status-badge"><?php echo $s['status'] ? 'Active' : 'Inactive'; ?></span></td><td><a href="manage_sliders.php?edit=<?php echo $s['id']; ?>" class="btn-sm-outline-primary">Edit</a></td></tr>
                <?php endforeach; ?></tbody>
            </table>
        </div>
        <div class="panel"><h4><i class="fas fa-cogs me-2"></i> Recent Services</h4>
            <table class="recent-table w-100">
                <thead><tr style="color:var(--muted);"><th>Title</th><th></th></tr></thead>
                <tbody><?php foreach($recentServices as $s): ?>
                <tr><td><?php echo htmlspecialchars($s['title']); ?></td><td><a href="manage_services.php?edit=<?php echo $s['id']; ?>" class="btn-sm-outline-primary">Edit</a></td></tr>
                <?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
    <div class="panel mt-3">
        <h4><i class="fas fa-briefcase me-2"></i> Recent Portfolios</h4>
        <table class="recent-table w-100">
            <thead><tr style="color:var(--muted);"><th>Title</th><th>Client</th><th></th></tr></thead>
            <tbody><?php foreach($recentPortfolios as $p): ?>
            <tr><td><?php echo htmlspecialchars($p['title']); ?></td><td><?php echo htmlspecialchars($p['client']); ?></td><td><a href="manage_portfolios.php?edit=<?php echo $p['id']; ?>" class="btn-sm-outline-primary">Edit</a></td></tr>
            <?php endforeach; ?></tbody>
        </table>
    </div>

    <div class="footer">© 2025 NEXORA AI | Full Intelligence Dashboard · Revenue tracked from actual purchases</div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add New Course</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="addCourseForm">
                    <div class="mb-3"><label>Title</label><input type="text" id="courseTitle" class="form-control" required></div>
                    <div class="mb-3"><label>Price ($)</label><input type="number" step="0.01" id="coursePrice" class="form-control" required></div>
                    <div class="mb-3"><label>Enrolled Students (initial)</label><input type="number" id="courseEnrolled" class="form-control" value="0"></div>
                    <button type="submit" class="btn btn-primary w-100">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Fee Modal -->
<div class="modal fade" id="editFeeModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Update Fee</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="editCourseId">
                <label>New Price ($)</label>
                <input type="number" step="0.01" id="editCoursePrice" class="form-control">
                <button id="updateFeeBtn" class="btn btn-primary mt-3 w-100">Update</button>
            </div>
        </div>
    </div>
</div>

<!-- Purchase Simulation Modal (only if purchases table exists) -->
<?php if ($purchasesExist): ?>
<div class="modal fade" id="purchaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Simulate Course Purchase</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="purchaseForm">
                    <div class="mb-3"><label>Select Course</label>
                        <select id="purchaseCourseId" class="form-control" required>
                            <option value="">-- Choose a course --</option>
                            <?php foreach($allCourses as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?> ($<?php echo number_format($c['price'], 2); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label>Customer Name</label><input type="text" id="purchaseName" class="form-control" value="Demo Customer"></div>
                    <div class="mb-3"><label>Email</label><input type="email" id="purchaseEmail" class="form-control" value="demo@example.com"></div>
                    <button type="submit" class="btn btn-success w-100">Complete Purchase</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    if (localStorage.getItem('nexoraTheme') === 'light') document.body.classList.add('light');
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('light');
        localStorage.setItem('nexoraTheme', document.body.classList.contains('light') ? 'light' : 'dark');
        themeToggle.innerHTML = document.body.classList.contains('light') ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
        location.reload();
    });
    if(document.body.classList.contains('light')) themeToggle.innerHTML = '<i class="fas fa-moon"></i>'; else themeToggle.innerHTML = '<i class="fas fa-sun"></i>';

    <?php if ($coursesExist): ?>
    // Bar Chart: Purchases by Course
    new Chart(document.getElementById('courseChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($courseNames); ?>,
            datasets: [{
                label: 'Number of Purchases',
                data: <?php echo json_encode($courseEnrollments); ?>,
                backgroundColor: '#7c3aed',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { labels: { color: getComputedStyle(document.body).getPropertyValue('--muted') } } },
            scales: {
                y: { ticks: { color: 'var(--muted)' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                x: { ticks: { color: 'var(--muted)' } }
            }
        }
    });

    // Line Chart: Monthly Revenue Trend
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Monthly Revenue ($)',
                data: <?php echo json_encode($revenueByMonth); ?>,
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6,182,212,0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: getComputedStyle(document.body).getPropertyValue('--muted') } } },
            scales: {
                x: { ticks: { color: 'var(--muted)' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { ticks: { color: 'var(--muted)' }, grid: { color: 'rgba(255,255,255,0.05)' } }
            }
        }
    });

    // Add Course AJAX
    document.getElementById('addCourseForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData();
        fd.append('action', 'add_course');
        fd.append('title', document.getElementById('courseTitle').value);
        fd.append('price', document.getElementById('coursePrice').value);
        fd.append('enrolled_students', document.getElementById('courseEnrolled').value);
        const res = await fetch(window.location.href, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Error: ' + (data.message || 'Unknown error'));
    });

    // Edit Fee AJAX
    const editBtns = document.querySelectorAll('.edit-fee-btn');
    const editModal = new bootstrap.Modal(document.getElementById('editFeeModal'));
    editBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editCourseId').value = btn.dataset.id;
            document.getElementById('editCoursePrice').value = btn.dataset.price;
            editModal.show();
        });
    });
    document.getElementById('updateFeeBtn').addEventListener('click', async () => {
        const fd = new FormData();
        fd.append('action', 'update_fee');
        fd.append('id', document.getElementById('editCourseId').value);
        fd.append('price', document.getElementById('editCoursePrice').value);
        const res = await fetch(window.location.href, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Update failed');
    });

    <?php if ($purchasesExist): ?>
    // Simulate Purchase AJAX
    document.getElementById('purchaseForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const courseId = document.getElementById('purchaseCourseId').value;
        if (!courseId) { alert('Please select a course'); return; }
        const fd = new FormData();
        fd.append('action', 'purchase_course');
        fd.append('course_id', courseId);
        fd.append('user_name', document.getElementById('purchaseName').value);
        fd.append('user_email', document.getElementById('purchaseEmail').value);
        const res = await fetch(window.location.href, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        const data = await res.json();
        if (data.success) {
            alert(`Purchase successful! $${data.amount} added to revenue.`);
            location.reload();
        } else {
            alert('Purchase failed: ' + (data.message || 'Unknown error'));
        }
    });
    <?php endif; ?>

    // Search courses
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('#coursesTable tbody tr').forEach(row => {
            const title = row.cells[0].innerText.toLowerCase();
            row.style.display = title.includes(filter) ? '' : 'none';
        });
    });
    <?php endif; ?>
</script>
</body>
</html>