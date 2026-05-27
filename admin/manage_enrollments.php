<?php
// admin/manage_enrollments.php - View enrolled students with course details
require_once 'auth.php';
require_once '../config.php';

// Handle AJAX requests for delete and status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_enrollment') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'update_status') {
        $id = intval($_POST['id']);
        $status = $_POST['status'];
        $allowed = ['pending', 'completed', 'failed', 'cancelled'];
        if (in_array($status, $allowed)) {
            $stmt = $pdo->prepare("UPDATE enrollments SET payment_status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
        }
        exit;
    }
}

// Fetch enrollments with course title
$enrollments = $pdo->query("
    SELECT e.*, c.title as course_title 
    FROM enrollments e 
    LEFT JOIN courses c ON e.course_id = c.id 
    ORDER BY e.enrollment_date DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Manage Enrollments | NEXORA AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* ========== NEXORA DASHBOARD STYLES (shared) ========== */
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
        /* Main content area (sidebar handled by navigation.php) */
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
        .enrollment-table {
            width: 100%;
            border-collapse: collapse;
        }
        .enrollment-table th, .enrollment-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .enrollment-table th {
            color: var(--muted);
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .enrollment-table td {
            color: var(--text);
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-pending { background: rgba(245,158,11,0.2); color: #f59e0b; }
        .status-completed { background: rgba(16,185,129,0.2); color: #10b981; }
        .status-failed { background: rgba(239,68,68,0.2); color: #ef4444; }
        .status-cancelled { background: rgba(107,114,128,0.2); color: #9ca3af; }
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
        .status-select {
            background: rgba(255,255,255,0.1);
            border: 1px solid var(--border);
            border-radius: 2rem;
            padding: 0.2rem 0.5rem;
            color: var(--text);
            font-size: 0.75rem;
        }
        @media (max-width: 768px) {
            .main { margin-left: 100px; }
            .enrollment-table th, .enrollment-table td { padding: 8px; font-size: 0.75rem; }
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            color: var(--muted);
        }
        .modal-content {
            background: var(--panel);
            color: var(--text);
            border-radius: 1.5rem;
        }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="main" id="main">
    <div class="topbar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search by name, email or course...">
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon"></i></button>
            <div class="profile-img"><i class="fas fa-user-astronaut"></i></div>
            <div><strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong><br><small style="color:var(--muted)">Admin</small></div>
        </div>
    </div>

    <div class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
            <h3><i class="fas fa-users me-2"></i> Student Enrollments</h3>
            <span class="text-muted">Total: <?php echo count($enrollments); ?></span>
        </div>
        <div class="table-responsive">
            <table class="enrollment-table w-100" id="enrollmentsTable">
                <thead>
                    <tr>
                        <th>ID</th><th>Student</th><th>Course</th><th>Amount</th><th>Status</th><th>Date</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($enrollments as $e): ?>
                    <tr data-id="<?php echo $e['id']; ?>">
                        <td><?php echo $e['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($e['student_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($e['student_email']); ?></small><br>
                            <small><?php echo htmlspecialchars($e['student_phone']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($e['course_title']); ?></td>
                        <td>$<?php echo number_format($e['amount'], 2); ?></td>
                        <td>
                            <select class="status-select status-update" data-id="<?php echo $e['id']; ?>">
                                <option value="pending" <?php echo $e['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?php echo $e['payment_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="failed" <?php echo $e['payment_status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                <option value="cancelled" <?php echo $e['payment_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </td>
                        <td><?php echo date('d M Y', strtotime($e['enrollment_date'])); ?></td>
                        <td>
                            <button class="view-btn btn-sm-custom" data-id="<?php echo $e['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($e['student_name']); ?>"
                                data-email="<?php echo htmlspecialchars($e['student_email']); ?>"
                                data-phone="<?php echo htmlspecialchars($e['student_phone']); ?>"
                                data-address="<?php echo htmlspecialchars($e['student_address']); ?>"
                                data-course="<?php echo htmlspecialchars($e['course_title']); ?>"
                                data-amount="<?php echo $e['amount']; ?>"
                                data-status="<?php echo $e['payment_status']; ?>"
                                data-transaction="<?php echo htmlspecialchars($e['transaction_id']); ?>"
                                data-date="<?php echo date('d M Y, h:i A', strtotime($e['enrollment_date'])); ?>">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="delete-btn btn-sm-custom" data-id="<?php echo $e['id']; ?>" style="border-color:var(--danger); color:var(--danger);"><i class="fas fa-trash"></i> Delete</button>
                         </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            <tr>
        </div>
    </div>
    <div class="footer">© 2025 NEXORA AI | Enrollment Management</div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-graduate me-2"></i>Enrollment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2"><strong>Student Name:</strong> <span id="viewName"></span></div>
                <div class="mb-2"><strong>Email:</strong> <span id="viewEmail"></span></div>
                <div class="mb-2"><strong>Phone:</strong> <span id="viewPhone"></span></div>
                <div class="mb-2"><strong>Address:</strong> <span id="viewAddress"></span></div>
                <div class="mb-2"><strong>Course:</strong> <span id="viewCourse"></span></div>
                <div class="mb-2"><strong>Amount:</strong> <span id="viewAmount"></span></div>
                <div class="mb-2"><strong>Payment Status:</strong> <span id="viewStatus"></span></div>
                <div class="mb-2"><strong>Transaction ID:</strong> <span id="viewTransaction"></span></div>
                <div class="mb-2"><strong>Enrollment Date:</strong> <span id="viewDate"></span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        themeToggle.innerHTML = document.body.classList.contains('light') ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
        location.reload();
    });
    if(document.body.classList.contains('light')) themeToggle.innerHTML = '<i class="fas fa-moon"></i>'; else themeToggle.innerHTML = '<i class="fas fa-sun"></i>';

    // Search filter
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#enrollmentsTable tbody tr');
        rows.forEach(row => {
            const name = row.cells[1].innerText.toLowerCase();
            const course = row.cells[2].innerText.toLowerCase();
            row.style.display = (name.includes(filter) || course.includes(filter)) ? '' : 'none';
        });
    });

    // Update payment status via AJAX
    document.querySelectorAll('.status-update').forEach(select => {
        select.addEventListener('change', async function() {
            const id = this.dataset.id;
            const status = this.value;
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('id', id);
            formData.append('status', status);
            const res = await fetch('manage_enrollments.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            if (data.success) {
                // Update status badge in the row
                const row = this.closest('tr');
                const statusCell = row.cells[4];
                let badgeClass = '';
                if (status === 'pending') badgeClass = 'status-pending';
                else if (status === 'completed') badgeClass = 'status-completed';
                else if (status === 'failed') badgeClass = 'status-failed';
                else badgeClass = 'status-cancelled';
                statusCell.innerHTML = `<span class="status-badge ${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
                // Also update the select value to match (already selected)
            } else {
                alert('Update failed');
            }
        });
    });

    // View modal
    const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('viewName').innerText = btn.dataset.name;
            document.getElementById('viewEmail').innerText = btn.dataset.email;
            document.getElementById('viewPhone').innerText = btn.dataset.phone || 'Not provided';
            document.getElementById('viewAddress').innerText = btn.dataset.address || 'Not provided';
            document.getElementById('viewCourse').innerText = btn.dataset.course;
            document.getElementById('viewAmount').innerText = '$' + parseFloat(btn.dataset.amount).toFixed(2);
            let status = btn.dataset.status;
            let statusText = status.charAt(0).toUpperCase() + status.slice(1);
            document.getElementById('viewStatus').innerHTML = `<span class="status-badge status-${status}">${statusText}</span>`;
            document.getElementById('viewTransaction').innerText = btn.dataset.transaction || 'N/A';
            document.getElementById('viewDate').innerText = btn.dataset.date;
            viewModal.show();
        });
    });

    // Delete with confirmation
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Delete this enrollment permanently?')) return;
            const id = btn.dataset.id;
            const formData = new FormData();
            formData.append('action', 'delete_enrollment');
            formData.append('id', id);
            const res = await fetch('manage_enrollments.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            if (data.success) location.reload();
        });
    });
</script>
</body>
</html>