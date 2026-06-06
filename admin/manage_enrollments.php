<?php
// admin/manage_enrollments.php - Fixed PHPMailer inclusion
require_once 'auth.php';
require_once '../config.php';

// ========== EMAIL CONFIGURATION ==========
$EMAIL_METHOD = 'phpmailer'; // 'phpmailer' or 'mail'
$SMTP_DEBUG = false; // Set to true to see detailed errors

// Gmail SMTP settings
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_auth = true;
$smtp_username = 'artechsolution.online@gmail.com';
$smtp_password = 'giwr wrcr mnyi lkpf'; // Your App Password
$smtp_encryption = 'tls';
$smtp_from_email = 'artechsolution.online@gmail.com';
$smtp_from_name = 'AR Tech Solutions';

$admin_notify_email = 'artechsolution.online@gmail.com';

// Server mail() fallback
$mail_from_email = 'noreply@artechsolutions.com';
$mail_from_name = 'AR Tech Solutions';

// ========== PHPMailer Explicit Include ==========
// Adjust these paths to match your PHPMailer folder structure in the root directory
$phpmailer_base = '../PHPMailer/'; // Go up from admin/ to root, then into PHPMailer folder

if (file_exists($phpmailer_base . 'src/PHPMailer.php')) {
    require_once $phpmailer_base . 'src/PHPMailer.php';
    require_once $phpmailer_base . 'src/SMTP.php';
    require_once $phpmailer_base . 'src/Exception.php';
    $phpmailer_loaded = true;
} elseif (file_exists($phpmailer_base . 'PHPMailerAutoload.php')) {
    // Old version (pre-6.0)
    require_once $phpmailer_base . 'PHPMailerAutoload.php';
    $phpmailer_loaded = true;
} else {
    // Try alternative common paths
    $alt_paths = [
        '../vendor/autoload.php',
        '../PHPMailer/PHPMailer-master/src/PHPMailer.php'
    ];
    $phpmailer_loaded = false;
    foreach ($alt_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $phpmailer_loaded = true;
            break;
        }
    }
}

if (!$phpmailer_loaded) {
    // Fallback to server mail()
    $EMAIL_METHOD = 'mail';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// ========================================

// Email function (returns array with success and message)
function sendPaymentEmails($student_email, $student_name, $course_title, $amount, $transaction_id, $debug = false) {
    global $EMAIL_METHOD, $smtp_host, $smtp_port, $smtp_auth, $smtp_username, $smtp_password, $smtp_encryption, $smtp_from_email, $smtp_from_name, $mail_from_email, $mail_from_name, $admin_notify_email, $SMTP_DEBUG;
    
    $student_subject = "Payment Confirmed – Enrollment Successful | AR Tech Solutions";
    $admin_subject = "Payment Completed: $student_name - $course_title";
    
    $student_message = buildEmailBody($student_name, $course_title, $amount, $transaction_id);
    $admin_message = buildAdminEmailBody($student_name, $student_email, $course_title, $amount, $transaction_id);
    
    $errors = [];
    $success = false;
    
    if ($EMAIL_METHOD === 'phpmailer' && class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = $smtp_auth;
            $mail->Username   = $smtp_username;
            $mail->Password   = $smtp_password;
            $mail->SMTPSecure = $smtp_encryption;
            $mail->Port       = $smtp_port;
            if ($SMTP_DEBUG || $debug) {
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = function($str, $level) use (&$errors) { $errors[] = $str; };
            }
            $mail->setFrom($smtp_from_email, $smtp_from_name);
            $mail->addAddress($student_email, $student_name);
            $mail->isHTML(true);
            $mail->Subject = $student_subject;
            $mail->Body    = $student_message;
            $mail->send();
            
            // Send to admin
            $mail->clearAddresses();
            $mail->addAddress($admin_notify_email, 'Admin');
            $mail->Subject = $admin_subject;
            $mail->Body    = $admin_message;
            $mail->send();
            
            $success = true;
        } catch (Exception $e) {
            $errors[] = 'PHPMailer Error: ' . $mail->ErrorInfo;
            $success = false;
        } catch (Throwable $e) {
            $errors[] = 'Exception: ' . $e->getMessage();
            $success = false;
        }
    }
    
    // Fallback to mail()
    if (!$success && $EMAIL_METHOD !== 'phpmailer') {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: $mail_from_name <$mail_from_email>\r\n";
        $headers .= "Reply-To: support@artechsolutions.com\r\n";
        
        $to_student = @mail($student_email, $student_subject, $student_message, $headers);
        $to_admin = @mail($admin_notify_email, $admin_subject, $admin_message, $headers);
        $success = ($to_student && $to_admin);
        if (!$success) $errors[] = 'mail() function failed. Check server mail configuration.';
    }
    
    return ['success' => $success, 'errors' => $errors];
}

