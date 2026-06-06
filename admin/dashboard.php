<?php
// admin/dashboard.php - Dashboard using enrollments table with BDT Taka
require_once 'auth.php';
require_once '../config.php';

/* =========================================
   CHECK TABLES
========================================= */
$coursesExist = false;
$enrollmentsExist = false;

try {
    $checkCourses = $pdo->query("SHOW TABLES LIKE 'courses'")->rowCount();
    $coursesExist = ($checkCourses > 0);
    $checkEnrollments = $pdo->query("SHOW TABLES LIKE 'enrollments'")->rowCount();
    $enrollmentsExist = ($checkEnrollments > 0);
} catch (Exception $e) {
    $coursesExist = false;
    $enrollmentsExist = false;
}

// Initialize variables
$totalCourses      = 0;
$totalEnrollments  = 0;
$totalRevenue      = 0;
$paidRevenue       = 0;
$pendingRevenue    = 0;
$avgFee            = 0;
$mostEnrolled      = null;
$highestRevenue    = null;
$courseNames       = [];
$courseEnrollments = [];
$allCourses        = [];
$recentEnrollments = [];
$months            = [];
$revenueByMonth    = [];

if ($coursesExist) {
    $totalCourses = $pdo->query("SELECT COUNT(*) FROM courses WHERE status = 1")->fetchColumn();
    $avgFee       = $pdo->query("SELECT AVG(price) FROM courses WHERE status = 1")->fetchColumn() ?: 0;
    $allCourses   = $pdo->query("SELECT id, title, price, enrolled_students, status FROM courses WHERE status = 1 ORDER BY id DESC LIMIT 10")->fetchAll();
}

