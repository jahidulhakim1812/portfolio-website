<?php
// admin/manage_sliders.php - Complete slider management with NEXORA styling
require_once 'auth.php';
require_once '../config.php';

// Handle AJAX requests (unchanged)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'add_slider') {
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $button_text = trim($_POST['button_text'] ?? '');
        $button_link = trim($_POST['button_link'] ?? '#');
        $image_url = trim($_POST['image_url'] ?? '');
        $order_position = intval($_POST['order_position'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        if ($title && $image_url) {
            $stmt = $pdo->prepare("INSERT INTO sliders (title, subtitle, button_text, button_link, image_url, order_position, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $button_text, $button_link, $image_url, $order_position, $status]);
            echo json_encode(['success' => true, 'message' => 'Slider added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Title and image are required']);
        }
        exit;
    }

    if ($action === 'edit_slider') {
        $id = intval($_POST['id']);
        $title = trim($_POST['title']);
        $subtitle = trim($_POST['subtitle']);
        $button_text = trim($_POST['button_text']);
        $button_link = trim($_POST['button_link']);
        $image_url = trim($_POST['image_url']);
        $order_position = intval($_POST['order_position']);
        $status = isset($_POST['status']) ? 1 : 0;

        if ($id && $title) {
            $stmt = $pdo->prepare("UPDATE sliders SET title = ?, subtitle = ?, button_text = ?, button_link = ?, image_url = ?, order_position = ?, status = ? WHERE id = ?");
            $stmt->execute([$title, $subtitle, $button_text, $button_link, $image_url, $order_position, $status, $id]);
            echo json_encode(['success' => true, 'message' => 'Slider updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
        }
        exit;
    }

    if ($action === 'upload_image') {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/sliders/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                echo json_encode(['success' => true, 'image_url' => 'uploads/sliders/' . $fileName]);
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
        $stmt = $pdo->prepare("UPDATE sliders SET status = NOT status WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'delete_slider') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("SELECT image_url FROM sliders WHERE id = ?");
        $stmt->execute([$id]);
        $slider = $stmt->fetch();
        if ($slider && !empty($slider['image_url']) && file_exists('../' . $slider['image_url'])) {
            unlink('../' . $slider['image_url']);
        }
        $stmt = $pdo->prepare("DELETE FROM sliders WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }
}

// Fetch all sliders ordered by position
$sliders = $pdo->query("SELECT * FROM sliders ORDER BY order_position ASC, id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Manage Sliders | NEXORA AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* ========== IDENTICAL TO DASHBOARD / COURSE MANAGER ========== */
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
        /* Sidebar will be styled by navigation.php */
        .main {
            margin-left: 310px;
            padding: 20px;
            transition: margin 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .main.expand {
            margin-left: 120px;
        }
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
        .panel {
            background: rgba(255,255,255,0.03);
            border-radius: 1.8rem;
            padding: 1.5rem;
            border: 1px solid var(--border);
        }
        .slider-table {
            width: 100%;
            border-collapse: collapse;
        }
        .slider-table th, .slider-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .slider-table th {
            color: var(--muted);
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .slider-table td {
            color: var(--text);
        }
        .slide-thumb {
            width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 0.8rem;
        }
        .status-badge {
            background: rgba(16,185,129,0.2);
            color: #10b981;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-badge.inactive {
            background: rgba(239,68,68,0.2);
            color: #ef4444;
        }
        .btn-sm-custom {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
            border-radius: 2rem;
            padding: 0.3rem 0.8rem;
            font-size: 0.75rem;
            transition: 0.2s;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
        }
        .btn-sm-custom:hover {
            background: var(--primary);
            color: white;
        }
        .btn-add {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 2rem;
            padding: 0.6rem 1.5rem;
            color: white;
            font-weight: 600;
        }
        .modal-content {
            background: var(--panel);
            color: var(--text);
            border-radius: 1.5rem;
        }
        .form-control, .form-select {
            background: rgba(255,255,255,0.1);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 1rem;
        }
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 1rem;
            margin-top: 0.5rem;
            border: 1px solid var(--border);
        }
        @media (max-width: 768px) {
            .main { margin-left: 100px; }
            .slider-table th, .slider-table td { padding: 8px; font-size: 0.75rem; }
            .slide-thumb { width: 60px; height: 40px; }
            .btn-sm-custom { font-size: 0.65rem; padding: 0.2rem 0.5rem; }
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            color: var(--muted);
        }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="main" id="main">
    <div class="topbar">
        <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search sliders..."></div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon"></i></button>
            <div class="profile-img"><i class="fas fa-user-astronaut"></i></div>
            <div><strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong><br><small style="color:var(--muted)">Admin</small></div>
        </div>
    </div>

    <div class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
            <h3><i class="fas fa-images me-2"></i> Slider Management</h3>
            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addSliderModal"><i class="fas fa-plus me-2"></i>Add Slider</button>
        </div>
        <div class="table-responsive">
            <table class="slider-table w-100" id="slidersTable">
                <thead>
                    <tr>
                        <th>Image</th><th>ID</th><th>Title</th><th>Subtitle</th><th>Button</th><th>Order</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($sliders as $s): ?>
                    <tr data-id="<?php echo $s['id']; ?>">
                        <td><img src="../<?php echo htmlspecialchars($s['image_url']); ?>" class="slide-thumb" alt="slide"></td>
                        <td><?php echo $s['id']; ?></td>
                        <td><?php echo htmlspecialchars($s['title']); ?> <br><small class="text-muted"><?php echo htmlspecialchars(substr($s['subtitle'], 0, 50)); ?>...</small></td>
                        <td><?php echo htmlspecialchars($s['subtitle']); ?></td>
                        <td><?php echo htmlspecialchars($s['button_text']); ?> →<br><small><?php echo htmlspecialchars($s['button_link']); ?></small></td>
                        <td><?php echo $s['order_position']; ?></td>
                        <td><span class="status-badge <?php echo $s['status'] ? '' : 'inactive'; ?>"><?php echo $s['status'] ? 'Active' : 'Inactive'; ?></span></td>
                        <td>
                            <button class="edit-btn btn-sm-custom" data-id="<?php echo $s['id']; ?>" 
                                data-title="<?php echo htmlspecialchars($s['title']); ?>"
                                data-subtitle="<?php echo htmlspecialchars($s['subtitle']); ?>"
                                data-button_text="<?php echo htmlspecialchars($s['button_text']); ?>"
                                data-button_link="<?php echo htmlspecialchars($s['button_link']); ?>"
                                data-image="<?php echo htmlspecialchars($s['image_url']); ?>"
                                data-order="<?php echo $s['order_position']; ?>"
                                data-status="<?php echo $s['status']; ?>"><i class="fas fa-edit"></i> Edit</button>
                            <button class="toggle-status btn-sm-custom" data-id="<?php echo $s['id']; ?>"><i class="fas fa-sync-alt"></i> Toggle</button>
                            <button class="delete-btn btn-sm-custom" data-id="<?php echo $s['id']; ?>" style="border-color:var(--danger); color:var(--danger);"><i class="fas fa-trash"></i> Delete</button>
                         </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="footer">© 2025 NEXORA AI | Slider Management System</div>
</div>

<!-- Add Slider Modal -->
<div class="modal fade" id="addSliderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add New Slider</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="addSliderForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Title *</label><input type="text" id="addTitle" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Order Position</label><input type="number" id="addOrder" class="form-control" value="0"></div>
                        <div class="col-md-12 mb-3"><label>Subtitle</label><textarea id="addSubtitle" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-6 mb-3"><label>Button Text</label><input type="text" id="addButtonText" class="form-control" placeholder="Learn More"></div>
                        <div class="col-md-6 mb-3"><label>Button Link</label><input type="text" id="addButtonLink" class="form-control" value="#"></div>
                        <div class="col-md-12 mb-3"><label>Slider Image *</label><input type="file" id="addImage" class="form-control" accept="image/*"><img id="addImagePreview" class="image-preview" style="display:none;"><input type="hidden" id="addImageUrl"></div>
                        <div class="col-md-12 mb-3"><div class="form-check"><input type="checkbox" id="addStatus" class="form-check-input" checked><label class="form-check-label">Active</label></div></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-2">Create Slider</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Slider Modal -->
<div class="modal fade" id="editSliderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Slider</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="editSliderForm" enctype="multipart/form-data">
                    <input type="hidden" id="editId">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Title *</label><input type="text" id="editTitle" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Order Position</label><input type="number" id="editOrder" class="form-control"></div>
                        <div class="col-md-12 mb-3"><label>Subtitle</label><textarea id="editSubtitle" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-6 mb-3"><label>Button Text</label><input type="text" id="editButtonText" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Button Link</label><input type="text" id="editButtonLink" class="form-control"></div>
                        <div class="col-md-12 mb-3"><label>Slider Image</label><input type="file" id="editImage" class="form-control" accept="image/*"><img id="editImagePreview" class="image-preview" style="display:none;"><input type="hidden" id="editImageUrl"></div>
                        <div class="col-md-12 mb-3"><div class="form-check"><input type="checkbox" id="editStatus" class="form-check-input"><label class="form-check-label">Active</label></div></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-2">Update Slider</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Theme toggle (sidebar is handled by navigation.php)
    const themeToggle = document.getElementById('themeToggle');
    if (localStorage.getItem('nexoraTheme') === 'light') document.body.classList.add('light');
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('light');
        localStorage.setItem('nexoraTheme', document.body.classList.contains('light') ? 'light' : 'dark');
        themeToggle.innerHTML = document.body.classList.contains('light') ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
        location.reload();
    });
    if(document.body.classList.contains('light')) themeToggle.innerHTML = '<i class="fas fa-moon"></i>'; else themeToggle.innerHTML = '<i class="fas fa-sun"></i>';

    // Search filter
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#slidersTable tbody tr');
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
                const formData = new FormData();
                formData.append('action', 'upload_image');
                formData.append('image', this.files[0]);
                const res = await fetch('manage_sliders.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                const data = await res.json();
                if (data.success) hiddenUrlInput.value = data.image_url;
                else alert('Upload failed: ' + data.message);
            }
        });
    }

    setupImagePreview(document.getElementById('addImage'), document.getElementById('addImagePreview'), document.getElementById('addImageUrl'));
    setupImagePreview(document.getElementById('editImage'), document.getElementById('editImagePreview'), document.getElementById('editImageUrl'));

    // Add slider AJAX
    document.getElementById('addSliderForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'add_slider');
        formData.append('title', document.getElementById('addTitle').value);
        formData.append('subtitle', document.getElementById('addSubtitle').value);
        formData.append('button_text', document.getElementById('addButtonText').value);
        formData.append('button_link', document.getElementById('addButtonLink').value);
        formData.append('order_position', document.getElementById('addOrder').value);
        formData.append('status', document.getElementById('addStatus').checked ? 1 : 0);
        formData.append('image_url', document.getElementById('addImageUrl').value);
        const res = await fetch('manage_sliders.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Error: ' + data.message);
    });

    // Edit modal population
    const editModal = new bootstrap.Modal(document.getElementById('editSliderModal'));
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editId').value = btn.dataset.id;
            document.getElementById('editTitle').value = btn.dataset.title;
            document.getElementById('editSubtitle').value = btn.dataset.subtitle;
            document.getElementById('editButtonText').value = btn.dataset.button_text;
            document.getElementById('editButtonLink').value = btn.dataset.button_link;
            document.getElementById('editOrder').value = btn.dataset.order;
            document.getElementById('editStatus').checked = btn.dataset.status == 1;
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
    document.getElementById('editSliderForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'edit_slider');
        formData.append('id', document.getElementById('editId').value);
        formData.append('title', document.getElementById('editTitle').value);
        formData.append('subtitle', document.getElementById('editSubtitle').value);
        formData.append('button_text', document.getElementById('editButtonText').value);
        formData.append('button_link', document.getElementById('editButtonLink').value);
        formData.append('order_position', document.getElementById('editOrder').value);
        formData.append('status', document.getElementById('editStatus').checked ? 1 : 0);
        formData.append('image_url', document.getElementById('editImageUrl').value);
        const res = await fetch('manage_sliders.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
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
            const res = await fetch('manage_sliders.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            if (data.success) location.reload();
        });
    });

    // Delete with confirmation
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Delete this slider permanently?')) return;
            const id = btn.dataset.id;
            const formData = new FormData();
            formData.append('action', 'delete_slider');
            formData.append('id', id);
            const res = await fetch('manage_sliders.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            if (data.success) location.reload();
        });
    });
</script>
</body>
</html>