function buildEmailBody($student_name, $course_title, $amount, $transaction_id) {
    return "
        <html>
        <head><title>Payment Confirmed</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #7c3aed, #06b6d4); padding: 20px; text-align: center; color: white; border-radius: 10px 10px 0 0; }
            .content { background: #f9fafb; padding: 20px; border-radius: 0 0 10px 10px; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #6c757d; }
        </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>AR Tech Solutions</h2>
                    <p>Payment Confirmation</p>
                </div>
                <div class='content'>
                    <p>Dear <strong>$student_name</strong>,</p>
                    <p>Your payment for <strong>$course_title</strong> has been successfully verified.</p>
                    <p><strong>Amount:</strong> $$amount</p>
                    <p><strong>Transaction ID:</strong> $transaction_id</p>
                    <p>You now have full access to the course.</p>
                    <p>Thank you for choosing AR Tech Solutions!</p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " AR Tech Solutions. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
    ";
}

function buildAdminEmailBody($student_name, $student_email, $course_title, $amount, $transaction_id) {
    return "
        <html>
        <head><title>Payment Completed Notification</title></head>
        <body>
            <h3>Payment Completed</h3>
            <p><strong>Student:</strong> $student_name</p>
            <p><strong>Email:</strong> $student_email</p>
            <p><strong>Course:</strong> $course_title</p>
            <p><strong>Amount:</strong> $$amount</p>
            <p><strong>Transaction ID:</strong> $transaction_id</p>
            <p>Status has been updated to <strong>Completed</strong>.</p>
        </body>
        </html>
    ";
}

// AJAX handlers (same as before)
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
            $paidAt = ($status === 'completed') ? date('Y-m-d H:i:s') : null;
            $stmt = $pdo->prepare("UPDATE enrollments SET payment_status = ?, paid_at = ? WHERE id = ?");
            $stmt->execute([$status, $paidAt, $id]);

            if ($status === 'completed') {
                $enroll = $pdo->prepare("SELECT e.*, c.title as course_title FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.id = ?");
                $enroll->execute([$id]);
                $enrollData = $enroll->fetch();
                if ($enrollData && !empty($enrollData['student_email'])) {
                    $emailResult = sendPaymentEmails(
                        $enrollData['student_email'],
                        $enrollData['student_name'],
                        $enrollData['course_title'],
                        $enrollData['amount'],
                        $enrollData['transaction_id']
                    );
                    if (!$emailResult['success']) {
                        echo json_encode(['success' => true, 'paid_at' => $paidAt, 'email_warning' => 'Status updated but email failed: ' . implode(', ', $emailResult['errors'])]);
                        exit;
                    }
                }
            }
            echo json_encode(['success' => true, 'paid_at' => $paidAt]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
        }
        exit;
    }
    
    if ($action === 'resend_email') {
        $id = intval($_POST['id']);
        $enroll = $pdo->prepare("SELECT e.*, c.title as course_title FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.id = ?");
        $enroll->execute([$id]);
        $enrollData = $enroll->fetch();
        if ($enrollData && $enrollData['payment_status'] === 'completed') {
            $result = sendPaymentEmails(
                $enrollData['student_email'],
                $enrollData['student_name'],
                $enrollData['course_title'],
                $enrollData['amount'],
                $enrollData['transaction_id']
            );
            echo json_encode(['success' => $result['success'], 'message' => $result['success'] ? 'Email sent successfully' : 'Email failed: ' . implode(', ', $result['errors'])]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Enrollment not found or not completed']);
        }
        exit;
    }
    
    if ($action === 'test_email') {
        $result = sendPaymentEmails($admin_notify_email, 'Test Admin', 'Test Course', 99.99, 'TEST123', true);
        echo json_encode(['success' => $result['success'], 'message' => $result['success'] ? 'Test email sent to admin' : 'Failed: ' . implode(', ', $result['errors'])]);
        exit;
    }
}

