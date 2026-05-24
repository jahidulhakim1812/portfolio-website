<?php
// admin/manage_courses.php - Complete course management with image upload
require_once 'auth.php';
require_once '../config.php';

// Handle AJAX requests (including file upload)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'add_course') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon_class = trim($_POST['icon_class'] ?? 'fas fa-cube');
        $link_url = trim($_POST['link_url'] ?? '#');
        $duration = trim($_POST['duration'] ?? '');
        $level = trim($_POST['level'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $enrolled = intval($_POST['enrolled_students'] ?? 0);
        $image_url = trim($_POST['image_url'] ?? ''); // from hidden input after upload
        
        if ($title) {
            $stmt = $pdo->prepare("INSERT INTO courses (title, description, icon_class, link_url, image_url, duration, level, price, enrolled_students, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$title, $description, $icon_class, $link_url, $image_url, $duration, $level, $price, $enrolled]);
            echo json_encode(['success' => true, 'message' => 'Course added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Title is required']);
        }
        exit;
    }

    if ($action === 'edit_course') {
        $id = intval($_POST['id']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $icon_class = trim($_POST['icon_class']);
        $link_url = trim($_POST['link_url']);
        $image_url = trim($_POST['image_url'] ?? '');
        $duration = trim($_POST['duration']);
        $level = trim($_POST['level']);
        $price = floatval($_POST['price']);
        $enrolled = intval($_POST['enrolled_students']);
        
        if ($id && $title) {
            $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ?, icon_class = ?, link_url = ?, image_url = ?, duration = ?, level = ?, price = ?, enrolled_students = ? WHERE id = ?");
            $stmt->execute([$title, $description, $icon_class, $link_url, $image_url, $duration, $level, $price, $enrolled, $id]);
            echo json_encode(['success' => true, 'message' => 'Course updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
        }
        exit;
    }

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
                echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        }
        exit;
    }

    if ($action === 'toggle_status') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("UPDATE courses SET status = NOT status WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'delete_course') {
        $id = intval($_POST['id']);
        // Optional: delete associated image file
        $stmt = $pdo->prepare("SELECT image_url FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $course = $stmt->fetch();
        if ($course && !empty($course['image_url']) && file_exists('../' . $course['image_url'])) {
            unlink('../' . $course['image_url']);
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
            --secondary: #06b6d4;
            --text: #ffffff;
            --muted: #94a3b8;
            --border: rgba(255,255,255,0.08);
        }
        body.light {
            --bg: #f8fafc;
            --panel: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --border: rgba(0,0,0,0.08);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--text);
            transition: all 0.3s;
            overflow-x: hidden;
        }
        .sidebar {
            position: fixed; left: 20px; top: 20px; bottom: 20px; width: 280px;
            background: rgba(15,23,42,0.9); backdrop-filter: blur(20px);
            border-radius: 2rem; border: 1px solid var(--border); transition: 0.3s; z-index: 1050;
        }
        body.light .sidebar { background: rgba(255,255,255,0.9); }
        .sidebar.collapsed { width: 90px; }
        .logo-area { padding: 1.5rem; display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); }
        .logo { font-size: 1.8rem; font-weight: 800; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .toggle-btn { background: rgba(255,255,255,0.1); border: none; border-radius: 1rem; width: 40px; height: 40px; color: white; cursor: pointer; }
        body.light .toggle-btn { background: rgba(0,0,0,0.05); color: #0f172a; }
        .menu { padding: 1rem; }
        .menu a {
            display: flex; align-items: center; gap: 14px; padding: 0.8rem 1rem;
            border-radius: 1.2rem; color: var(--muted); text-decoration: none; margin-bottom: 0.5rem;
        }
        .menu a i { width: 24px; }
        .menu a:hover, .menu a.active { background: rgba(124,58,237,0.2); color: #a855f7; transform: translateX(5px); }
        .sidebar.collapsed .logo, .sidebar.collapsed .menu span { display: none; }
        .main { margin-left: 310px; padding: 20px; transition: 0.3s; }
        .main.expand { margin-left: 120px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .search-box { position: relative; width: 320px; }
        .search-box input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 2rem; padding: 12px 20px 12px 45px; color: var(--text); }
        .search-box i { position: absolute; left: 18px; top: 15px; color: var(--muted); }
        .theme-toggle { background: rgba(255,255,255,0.1); border: none; border-radius: 2rem; width: 45px; height: 45px; cursor: pointer; color: var(--text); }
        .profile-img { width: 48px; height: 48px; border-radius: 1.2rem; background: var(--primary); display: flex; align-items: center; justify-content: center; }
        .panel { background: rgba(255,255,255,0.03); border-radius: 1.8rem; padding: 1.5rem; border: 1px solid var(--border); }
        .course-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; }
        .course-table td { background: rgba(255,255,255,0.04); padding: 12px; border-radius: 1rem; vertical-align: middle; }
        .course-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 0.8rem; }
        .status-badge { background: rgba(16,185,129,0.2); color: #10b981; padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
        .btn-sm-custom { background: transparent; border: 1px solid var(--primary); color: var(--primary); border-radius: 2rem; padding: 0.3rem 0.8rem; font-size: 0.75rem; transition: 0.2s; }
        .btn-sm-custom:hover { background: var(--primary); color: white; }
        .btn-add { background: linear-gradient(135deg, var(--primary), var(--secondary)); border: none; border-radius: 2rem; padding: 0.6rem 1.5rem; color: white; font-weight: 600; }
        .modal-content { background: var(--panel); color: var(--text); border-radius: 1.5rem; }
        .form-control, .form-select { background: rgba(255,255,255,0.1); border: 1px solid var(--border); color: var(--text); border-radius: 1rem; }
        .form-control:focus { background: rgba(255,255,255,0.15); color: var(--text); box-shadow: none; border-color: var(--primary); }
        .image-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 1rem; margin-top: 0.5rem; border: 1px solid var(--border); }
        @media (max-width: 768px) { .sidebar { width: 80px; left: 10px; } .main { margin-left: 100px; } .course-table { font-size: 0.8rem; } }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: var(--muted); }
    </style>
</head>
<body>
<div class="sidebar" id="sidebar">
    <div class="logo-area"><div class="logo">⚡ NEXORA</div><button class="toggle-btn" id="toggleBtn"><i class="fas fa-bars"></i></button></div>
    <div class="menu">
        <div class="menu-title">CORE</div>
        <a href="dashboard.php"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
        <a href="manage_courses.php" class="active"><i class="fas fa-graduation-cap"></i><span>Courses</span></a>
        <a href="manage_sliders.php"><i class="fas fa-images"></i><span>Sliders</span></a>
        <a href="manage_services.php"><i class="fas fa-cogs"></i><span>Services</span></a>
        <a href="manage_portfolios.php"><i class="fas fa-briefcase"></i><span>Portfolio</span></a>
        <a href="manage_testimonials.php"><i class="fas fa-star"></i><span>Testimonials</span></a>
        <a href="manage_customers.php"><i class="fas fa-users"></i><span>Customers</span></a>
        <div class="menu-title">SYSTEM</div>
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
                <thead><tr style="color:var(--muted);"><th>Image</th><th>ID</th><th>Title</th><th>Level</th><th>Duration</th><th>Price</th><th>Enrolled</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($courses as $c): ?>
                    <tr data-id="<?php echo $c['id']; ?>">
                        <td><?php if($c['image_url']): ?><img src="../<?php echo htmlspecialchars($c['image_url']); ?>" class="course-thumb" alt="course"><?php else: ?><i class="fas fa-image fa-2x text-muted"></i><?php endif; ?></td>
                        <td><?php echo $c['id']; ?></td>
                        <td><?php echo htmlspecialchars($c['title']); ?> <br><small class="text-muted"><?php echo htmlspecialchars(substr($c['description'], 0, 40)); ?>...</small></td>
                        <td><?php echo htmlspecialchars($c['level']); ?></td>
                        <td><?php echo htmlspecialchars($c['duration']); ?></td>
                        <td class="price-cell">$<?php echo number_format($c['price'], 2); ?></td>
                        <td><?php echo $c['enrolled_students']; ?></td>
                        <td><span class="status-badge"><?php echo $c['status'] ? 'Active' : 'Draft'; ?></span></td>
                        <td>
                            <button class="edit-btn btn-sm-custom" data-id="<?php echo $c['id']; ?>" 
                                data-title="<?php echo htmlspecialchars($c['title']); ?>"
                                data-description="<?php echo htmlspecialchars($c['description']); ?>"
                                data-icon="<?php echo htmlspecialchars($c['icon_class']); ?>"
                                data-link="<?php echo htmlspecialchars($c['link_url']); ?>"
                                data-image="<?php echo htmlspecialchars($c['image_url']); ?>"
                                data-duration="<?php echo htmlspecialchars($c['duration']); ?>"
                                data-level="<?php echo htmlspecialchars($c['level']); ?>"
                                data-price="<?php echo $c['price']; ?>"
                                data-enrolled="<?php echo $c['enrolled_students']; ?>"><i class="fas fa-edit"></i> Edit</button>
                            <button class="toggle-status btn-sm-custom" data-id="<?php echo $c['id']; ?>"><i class="fas fa-sync-alt"></i> Toggle</button>
                            <button class="delete-btn btn-sm-custom text-danger" data-id="<?php echo $c['id']; ?>"><i class="fas fa-trash"></i> Delete</button>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add New Course</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="addCourseForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Course Title *</label><input type="text" id="addTitle" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Price ($)</label><input type="number" step="0.01" id="addPrice" class="form-control" value="0"></div>
                        <div class="col-md-12 mb-3"><label>Description</label><textarea id="addDescription" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-4 mb-3"><label>Icon Class</label><input type="text" id="addIcon" class="form-control" value="fas fa-cube"></div>
                        <div class="col-md-4 mb-3"><label>Link URL</label><input type="text" id="addLink" class="form-control" value="#"></div>
                        <div class="col-md-4 mb-3"><label>Duration</label><input type="text" id="addDuration" class="form-control" placeholder="6 Weeks"></div>
                        <div class="col-md-4 mb-3"><label>Level</label>
                            <select id="addLevel" class="form-select"><option>Beginner</option><option>Intermediate</option><option>Advanced</option><option>All Levels</option></select>
                        </div>
                        <div class="col-md-4 mb-3"><label>Enrolled Students</label><input type="number" id="addEnrolled" class="form-control" value="0"></div>
                        <div class="col-md-4 mb-3"><label>Course Image</label>
                            <input type="file" id="addImage" class="form-control" accept="image/*">
                            <img id="addImagePreview" class="image-preview" style="display:none;">
                            <input type="hidden" id="addImageUrl">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-2">Create Course</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Course</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="editCourseForm" enctype="multipart/form-data">
                    <input type="hidden" id="editId">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Course Title *</label><input type="text" id="editTitle" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Price ($)</label><input type="number" step="0.01" id="editPrice" class="form-control"></div>
                        <div class="col-md-12 mb-3"><label>Description</label><textarea id="editDescription" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-4 mb-3"><label>Icon Class</label><input type="text" id="editIcon" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label>Link URL</label><input type="text" id="editLink" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label>Duration</label><input type="text" id="editDuration" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label>Level</label><select id="editLevel" class="form-select"><option>Beginner</option><option>Intermediate</option><option>Advanced</option><option>All Levels</option></select></div>
                        <div class="col-md-4 mb-3"><label>Enrolled Students</label><input type="number" id="editEnrolled" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label>Course Image</label>
                            <input type="file" id="editImage" class="form-control" accept="image/*">
                            <img id="editImagePreview" class="image-preview" style="display:none;">
                            <input type="hidden" id="editImageUrl">
                        </div>
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
    const sidebar = document.getElementById('sidebar'), main = document.getElementById('main');
    document.getElementById('toggleBtn').onclick = () => {
        sidebar.classList.toggle('collapsed');
        main.classList.toggle('expand');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    };
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        main.classList.add('expand');
    }

    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    if (localStorage.getItem('adminTheme') === 'light') document.body.classList.add('light');
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('light');
        localStorage.setItem('adminTheme', document.body.classList.contains('light') ? 'light' : 'dark');
        themeToggle.innerHTML = document.body.classList.contains('light') ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
        location.reload();
    });
    if(document.body.classList.contains('light')) themeToggle.innerHTML = '<i class="fas fa-moon"></i>'; else themeToggle.innerHTML = '<i class="fas fa-sun"></i>';

    // Search filter
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#coursesTable tbody tr');
        rows.forEach(row => {
            const title = row.cells[2].innerText.toLowerCase();
            row.style.display = title.includes(filter) ? '' : 'none';
        });
    });

    // Image preview helper
    function setupImagePreview(fileInput, previewImg, hiddenUrlInput) {
        fileInput.addEventListener('change', async function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => { previewImg.src = e.target.result; previewImg.style.display = 'block'; };
                reader.readAsDataURL(this.files[0]);
                // Upload image to server
                const formData = new FormData();
                formData.append('action', 'upload_image');
                formData.append('image', this.files[0]);
                const res = await fetch('manage_courses.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                const data = await res.json();
                if (data.success) hiddenUrlInput.value = data.image_url;
                else alert('Upload failed: ' + data.message);
            }
        });
    }

    // Add modal preview
    setupImagePreview(
        document.getElementById('addImage'),
        document.getElementById('addImagePreview'),
        document.getElementById('addImageUrl')
    );
    // Edit modal preview
    setupImagePreview(
        document.getElementById('editImage'),
        document.getElementById('editImagePreview'),
        document.getElementById('editImageUrl')
    );

    // Add course AJAX
    document.getElementById('addCourseForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'add_course');
        formData.append('title', document.getElementById('addTitle').value);
        formData.append('description', document.getElementById('addDescription').value);
        formData.append('icon_class', document.getElementById('addIcon').value);
        formData.append('link_url', document.getElementById('addLink').value);
        formData.append('duration', document.getElementById('addDuration').value);
        formData.append('level', document.getElementById('addLevel').value);
        formData.append('price', document.getElementById('addPrice').value);
        formData.append('enrolled_students', document.getElementById('addEnrolled').value);
        formData.append('image_url', document.getElementById('addImageUrl').value);
        const res = await fetch('manage_courses.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Error: ' + data.message);
    });

    // Edit modal population
    const editModal = new bootstrap.Modal(document.getElementById('editCourseModal'));
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editId').value = btn.dataset.id;
            document.getElementById('editTitle').value = btn.dataset.title;
            document.getElementById('editDescription').value = btn.dataset.description;
            document.getElementById('editIcon').value = btn.dataset.icon;
            document.getElementById('editLink').value = btn.dataset.link;
            document.getElementById('editDuration').value = btn.dataset.duration;
            document.getElementById('editLevel').value = btn.dataset.level;
            document.getElementById('editPrice').value = btn.dataset.price;
            document.getElementById('editEnrolled').value = btn.dataset.enrolled;
            const existingImage = btn.dataset.image;
            if (existingImage) {
                document.getElementById('editImagePreview').src = '../' + existingImage;
                document.getElementById('editImagePreview').style.display = 'block';
                document.getElementById('editImageUrl').value = existingImage;
            } else {
                document.getElementById('editImagePreview').style.display = 'none';
                document.getElementById('editImageUrl').value = '';
            }
            editModal.show();
        });
    });
    document.getElementById('editCourseForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'edit_course');
        formData.append('id', document.getElementById('editId').value);
        formData.append('title', document.getElementById('editTitle').value);
        formData.append('description', document.getElementById('editDescription').value);
        formData.append('icon_class', document.getElementById('editIcon').value);
        formData.append('link_url', document.getElementById('editLink').value);
        formData.append('duration', document.getElementById('editDuration').value);
        formData.append('level', document.getElementById('editLevel').value);
        formData.append('price', document.getElementById('editPrice').value);
        formData.append('enrolled_students', document.getElementById('editEnrolled').value);
        formData.append('image_url', document.getElementById('editImageUrl').value);
        const res = await fetch('manage_courses.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Error');
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

    // Delete with confirmation
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Delete this course permanently?')) return;
            const id = btn.dataset.id;
            const formData = new FormData();
            formData.append('action', 'delete_course');
            formData.append('id', id);
            const res = await fetch('manage_courses.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            if (data.success) location.reload();
        });
    });
</script>
</body>
</html>