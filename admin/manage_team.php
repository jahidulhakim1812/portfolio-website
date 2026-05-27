<?php
// admin/manage_team.php - Complete team management with security & UX enhancements
require_once 'auth.php';
require_once '../config.php';

// Start session only if not already active (prevents notice)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF token generation & validation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// Helper to delete image file if exists
function deleteTeamImage($imagePath) {
    if (!empty($imagePath) && file_exists('../' . $imagePath)) {
        unlink('../' . $imagePath);
    }
}

// Ensure status column exists (run once)
try {
    $pdo->exec("ALTER TABLE `team_members` ADD COLUMN IF NOT EXISTS `status` TINYINT DEFAULT 1 AFTER `order_position`");
} catch (PDOException $e) {
    // ignore if column already exists or error
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $action = $_POST['action'] ?? '';
    $writeActions = ['add_member', 'edit_member', 'upload_image', 'toggle_status', 'delete_member'];
    
    // Verify CSRF token for all write actions
    if (in_array($action, $writeActions)) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            jsonResponse(false, 'Security validation failed. Please refresh the page.');
        }
    }

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

        if (strlen($name) < 2 || strlen($position) < 2) {
            jsonResponse(false, 'Name and position (min 2 chars each) are required');
        }
        $stmt = $pdo->prepare("INSERT INTO team_members (name, position, bio, image_url, social_facebook, social_twitter, social_linkedin, order_position, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $position, $bio, $image_url, $social_fb, $social_tw, $social_li, $order, $status]);
        jsonResponse(true, 'Team member added successfully');
    }

    if ($action === 'edit_member') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $position = trim($_POST['position']);
        $bio = trim($_POST['bio']);
        $new_image_url = trim($_POST['image_url'] ?? '');
        $social_fb = trim($_POST['social_facebook'] ?? '');
        $social_tw = trim($_POST['social_twitter'] ?? '');
        $social_li = trim($_POST['social_linkedin'] ?? '');
        $order = intval($_POST['order_position']);
        $status = isset($_POST['status']) ? 1 : 0;

        if (!$id || strlen($name) < 2 || strlen($position) < 2) {
            jsonResponse(false, 'Invalid data: name and position required');
        }
        // Fetch old image to delete if replaced
        $stmt = $pdo->prepare("SELECT image_url FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        $old = $stmt->fetch();
        if ($old && !empty($old['image_url']) && $old['image_url'] !== $new_image_url && !empty($new_image_url)) {
            deleteTeamImage($old['image_url']);
        }
        $stmt = $pdo->prepare("UPDATE team_members SET name = ?, position = ?, bio = ?, image_url = ?, social_facebook = ?, social_twitter = ?, social_linkedin = ?, order_position = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $position, $bio, $new_image_url, $social_fb, $social_tw, $social_li, $order, $status, $id]);
        jsonResponse(true, 'Team member updated');
    }

    if ($action === 'upload_image') {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            jsonResponse(false, 'No valid file uploaded');
        }
        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowedTypes)) {
            jsonResponse(false, 'Only JPG, PNG, WEBP, GIF allowed');
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            jsonResponse(false, 'Image size must be less than 2MB');
        }
        $uploadDir = '../uploads/team/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeExt = strtolower($ext);
        $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $safeExt;
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            jsonResponse(true, 'Upload successful', ['image_url' => 'uploads/team/' . $fileName]);
        } else {
            jsonResponse(false, 'Failed to save file');
        }
    }

    if ($action === 'toggle_status') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("UPDATE team_members SET status = NOT status WHERE id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("SELECT status FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        $newStatus = $stmt->fetchColumn();
        jsonResponse(true, '', ['status' => $newStatus]);
    }

    if ($action === 'delete_member') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("SELECT image_url FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch();
        if ($member && !empty($member['image_url'])) {
            deleteTeamImage($member['image_url']);
        }
        $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(true, 'Team member deleted');
    }
    exit;
}

