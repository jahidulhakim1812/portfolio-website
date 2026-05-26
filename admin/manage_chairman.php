<?php
// admin/manage_chairman.php - Manage chairman speech (image, signature, text)
require_once 'auth.php';
require_once '../config.php';

// Fetch current active chairman speech record
$speech = $pdo->query("SELECT * FROM chairman_speech WHERE is_active = 1 LIMIT 1")->fetch();
if (!$speech) {
    // Create a default if none exists
    $pdo->exec("INSERT INTO chairman_speech (speech_text, image_url, chairman_name, title, is_active) VALUES ('Welcome to AR Tech Solutions...', 'https://randomuser.me/api/portraits/men/32.jpg', 'John Carter', 'Chairman & Founder', 1)");
    $speech = $pdo->query("SELECT * FROM chairman_speech WHERE is_active = 1 LIMIT 1")->fetch();
}

// Handle AJAX requests (update, upload image, upload signature)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'update_speech') {
        $speech_text = trim($_POST['speech_text']);
        $chairman_name = trim($_POST['chairman_name']);
        $title = trim($_POST['title']);
        $image_url = trim($_POST['image_url']);
        $signature_url = trim($_POST['signature_url']);
        
        if ($speech_text && $chairman_name) {
            $stmt = $pdo->prepare("UPDATE chairman_speech SET speech_text = ?, chairman_name = ?, title = ?, image_url = ?, signature_url = ? WHERE is_active = 1");
            $stmt->execute([$speech_text, $chairman_name, $title, $image_url, $signature_url]);
            echo json_encode(['success' => true, 'message' => 'Chairman speech updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Speech text and chairman name are required']);
        }
        exit;
    }

    if ($action === 'upload_image') {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/chairman/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                echo json_encode(['success' => true, 'image_url' => 'uploads/chairman/' . $fileName]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        }
        exit;
    }

    if ($action === 'upload_signature') {
        if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/chairman/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION);
            $fileName = 'signature_' . time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['signature']['tmp_name'], $targetPath)) {
                echo json_encode(['success' => true, 'signature_url' => 'uploads/chairman/' . $fileName]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload signature']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
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
    <title>Manage Chairman Speech | NEXORA AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* ========== NEXORA DASHBOARD STYLES (same as other manage pages) ========== */
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
        .form-group { margin-bottom: 1.2rem; }
        .form-control, .form-select { background: rgba(255,255,255,0.1); border: 1px solid var(--border); color: var(--text); border-radius: 1rem; padding: 0.75rem 1rem; }
        .form-control:focus { background: rgba(255,255,255,0.15); color: var(--text); box-shadow: none; border-color: var(--primary); }
        .image-preview { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; margin-top: 0.5rem; border: 2px solid var(--primary); background: rgba(255,255,255,0.1); }
        .signature-preview { max-width: 200px; max-height: 80px; object-fit: contain; margin-top: 0.5rem; border: 1px solid var(--border); background: rgba(255,255,255,0.05); }
        .btn-primary-custom { background: linear-gradient(95deg, var(--primary), var(--secondary)); border: none; padding: 0.6rem 1.5rem; border-radius: 2rem; font-weight: 600; color: white; transition: 0.2s; }
        .btn-primary-custom:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(124,58,237,0.3); }
        @media (max-width: 768px) {
            .sidebar { width: 80px; left: 10px; }
            .main { margin-left: 100px; }
            .image-preview { width: 80px; height: 80px; }
        }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: var(--muted); }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="main" id="main">
    <div class="topbar">
        <div class="search-box"><i class="fas fa-search"></i><input type="text" placeholder="Search... (disabled for this page)"></div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon"></i></button>
            <div class="profile-img"><i class="fas fa-user-astronaut"></i></div>
            <div><strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong><br><small style="color:var(--muted)">Admin</small></div>
        </div>
    </div>

    <div class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
            <h3><i class="fas fa-microphone-alt me-2"></i> Chairman Speech Management</h3>
        </div>
        <form id="chairmanSpeechForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Chairman Name *</label>
                        <input type="text" id="chairmanName" class="form-control" value="<?php echo htmlspecialchars($speech['chairman_name']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Title (e.g., Chairman & Founder)</label>
                        <input type="text" id="chairmanTitle" class="form-control" value="<?php echo htmlspecialchars($speech['title']); ?>">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label>Speech Text *</label>
                        <textarea id="speechText" rows="8" class="form-control" required><?php echo htmlspecialchars($speech['speech_text']); ?></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Chairman Photo</label>
                        <input type="file" id="chairmanImage" class="form-control" accept="image/*">
                        <img id="chairmanImagePreview" class="image-preview" src="<?php echo htmlspecialchars($speech['image_url']); ?>" alt="Chairman">
                        <input type="hidden" id="chairmanImageUrl" value="<?php echo htmlspecialchars($speech['image_url']); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Signature Image (optional)</label>
                        <input type="file" id="signatureImage" class="form-control" accept="image/*">
                        <img id="signaturePreview" class="signature-preview" src="<?php echo htmlspecialchars($speech['signature_url']); ?>" alt="Signature">
                        <input type="hidden" id="signatureUrl" value="<?php echo htmlspecialchars($speech['signature_url']); ?>">
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary-custom">Save Changes</button>
                <div id="updateMessage" class="mt-2"></div>
            </div>
        </form>
    </div>
    <div class="footer">© 2025 NEXORA AI | Chairman Speech Management</div>
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
    if (localStorage.getItem('nexoraTheme') === 'light') document.body.classList.add('light');
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('light');
        localStorage.setItem('nexoraTheme', document.body.classList.contains('light') ? 'light' : 'dark');
        themeToggle.innerHTML = document.body.classList.contains('light') ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
        location.reload();
    });
    if(document.body.classList.contains('light')) themeToggle.innerHTML = '<i class="fas fa-moon"></i>'; else themeToggle.innerHTML = '<i class="fas fa-sun"></i>';

    // Image upload helper
    function setupImageUpload(fileInput, previewImg, hiddenInput, uploadAction) {
        fileInput.addEventListener('change', async function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => { previewImg.src = e.target.result; previewImg.style.display = 'block'; };
                reader.readAsDataURL(this.files[0]);
                const formData = new FormData();
                formData.append('action', uploadAction);
                formData.append(uploadAction === 'upload_image' ? 'image' : 'signature', this.files[0]);
                const res = await fetch('manage_chairman.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                const data = await res.json();
                if (data.success) {
                    hiddenInput.value = uploadAction === 'upload_image' ? data.image_url : data.signature_url;
                } else {
                    alert('Upload failed: ' + data.message);
                }
            }
        });
    }

    setupImageUpload(document.getElementById('chairmanImage'), document.getElementById('chairmanImagePreview'), document.getElementById('chairmanImageUrl'), 'upload_image');
    setupImageUpload(document.getElementById('signatureImage'), document.getElementById('signaturePreview'), document.getElementById('signatureUrl'), 'upload_signature');

    // Save form via AJAX
    document.getElementById('chairmanSpeechForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'update_speech');
        formData.append('speech_text', document.getElementById('speechText').value);
        formData.append('chairman_name', document.getElementById('chairmanName').value);
        formData.append('title', document.getElementById('chairmanTitle').value);
        formData.append('image_url', document.getElementById('chairmanImageUrl').value);
        formData.append('signature_url', document.getElementById('signatureUrl').value);
        const res = await fetch('manage_chairman.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
        const data = await res.json();
        const msgDiv = document.getElementById('updateMessage');
        if (data.success) {
            msgDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
            setTimeout(() => msgDiv.innerHTML = '', 3000);
        } else {
            msgDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    });
</script>
</body>
</html>