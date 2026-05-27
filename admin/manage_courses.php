<?php
// admin/manage_courses.php - Complete course management with working add/update
require_once 'auth.php';
require_once '../config.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    function safe($value) {
        return $value === null ? '' : trim($value);
    }

    function getValidCurriculum($input) {
        $curriculum = safe($input);
        if ($curriculum === '') {
            return '{"modules":[]}';
        }
        json_decode($curriculum);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return '{"modules":[]}';
        }
        return $curriculum;
    }

    // ADD COURSE
    if ($action === 'add_course') {
        $title = safe($_POST['title'] ?? '');
        if (!$title) {
            echo json_encode(['success' => false, 'message' => 'Title is required']);
            exit;
        }
        $curriculum = getValidCurriculum($_POST['curriculum'] ?? '');
        try {
            $stmt = $pdo->prepare("INSERT INTO courses (title, description, short_description, icon_class, link_url, image_url, duration, level, price, price_offline, enrolled_students, total_classes, total_projects, instructor_name, instructor_bio, instructor_image, curriculum, career_outcomes, prerequisites, software_learned, is_popular, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([
                $title,
                safe($_POST['description'] ?? ''),
                safe($_POST['short_description'] ?? ''),
                safe($_POST['icon_class'] ?? 'fas fa-cube'),
                safe($_POST['link_url'] ?? '#'),
                safe($_POST['image_url'] ?? ''),
                safe($_POST['duration'] ?? ''),
                safe($_POST['level'] ?? 'Beginner'),
                floatval($_POST['price'] ?? 0),
                floatval($_POST['price_offline'] ?? 0),
                intval($_POST['enrolled_students'] ?? 0),
                intval($_POST['total_classes'] ?? 0),
                intval($_POST['total_projects'] ?? 0),
                safe($_POST['instructor_name'] ?? ''),
                safe($_POST['instructor_bio'] ?? ''),
                safe($_POST['instructor_image'] ?? ''),
                $curriculum,
                safe($_POST['career_outcomes'] ?? ''),
                safe($_POST['prerequisites'] ?? ''),
                safe($_POST['software_learned'] ?? ''),
                isset($_POST['is_popular']) ? 1 : 0
            ]);
            echo json_encode(['success' => true, 'message' => 'Course added']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
        }
        exit;
    }

    // UPDATE COURSE
    if ($action === 'edit_course') {
        $id = intval($_POST['id']);
        $title = safe($_POST['title'] ?? '');
        if (!$id || !$title) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }
        $curriculum = getValidCurriculum($_POST['curriculum'] ?? '');
        try {
            $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ?, short_description = ?, icon_class = ?, link_url = ?, image_url = ?, duration = ?, level = ?, price = ?, price_offline = ?, enrolled_students = ?, total_classes = ?, total_projects = ?, instructor_name = ?, instructor_bio = ?, instructor_image = ?, curriculum = ?, career_outcomes = ?, prerequisites = ?, software_learned = ?, is_popular = ? WHERE id = ?");
            $stmt->execute([
                $title,
                safe($_POST['description'] ?? ''),
                safe($_POST['short_description'] ?? ''),
                safe($_POST['icon_class'] ?? 'fas fa-cube'),
                safe($_POST['link_url'] ?? '#'),
                safe($_POST['image_url'] ?? ''),
                safe($_POST['duration'] ?? ''),
                safe($_POST['level'] ?? 'Beginner'),
                floatval($_POST['price'] ?? 0),
                floatval($_POST['price_offline'] ?? 0),
                intval($_POST['enrolled_students'] ?? 0),
                intval($_POST['total_classes'] ?? 0),
                intval($_POST['total_projects'] ?? 0),
                safe($_POST['instructor_name'] ?? ''),
                safe($_POST['instructor_bio'] ?? ''),
                safe($_POST['instructor_image'] ?? ''),
                $curriculum,
                safe($_POST['career_outcomes'] ?? ''),
                safe($_POST['prerequisites'] ?? ''),
                safe($_POST['software_learned'] ?? ''),
                isset($_POST['is_popular']) ? 1 : 0,
                $id
            ]);
            echo json_encode(['success' => true, 'message' => 'Course updated']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
        }
        exit;
    }

    // UPLOAD IMAGES
    if ($action === 'upload_image') {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/courses/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                echo json_encode(['success' => true, 'image_url' => 'uploads/courses/' . $fileName]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to move file']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        }
        exit;
    }

    if ($action === 'upload_instructor_image') {
        if (isset($_FILES['instructor_image']) && $_FILES['instructor_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/instructors/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['instructor_image']['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['instructor_image']['tmp_name'], $targetPath)) {
                echo json_encode(['success' => true, 'image_url' => 'uploads/instructors/' . $fileName]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to move file']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        }
        exit;
    }

    // TOGGLE STATUS
    if ($action === 'toggle_status') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("UPDATE courses SET status = NOT status WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    // DELETE COURSE
    if ($action === 'delete_course') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("SELECT image_url, instructor_image FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $course = $stmt->fetch();
        if ($course) {
            if (!empty($course['image_url']) && file_exists('../' . $course['image_url'])) unlink('../' . $course['image_url']);
            if (!empty($course['instructor_image']) && file_exists('../' . $course['instructor_image'])) unlink('../' . $course['instructor_image']);
        }
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }
}

// Fetch all courses
$courses = $pdo->query("SELECT * FROM courses ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Manage Courses | NEXORA AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
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
            top: auto;
            bottom: -200px;
            right: -200px;
            left: auto;
            animation: floatBg2 18s infinite alternate;
        }
        @keyframes floatBg { 0% { transform: translate(0,0); } 100% { transform: translate(100px, 80px); } }
        @keyframes floatBg2 { 0% { transform: translate(0,0); } 100% { transform: translate(-80px, -60px); } }
        .sidebar {
            position: fixed; left: 20px; top: 20px; bottom: 20px; width: 280px;
            background: rgba(15,23,42,0.9); backdrop-filter: blur(20px);
            border-radius: 2rem; border: 1px solid var(--border); transition: 0.3s; z-index: 1050; box-shadow: var(--shadow);
        }
        body.light .sidebar { background: rgba(255,255,255,0.9); }
        .sidebar.collapsed { width: 90px; }
        .logo-area { padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); }
        .logo { font-size: 1.8rem; font-weight: 800; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .toggle-btn { background: rgba(255,255,255,0.1); border: none; border-radius: 1rem; width: 40px; height: 40px; color: white; cursor: pointer; transition: 0.2s; }
        body.light .toggle-btn { background: rgba(0,0,0,0.05); color: #0f172a; }
        .toggle-btn:hover { background: var(--primary); color: white; }
        .menu { padding: 1rem; }
        .menu-title { color: var(--muted); font-size: 0.7rem; letter-spacing: 2px; margin: 1rem 1rem 0.5rem; }
        .menu a {
            display: flex; align-items: center; gap: 14px; padding: 0.8rem 1rem;
            border-radius: 1.2rem; color: var(--muted); text-decoration: none; margin-bottom: 0.5rem; transition: 0.2s;
        }
        .menu a i { width: 24px; font-size: 1.2rem; }
        .menu a:hover, .menu a.active { background: rgba(124,58,237,0.2); color: var(--primary-glow); transform: translateX(5px); }
        .sidebar.collapsed .logo, .sidebar.collapsed .menu span, .sidebar.collapsed .menu-title { display: none; }
        .sidebar.collapsed .menu a { justify-content: center; }
        .main { margin-left: 310px; padding: 20px; transition: 0.3s; }
        .main.expand { margin-left: 120px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .search-box { position: relative; width: 320px; }
        .search-box input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 2rem; padding: 12px 20px 12px 45px; color: var(--text); }
        .search-box input::placeholder { color: var(--muted); opacity: 0.7; }
        .search-box i { position: absolute; left: 18px; top: 15px; color: var(--muted); }
        .theme-toggle { background: rgba(255,255,255,0.1); border: none; border-radius: 2rem; width: 45px; height: 45px; cursor: pointer; color: var(--text); }
        .profile-img { width: 48px; height: 48px; border-radius: 1.2rem; background: var(--primary); display: flex; align-items: center; justify-content: center; }
        .panel { background: rgba(255,255,255,0.03); border-radius: 1.8rem; padding: 1.5rem; border: 1px solid var(--border); }
        .course-table { width: 100%; border-collapse: collapse; }
        .course-table th, .course-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .course-table th { color: var(--muted); font-weight: 600; font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase; }
        .course-table td { color: var(--text); }
        .course-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 0.8rem; background: rgba(255,255,255,0.1); }
        .status-badge { background: rgba(16,185,129,0.2); color: #10b981; padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; display: inline-block; }
        .popular-badge { background: rgba(245,158,11,0.2); color: #f59e0b; padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; display: inline-block; }
        .btn-sm-custom { background: transparent; border: 1px solid var(--primary); color: var(--primary); border-radius: 2rem; padding: 0.3rem 0.8rem; font-size: 0.75rem; transition: 0.2s; text-decoration: none; display: inline-block; margin: 2px; cursor: pointer; }
        .btn-sm-custom:hover { background: var(--primary); color: white; }
        .btn-add { background: linear-gradient(135deg, var(--primary), var(--secondary)); border: none; border-radius: 2rem; padding: 0.6rem 1.5rem; color: white; font-weight: 600; cursor: pointer; }
        .modal-content { background: var(--panel); color: var(--text); border-radius: 1.5rem; }
        .form-control, .form-select { background: rgba(255,255,255,0.1); border: 1px solid var(--border); color: var(--text); border-radius: 1rem; }
        .form-control:focus { background: rgba(255,255,255,0.15); color: var(--text); border-color: var(--primary); box-shadow: none; }
        .image-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 1rem; margin-top: 0.5rem; border: 1px solid var(--border); background: rgba(0,0,0,0.2); }
        @media (max-width: 768px) {
            .sidebar { width: 80px; left: 10px; }
            .main { margin-left: 100px; }
            .course-table th, .course-table td { padding: 8px; font-size: 0.75rem; }
            .btn-sm-custom { font-size: 0.65rem; padding: 0.2rem 0.5rem; }
        }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: var(--muted); }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="logo-area">
        <div class="logo"><i class="fas fa-brain me-2"></i>NEXORA</div>
        <button class="toggle-btn" id="toggleBtn"><i class="fas fa-bars"></i></button>
    </div>
    <div class="menu">
        <div class="menu-title">MAIN</div>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
        <a href="manage_courses.php" class="active"><i class="fas fa-graduation-cap"></i><span>Courses</span></a>
        <a href="manage_services.php"><i class="fas fa-cogs"></i><span>Services</span></a>
        <a href="manage_portfolio.php"><i class="fas fa-briefcase"></i><span>Portfolio</span></a>
        <a href="manage_testimonials.php"><i class="fas fa-star"></i><span>Testimonials</span></a>
        <a href="manage_sliders.php"><i class="fas fa-images"></i><span>Sliders</span></a>
        <a href="manage_customers.php"><i class="fas fa-users"></i><span>Customers</span></a>
        <div class="menu-title">SYSTEM</div>
        <a href="profile.php"><i class="fas fa-user-cog"></i><span>Profile</span></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </div>
</div>

<div class="main" id="main">
    <div class="topbar">
        <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search courses..."></div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon"></i></button>
            <div class="profile-img"><i class="fas fa-user-astronaut"></i></div>
            <div><strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong><br><small style="color:var(--muted)">Admin</small></div>
        </div>
    </div>

    <div class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
            <h3><i class="fas fa-graduation-cap me-2"></i> Course Management</h3>
            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addCourseModal"><i class="fas fa-plus me-2"></i>Add Course</button>
        </div>
        <div class="table-responsive">
            <table class="course-table w-100" id="coursesTable">
                <thead>
                    <tr><th>Image</th><th>ID</th><th>Title</th><th>Level</th><th>Duration</th><th>Price</th><th>Enrolled</th><th>Popular</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($courses as $c): ?>
                    <tr data-id="<?php echo $c['id']; ?>">
                        <td><?php if($c['image_url']): ?><img src="../<?php echo htmlspecialchars($c['image_url']); ?>" class="course-thumb" alt="course"><?php else: ?><i class="fas fa-image fa-2x" style="color:var(--muted);"></i><?php endif; ?></td>
                        <td><?php echo $c['id']; ?></td>
                        <td><?php echo htmlspecialchars($c['title']); ?><br><small class="text-muted" style="color:var(--muted);"><?php echo htmlspecialchars(substr($c['description'], 0, 40)); ?>...</small></td>
                        <td><?php echo htmlspecialchars($c['level']); ?></td>
                        <td><?php echo htmlspecialchars($c['duration']); ?></td>
                        <td class="price-cell">$<?php echo number_format($c['price'], 2); ?></td>
                        <td><?php echo $c['enrolled_students']; ?></td>
                        <td><?php if($c['is_popular']): ?><span class="popular-badge">🔥 Popular</span><?php else: ?>—<?php endif; ?></td>
                        <td><span class="status-badge"><?php echo $c['status'] ? 'Active' : 'Draft'; ?></span></td>
                        <td>
                            <button class="edit-btn btn-sm-custom" data-id="<?php echo $c['id']; ?>" 
                                data-title="<?php echo htmlspecialchars($c['title']); ?>"
                                data-description="<?php echo htmlspecialchars($c['description']); ?>"
                                data-short_description="<?php echo htmlspecialchars($c['short_description']); ?>"
                                data-icon="<?php echo htmlspecialchars($c['icon_class']); ?>"
                                data-link="<?php echo htmlspecialchars($c['link_url']); ?>"
                                data-image="<?php echo htmlspecialchars($c['image_url']); ?>"
                                data-duration="<?php echo htmlspecialchars($c['duration']); ?>"
                                data-level="<?php echo htmlspecialchars($c['level']); ?>"
                                data-price="<?php echo $c['price']; ?>"
                                data-price_offline="<?php echo $c['price_offline']; ?>"
                                data-enrolled="<?php echo $c['enrolled_students']; ?>"
                                data-total_classes="<?php echo $c['total_classes']; ?>"
                                data-total_projects="<?php echo $c['total_projects']; ?>"
                                data-instructor_name="<?php echo htmlspecialchars($c['instructor_name']); ?>"
                                data-instructor_bio="<?php echo htmlspecialchars($c['instructor_bio']); ?>"
                                data-instructor_image="<?php echo htmlspecialchars($c['instructor_image']); ?>"
                                data-curriculum="<?php echo htmlspecialchars($c['curriculum']); ?>"
                                data-career_outcomes="<?php echo htmlspecialchars($c['career_outcomes']); ?>"
                                data-prerequisites="<?php echo htmlspecialchars($c['prerequisites']); ?>"
                                data-software_learned="<?php echo htmlspecialchars($c['software_learned']); ?>"
                                data-is_popular="<?php echo $c['is_popular']; ?>"><i class="fas fa-edit"></i> Edit</button>
                            <button class="toggle-status btn-sm-custom" data-id="<?php echo $c['id']; ?>"><i class="fas fa-sync-alt"></i> Toggle</button>
                            <button class="delete-btn btn-sm-custom" data-id="<?php echo $c['id']; ?>" style="border-color:var(--danger); color:var(--danger);"><i class="fas fa-trash"></i> Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="footer">© 2025 NEXORA AI | Complete Course Management System</div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add New Course</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="addCourseForm">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Course Title *</label><input type="text" id="addTitle" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Icon Class</label><input type="text" id="addIcon" class="form-control" value="fas fa-cube"></div>
                        <div class="col-md-6 mb-3"><label>Link URL</label><input type="text" id="addLink" class="form-control" value="#"></div>
                        <div class="col-md-6 mb-3"><label>Duration</label><input type="text" id="addDuration" class="form-control" placeholder="6 Weeks"></div>
                        <div class="col-md-6 mb-3"><label>Level</label><select id="addLevel" class="form-select"><option>Beginner</option><option>Intermediate</option><option>Advanced</option><option>All Levels</option></select></div>
                        <div class="col-md-6 mb-3"><label>Total Classes</label><input type="number" id="addTotalClasses" class="form-control" value="0"></div>
                        <div class="col-md-6 mb-3"><label>Total Projects</label><input type="number" id="addTotalProjects" class="form-control" value="0"></div>
                        <div class="col-md-6 mb-3"><label>Enrolled Students</label><input type="number" id="addEnrolled" class="form-control" value="0"></div>
                        <div class="col-md-6 mb-3"><label>Price ($) Online</label><input type="number" step="0.01" id="addPrice" class="form-control" value="0"></div>
                        <div class="col-md-6 mb-3"><label>Price ($) Offline</label><input type="number" step="0.01" id="addPriceOffline" class="form-control" value="0"></div>
                        <div class="col-md-12 mb-3"><label>Description</label><textarea id="addDescription" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-12 mb-3"><label>Short Description</label><textarea id="addShortDesc" rows="1" class="form-control"></textarea></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Course Image</label><input type="file" id="addImage" class="form-control" accept="image/*"><img id="addImagePreview" class="image-preview" style="display:none;"><input type="hidden" id="addImageUrl"></div>
                        <div class="col-md-6 mb-3"><label>Instructor Image</label><input type="file" id="addInstructorImage" class="form-control" accept="image/*"><img id="addInstructorImagePreview" class="image-preview" style="display:none;"><input type="hidden" id="addInstructorImageUrl"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Instructor Name</label><input type="text" id="addInstructorName" class="form-control"></div>
                        <div class="col-md-12 mb-3"><label>Instructor Bio</label><textarea id="addInstructorBio" rows="2" class="form-control"></textarea></div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3"><label>Curriculum (JSON)</label><textarea id="addCurriculum" rows="4" class="form-control" placeholder='{"modules":[{"title":"Module 1","classes":[{"class_number":1,"topic":"Intro","type":"Video","resource":"Link"}]}]}'></textarea></div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3"><label>Career Outcomes</label><textarea id="addCareerOutcomes" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-12 mb-3"><label>Prerequisites</label><textarea id="addPrerequisites" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-12 mb-3"><label>Software Learned</label><textarea id="addSoftwareLearned" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-12 mb-3"><div class="form-check"><input type="checkbox" id="addIsPopular" class="form-check-input"><label class="form-check-label">Mark as Popular</label></div></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-2">Create Course</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Course</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="editCourseForm">
                    <input type="hidden" id="editId">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Course Title *</label><input type="text" id="editTitle" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Icon Class</label><input type="text" id="editIcon" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Link URL</label><input type="text" id="editLink" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Duration</label><input type="text" id="editDuration" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Level</label><select id="editLevel" class="form-select"><option>Beginner</option><option>Intermediate</option><option>Advanced</option><option>All Levels</option></select></div>
                        <div class="col-md-6 mb-3"><label>Total Classes</label><input type="number" id="editTotalClasses" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Total Projects</label><input type="number" id="editTotalProjects" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Enrolled Students</label><input type="number" id="editEnrolled" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Price ($) Online</label><input type="number" step="0.01" id="editPrice" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Price ($) Offline</label><input type="number" step="0.01" id="editPriceOffline" class="form-control"></div>
                        <div class="col-md-12 mb-3"><label>Description</label><textarea id="editDescription" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-12 mb-3"><label>Short Description</label><textarea id="editShortDesc" rows="1" class="form-control"></textarea></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Course Image</label><input type="file" id="editImage" class="form-control" accept="image/*"><img id="editImagePreview" class="image-preview" style="display:none;"><input type="hidden" id="editImageUrl"></div>
                        <div class="col-md-6 mb-3"><label>Instructor Image</label><input type="file" id="editInstructorImage" class="form-control" accept="image/*"><img id="editInstructorImagePreview" class="image-preview" style="display:none;"><input type="hidden" id="editInstructorImageUrl"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Instructor Name</label><input type="text" id="editInstructorName" class="form-control"></div>
                        <div class="col-md-12 mb-3"><label>Instructor Bio</label><textarea id="editInstructorBio" rows="2" class="form-control"></textarea></div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3"><label>Curriculum (JSON)</label><textarea id="editCurriculum" rows="4" class="form-control"></textarea></div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3"><label>Career Outcomes</label><textarea id="editCareerOutcomes" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-12 mb-3"><label>Prerequisites</label><textarea id="editPrerequisites" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-12 mb-3"><label>Software Learned</label><textarea id="editSoftwareLearned" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-12 mb-3"><div class="form-check"><input type="checkbox" id="editIsPopular" class="form-check-input"><label class="form-check-label">Mark as Popular</label></div></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-2">Update Course</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('main');
    const toggleBtn = document.getElementById('toggleBtn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            main.classList.toggle('expand');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        main.classList.add('expand');
    }

    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    if (localStorage.getItem('nexoraTheme') === 'light') document.body.classList.add('light');
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('light');
        const isLight = document.body.classList.contains('light');
        localStorage.setItem('nexoraTheme', isLight ? 'light' : 'dark');
        themeToggle.innerHTML = isLight ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
    });
    themeToggle.innerHTML = document.body.classList.contains('light') ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';

    // Search filter
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('#coursesTable tbody tr').forEach(row => {
            const title = row.cells[2]?.innerText.toLowerCase() || '';
            row.style.display = title.includes(filter) ? '' : 'none';
        });
    });

    // Image preview helper
    function setupImagePreview(fileInput, previewImg, hiddenUrlInput, uploadAction) {
        fileInput.addEventListener('change', async function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => { previewImg.src = e.target.result; previewImg.style.display = 'block'; };
                reader.readAsDataURL(this.files[0]);
                const formData = new FormData();
                formData.append('action', uploadAction);
                formData.append(uploadAction === 'upload_image' ? 'image' : 'instructor_image', this.files[0]);
                try {
                    const res = await fetch('manage_courses.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                    const data = await res.json();
                    if (data.success) hiddenUrlInput.value = data.image_url;
                    else alert('Upload failed: ' + data.message);
                } catch (err) { alert('Upload error'); }
            }
        });
    }

    // Setup for add modal
    const addImage = document.getElementById('addImage');
    const addInstructorImage = document.getElementById('addInstructorImage');
    if (addImage) setupImagePreview(addImage, document.getElementById('addImagePreview'), document.getElementById('addImageUrl'), 'upload_image');
    if (addInstructorImage) setupImagePreview(addInstructorImage, document.getElementById('addInstructorImagePreview'), document.getElementById('addInstructorImageUrl'), 'upload_instructor_image');

    // Setup for edit modal
    const editImage = document.getElementById('editImage');
    const editInstructorImage = document.getElementById('editInstructorImage');
    if (editImage) setupImagePreview(editImage, document.getElementById('editImagePreview'), document.getElementById('editImageUrl'), 'upload_image');
    if (editInstructorImage) setupImagePreview(editInstructorImage, document.getElementById('editInstructorImagePreview'), document.getElementById('editInstructorImageUrl'), 'upload_instructor_image');

    // Add course submission
    document.getElementById('addCourseForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'add_course');
        formData.append('title', document.getElementById('addTitle').value);
        formData.append('description', document.getElementById('addDescription').value);
        formData.append('short_description', document.getElementById('addShortDesc').value);
        formData.append('icon_class', document.getElementById('addIcon').value);
        formData.append('link_url', document.getElementById('addLink').value);
        formData.append('duration', document.getElementById('addDuration').value);
        formData.append('level', document.getElementById('addLevel').value);
        formData.append('price', document.getElementById('addPrice').value);
        formData.append('price_offline', document.getElementById('addPriceOffline').value);
        formData.append('enrolled_students', document.getElementById('addEnrolled').value);
        formData.append('total_classes', document.getElementById('addTotalClasses').value);
        formData.append('total_projects', document.getElementById('addTotalProjects').value);
        formData.append('instructor_name', document.getElementById('addInstructorName').value);
        formData.append('instructor_bio', document.getElementById('addInstructorBio').value);
        formData.append('instructor_image', document.getElementById('addInstructorImageUrl').value);
        let curriculum = document.getElementById('addCurriculum').value;
        if (!curriculum.trim()) curriculum = '{"modules":[]}';
        formData.append('curriculum', curriculum);
        formData.append('career_outcomes', document.getElementById('addCareerOutcomes').value);
        formData.append('prerequisites', document.getElementById('addPrerequisites').value);
        formData.append('software_learned', document.getElementById('addSoftwareLearned').value);
        formData.append('is_popular', document.getElementById('addIsPopular').checked ? 1 : 0);
        formData.append('image_url', document.getElementById('addImageUrl').value);
        const res = await fetch('manage_courses.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
        const data = await res.json();
        if (data.success) { alert('Course created!'); location.reload(); }
        else alert('Error: ' + data.message);
    });

    // Populate edit modal
    const editModal = new bootstrap.Modal(document.getElementById('editCourseModal'));
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const data = btn.dataset;
            document.getElementById('editId').value = data.id;
            document.getElementById('editTitle').value = data.title || '';
            document.getElementById('editDescription').value = data.description || '';
            document.getElementById('editShortDesc').value = data.short_description || '';
            document.getElementById('editIcon').value = data.icon || 'fas fa-cube';
            document.getElementById('editLink').value = data.link || '#';
            document.getElementById('editDuration').value = data.duration || '';
            document.getElementById('editLevel').value = data.level || 'Beginner';
            document.getElementById('editPrice').value = data.price || 0;
            document.getElementById('editPriceOffline').value = data.price_offline || 0;
            document.getElementById('editEnrolled').value = data.enrolled || 0;
            document.getElementById('editTotalClasses').value = data.total_classes || 0;
            document.getElementById('editTotalProjects').value = data.total_projects || 0;
            document.getElementById('editInstructorName').value = data.instructor_name || '';
            document.getElementById('editInstructorBio').value = data.instructor_bio || '';
            let curriculum = data.curriculum;
            if (!curriculum || curriculum === 'null' || curriculum === '') curriculum = '{"modules":[]}';
            document.getElementById('editCurriculum').value = curriculum;
            document.getElementById('editCareerOutcomes').value = data.career_outcomes || '';
            document.getElementById('editPrerequisites').value = data.prerequisites || '';
            document.getElementById('editSoftwareLearned').value = data.software_learned || '';
            document.getElementById('editIsPopular').checked = (data.is_popular == 1);
            if (data.image && data.image !== 'null') {
                document.getElementById('editImagePreview').src = '../' + data.image;
                document.getElementById('editImagePreview').style.display = 'block';
                document.getElementById('editImageUrl').value = data.image;
            } else {
                document.getElementById('editImagePreview').style.display = 'none';
                document.getElementById('editImageUrl').value = '';
            }
            if (data.instructor_image && data.instructor_image !== 'null') {
                document.getElementById('editInstructorImagePreview').src = '../' + data.instructor_image;
                document.getElementById('editInstructorImagePreview').style.display = 'block';
                document.getElementById('editInstructorImageUrl').value = data.instructor_image;
            } else {
                document.getElementById('editInstructorImagePreview').style.display = 'none';
                document.getElementById('editInstructorImageUrl').value = '';
            }
            editModal.show();
        });
    });

    // Update course submission
    document.getElementById('editCourseForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'edit_course');
        formData.append('id', document.getElementById('editId').value);
        formData.append('title', document.getElementById('editTitle').value);
        formData.append('description', document.getElementById('editDescription').value);
        formData.append('short_description', document.getElementById('editShortDesc').value);
        formData.append('icon_class', document.getElementById('editIcon').value);
        formData.append('link_url', document.getElementById('editLink').value);
        formData.append('duration', document.getElementById('editDuration').value);
        formData.append('level', document.getElementById('editLevel').value);
        formData.append('price', document.getElementById('editPrice').value);
        formData.append('price_offline', document.getElementById('editPriceOffline').value);
        formData.append('enrolled_students', document.getElementById('editEnrolled').value);
        formData.append('total_classes', document.getElementById('editTotalClasses').value);
        formData.append('total_projects', document.getElementById('editTotalProjects').value);
        formData.append('instructor_name', document.getElementById('editInstructorName').value);
        formData.append('instructor_bio', document.getElementById('editInstructorBio').value);
        formData.append('instructor_image', document.getElementById('editInstructorImageUrl').value);
        let curriculum = document.getElementById('editCurriculum').value;
        if (!curriculum.trim()) curriculum = '{"modules":[]}';
        formData.append('curriculum', curriculum);
        formData.append('career_outcomes', document.getElementById('editCareerOutcomes').value);
        formData.append('prerequisites', document.getElementById('editPrerequisites').value);
        formData.append('software_learned', document.getElementById('editSoftwareLearned').value);
        formData.append('is_popular', document.getElementById('editIsPopular').checked ? 1 : 0);
        formData.append('image_url', document.getElementById('editImageUrl').value);
        const res = await fetch('manage_courses.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
        const data = await res.json();
        if (data.success) { alert('Course updated!'); location.reload(); }
        else alert('Update failed: ' + data.message);
    });

    // Toggle status
    document.querySelectorAll('.toggle-status').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const formData = new FormData();
            formData.append('action', 'toggle_status');
            formData.append('id', id);
            const res = await fetch('manage_courses.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            if (data.success) location.reload();
        });
    });

    // Delete course
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('⚠️ Delete this course permanently?')) return;
            const id = btn.dataset.id;
            const formData = new FormData();
            formData.append('action', 'delete_course');
            formData.append('id', id);
            const res = await fetch('manage_courses.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            if (data.success) location.reload();
            else alert('Deletion failed');
        });
    });
</script>
</body>
</html>