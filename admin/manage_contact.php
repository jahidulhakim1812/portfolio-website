<?php
// admin/manage_contact.php - Complete contact message management with perfect message details modal
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

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $action = $_POST['action'] ?? '';
    $writeActions = ['mark_read', 'delete_message'];
    
    if (in_array($action, $writeActions)) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            jsonResponse(false, 'Security validation failed. Please refresh the page.');
        }
    }

    if ($action === 'mark_read') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(true, 'Message marked as read');
    }

    if ($action === 'delete_message') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(true, 'Message deleted');
    }
    exit;
}

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Contact Messages | NEXORA AI</title>
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
        .contact-table {
            width: 100%;
            border-collapse: collapse;
        }
        .contact-table th, .contact-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .contact-table th {
            color: var(--muted);
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .contact-table td {
            color: var(--text);
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
        .status-badge.unread {
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
        /* Enhanced Modal Styles */
        .modal-content {
            background: var(--panel);
            color: var(--text);
            border-radius: 1.5rem;
            border: 1px solid var(--border);
            overflow: hidden;
        }
        .modal-header {
            border-bottom: 1px solid var(--border);
            background: rgba(124,58,237,0.1);
            padding: 1.25rem 1.5rem;
        }
        .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        body.light .modal-header .btn-close {
            filter: invert(0);
        }
        .modal-body {
            padding: 1.5rem;
        }
        .detail-card {
            background: rgba(255,255,255,0.03);
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border);
        }
        .detail-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: 0.25rem;
        }
        .detail-value {
            font-size: 1rem;
            font-weight: 500;
            word-break: break-word;
        }
        .message-content {
            background: rgba(0,0,0,0.2);
            border-radius: 1rem;
            padding: 1.25rem;
            margin-top: 0.5rem;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 400px;
            overflow-y: auto;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        .action-btn {
            background: rgba(124,58,237,0.15);
            border: 1px solid var(--primary);
            color: var(--primary);
            border-radius: 2rem;
            padding: 0.4rem 1rem;
            font-size: 0.8rem;
            transition: 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .action-btn:hover {
            background: var(--primary);
            color: white;
        }
        .copy-success {
            background: var(--success);
            color: white;
            border-color: var(--success);
        }
        @media (max-width: 768px) {
            .sidebar { width: 80px; left: 10px; }
            .main { margin-left: 100px; }
            .contact-table th, .contact-table td { padding: 8px; font-size: 0.75rem; }
            .btn-sm-custom { font-size: 0.65rem; padding: 0.2rem 0.5rem; }
            .action-btn { font-size: 0.7rem; padding: 0.3rem 0.7rem; }
        }
        .toast-container { z-index: 1100; }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: var(--muted); }
        .message-preview { max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="main" id="main">
    <div class="topbar">
        <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search by name, email or phone..."></div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon"></i></button>
            <div class="profile-img"><i class="fas fa-user-astronaut"></i></div>
            <div><strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong><br><small style="color:var(--muted)">Admin</small></div>
        </div>
    </div>

    <div class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
            <h3><i class="fas fa-envelope me-2"></i> Contact Messages</h3>
            <span class="text-muted" id="totalCount">Total: <?php echo count($messages); ?></span>
        </div>
        <div class="table-responsive">
            <table class="contact-table w-100" id="contactTable">
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Message</th><th>Date</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($messages as $msg): ?>
                    <tr data-id="<?php echo $msg['id']; ?>">
                        <td><?php echo $msg['id']; ?></td>
                        <td><?php echo htmlspecialchars($msg['name']); ?></td>
                        <td><?php echo htmlspecialchars($msg['email']); ?></td>
                        <td><?php echo htmlspecialchars($msg['phone'] ?: '—'); ?></td>
                        <td class="message-preview" title="<?php echo htmlspecialchars($msg['message']); ?>"><?php echo htmlspecialchars(substr($msg['message'], 0, 80)) . (strlen($msg['message']) > 80 ? '…' : ''); ?></td>
                        <td><?php echo date('d M Y, h:i A', strtotime($msg['created_at'])); ?></td>
                        <td><span class="status-badge <?php echo $msg['is_read'] ? '' : 'unread'; ?>"><?php echo $msg['is_read'] ? 'Read' : 'Unread'; ?></span></td>
                        <td>
                            <?php if(!$msg['is_read']): ?>
                            <button class="mark-read-btn btn-sm-custom" data-id="<?php echo $msg['id']; ?>"><i class="fas fa-check"></i> Mark Read</button>
                            <?php endif; ?>
                            <button class="delete-btn btn-sm-custom" data-id="<?php echo $msg['id']; ?>" style="border-color:var(--danger); color:var(--danger);"><i class="fas fa-trash"></i> Delete</button>
                            <button class="view-btn btn-sm-custom" data-id="<?php echo $msg['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($msg['name'], ENT_QUOTES); ?>"
                                data-email="<?php echo htmlspecialchars($msg['email'], ENT_QUOTES); ?>"
                                data-phone="<?php echo htmlspecialchars($msg['phone'], ENT_QUOTES); ?>"
                                data-message="<?php echo htmlspecialchars($msg['message'], ENT_QUOTES); ?>"
                                data-date="<?php echo date('d M Y, h:i A', strtotime($msg['created_at'])); ?>"><i class="fas fa-eye"></i> View</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="footer">© 2025 NEXORA AI | Contact Messages</div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<!-- Enhanced Message Details Modal -->
<div class="modal fade" id="viewMessageModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-envelope-open-text me-2"></i>Message Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Sender Information Cards -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="detail-card">
                            <div class="detail-label"><i class="fas fa-user me-1"></i> Name</div>
                            <div class="detail-value" id="viewName"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-card">
                            <div class="detail-label"><i class="fas fa-calendar-alt me-1"></i> Date & Time</div>
                            <div class="detail-value" id="viewDate"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-card">
                            <div class="detail-label"><i class="fas fa-envelope me-1"></i> Email Address</div>
                            <div class="detail-value" id="viewEmail"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-card">
                            <div class="detail-label"><i class="fas fa-phone-alt me-1"></i> Phone Number</div>
                            <div class="detail-value" id="viewPhone"></div>
                        </div>
                    </div>
                </div>

                <!-- Message Content -->
                <div class="detail-card" style="margin-top: 0.5rem;">
                    <div class="detail-label"><i class="fas fa-comment-dots me-1"></i> Message</div>
                    <div class="message-content" id="viewMessage"></div>
                </div>

                <!-- Quick Action Buttons -->
                <div class="action-buttons">
                    <button class="action-btn" id="replyBtn"><i class="fas fa-reply"></i> Reply</button>
                    <button class="action-btn" id="copyEmailBtn"><i class="fas fa-copy"></i> Copy Email</button>
                    <button class="action-btn" id="copyPhoneBtn"><i class="fas fa-copy"></i> Copy Phone</button>
                    <button class="action-btn" id="markReadFromModal" style="display: none;"><i class="fas fa-check-circle"></i> Mark as Read</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

    // Copy to clipboard helper
    async function copyToClipboard(text, button, successMsg) {
        try {
            await navigator.clipboard.writeText(text);
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Copied!';
            button.classList.add('copy-success');
            setTimeout(() => {
                button.innerHTML = originalHtml;
                button.classList.remove('copy-success');
            }, 2000);
            showToast(successMsg, 'success');
        } catch (err) {
            showToast('Failed to copy', 'danger');
        }
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

    // Enhanced search
    document.getElementById('searchInput').addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#contactTable tbody tr');
        rows.forEach(row => {
            const name = row.cells[1].innerText.toLowerCase();
            const email = row.cells[2].innerText.toLowerCase();
            const phone = row.cells[3].innerText.toLowerCase();
            row.style.display = (name.includes(filter) || email.includes(filter) || phone.includes(filter)) ? '' : 'none';
        });
    });

    const csrfToken = '<?php echo $csrf_token; ?>';

    // Mark as read inline
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const row = document.querySelector(`tr[data-id="${id}"]`);
            const statusCell = row.cells[6];
            const actionsCell = row.cells[7];
            const formData = new FormData();
            formData.append('action', 'mark_read');
            formData.append('id', id);
            formData.append('csrf_token', csrfToken);
            try {
                const res = await fetch('manage_contact.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                const data = await res.json();
                if (data.success) {
                    statusCell.innerHTML = '<span class="status-badge">Read</span>';
                    const markBtn = actionsCell.querySelector('.mark-read-btn');
                    if (markBtn) markBtn.remove();
                    showToast(data.message, 'success');
                } else showToast(data.message, 'danger');
            } catch(e) { showToast('Network error', 'danger'); }
        });
    });

    // Delete inline
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Delete this message permanently? This action cannot be undone.')) return;
            const id = btn.dataset.id;
            const row = document.querySelector(`tr[data-id="${id}"]`);
            const formData = new FormData();
            formData.append('action', 'delete_message');
            formData.append('id', id);
            formData.append('csrf_token', csrfToken);
            try {
                const res = await fetch('manage_contact.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                const data = await res.json();
                if (data.success) {
                    row.remove();
                    const totalSpan = document.getElementById('totalCount');
                    const currentTotal = parseInt(totalSpan.innerText.replace('Total: ', ''));
                    totalSpan.innerText = `Total: ${currentTotal - 1}`;
                    showToast(data.message, 'success');
                } else showToast(data.message, 'danger');
            } catch(e) { showToast('Delete failed', 'danger'); }
        });
    });

    // View modal with enhanced interactions
    const viewModal = new bootstrap.Modal(document.getElementById('viewMessageModal'));
    let currentMessageId = null;
    let currentEmail = '';
    let currentPhone = '';
    let currentName = '';
    let isRead = false;

    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            currentMessageId = btn.dataset.id;
            currentName = btn.dataset.name;
            currentEmail = btn.dataset.email;
            currentPhone = btn.dataset.phone || '';
            const message = btn.dataset.message;
            const date = btn.dataset.date;
            const statusCell = document.querySelector(`tr[data-id="${currentMessageId}"] .status-badge`);
            isRead = statusCell ? !statusCell.classList.contains('unread') : true;

            document.getElementById('viewName').innerText = currentName;
            document.getElementById('viewEmail').innerText = currentEmail;
            document.getElementById('viewPhone').innerText = currentPhone || 'Not provided';
            document.getElementById('viewDate').innerText = date;
            document.getElementById('viewMessage').innerText = message;

            // Show/hide mark as read button in modal
            const markReadModalBtn = document.getElementById('markReadFromModal');
            if (!isRead) {
                markReadModalBtn.style.display = 'inline-flex';
            } else {
                markReadModalBtn.style.display = 'none';
            }

            viewModal.show();
        });
    });

    // Reply button - opens mail client
    document.getElementById('replyBtn').addEventListener('click', () => {
        if (currentEmail) {
            window.location.href = `mailto:${currentEmail}?subject=Re: Contact from ${currentName}`;
        } else {
            showToast('No email address available', 'danger');
        }
    });

    // Copy email
    document.getElementById('copyEmailBtn').addEventListener('click', async () => {
        if (currentEmail) {
            await copyToClipboard(currentEmail, document.getElementById('copyEmailBtn'), 'Email copied to clipboard');
        } else {
            showToast('No email to copy', 'danger');
        }
    });

    // Copy phone
    document.getElementById('copyPhoneBtn').addEventListener('click', async () => {
        if (currentPhone && currentPhone !== 'Not provided') {
            await copyToClipboard(currentPhone, document.getElementById('copyPhoneBtn'), 'Phone number copied');
        } else {
            showToast('No phone number to copy', 'danger');
        }
    });

    // Mark as read from modal
    document.getElementById('markReadFromModal').addEventListener('click', async () => {
        if (!currentMessageId) return;
        const formData = new FormData();
        formData.append('action', 'mark_read');
        formData.append('id', currentMessageId);
        formData.append('csrf_token', csrfToken);
        try {
            const res = await fetch('manage_contact.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            if (data.success) {
                // Update table row status
                const row = document.querySelector(`tr[data-id="${currentMessageId}"]`);
                if (row) {
                    const statusCell = row.cells[6];
                    statusCell.innerHTML = '<span class="status-badge">Read</span>';
                    const actionsCell = row.cells[7];
                    const markBtn = actionsCell.querySelector('.mark-read-btn');
                    if (markBtn) markBtn.remove();
                }
                // Hide button in modal
                document.getElementById('markReadFromModal').style.display = 'none';
                showToast('Message marked as read', 'success');
                isRead = true;
            } else showToast(data.message, 'danger');
        } catch(e) { showToast('Error', 'danger'); }
    });

    // When modal closes, reset any temporary styles
    document.getElementById('viewMessageModal').addEventListener('hidden.bs.modal', () => {
        // Reset copy buttons text if they were changed
        const copyEmailBtn = document.getElementById('copyEmailBtn');
        const copyPhoneBtn = document.getElementById('copyPhoneBtn');
        if (copyEmailBtn.classList.contains('copy-success')) {
            copyEmailBtn.innerHTML = '<i class="fas fa-copy"></i> Copy Email';
            copyEmailBtn.classList.remove('copy-success');
        }
        if (copyPhoneBtn.classList.contains('copy-success')) {
            copyPhoneBtn.innerHTML = '<i class="fas fa-copy"></i> Copy Phone';
            copyPhoneBtn.classList.remove('copy-success');
        }
    });
</script>
</body>
</html>