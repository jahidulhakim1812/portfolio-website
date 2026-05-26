<?php
// admin/manage_team.php - Team member management (fixed for missing status)
require_once 'auth.php';
require_once '../config.php';

// Ensure status column exists (run once)
try {
    $pdo->exec("ALTER TABLE `team_members` ADD COLUMN IF NOT EXISTS `status` TINYINT DEFAULT 1 AFTER `order_position`");
} catch (PDOException $e) {
    // ignore if column already exists
}

// Handle AJAX requests (unchanged, but add status handling safely)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'add_member') {
        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');
        $social_fb = trim($_POST['social_facebook'] ?? '');
        $social_tw = trim($_POST['social_twitter'] ?? '');
        $social_li = trim($_POST['social_linkedin'] ?? '');
        $order = intval($_POST['order_position'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        if ($name && $position) {
            $stmt = $pdo->prepare("INSERT INTO team_members (name, position, bio, image_url, social_facebook, social_twitter, social_linkedin, order_position, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $position, $bio, $image_url, $social_fb, $social_tw, $social_li, $order, $status]);
            echo json_encode(['success' => true, 'message' => 'Team member added']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Name and position required']);
        }
        exit;
    }

    if ($action === 'edit_member') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $position = trim($_POST['position']);
        $bio = trim($_POST['bio']);
        $image_url = trim($_POST['image_url'] ?? '');
        $social_fb = trim($_POST['social_facebook'] ?? '');
        $social_tw = trim($_POST['social_twitter'] ?? '');
        $social_li = trim($_POST['social_linkedin'] ?? '');
        $order = intval($_POST['order_position']);
        $status = isset($_POST['status']) ? 1 : 0;

        if ($id && $name) {
            $stmt = $pdo->prepare("UPDATE team_members SET name = ?, position = ?, bio = ?, image_url = ?, social_facebook = ?, social_twitter = ?, social_linkedin = ?, order_position = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $position, $bio, $image_url, $social_fb, $social_tw, $social_li, $order, $status, $id]);
            echo json_encode(['success' => true, 'message' => 'Member updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
        }
        exit;
    }

    if ($action === 'upload_image') {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/team/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                echo json_encode(['success' => true, 'image_url' => 'uploads/team/' . $fileName]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Upload failed']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        }
        exit;
    }

    if ($action === 'toggle_status') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("UPDATE team_members SET status = NOT status WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'delete_member') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("SELECT image_url FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch();
        if ($member && !empty($member['image_url']) && file_exists('../' . $member['image_url'])) {
            unlink('../' . $member['image_url']);
        }
        $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }
}

$members = $pdo->query("SELECT * FROM team_members ORDER BY order_position ASC, id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Team | NEXORA AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* ========== SAME NEXORA DASHBOARD STYLES ========== */
        :root { --bg: #050816; --panel: #0f172a; --primary: #7c3aed; --primary-glow: #a855f7; --secondary: #06b6d4; --success: #10b981; --warning: #f59e0b; --danger: #ef4444; --text: #ffffff; --muted: #94a3b8; --border: rgba(255,255,255,0.08); --shadow: 0 20px 35px -10px rgba(0,0,0,0.4); }
        body.light { --bg: #f8fafc; --panel: #ffffff; --text: #0f172a; --muted: #475569; --border: rgba(0,0,0,0.08); --shadow: 0 10px 25px -5px rgba(0,0,0,0.05); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); transition: all 0.3s; overflow-x: hidden; }
        body::before, body::after { content: ''; position: fixed; width: 800px; height: 800px; border-radius: 50%; background: radial-gradient(circle, rgba(124,58,237,0.15), transparent); top: -300px; left: -300px; z-index: -1; animation: floatBg 20s infinite alternate; }
        body::after { background: radial-gradient(circle, rgba(6,182,212,0.12), transparent); top: auto; bottom: -200px; right: -200px; left: auto; animation: floatBg2 18s infinite alternate; }
        @keyframes floatBg { 0% { transform: translate(0,0); } 100% { transform: translate(100px, 80px); } }
        @keyframes floatBg2 { 0% { transform: translate(0,0); } 100% { transform: translate(-80px, -60px); } }
        .sidebar { position: fixed; left: 20px; top: 20px; bottom: 20px; width: 280px; background: rgba(15,23,42,0.9); backdrop-filter: blur(20px); border-radius: 2rem; border: 1px solid var(--border); transition: 0.3s; z-index: 1050; box-shadow: var(--shadow); }
        body.light .sidebar { background: rgba(255,255,255,0.9); }
        .sidebar.collapsed { width: 90px; }
        .logo-area { padding: 1.5rem; display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); }
        .logo { font-size: 1.8rem; font-weight: 800; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .toggle-btn { background: rgba(255,255,255,0.1); border: none; border-radius: 1rem; width: 40px; height: 40px; color: white; cursor: pointer; }
        body.light .toggle-btn { background: rgba(0,0,0,0.05); color: #0f172a; }
        .menu { padding: 1rem; }
        .menu-title { color: var(--muted); font-size: 0.7rem; letter-spacing: 2px; margin: 1rem 1rem 0.5rem; }
        .menu a { display: flex; align-items: center; gap: 14px; padding: 0.8rem 1rem; border-radius: 1.2rem; color: var(--muted); text-decoration: none; margin-bottom: 0.5rem; transition: 0.2s; }
        .menu a i { width: 24px; }
        .menu a:hover, .menu a.active { background: rgba(124,58,237,0.2); color: var(--primary-glow); transform: translateX(5px); }
        .sidebar.collapsed .logo, .sidebar.collapsed .menu span, .sidebar.collapsed .menu-title { display: none; }
        .main { margin-left: 310px; padding: 20px; transition: 0.3s; }
        .main.expand { margin-left: 120px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .search-box { position: relative; width: 320px; }
        .search-box input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 2rem; padding: 12px 20px 12px 45px; color: var(--text); }
        .search-box i { position: absolute; left: 18px; top: 15px; color: var(--muted); }
        .theme-toggle { background: rgba(255,255,255,0.1); border: none; border-radius: 2rem; width: 45px; height: 45px; cursor: pointer; color: var(--text); }
        .profile-img { width: 48px; height: 48px; border-radius: 1.2rem; background: var(--primary); display: flex; align-items: center; justify-content: center; }
        .panel { background: rgba(255,255,255,0.03); border-radius: 1.8rem; padding: 1.5rem; border: 1px solid var(--border); }
        .team-table { width: 100%; border-collapse: collapse; }
        .team-table th, .team-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .team-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; }
        .status-badge { background: rgba(16,185,129,0.2); color: #10b981; padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
        .status-badge.inactive { background: rgba(239,68,68,0.2); color: #ef4444; }
        .btn-sm-custom { background: transparent; border: 1px solid var(--primary); color: var(--primary); border-radius: 2rem; padding: 0.3rem 0.8rem; font-size: 0.75rem; transition: 0.2s; }
        .btn-sm-custom:hover { background: var(--primary); color: white; }
        .btn-add { background: linear-gradient(135deg, var(--primary), var(--secondary)); border: none; border-radius: 2rem; padding: 0.6rem 1.5rem; color: white; font-weight: 600; }
        .modal-content { background: var(--panel); color: var(--text); border-radius: 1.5rem; }
        .form-control, .form-select { background: rgba(255,255,255,0.1); border: 1px solid var(--border); color: var(--text); border-radius: 1rem; }
        .image-preview { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; margin-top: 0.5rem; border: 2px solid var(--primary); }
        @media (max-width: 768px) { .sidebar { width: 80px; left: 10px; } .main { margin-left: 100px; } }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: var(--muted); }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="main" id="main">
    <div class="topbar">
        <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search team members..."></div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon"></i></button>
            <div class="profile-img"><i class="fas fa-user-astronaut"></i></div>
            <div><strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong><br><small style="color:var(--muted)">Admin</small></div>
        </div>
    </div>

    <div class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
            <h3><i class="fas fa-users me-2"></i> Team Management</h3>
            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addTeamModal"><i class="fas fa-plus me-2"></i>Add Member</button>
        </div>
        <div class="table-responsive">
            <table class="team-table w-100" id="teamTable">
                <thead><tr><th>Photo</th><th>ID</th><th>Name</th><th>Position</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($members as $m): ?>
                    <tr data-id="<?php echo $m['id']; ?>">
                        <td><?php if($m['image_url']): ?><img src="../<?php echo htmlspecialchars($m['image_url']); ?>" class="team-thumb"><?php else: ?><i class="fas fa-user-circle fa-2x text-muted"></i><?php endif; ?></td>
                        <td><?php echo $m['id']; ?></td>
                        <td><?php echo htmlspecialchars($m['name']); ?></td>
                        <td><?php echo htmlspecialchars($m['position']); ?></td>
                        <td><?php echo $m['order_position']; ?></td>
                        <td><span class="status-badge <?php echo ($m['status'] ?? 1) ? '' : 'inactive'; ?>"><?php echo ($m['status'] ?? 1) ? 'Active' : 'Inactive'; ?></span></td>
                        <td>
                            <button class="edit-btn btn-sm-custom" data-id="<?php echo $m['id']; ?>" data-name="<?php echo htmlspecialchars($m['name']); ?>" data-position="<?php echo htmlspecialchars($m['position']); ?>" data-bio="<?php echo htmlspecialchars($m['bio']); ?>" data-image="<?php echo htmlspecialchars($m['image_url']); ?>" data-fb="<?php echo htmlspecialchars($m['social_facebook']); ?>" data-tw="<?php echo htmlspecialchars($m['social_twitter']); ?>" data-li="<?php echo htmlspecialchars($m['social_linkedin']); ?>" data-order="<?php echo $m['order_position']; ?>" data-status="<?php echo $m['status'] ?? 1; ?>"><i class="fas fa-edit"></i> Edit</button>
                            <button class="toggle-status btn-sm-custom" data-id="<?php echo $m['id']; ?>"><i class="fas fa-sync-alt"></i> Toggle</button>
                            <button class="delete-btn btn-sm-custom" data-id="<?php echo $m['id']; ?>" style="border-color:var(--danger); color:var(--danger);"><i class="fas fa-trash"></i> Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="footer">© 2025 NEXORA AI | Team Management</div>
</div>

<!-- Add Member Modal (unchanged) -->
<div class="modal fade" id="addTeamModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Team Member</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="addTeamForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Name *</label><input type="text" id="addName" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Position *</label><input type="text" id="addPosition" class="form-control" required></div>
                        <div class="col-12 mb-3"><label>Bio</label><textarea id="addBio" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-6 mb-3"><label>Order Position</label><input type="number" id="addOrder" class="form-control" value="0"></div>
                        <div class="col-md-6 mb-3"><label>Photo</label><input type="file" id="addImage" class="form-control" accept="image/*"><img id="addImagePreview" class="image-preview" style="display:none;"><input type="hidden" id="addImageUrl"></div>
                        <div class="col-md-4 mb-3"><label>Facebook URL</label><input type="text" id="addFb" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label>Twitter URL</label><input type="text" id="addTw" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label>LinkedIn URL</label><input type="text" id="addLi" class="form-control"></div>
                        <div class="col-12 mb-3"><div class="form-check"><input type="checkbox" id="addStatus" class="form-check-input" checked><label class="form-check-label">Active</label></div></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Member</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Member Modal (unchanged) -->
<div class="modal fade" id="editTeamModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Edit Team Member</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="editTeamForm" enctype="multipart/form-data">
                    <input type="hidden" id="editId">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Name *</label><input type="text" id="editName" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Position *</label><input type="text" id="editPosition" class="form-control" required></div>
                        <div class="col-12 mb-3"><label>Bio</label><textarea id="editBio" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-6 mb-3"><label>Order Position</label><input type="number" id="editOrder" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Photo</label><input type="file" id="editImage" class="form-control" accept="image/*"><img id="editImagePreview" class="image-preview" style="display:none;"><input type="hidden" id="editImageUrl"></div>
                        <div class="col-md-4 mb-3"><label>Facebook URL</label><input type="text" id="editFb" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label>Twitter URL</label><input type="text" id="editTw" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label>LinkedIn URL</label><input type="text" id="editLi" class="form-control"></div>
                        <div class="col-12 mb-3"><div class="form-check"><input type="checkbox" id="editStatus" class="form-check-input"><label class="form-check-label">Active</label></div></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Member</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar toggle, theme toggle, search filter, image previews – unchanged from previous version
    // Add the same JS as before (copy from the original, but it's identical)
    const sidebar = document.getElementById('sidebar'), main = document.getElementById('main');
    document.getElementById('toggleBtn').onclick = () => { sidebar.classList.toggle('collapsed'); main.classList.toggle('expand'); localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed')); };
    if (localStorage.getItem('sidebarCollapsed') === 'true') { sidebar.classList.add('collapsed'); main.classList.add('expand'); }

    const themeToggle = document.getElementById('themeToggle');
    if (localStorage.getItem('nexoraTheme') === 'light') document.body.classList.add('light');
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('light');
        localStorage.setItem('nexoraTheme', document.body.classList.contains('light') ? 'light' : 'dark');
        themeToggle.innerHTML = document.body.classList.contains('light') ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
        location.reload();
    });
    if(document.body.classList.contains('light')) themeToggle.innerHTML = '<i class="fas fa-moon"></i>'; else themeToggle.innerHTML = '<i class="fas fa-sun"></i>';

    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('#teamTable tbody tr').forEach(row => {
            const name = row.cells[2].innerText.toLowerCase();
            row.style.display = name.includes(filter) ? '' : 'none';
        });
    });

    function setupImagePreview(fileInput, previewImg, hiddenUrlInput) {
        fileInput.addEventListener('change', async function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => { previewImg.src = e.target.result; previewImg.style.display = 'block'; };
                reader.readAsDataURL(this.files[0]);
                const formData = new FormData();
                formData.append('action', 'upload_image');
                formData.append('image', this.files[0]);
                const res = await fetch('manage_team.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                const data = await res.json();
                if (data.success) hiddenUrlInput.value = data.image_url;
                else alert('Upload failed: ' + data.message);
            }
        });
    }
    setupImagePreview(document.getElementById('addImage'), document.getElementById('addImagePreview'), document.getElementById('addImageUrl'));
    setupImagePreview(document.getElementById('editImage'), document.getElementById('editImagePreview'), document.getElementById('editImageUrl'));

    document.getElementById('addTeamForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData();
        fd.append('action', 'add_member');
        fd.append('name', document.getElementById('addName').value);
        fd.append('position', document.getElementById('addPosition').value);
        fd.append('bio', document.getElementById('addBio').value);
        fd.append('order_position', document.getElementById('addOrder').value);
        fd.append('social_facebook', document.getElementById('addFb').value);
        fd.append('social_twitter', document.getElementById('addTw').value);
        fd.append('social_linkedin', document.getElementById('addLi').value);
        fd.append('status', document.getElementById('addStatus').checked ? 1 : 0);
        fd.append('image_url', document.getElementById('addImageUrl').value);
        const res = await fetch('manage_team.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Error: ' + data.message);
    });

    const editModal = new bootstrap.Modal(document.getElementById('editTeamModal'));
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editId').value = btn.dataset.id;
            document.getElementById('editName').value = btn.dataset.name;
            document.getElementById('editPosition').value = btn.dataset.position;
            document.getElementById('editBio').value = btn.dataset.bio;
            document.getElementById('editOrder').value = btn.dataset.order;
            document.getElementById('editFb').value = btn.dataset.fb;
            document.getElementById('editTw').value = btn.dataset.tw;
            document.getElementById('editLi').value = btn.dataset.li;
            document.getElementById('editStatus').checked = btn.dataset.status == 1;
            const img = btn.dataset.image;
            if (img) {
                document.getElementById('editImagePreview').src = '../' + img;
                document.getElementById('editImagePreview').style.display = 'block';
                document.getElementById('editImageUrl').value = img;
            } else {
                document.getElementById('editImagePreview').style.display = 'none';
                document.getElementById('editImageUrl').value = '';
            }
            editModal.show();
        });
    });
    document.getElementById('editTeamForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData();
        fd.append('action', 'edit_member');
        fd.append('id', document.getElementById('editId').value);
        fd.append('name', document.getElementById('editName').value);
        fd.append('position', document.getElementById('editPosition').value);
        fd.append('bio', document.getElementById('editBio').value);
        fd.append('order_position', document.getElementById('editOrder').value);
        fd.append('social_facebook', document.getElementById('editFb').value);
        fd.append('social_twitter', document.getElementById('editTw').value);
        fd.append('social_linkedin', document.getElementById('editLi').value);
        fd.append('status', document.getElementById('editStatus').checked ? 1 : 0);
        fd.append('image_url', document.getElementById('editImageUrl').value);
        const res = await fetch('manage_team.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Error');
    });

    document.querySelectorAll('.toggle-status').forEach(btn => {
        btn.addEventListener('click', async () => {
            const fd = new FormData(); fd.append('action', 'toggle_status'); fd.append('id', btn.dataset.id);
            const res = await fetch('manage_team.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
            const data = await res.json();
            if (data.success) location.reload();
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Delete this member?')) return;
            const fd = new FormData(); fd.append('action', 'delete_member'); fd.append('id', btn.dataset.id);
            const res = await fetch('manage_team.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
            const data = await res.json();
            if (data.success) location.reload();
        });
    });
</script>
</body>
</html>