<?php
// admin/manage_portfolios.php - Complete portfolio management with security & UX enhancements
require_once 'auth.php';
require_once '../config.php';

// Start session only if not already active (avoid notice)
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
function deletePortfolioImage($imagePath) {
    if (!empty($imagePath) && file_exists('../' . $imagePath)) {
        unlink('../' . $imagePath);
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Verify CSRF token for all write actions
    $action = $_POST['action'] ?? '';
    $writeActions = ['add_portfolio', 'edit_portfolio', 'upload_image', 'toggle_featured', 'toggle_status', 'delete_portfolio'];
    if (in_array($action, $writeActions)) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            jsonResponse(false, 'Security validation failed. Please refresh the page.');
        }
    }

    if ($action === 'add_portfolio') {
        $title = trim($_POST['title'] ?? '');
        $client = trim($_POST['client'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');
        $featured = isset($_POST['featured']) ? 1 : 0;
        $order_position = intval($_POST['order_position'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        if (strlen($title) < 2 || strlen($description) < 5) {
            jsonResponse(false, 'Title (min 2 chars) and description (min 5 chars) are required');
        }
        $stmt = $pdo->prepare("INSERT INTO portfolios (title, client, description, image_url, featured, order_position, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $client, $description, $image_url, $featured, $order_position, $status]);
        jsonResponse(true, 'Portfolio added successfully');
    }

    if ($action === 'edit_portfolio') {
        $id = intval($_POST['id']);
        $title = trim($_POST['title']);
        $client = trim($_POST['client']);
        $description = trim($_POST['description']);
        $new_image_url = trim($_POST['image_url'] ?? '');
        $featured = isset($_POST['featured']) ? 1 : 0;
        $order_position = intval($_POST['order_position']);
        $status = isset($_POST['status']) ? 1 : 0;

        if (!$id || strlen($title) < 2 || strlen($description) < 5) {
            jsonResponse(false, 'Invalid data: title and description required');
        }
        // Fetch old image to delete if replaced
        $stmt = $pdo->prepare("SELECT image_url FROM portfolios WHERE id = ?");
        $stmt->execute([$id]);
        $old = $stmt->fetch();
        if ($old && !empty($old['image_url']) && $old['image_url'] !== $new_image_url && !empty($new_image_url)) {
            deletePortfolioImage($old['image_url']);
        }
        $stmt = $pdo->prepare("UPDATE portfolios SET title = ?, client = ?, description = ?, image_url = ?, featured = ?, order_position = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $client, $description, $new_image_url, $featured, $order_position, $status, $id]);
        jsonResponse(true, 'Portfolio updated');
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
        $uploadDir = '../uploads/portfolios/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeExt = strtolower($ext);
        $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $safeExt;
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            jsonResponse(true, 'Upload successful', ['image_url' => 'uploads/portfolios/' . $fileName]);
        } else {
            jsonResponse(false, 'Failed to save file');
        }
    }

    if ($action === 'toggle_featured') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("UPDATE portfolios SET featured = NOT featured WHERE id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("SELECT featured FROM portfolios WHERE id = ?");
        $stmt->execute([$id]);
        $newFeatured = $stmt->fetchColumn();
        jsonResponse(true, '', ['featured' => $newFeatured]);
    }

    if ($action === 'toggle_status') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("UPDATE portfolios SET status = NOT status WHERE id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("SELECT status FROM portfolios WHERE id = ?");
        $stmt->execute([$id]);
        $newStatus = $stmt->fetchColumn();
        jsonResponse(true, '', ['status' => $newStatus]);
    }

    if ($action === 'delete_portfolio') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("SELECT image_url FROM portfolios WHERE id = ?");
        $stmt->execute([$id]);
        $portfolio = $stmt->fetch();
        if ($portfolio && !empty($portfolio['image_url'])) {
            deletePortfolioImage($portfolio['image_url']);
        }
        $stmt = $pdo->prepare("DELETE FROM portfolios WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(true, 'Portfolio deleted');
    }
    exit;
}

// Fetch all portfolios ordered by position
$portfolios = $pdo->query("SELECT * FROM portfolios ORDER BY order_position ASC, id DESC")->fetchAll();
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Manage Portfolios | NEXORA AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* ========== NEXORA DASHBOARD STYLES ========== */
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
        .portfolio-table {
            width: 100%;
            border-collapse: collapse;
        }
        .portfolio-table th, .portfolio-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .portfolio-table th {
            color: var(--muted);
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .portfolio-table td {
            color: var(--text);
        }
        .portfolio-thumb {
            width: 80px;
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
        .featured-badge {
            background: rgba(245,158,11,0.2);
            color: #f59e0b;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
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
        .image-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 1rem; margin-top: 0.5rem; border: 1px solid var(--border); }
        .btn-remove-img { background: var(--danger); border: none; border-radius: 1rem; font-size: 0.7rem; padding: 2px 6px; margin-top: 5px; color: white; }
        @media (max-width: 768px) {
            .sidebar { width: 80px; left: 10px; }
            .main { margin-left: 100px; }
            .portfolio-table th, .portfolio-table td { padding: 8px; font-size: 0.75rem; }
            .portfolio-thumb { width: 50px; height: 40px; }
            .btn-sm-custom { font-size: 0.65rem; padding: 0.2rem 0.5rem; }
        }
        .toast-container { z-index: 1100; }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: var(--muted); }
        .desc-truncate { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="main" id="main">
    <div class="topbar">
        <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search by title, client or description..."></div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon"></i></button>
            <div class="profile-img"><i class="fas fa-user-astronaut"></i></div>
            <div><strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong><br><small style="color:var(--muted)">Admin</small></div>
        </div>
    </div>

    <div class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
            <h3><i class="fas fa-briefcase me-2"></i> Portfolio Management</h3>
            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addPortfolioModal"><i class="fas fa-plus me-2"></i>Add Portfolio</button>
        </div>
        <div class="table-responsive">
            <table class="portfolio-table w-100" id="portfoliosTable">
                <thead>
                    <tr><th>Image</th><th>ID</th><th>Title / Description</th><th>Client</th><th>Featured</th><th>Order</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($portfolios as $p): ?>
                    <tr data-id="<?php echo $p['id']; ?>">
                        <td>
                            <?php if($p['image_url']): ?>
                                <img src="../<?php echo htmlspecialchars($p['image_url']); ?>" class="portfolio-thumb" alt="portfolio">
                            <?php else: ?>
                                <i class="fas fa-image fa-2x" style="color:var(--muted);"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $p['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($p['title']); ?></strong><br>
                            <span class="desc-truncate" title="<?php echo htmlspecialchars($p['description']); ?>"><?php echo htmlspecialchars(substr($p['description'], 0, 80)) . (strlen($p['description']) > 80 ? '…' : ''); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($p['client'] ?: '—'); ?></td>
                        <td><?php if($p['featured']): ?><span class="featured-badge">⭐ Featured</span><?php else: ?>—<?php endif; ?></td>
                        <td><?php echo $p['order_position']; ?></td>
                        <td><span class="status-badge <?php echo $p['status'] ? '' : 'inactive'; ?>"><?php echo $p['status'] ? 'Active' : 'Inactive'; ?></span></td>
                        <td>
                            <button class="edit-btn btn-sm-custom" data-id="<?php echo $p['id']; ?>" 
                                data-title="<?php echo htmlspecialchars($p['title'], ENT_QUOTES); ?>"
                                data-client="<?php echo htmlspecialchars($p['client'], ENT_QUOTES); ?>"
                                data-description="<?php echo htmlspecialchars($p['description'], ENT_QUOTES); ?>"
                                data-image="<?php echo htmlspecialchars($p['image_url'], ENT_QUOTES); ?>"
                                data-featured="<?php echo $p['featured']; ?>"
                                data-order="<?php echo $p['order_position']; ?>"
                                data-status="<?php echo $p['status']; ?>"><i class="fas fa-edit"></i> Edit</button>
                            <button class="toggle-featured btn-sm-custom" data-id="<?php echo $p['id']; ?>" style="border-color:var(--warning); color:var(--warning);"><i class="fas fa-star"></i> Featured</button>
                            <button class="toggle-status btn-sm-custom" data-id="<?php echo $p['id']; ?>"><i class="fas fa-sync-alt"></i> Toggle</button>
                            <button class="delete-btn btn-sm-custom" data-id="<?php echo $p['id']; ?>" style="border-color:var(--danger); color:var(--danger);"><i class="fas fa-trash"></i> Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="footer">© 2025 NEXORA AI | Portfolio Management System</div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<!-- Add Portfolio Modal -->
<div class="modal fade" id="addPortfolioModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add New Portfolio</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="addPortfolioForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Title *</label><input type="text" id="addTitle" class="form-control" required minlength="2"></div>
                        <div class="col-md-6 mb-3"><label>Client</label><input type="text" id="addClient" class="form-control"></div>
                        <div class="col-md-12 mb-3"><label>Description *</label><textarea id="addDescription" rows="3" class="form-control" required minlength="5"></textarea></div>
                        <div class="col-md-6 mb-3"><label>Order Position</label><input type="number" id="addOrder" class="form-control" value="0"></div>
                        <div class="col-md-6 mb-3"><label>Portfolio Image</label><input type="file" id="addImage" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif"><img id="addImagePreview" class="image-preview" style="display:none;"><input type="hidden" id="addImageUrl"><button type="button" id="addRemoveImage" class="btn-remove-img" style="display:none;">Remove image</button></div>
                        <div class="col-md-6 mb-3"><div class="form-check"><input type="checkbox" id="addFeatured" class="form-check-input"><label class="form-check-label">Mark as Featured</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-check"><input type="checkbox" id="addStatus" class="form-check-input" checked><label class="form-check-label">Active</label></div></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-2" id="addSubmitBtn"><span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span> Create Portfolio</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Portfolio Modal -->
<div class="modal fade" id="editPortfolioModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Portfolio</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="editPortfolioForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" id="editId">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Title *</label><input type="text" id="editTitle" class="form-control" required minlength="2"></div>
                        <div class="col-md-6 mb-3"><label>Client</label><input type="text" id="editClient" class="form-control"></div>
                        <div class="col-md-12 mb-3"><label>Description *</label><textarea id="editDescription" rows="3" class="form-control" required minlength="5"></textarea></div>
                        <div class="col-md-6 mb-3"><label>Order Position</label><input type="number" id="editOrder" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Portfolio Image</label><input type="file" id="editImage" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif"><img id="editImagePreview" class="image-preview" style="display:none;"><input type="hidden" id="editImageUrl"><button type="button" id="editRemoveImage" class="btn-remove-img" style="display:none;">Remove image</button></div>
                        <div class="col-md-6 mb-3"><div class="form-check"><input type="checkbox" id="editFeatured" class="form-check-input"><label class="form-check-label">Mark as Featured</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-check"><input type="checkbox" id="editStatus" class="form-check-input"><label class="form-check-label">Active</label></div></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-2" id="editSubmitBtn"><span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span> Update Portfolio</button>
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

    // Enhanced search (title, client, description)
    document.getElementById('searchInput').addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#portfoliosTable tbody tr');
        rows.forEach(row => {
            const title = row.cells[2].querySelector('strong')?.innerText.toLowerCase() || '';
            const client = row.cells[3].innerText.toLowerCase();
            const desc = row.cells[2].querySelector('.desc-truncate')?.getAttribute('title')?.toLowerCase() || '';
            const matches = title.includes(filter) || client.includes(filter) || desc.includes(filter);
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
                    const res = await fetch('manage_portfolios.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
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

    // Add portfolio AJAX
    const addForm = document.getElementById('addPortfolioForm');
    addForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('addSubmitBtn');
        const spinner = btn.querySelector('.spinner-border');
        spinner.classList.remove('d-none');
        btn.disabled = true;
        const formData = new FormData(addForm);
        formData.append('action', 'add_portfolio');
        formData.append('title', document.getElementById('addTitle').value);
        formData.append('client', document.getElementById('addClient').value);
        formData.append('description', document.getElementById('addDescription').value);
        formData.append('order_position', document.getElementById('addOrder').value);
        formData.append('featured', document.getElementById('addFeatured').checked ? 1 : 0);
        formData.append('status', document.getElementById('addStatus').checked ? 1 : 0);
        formData.append('image_url', document.getElementById('addImageUrl').value);
        try {
            const res = await fetch('manage_portfolios.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            if (data.success) {
                showToast(data.message);
                setTimeout(() => location.reload(), 1000);
            } else showToast(data.message, 'danger');
        } catch(e) { showToast('Network error', 'danger'); }
        finally { spinner.classList.add('d-none'); btn.disabled = false; }
    });

    // Edit modal population
    const editModal = new bootstrap.Modal(document.getElementById('editPortfolioModal'));
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editId').value = btn.dataset.id;
            document.getElementById('editTitle').value = btn.dataset.title;
            document.getElementById('editClient').value = btn.dataset.client;
            document.getElementById('editDescription').value = btn.dataset.description;
            document.getElementById('editOrder').value = btn.dataset.order;
            document.getElementById('editFeatured').checked = btn.dataset.featured == 1;
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
    // Edit submit
    const editForm = document.getElementById('editPortfolioForm');
    editForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('editSubmitBtn');
        const spinner = btn.querySelector('.spinner-border');
        spinner.classList.remove('d-none');
        btn.disabled = true;
        const formData = new FormData(editForm);
        formData.append('action', 'edit_portfolio');
        formData.append('id', document.getElementById('editId').value);
        formData.append('title', document.getElementById('editTitle').value);
        formData.append('client', document.getElementById('editClient').value);
        formData.append('description', document.getElementById('editDescription').value);
        formData.append('order_position', document.getElementById('editOrder').value);
        formData.append('featured', document.getElementById('editFeatured').checked ? 1 : 0);
        formData.append('status', document.getElementById('editStatus').checked ? 1 : 0);
        formData.append('image_url', document.getElementById('editImageUrl').value);
        try {
            const res = await fetch('manage_portfolios.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            if (data.success) {
                showToast(data.message);
                setTimeout(() => location.reload(), 1000);
            } else showToast(data.message, 'danger');
        } catch(e) { showToast('Network error', 'danger'); }
        finally { spinner.classList.add('d-none'); btn.disabled = false; }
    });

    // Toggle Featured (no reload, update UI)
    document.querySelectorAll('.toggle-featured').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const formData = new FormData();
            formData.append('action', 'toggle_featured');
            formData.append('id', id);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
            try {
                const res = await fetch('manage_portfolios.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                const data = await res.json();
                if (data.success) {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    const featuredCell = row.cells[4];
                    if (data.featured) featuredCell.innerHTML = '<span class="featured-badge">⭐ Featured</span>';
                    else featuredCell.innerHTML = '—';
                    showToast(`Featured ${data.featured ? 'enabled' : 'disabled'}`, 'success');
                } else showToast(data.message, 'danger');
            } catch(e) { showToast('Error toggling featured', 'danger'); }
        });
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
                const res = await fetch('manage_portfolios.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                const data = await res.json();
                if (data.success) {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    const statusCell = row.cells[6];
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
            if (!confirm('Delete this portfolio permanently? This action cannot be undone.')) return;
            const id = btn.dataset.id;
            const formData = new FormData();
            formData.append('action', 'delete_portfolio');
            formData.append('id', id);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
            try {
                const res = await fetch('manage_portfolios.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                const data = await res.json();
                if (data.success) {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    row.remove();
                    showToast('Portfolio deleted', 'success');
                } else showToast(data.message, 'danger');
            } catch(e) { showToast('Delete failed', 'danger'); }
        });
    });

    // Modal reset on close
    ['addPortfolioModal', 'editPortfolioModal'].forEach(modalId => {
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