if ($enrollmentsExist) {
    // Total enrollments
    $totalEnrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();

    // Total gross revenue from amount column
    $totalRevenue = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM enrollments")->fetchColumn();

    // Paid revenue
    $paidRevenue = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM enrollments WHERE payment_status = 'paid'")->fetchColumn();

    // Pending revenue
    $pendingRevenue = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM enrollments WHERE payment_status = 'pending'")->fetchColumn();

    // Most enrolled course
    $mostEnrolled = $pdo->query("
        SELECT c.title, COUNT(e.id) as enrollments
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        GROUP BY e.course_id
        ORDER BY enrollments DESC LIMIT 1
    ")->fetch();

    // Highest revenue course
    $highestRevenue = $pdo->query("
        SELECT c.title, SUM(e.amount) as revenue
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        GROUP BY e.course_id
        ORDER BY revenue DESC LIMIT 1
    ")->fetch();

    // Bar chart data: top 5 courses by enrollments
    $courseData = $pdo->query("
        SELECT c.title, COUNT(e.id) as enrollments
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        GROUP BY e.course_id
        ORDER BY enrollments DESC LIMIT 5
    ")->fetchAll();
    foreach ($courseData as $c) {
        $courseNames[]       = $c['title'];
        $courseEnrollments[] = $c['enrollments'];
    }

    // Monthly revenue trend (last 6 months)
    for ($i = 5; $i >= 0; $i--) {
        $monthStart   = date('Y-m-01', strtotime("-$i months"));
        $monthEnd     = date('Y-m-t',  strtotime("-$i months"));
        $monthRevenue = $pdo->query("
            SELECT COALESCE(SUM(amount), 0)
            FROM enrollments
            WHERE enrollment_date BETWEEN '$monthStart' AND '$monthEnd 23:59:59'
        ")->fetchColumn();
        $revenueByMonth[] = $monthRevenue;
        $months[]         = date('M', strtotime("-$i months"));
    }

    // Recent 10 enrollments
    $recentEnrollments = $pdo->query("
        SELECT e.id, e.student_name, e.student_email, e.amount,
               e.payment_status, e.enrollment_date, e.transaction_id,
               c.title as course_title
        FROM enrollments e
        LEFT JOIN courses c ON e.course_id = c.id
        ORDER BY e.enrollment_date DESC LIMIT 10
    ")->fetchAll();

} elseif ($coursesExist) {
    // Fallback: derive from courses table
    $totalEnrollments = $pdo->query("SELECT SUM(enrolled_students) FROM courses WHERE status = 1")->fetchColumn() ?: 0;
    $totalRevenue     = $pdo->query("SELECT SUM(price * enrolled_students) FROM courses WHERE status = 1")->fetchColumn() ?: 0;
    $mostEnrolled     = $pdo->query("SELECT title, enrolled_students FROM courses WHERE status = 1 ORDER BY enrolled_students DESC LIMIT 1")->fetch();
    $highestRevenue   = $pdo->query("SELECT title, price * enrolled_students as revenue FROM courses WHERE status = 1 ORDER BY revenue DESC LIMIT 1")->fetch();
    $courseData       = $pdo->query("SELECT title, enrolled_students FROM courses WHERE status = 1 ORDER BY enrolled_students DESC LIMIT 5")->fetchAll();
    foreach ($courseData as $c) {
        $courseNames[]       = $c['title'];
        $courseEnrollments[] = $c['enrolled_students'];
    }
    for ($i = 5; $i >= 0; $i--) {
        $months[]         = date('M', strtotime("-$i months"));
        $revenueByMonth[] = 0;
    }
}

// Other content counts
$totalSliders      = $pdo->query("SELECT COUNT(*) FROM sliders")->fetchColumn();
$totalServices     = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$totalPortfolios   = $pdo->query("SELECT COUNT(*) FROM portfolios")->fetchColumn();
$totalTestimonials = $pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
$totalCustomers    = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();

$recentSliders     = $pdo->query("SELECT id, title, status FROM sliders ORDER BY id DESC LIMIT 4")->fetchAll();
$recentServices    = $pdo->query("SELECT id, title FROM services ORDER BY id DESC LIMIT 4")->fetchAll();
$recentPortfolios  = $pdo->query("SELECT id, title, client FROM portfolios ORDER BY id DESC LIMIT 4")->fetchAll();

// AJAX handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'add_course') {
        $title    = trim($_POST['title'] ?? '');
        $price    = floatval($_POST['price'] ?? 0);
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
        $id    = intval($_POST['id']);
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

// Helper: format Taka
function taka($amount, $decimals = 0) {
    return '৳' . number_format($amount, $decimals);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Admin Dashboard | AR Tech Solutions</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg:           #050816;
            --panel:        #0f172a;
            --primary:      #7c3aed;
            --primary-glow: #a855f7;
            --secondary:    #06b6d4;
            --success:      #10b981;
            --warning:      #f59e0b;
            --danger:       #ef4444;
            --text:         #ffffff;
            --muted:        #94a3b8;
            --border:       rgba(255,255,255,0.08);
            --shadow:       0 20px 35px -10px rgba(0,0,0,0.4);
        }
        body.light {
            --bg:     #f1f5f9;
            --panel:  #ffffff;
            --text:   #0f172a;
            --muted:  #475569;
            --border: rgba(0,0,0,0.08);
            --shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--text);
            transition: all 0.3s ease;
            overflow-x: hidden;
        }
        body::before, body::after {
            content:''; position:fixed; width:800px; height:800px; border-radius:50%;
            background:radial-gradient(circle, rgba(124,58,237,0.15), transparent);
            top:-300px; left:-300px; z-index:-1;
            animation:floatBg 20s infinite alternate;
        }
        body::after {
            background:radial-gradient(circle, rgba(6,182,212,0.12), transparent);
            top:auto; bottom:-200px; right:-200px; left:auto;
            animation:floatBg2 18s infinite alternate;
        }
        @keyframes floatBg  { 0%{transform:translate(0,0)} 100%{transform:translate(100px,80px)} }
        @keyframes floatBg2 { 0%{transform:translate(0,0)} 100%{transform:translate(-80px,-60px)} }

        .main { margin-left:310px; padding:24px; transition:margin 0.3s cubic-bezier(.4,0,.2,1); }
        .main.expand { margin-left:120px; }

        /* Topbar */
        .topbar {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:28px; flex-wrap:wrap; gap:15px;
        }
        .search-box { position:relative; width:320px; }
        .search-box input {
            width:100%; background:rgba(255,255,255,0.05); border:1px solid var(--border);
            border-radius:2rem; padding:12px 20px 12px 45px; color:var(--text);
        }
        .search-box i { position:absolute; left:18px; top:15px; color:var(--muted); }
        .theme-toggle {
            background:rgba(255,255,255,0.1); border:none; border-radius:2rem;
            width:45px; height:45px; cursor:pointer; color:var(--text); font-size:1.1rem;
        }
        .profile-img {
            width:48px; height:48px; border-radius:1.2rem;
            background:var(--primary); display:flex; align-items:center; justify-content:center;
        }

        /* Hero */
        .hero-card {
            background:linear-gradient(135deg,rgba(124,58,237,0.18),rgba(6,182,212,0.08));
            border-radius:2rem; padding:2rem 2.2rem; margin-bottom:28px;
            border:1px solid var(--border);
        }

        /* KPI Stat Grid */
        .kpi-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(185px,1fr));
            gap:18px; margin-top:22px;
        }
        .kpi-box {
            background:rgba(255,255,255,0.03); border-radius:1.5rem;
            padding:1.3rem 1.2rem; border:1px solid var(--border);
            transition:transform 0.2s, background 0.2s;
            position:relative; overflow:hidden;
        }
        .kpi-box:hover { transform:translateY(-5px); background:rgba(124,58,237,0.1); }
        .kpi-icon {
            width:50px; height:50px; border-radius:1rem;
            background:linear-gradient(135deg,var(--primary),var(--secondary));
            display:flex; align-items:center; justify-content:center;
            font-size:1.3rem; margin-bottom:1rem; color:#fff;
        }
        .kpi-icon.green  { background:linear-gradient(135deg,#10b981,#059669); }
        .kpi-icon.amber  { background:linear-gradient(135deg,#f59e0b,#d97706); }
        .kpi-icon.red    { background:linear-gradient(135deg,#ef4444,#dc2626); }
        .kpi-icon.cyan   { background:linear-gradient(135deg,#06b6d4,#0891b2); }
        .kpi-number { font-size:1.9rem; font-weight:800; line-height:1.1; word-break:break-word; }
        .kpi-label  { color:var(--muted); font-size:0.82rem; margin-top:4px; }
        .kpi-sub    { color:var(--muted); font-size:0.75rem; margin-top:4px; }

        /* Summary cards */
        .summary-grid {
            display:grid; grid-template-columns:repeat(auto-fit,minmax(155px,1fr));
            gap:18px; margin-bottom:28px;
        }
        .summary-card {
            background:rgba(255,255,255,0.03); border-radius:1.5rem;
            padding:1.2rem; border:1px solid var(--border);
            transition:0.2s; text-align:center;
        }
        .summary-card:hover { transform:translateY(-3px); background:rgba(124,58,237,0.1); }
        .summary-icon {
            width:46px; height:46px; border-radius:1rem;
            background:rgba(124,58,237,0.2); display:inline-flex;
            align-items:center; justify-content:center; font-size:1.3rem;
            margin-bottom:0.7rem; color:var(--primary);
        }
        .summary-number { font-size:1.7rem; font-weight:700; }
        .manage-btn {
            background:transparent; border:1px solid var(--primary); color:var(--primary);
            border-radius:2rem; padding:0.3rem 1rem; font-size:0.75rem;
            transition:0.2s; text-decoration:none; display:inline-block; margin-top:0.5rem;
        }
        .manage-btn:hover { background:var(--primary); color:#fff; }

        /* Grid 2 col */
        .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:25px; }
        @media(max-width:1000px){ .grid-2{ grid-template-columns:1fr; } }

        /* Panel */
        .panel {
            background:rgba(255,255,255,0.03); border-radius:1.8rem;
            padding:1.5rem; border:1px solid var(--border);
        }
        .panel-title { font-size:1rem; font-weight:700; margin-bottom:1rem; }

        /* Tables */
        .data-table { width:100%; border-collapse:separate; border-spacing:0 7px; }
        .data-table thead th { color:var(--muted); font-size:0.8rem; padding:6px 10px; }
        .data-table tbody td {
            background:rgba(255,255,255,0.04); padding:10px 12px;
        }
        .data-table tbody td:first-child { border-radius:1rem 0 0 1rem; }
        .data-table tbody td:last-child  { border-radius:0 1rem 1rem 0; }

        /* Badges */
        .badge-paid    { background:rgba(16,185,129,0.18); color:#10b981; padding:4px 11px; border-radius:50px; font-size:0.72rem; font-weight:600; }
        .badge-pending { background:rgba(245,158,11,0.18); color:#f59e0b; padding:4px 11px; border-radius:50px; font-size:0.72rem; font-weight:600; }
        .badge-failed  { background:rgba(239,68,68,0.18);  color:#ef4444; padding:4px 11px; border-radius:50px; font-size:0.72rem; font-weight:600; }
        .badge-active  { background:rgba(16,185,129,0.18); color:#10b981; padding:4px 11px; border-radius:50px; font-size:0.72rem; font-weight:600; }
        .status-badge  { background:rgba(16,185,129,0.18); color:#10b981; padding:4px 12px; border-radius:50px; font-size:0.75rem; font-weight:600; }

        /* Buttons */
        .btn-add {
            background:linear-gradient(135deg,var(--primary),var(--secondary));
            border:none; border-radius:2rem; padding:0.6rem 1.5rem;
            color:#fff; font-weight:600; cursor:pointer;
        }
        .btn-sm-outline {
            background:transparent; border:1px solid var(--primary); color:var(--primary);
            border-radius:2rem; padding:0.28rem 0.75rem; font-size:0.74rem;
            transition:0.2s; text-decoration:none; display:inline-block; cursor:pointer;
        }
        .btn-sm-outline:hover { background:var(--primary); color:#fff; }

        /* Modal */
        .modal-content { background:var(--panel); color:var(--text); border-radius:1.5rem; }

        /* Footer */
        .footer { text-align:center; margin-top:32px; padding:20px; color:var(--muted); font-size:0.8rem; }

        /* Taka highlight */
        .taka-green { color:#10b981; font-weight:700; }
        .taka-amber { color:#f59e0b; font-weight:700; }

        @media(max-width:768px){
            .main { margin-left:100px; }
            .summary-grid { grid-template-columns:repeat(2,1fr); }
            .kpi-grid { grid-template-columns:repeat(2,1fr); }
        }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="main" id="main">

    <!-- Topbar -->
    <div class="topbar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search courses...">
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon"></i></button>
            <div class="profile-img"><i class="fas fa-user-astronaut"></i></div>
            <div>
                <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong><br>
                <small style="color:var(--muted)">Super Admin</small>
            </div>
        </div>
    </div>

    <!-- ── HERO: Enrollment Revenue Intelligence ── -->
    <div class="hero-card">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <h1 style="font-size:2.3rem;font-weight:800;">
                    Enrollment<span style="color:var(--primary);"> Intelligence</span>
                </h1>
                <p style="color:var(--muted);margin-top:4px;">
                    <?php echo $enrollmentsExist ? 'Live enrollment data · Revenue in Bangladeshi Taka (৳)' : 'Course analytics · Revenue overview'; ?>
                </p>
            </div>
            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                <i class="fas fa-plus me-2"></i>Add Course
            </button>
        </div>

        <!-- KPI Boxes -->
        <div class="kpi-grid">
            <!-- Active Courses -->
            <div class="kpi-box">
                <div class="kpi-icon"><i class="fas fa-book-open"></i></div>
                <div class="kpi-number"><?php echo $totalCourses; ?></div>
                <div class="kpi-label">Active Courses</div>
            </div>

            <!-- Total Enrollments -->
            <div class="kpi-box">
                <div class="kpi-icon cyan"><i class="fas fa-user-graduate"></i></div>
                <div class="kpi-number"><?php echo number_format($totalEnrollments); ?></div>
                <div class="kpi-label"><?php echo $enrollmentsExist ? 'Total Enrollments' : 'Total Students'; ?></div>
            </div>

            <!-- Gross Revenue -->
            <div class="kpi-box">
                <div class="kpi-icon green"><i class="fas fa-bangladeshi-taka-sign"></i></div>
                <div class="kpi-number"><?php echo taka($totalRevenue); ?></div>
                <div class="kpi-label">Gross Revenue</div>
            </div>

            <!-- Paid Revenue -->
            <?php if ($enrollmentsExist): ?>
            <div class="kpi-box">
                <div class="kpi-icon green"><i class="fas fa-circle-check"></i></div>
                <div class="kpi-number"><?php echo taka($paidRevenue); ?></div>
                <div class="kpi-label">Paid Revenue</div>
            </div>

            <!-- Pending Revenue -->
            <div class="kpi-box">
                <div class="kpi-icon amber"><i class="fas fa-clock"></i></div>
                <div class="kpi-number"><?php echo taka($pendingRevenue); ?></div>
                <div class="kpi-label">Pending Revenue</div>
            </div>
            <?php endif; ?>

            <!-- Avg. Course Fee -->
            <div class="kpi-box">
                <div class="kpi-icon"><i class="fas fa-tag"></i></div>
                <div class="kpi-number"><?php echo taka($avgFee); ?></div>
                <div class="kpi-label">Avg. Course Fee</div>
            </div>

            <!-- Most Enrolled -->
            <div class="kpi-box">
                <div class="kpi-icon amber"><i class="fas fa-trophy"></i></div>
                <div class="kpi-number" style="font-size:1rem;font-weight:700;line-height:1.4;">
                    <?php echo $mostEnrolled ? htmlspecialchars($mostEnrolled['title']) : 'N/A'; ?>
                </div>
                <div class="kpi-label">Most Enrolled</div>
                <?php if ($mostEnrolled): ?>
                <div class="kpi-sub">
                    <?php echo $enrollmentsExist ? $mostEnrolled['enrollments'].' enrollments' : $mostEnrolled['enrolled_students'].' students'; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Highest Revenue Course -->
            <div class="kpi-box">
                <div class="kpi-icon green"><i class="fas fa-chart-line"></i></div>
                <div class="kpi-number" style="font-size:1rem;font-weight:700;line-height:1.4;">
                    <?php echo $highestRevenue ? htmlspecialchars($highestRevenue['title']) : 'N/A'; ?>
                </div>
                <div class="kpi-label">Highest Revenue Course</div>
                <?php if ($highestRevenue): ?>
                <div class="kpi-sub"><?php echo taka($highestRevenue['revenue']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Content Summary Cards ── -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-icon"><i class="fas fa-images"></i></div>
            <div class="summary-number"><?php echo $totalSliders; ?></div>
            <div>Sliders</div>
            <a href="manage_sliders.php" class="manage-btn">Manage</a>
        </div>
        <div class="summary-card">
            <div class="summary-icon"><i class="fas fa-cogs"></i></div>
            <div class="summary-number"><?php echo $totalServices; ?></div>
            <div>Services</div>
            <a href="manage_services.php" class="manage-btn">Manage</a>
        </div>
        <div class="summary-card">
            <div class="summary-icon"><i class="fas fa-briefcase"></i></div>
            <div class="summary-number"><?php echo $totalPortfolios; ?></div>
            <div>Portfolios</div>
            <a href="manage_portfolios.php" class="manage-btn">Manage</a>
        </div>
        <div class="summary-card">
            <div class="summary-icon"><i class="fas fa-star"></i></div>
            <div class="summary-number"><?php echo $totalTestimonials; ?></div>
            <div>Testimonials</div>
            <a href="manage_testimonials.php" class="manage-btn">Manage</a>
        </div>
        <div class="summary-card">
            <div class="summary-icon"><i class="fas fa-users"></i></div>
            <div class="summary-number"><?php echo $totalCustomers; ?></div>
            <div>Customers</div>
            <a href="manage_customers.php" class="manage-btn">Manage</a>
        </div>
        <div class="summary-card">
            <div class="summary-icon"><i class="fas fa-user-graduate"></i></div>
            <div class="summary-number"><?php echo $totalEnrollments; ?></div>
            <div>Enrollments</div>
            <a href="manage_enrollments.php" class="manage-btn">Manage</a>
        </div>
    </div>

    <!-- ── Charts ── -->
    <div class="grid-2">
        <div class="panel">
            <div class="panel-title"><i class="fas fa-chart-bar me-2"></i>Top Courses by Enrollments</div>
            <canvas id="courseChart" height="200"></canvas>
        </div>
        <div class="panel">
            <div class="panel-title"><i class="fas fa-chart-line me-2"></i>Monthly Revenue Trend (৳)</div>
            <canvas id="revenueChart" height="200"></canvas>
        </div>
    </div>

    <!-- ── Recent Enrollments Table ── -->
    <?php if ($enrollmentsExist && count($recentEnrollments) > 0): ?>
    <div class="panel mb-4">
        <div class="panel-title"><i class="fas fa-list-check me-2"></i>Recent Enrollments</div>
        <div class="table-responsive">
            <table class="data-table w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Amount (৳)</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recentEnrollments as $i => $e): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($e['student_name']); ?></strong><br>
                            <small style="color:var(--muted)"><?php echo htmlspecialchars($e['student_email']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($e['course_title'] ?? '—'); ?></td>
                        <td>
                            <span class="<?php echo $e['payment_status'] === 'paid' ? 'taka-green' : 'taka-amber'; ?>">
                                ৳<?php echo number_format($e['amount'], 0); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                                $st = strtolower($e['payment_status'] ?? '');
                                $cls = $st === 'paid' ? 'badge-paid' : ($st === 'failed' ? 'badge-failed' : 'badge-pending');
                                echo "<span class=\"$cls\">".ucfirst($st)."</span>";
                            ?>
                        </td>
                        <td><?php echo date('d M Y', strtotime($e['enrollment_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">
            <a href="manage_enrollments.php" class="btn-sm-outline">View All Enrollments →</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── All Courses Table ── -->
    <div class="panel mb-4">
        <div class="panel-title"><i class="fas fa-graduation-cap me-2"></i>All Active Courses</div>
        <div class="table-responsive">
            <table class="data-table w-100" id="coursesTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Fee (৳)</th>
                        <th>Enrolled</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($allCourses as $c): ?>
                    <tr data-id="<?php echo $c['id']; ?>">
                        <td><?php echo htmlspecialchars($c['title']); ?></td>
                        <td class="taka-green price-cell">৳<?php echo number_format($c['price'], 0); ?></td>
                        <td><?php echo number_format($c['enrolled_students']); ?></td>
                        <td><span class="status-badge"><?php echo $c['status'] ? 'Active' : 'Draft'; ?></span></td>
                        <td>
                            <button class="edit-fee-btn btn-sm-outline"
                                    data-id="<?php echo $c['id']; ?>"
                                    data-price="<?php echo $c['price']; ?>">
                                <i class="fas fa-edit"></i> Edit Fee
                            </button>
                            <a href="manage_courses.php?edit=<?php echo $c['id']; ?>" class="btn-sm-outline ms-1">Full Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Recent Sliders & Services ── -->
    <div class="grid-2">
        <div class="panel">
            <div class="panel-title"><i class="fas fa-images me-2"></i>Recent Sliders</div>
            <table class="data-table w-100">
                <thead><tr><th>Title</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    <?php foreach($recentSliders as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['title']); ?></td>
                        <td><span class="status-badge"><?php echo $s['status'] ? 'Active' : 'Inactive'; ?></span></td>
                        <td><a href="manage_sliders.php?edit=<?php echo $s['id']; ?>" class="btn-sm-outline">Edit</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="panel">
            <div class="panel-title"><i class="fas fa-cogs me-2"></i>Recent Services</div>
            <table class="data-table w-100">
                <thead><tr><th>Title</th><th></th></tr></thead>
                <tbody>
                    <?php foreach($recentServices as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['title']); ?></td>
                        <td><a href="manage_services.php?edit=<?php echo $s['id']; ?>" class="btn-sm-outline">Edit</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Recent Portfolios ── -->
    <div class="panel mt-3">
        <div class="panel-title"><i class="fas fa-briefcase me-2"></i>Recent Portfolios</div>
        <table class="data-table w-100">
            <thead><tr><th>Title</th><th>Client</th><th></th></tr></thead>
            <tbody>
                <?php foreach($recentPortfolios as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['title']); ?></td>
                    <td><?php echo htmlspecialchars($p['client']); ?></td>
                    <td><a href="manage_portfolios.php?edit=<?php echo $p['id']; ?>" class="btn-sm-outline">Edit</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">© 2025 AR Tech Solutions | Admin Dashboard · All amounts in Bangladeshi Taka (৳)</div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Course</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" id="courseTitle" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fee (৳)</label>
                    <input type="number" step="1" id="coursePrice" class="form-control" required placeholder="e.g. 5000">
                </div>
                <div class="mb-3">
                    <label class="form-label">Initial Enrolled Count</label>
                    <input type="number" id="courseEnrolled" class="form-control" value="0">
                </div>
                <button id="addCourseBtn" class="btn btn-primary w-100">Create Course</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Fee Modal -->
<div class="modal fade" id="editFeeModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Course Fee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editCourseId">
                <label class="form-label">New Fee (৳)</label>
                <input type="number" step="1" id="editCoursePrice" class="form-control" placeholder="e.g. 6000">
                <button id="updateFeeBtn" class="btn btn-primary mt-3 w-100">Update Fee</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    if (localStorage.getItem('nexoraTheme') === 'light') document.body.classList.add('light');
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('light');
        localStorage.setItem('nexoraTheme', document.body.classList.contains('light') ? 'light' : 'dark');
        location.reload();
    });
    themeToggle.innerHTML = document.body.classList.contains('light') ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';

    const mutedColor = getComputedStyle(document.documentElement).getPropertyValue('--muted') || '#94a3b8';
    const gridColor  = 'rgba(255,255,255,0.05)';

    <?php if ($coursesExist && count($courseNames) > 0): ?>
    // Bar Chart: enrollments per course
    new Chart(document.getElementById('courseChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($courseNames); ?>,
            datasets: [{
                label: 'Enrollments',
                data: <?php echo json_encode($courseEnrollments); ?>,
                backgroundColor: '#7c3aed',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: mutedColor } } },
            scales: {
                y: { ticks: { color: mutedColor }, grid: { color: gridColor } },
                x: { ticks: { color: mutedColor, maxRotation: 30 } }
            }
        }
    });

    // Line Chart: monthly revenue in Taka
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Revenue (৳)',
                data: <?php echo json_encode($revenueByMonth); ?>,
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6,182,212,0.1)',
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#06b6d4',
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: mutedColor } } },
            scales: {
                x: { ticks: { color: mutedColor }, grid: { color: gridColor } },
                y: {
                    ticks: {
                        color: mutedColor,
                        callback: function(v){ return '৳' + v.toLocaleString(); }
                    },
                    grid: { color: gridColor }
                }
            }
        }
    });
    <?php endif; ?>

    // Add Course
    document.getElementById('addCourseBtn').addEventListener('click', async () => {
        const fd = new FormData();
        fd.append('action', 'add_course');
        fd.append('title', document.getElementById('courseTitle').value);
        fd.append('price', document.getElementById('coursePrice').value);
        fd.append('enrolled_students', document.getElementById('courseEnrolled').value);
        const res  = await fetch(window.location.href, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Error: ' + (data.message || 'Unknown'));
    });

    // Edit Fee
    const editModal = new bootstrap.Modal(document.getElementById('editFeeModal'));
    document.querySelectorAll('.edit-fee-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editCourseId').value    = btn.dataset.id;
            document.getElementById('editCoursePrice').value = btn.dataset.price;
            editModal.show();
        });
    });
    document.getElementById('updateFeeBtn').addEventListener('click', async () => {
        const fd = new FormData();
        fd.append('action', 'update_fee');
        fd.append('id',    document.getElementById('editCourseId').value);
        fd.append('price', document.getElementById('editCoursePrice').value);
        const res  = await fetch(window.location.href, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Update failed');
    });

    // Search courses table
    document.getElementById('searchInput').addEventListener('keyup', function () {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('#coursesTable tbody tr').forEach(row => {
            row.style.display = row.cells[0].innerText.toLowerCase().includes(filter) ? '' : 'none';
        });
    });
</script>
</body>
</html>