// Fetch all team members ordered by position
$members = $pdo->query("SELECT * FROM team_members ORDER BY order_position ASC, id DESC")->fetchAll();
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Manage Team | NEXORA AI</title>
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
        .logo-area { padding: 1.5rem; display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); }
        .logo { font-size: 1.8rem; font-weight: 800; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .toggle-btn { background: rgba(255,255,255,0.1); border: none; border-radius: 1rem; width: 40px; height: 40px; color: white; cursor: pointer; }
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
        .team-table {
            width: 100%;
            border-collapse: collapse;
        }
        .team-table th, .team-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .team-table th {
            color: var(--muted);
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .team-table td {
            color: var(--text);
        }
        .team-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
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
        .modal-content { background: var(--panel); color: var(--text); border-radius: 1.5rem; }
        .form-control, .form-select { background: rgba(255,255,255,0.1); border: 1px solid var(--border); color: var(--text); border-radius: 1rem; }
        .form-control::placeholder { color: var(--muted); opacity: 0.7; }
        .form-control:focus { background: rgba(255,255,255,0.15); color: var(--text); box-shadow: none; border-color: var(--primary); }
        .form-check-label { color: var(--text); }
        .image-preview { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; margin-top: 0.5rem; border: 2px solid var(--primary); }
        .btn-remove-img { background: var(--danger); border: none; border-radius: 1rem; font-size: 0.7rem; padding: 2px 6px; margin-top: 5px; color: white; }
        @media (max-width: 768px) {
            .sidebar { width: 80px; left: 10px; }
            .main { margin-left: 100px; }
            .team-table th, .team-table td { padding: 8px; font-size: 0.75rem; }
            .team-thumb { width: 35px; height: 35px; }
            .btn-sm-custom { font-size: 0.65rem; padding: 0.2rem 0.5rem; }
        }
        .toast-container { z-index: 1100; }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: var(--muted); }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="main" id="main">
    <div class="topbar">
        <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search by name or position..."></div>
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
                <thead>
                    <tr><th>Photo</th><th>ID</th><th>Name</th><th>Position</th><th>Order</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($members as $m): ?>
                    <tr data-id="<?php echo $m['id']; ?>">
                        <td>
                            <?php if($m['image_url']): ?>
                                <img src="../<?php echo htmlspecialchars($m['image_url']); ?>" class="team-thumb" alt="photo">
                            <?php else: ?>
                                <i class="fas fa-user-circle fa-2x" style="color:var(--muted);"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $m['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($m['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($m['position']); ?></td>
                        <td><?php echo $m['order_position']; ?></td>
                        <td><span class="status-badge <?php echo ($m['status'] ?? 1) ? '' : 'inactive'; ?>"><?php echo ($m['status'] ?? 1) ? 'Active' : 'Inactive'; ?></span></td>
                        <td>
                            <button class="edit-btn btn-sm-custom" data-id="<?php echo $m['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($m['name'], ENT_QUOTES); ?>"
                                data-position="<?php echo htmlspecialchars($m['position'], ENT_QUOTES); ?>"
                                data-bio="<?php echo htmlspecialchars($m['bio'] ?? '', ENT_QUOTES); ?>"
                                data-image="<?php echo htmlspecialchars($m['image_url'] ?? '', ENT_QUOTES); ?>"
                                data-fb="<?php echo htmlspecialchars($m['social_facebook'] ?? '', ENT_QUOTES); ?>"
                                data-tw="<?php echo htmlspecialchars($m['social_twitter'] ?? '', ENT_QUOTES); ?>"
                                data-li="<?php echo htmlspecialchars($m['social_linkedin'] ?? '', ENT_QUOTES); ?>"
                                data-order="<?php echo $m['order_position']; ?>"
                                data-status="<?php echo $m['status'] ?? 1; ?>"><i class="fas fa-edit"></i> Edit</button>
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

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<!-- Add Team Member Modal -->
<div class="modal fade" id="addTeamModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Team Member</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="addTeamForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Name *</label><input type="text" id="addName" class="form-control" required minlength="2"></div>
                        <div class="col-md-6 mb-3"><label>Position *</label><input type="text" id="addPosition" class="form-control" required minlength="2"></div>
                        <div class="col-12 mb-3"><label>Bio</label><textarea id="addBio" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-6 mb-3"><label>Order Position</label><input type="number" id="addOrder" class="form-control" value="0"></div>
                        <div class="col-md-6 mb-3"><label>Photo</label><input type="file" id="addImage" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif"><img id="addImagePreview" class="image-preview" style="display:none;"><input type="hidden" id="addImageUrl"><button type="button" id="addRemoveImage" class="btn-remove-img" style="display:none;">Remove image</button></div>
                        <div class="col-md-4 mb-3"><label>Facebook URL</label><input type="text" id="addFb" class="form-control" placeholder="https://facebook.com/..."></div>
                        <div class="col-md-4 mb-3"><label>Twitter URL</label><input type="text" id="addTw" class="form-control" placeholder="https://twitter.com/..."></div>
                        <div class="col-md-4 mb-3"><label>LinkedIn URL</label><input type="text" id="addLi" class="form-control" placeholder="https://linkedin.com/..."></div>
                        <div class="col-12 mb-3"><div class="form-check"><input type="checkbox" id="addStatus" class="form-check-input" checked><label class="form-check-label">Active</label></div></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="addSubmitBtn"><span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span> Add Member</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Team Member Modal -->
<div class="modal fade" id="editTeamModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Team Member</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="editTeamForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" id="editId">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Name *</label><input type="text" id="editName" class="form-control" required minlength="2"></div>
                        <div class="col-md-6 mb-3"><label>Position *</label><input type="text" id="editPosition" class="form-control" required minlength="2"></div>
                        <div class="col-12 mb-3"><label>Bio</label><textarea id="editBio" rows="2" class="form-control"></textarea></div>
                        <div class="col-md-6 mb-3"><label>Order Position</label><input type="number" id="editOrder" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Photo</label><input type="file" id="editImage" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif"><img id="editImagePreview" class="image-preview" style="display:none;"><input type="hidden" id="editImageUrl"><button type="button" id="editRemoveImage" class="btn-remove-img" style="display:none;">Remove image</button></div>
                        <div class="col-md-4 mb-3"><label>Facebook URL</label><input type="text" id="editFb" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label>Twitter URL</label><input type="text" id="editTw" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label>LinkedIn URL</label><input type="text" id="editLi" class="form-control"></div>
                        <div class="col-12 mb-3"><div class="form-check"><input type="checkbox" id="editStatus" class="form-check-input"><label class="form-check-label">Active</label></div></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="editSubmitBtn"><span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span> Update Member</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toast helper
    function showToast(message, type = 'success') {
        const toastContainer = document.querySelector('.toast-container');
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        toastContainer.appendChild(toastEl);
        const bsToast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }

    // Sidebar toggle
    const sidebar = document.getElementById('sidebar'), main = document.getElementById('main');
    const toggleBtn = document.getElementById('toggleBtn');
    if (toggleBtn) {
        toggleBtn.onclick = () => {
            sidebar.classList.toggle('collapsed');
            main.classList.toggle('expand');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        };
    }
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar?.classList.add('collapsed');
        main?.classList.add('expand');
    }

    // Theme toggle without reload
    const themeToggle = document.getElementById('themeToggle');
    if (localStorage.getItem('nexoraTheme') === 'light') document.body.classList.add('light');
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('light');
        const isLight = document.body.classList.contains('light');
        localStorage.setItem('nexoraTheme', isLight ? 'light' : 'dark');
        themeToggle.innerHTML = isLight ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
    });
    if(document.body.classList.contains('light')) themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
    else themeToggle.innerHTML = '<i class="fas fa-sun"></i>';

    // Enhanced search (name and position)
    document.getElementById('searchInput').addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#teamTable tbody tr');
        rows.forEach(row => {
            const name = row.cells[2].innerText.toLowerCase();
            const position = row.cells[3].innerText.toLowerCase();
            const matches = name.includes(filter) || position.includes(filter);
            row.style.display = matches ? '' : 'none';
        });
    });

    // Image preview & upload helper with remove functionality
    function setupImageUpload(fileInput, previewImg, hiddenUrl, removeBtn) {
        fileInput.addEventListener('change', async function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => { previewImg.src = e.target.result; previewImg.style.display = 'block'; };
                reader.readAsDataURL(this.files[0]);
                const formData = new FormData();
                formData.append('action', 'upload_image');
                formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
                formData.append('image', this.files[0]);
                try {
                    const res = await fetch('manage_team.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                    const data = await res.json();
                    if (data.success) {
                        hiddenUrl.value = data.image_url;
                        if (removeBtn) removeBtn.style.display = 'inline-block';
                        showToast('Image uploaded', 'success');
                    } else showToast(data.message, 'danger');
                } catch(e) { showToast('Upload failed', 'danger'); }
            }
        });
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                previewImg.style.display = 'none';
                previewImg.src = '';
                hiddenUrl.value = '';
                fileInput.value = '';
                removeBtn.style.display = 'none';
            });
        }
    }

    setupImageUpload(document.getElementById('addImage'), document.getElementById('addImagePreview'), document.getElementById('addImageUrl'), document.getElementById('addRemoveImage'));
    setupImageUpload(document.getElementById('editImage'), document.getElementById('editImagePreview'), document.getElementById('editImageUrl'), document.getElementById('editRemoveImage'));

    // Add member AJAX
    const addForm = document.getElementById('addTeamForm');
    addForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('addSubmitBtn');
        const spinner = btn.querySelector('.spinner-border');
        spinner.classList.remove('d-none');
        btn.disabled = true;
        const formData = new FormData(addForm);
        formData.append('action', 'add_member');
        formData.append('name', document.getElementById('addName').value);
        formData.append('position', document.getElementById('addPosition').value);
        formData.append('bio', document.getElementById('addBio').value);
        formData.append('order_position', document.getElementById('addOrder').value);
        formData.append('social_facebook', document.getElementById('addFb').value);
        formData.append('social_twitter', document.getElementById('addTw').value);
        formData.append('social_linkedin', document.getElementById('addLi').value);
        formData.append('status', document.getElementById('addStatus').checked ? 1 : 0);
        formData.append('image_url', document.getElementById('addImageUrl').value);
        try {
            const res = await fetch('manage_team.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            if (data.success) {
                showToast(data.message);
                setTimeout(() => location.reload(), 1000);
            } else showToast(data.message, 'danger');
        } catch(e) { showToast('Network error', 'danger'); }
        finally { spinner.classList.add('d-none'); btn.disabled = false; }
    });

    // Edit modal population
    const editModal = new bootstrap.Modal(document.getElementById('editTeamModal'));
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editId').value = btn.dataset.id;
            document.getElementById('editName').value = btn.dataset.name;
            document.getElementById('editPosition').value = btn.dataset.position;
            document.getElementById('editBio').value = btn.dataset.bio || '';
            document.getElementById('editOrder').value = btn.dataset.order;
            document.getElementById('editFb').value = btn.dataset.fb || '';
            document.getElementById('editTw').value = btn.dataset.tw || '';
            document.getElementById('editLi').value = btn.dataset.li || '';
            document.getElementById('editStatus').checked = btn.dataset.status == 1;
            const existingImage = btn.dataset.image;
            const preview = document.getElementById('editImagePreview');
            const hiddenUrl = document.getElementById('editImageUrl');
            const removeBtn = document.getElementById('editRemoveImage');
            if (existingImage) {
                preview.src = '../' + existingImage;
                preview.style.display = 'block';
                hiddenUrl.value = existingImage;
                removeBtn.style.display = 'inline-block';
            } else {
                preview.style.display = 'none';
                hiddenUrl.value = '';
                removeBtn.style.display = 'none';
            }
            editModal.show();
        });
    });

    // Edit member AJAX
    const editForm = document.getElementById('editTeamForm');
    editForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('editSubmitBtn');
        const spinner = btn.querySelector('.spinner-border');
        spinner.classList.remove('d-none');
        btn.disabled = true;
        const formData = new FormData(editForm);
        formData.append('action', 'edit_member');
        formData.append('id', document.getElementById('editId').value);
        formData.append('name', document.getElementById('editName').value);
        formData.append('position', document.getElementById('editPosition').value);
        formData.append('bio', document.getElementById('editBio').value);
        formData.append('order_position', document.getElementById('editOrder').value);
        formData.append('social_facebook', document.getElementById('editFb').value);
        formData.append('social_twitter', document.getElementById('editTw').value);
        formData.append('social_linkedin', document.getElementById('editLi').value);
        formData.append('status', document.getElementById('editStatus').checked ? 1 : 0);
        formData.append('image_url', document.getElementById('editImageUrl').value);
        try {
            const res = await fetch('manage_team.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            if (data.success) {
                showToast(data.message);
                setTimeout(() => location.reload(), 1000);
            } else showToast(data.message, 'danger');
        } catch(e) { showToast('Network error', 'danger'); }
        finally { spinner.classList.add('d-none'); btn.disabled = false; }
    });

    // Toggle Status (no reload, update UI)
    document.querySelectorAll('.toggle-status').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const formData = new FormData();
            formData.append('action', 'toggle_status');
            formData.append('id', id);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
            try {
                const res = await fetch('manage_team.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                const data = await res.json();
                if (data.success) {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    const statusCell = row.cells[5];
                    const newStatus = data.status;
                    statusCell.innerHTML = `<span class="status-badge ${newStatus ? '' : 'inactive'}">${newStatus ? 'Active' : 'Inactive'}</span>`;
                    showToast(`Status changed to ${newStatus ? 'Active' : 'Inactive'}`, 'success');
                } else showToast(data.message, 'danger');
            } catch(e) { showToast('Error toggling status', 'danger'); }
        });
    });

    // Delete with confirmation, remove row on success
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Delete this team member permanently? This action cannot be undone.')) return;
            const id = btn.dataset.id;
            const formData = new FormData();
            formData.append('action', 'delete_member');
            formData.append('id', id);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
            try {
                const res = await fetch('manage_team.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                const data = await res.json();
                if (data.success) {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    row.remove();
                    showToast('Team member deleted', 'success');
                } else showToast(data.message, 'danger');
            } catch(e) { showToast('Delete failed', 'danger'); }
        });
    });

    // Modal reset on close
    ['addTeamModal', 'editTeamModal'].forEach(modalId => {
        const modalEl = document.getElementById(modalId);
        modalEl.addEventListener('hidden.bs.modal', () => {
            const form = modalEl.querySelector('form');
            if (form) form.reset();
            const preview = modalEl.querySelector('.image-preview');
            if (preview) { preview.style.display = 'none'; preview.src = ''; }
            const hiddenUrl = modalEl.querySelector('input[type="hidden"][id*="ImageUrl"]');
            if (hiddenUrl) hiddenUrl.value = '';
            const removeBtn = modalEl.querySelector('.btn-remove-img');
            if (removeBtn) removeBtn.style.display = 'none';
        });
    });
</script>
</body>
</html>