// Fetch all enrollments (same as before)
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Enrollments | AR Tech Solutions</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --bg: #050816; --panel: #0f172a; --primary: #7c3aed; --text: #ffffff; --muted: #94a3b8; --border: rgba(255,255,255,0.08); --danger: #ef4444; --success: #10b981; }
        body.light { --bg: #f8fafc; --panel: #ffffff; --text: #0f172a; --muted: #475569; --border: rgba(0,0,0,0.08); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); transition: all 0.3s ease; overflow-x: hidden; }
        .main { margin-left: 310px; padding: 20px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .search-box { position: relative; width: 320px; }
        .search-box input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 2rem; padding: 12px 20px 12px 45px; color: var(--text); }
        .search-box i { position: absolute; left: 18px; top: 15px; color: var(--muted); }
        .theme-toggle { background: rgba(255,255,255,0.1); border: none; border-radius: 2rem; width: 45px; height: 45px; cursor: pointer; color: var(--text); }
        .profile-img { width: 48px; height: 48px; border-radius: 1.2rem; background: var(--primary); display: flex; align-items: center; justify-content: center; }
        .panel { background: rgba(255,255,255,0.03); border-radius: 1.8rem; padding: 1.5rem; border: 1px solid var(--border); }
        .enrollment-table { width: 100%; border-collapse: collapse; }
        .enrollment-table th, .enrollment-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .status-badge { padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; display: inline-block; }
        .status-pending { background: rgba(245,158,11,0.2); color: #f59e0b; }
        .status-completed { background: rgba(16,185,129,0.2); color: #10b981; }
        .status-failed { background: rgba(239,68,68,0.2); color: #ef4444; }
        .status-cancelled { background: rgba(107,114,128,0.2); color: #9ca3af; }
        .btn-sm-custom { background: transparent; border: 1px solid var(--primary); color: var(--primary); border-radius: 2rem; padding: 0.3rem 0.8rem; font-size: 0.75rem; transition: 0.2s; margin: 2px; text-decoration: none; display: inline-block; }
        .btn-sm-custom:hover { background: var(--primary); color: white; }
        .btn-sm-email { border-color: var(--success); color: var(--success); }
        .btn-sm-test { border-color: #f59e0b; color: #f59e0b; }
        .status-select { background: rgba(255,255,255,0.1); border: 1px solid var(--border); border-radius: 2rem; padding: 0.2rem 0.5rem; color: var(--text); font-size: 0.75rem; }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: var(--muted); }
        .modal-content { background: var(--panel); color: var(--text); border-radius: 1.5rem; }
        @media (max-width: 768px) { .main { margin-left: 100px; } }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="main" id="main">
    <div class="topbar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search...">
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon"></i></button>
            <button class="btn-sm-custom btn-sm-test" id="testEmailBtn"><i class="fas fa-envelope-open-text"></i> Test Email</button>
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
                <thead><tr><th>ID</th><th>Student</th><th>Course</th><th>Amount</th><th>Status</th><th>Paid At</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($enrollments as $e): ?>
                    <tr>
                        <td><?php echo $e['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($e['student_name']); ?></strong><br><small><?php echo htmlspecialchars($e['student_email']); ?></small><br><small><?php echo htmlspecialchars($e['student_phone']); ?></small></td>
                        <td><?php echo htmlspecialchars($e['course_title']); ?></td>
                        <td>$<?php echo number_format($e['amount'], 2); ?></td>
                        <td>
                            <select class="status-select status-update" data-id="<?php echo $e['id']; ?>">
                                <option value="pending" <?php echo $e['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?php echo $e['payment_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="failed" <?php echo $e['payment_status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                <option value="cancelled" <?php echo $e['payment_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <?php if($e['paid_at'] && $e['paid_at'] != '0000-00-00 00:00:00'): ?>
                                <div class="mt-1" style="font-size:0.7rem;"><i class="fas fa-check-circle"></i> <?php echo date('d M Y H:i', strtotime($e['paid_at'])); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d M Y', strtotime($e['enrollment_date'])); ?></td>
                        <td>
                            <button class="view-btn btn-sm-custom" data-id="<?php echo $e['id']; ?>" data-name="<?php echo htmlspecialchars($e['student_name']); ?>" data-email="<?php echo htmlspecialchars($e['student_email']); ?>" data-phone="<?php echo htmlspecialchars($e['student_phone']); ?>" data-address="<?php echo htmlspecialchars($e['student_address']); ?>" data-course="<?php echo htmlspecialchars($e['course_title']); ?>" data-amount="<?php echo $e['amount']; ?>" data-status="<?php echo $e['payment_status']; ?>" data-transaction="<?php echo htmlspecialchars($e['transaction_id']); ?>" data-payment-method="<?php echo htmlspecialchars($e['payment_method']); ?>" data-paid-at="<?php echo $e['paid_at']; ?>" data-date="<?php echo date('d M Y, h:i A', strtotime($e['enrollment_date'])); ?>"><i class="fas fa-eye"></i> View</button>
                            <?php if($e['payment_status'] === 'completed'): ?>
                                <button class="resend-email-btn btn-sm-custom btn-sm-email" data-id="<?php echo $e['id']; ?>"><i class="fas fa-envelope"></i> Send Email</button>
                            <?php endif; ?>
                            <button class="delete-btn btn-sm-custom" data-id="<?php echo $e['id']; ?>" style="border-color:var(--danger); color:var(--danger);"><i class="fas fa-trash"></i> Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="footer">© <?php echo date('Y'); ?> AR Tech Solutions | Enrollment Management</div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1"><div class="modal-dialog modal-md"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-graduate me-2"></i>Enrollment Details</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><div><strong>Student Name:</strong> <span id="viewName"></span></div><div><strong>Email:</strong> <span id="viewEmail"></span></div><div><strong>Phone:</strong> <span id="viewPhone"></span></div><div><strong>Address:</strong> <span id="viewAddress"></span></div><div><strong>Course:</strong> <span id="viewCourse"></span></div><div><strong>Amount:</strong> <span id="viewAmount"></span></div><div><strong>Payment Method:</strong> <span id="viewPaymentMethod"></span></div><div><strong>Status:</strong> <span id="viewStatus"></span></div><div><strong>Transaction ID:</strong> <span id="viewTransaction"></span></div><div><strong>Enrollment Date:</strong> <span id="viewDate"></span></div><div><strong>Paid At:</strong> <span id="viewPaidAt"></span></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    if (localStorage.getItem('artechTheme') === 'light') document.body.classList.add('light');
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('light');
        localStorage.setItem('artechTheme', document.body.classList.contains('light') ? 'light' : 'dark');
        themeToggle.innerHTML = document.body.classList.contains('light') ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
    });
    if(document.body.classList.contains('light')) themeToggle.innerHTML = '<i class="fas fa-moon"></i>'; else themeToggle.innerHTML = '<i class="fas fa-sun"></i>';

    // Search
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#enrollmentsTable tbody tr');
        rows.forEach(row => {
            const name = row.cells[1]?.innerText.toLowerCase() || '';
            const course = row.cells[2]?.innerText.toLowerCase() || '';
            row.style.display = (name.includes(filter) || course.includes(filter)) ? '' : 'none';
        });
    });

    // Status update
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
            if (data.success) location.reload();
            else alert('Update failed: ' + (data.message || 'Unknown error'));
        });
    });

    // Resend email
    document.querySelectorAll('.resend-email-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            const original = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            this.disabled = true;
            const formData = new FormData();
            formData.append('action', 'resend_email');
            formData.append('id', id);
            const res = await fetch('manage_enrollments.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            alert(data.message);
            this.innerHTML = original;
            this.disabled = false;
        });
    });

    // Test email
    document.getElementById('testEmailBtn').addEventListener('click', async () => {
        const btn = document.getElementById('testEmailBtn');
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
        btn.disabled = true;
        const formData = new FormData();
        formData.append('action', 'test_email');
        const res = await fetch('manage_enrollments.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
        const data = await res.json();
        alert(data.message);
        btn.innerHTML = original;
        btn.disabled = false;
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
            document.getElementById('viewPaymentMethod').innerText = btn.dataset.paymentMethod || 'Not specified';
            let status = btn.dataset.status;
            let statusText = status.charAt(0).toUpperCase() + status.slice(1);
            document.getElementById('viewStatus').innerHTML = `<span class="status-badge status-${status}">${statusText}</span>`;
            document.getElementById('viewTransaction').innerText = btn.dataset.transaction || 'N/A';
            document.getElementById('viewDate').innerText = btn.dataset.date;
            let paidAt = btn.dataset.paidAt;
            document.getElementById('viewPaidAt').innerText = (paidAt && paidAt !== '0000-00-00 00:00:00') ? new Date(paidAt).toLocaleString() : 'Not paid yet';
            viewModal.show();
        });
    });

    // Delete
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Delete permanently?')) return;
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