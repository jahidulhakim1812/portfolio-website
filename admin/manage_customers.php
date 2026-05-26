<?php
// admin/manage_customers.php - Customer management with country/district autocomplete
require_once 'auth.php';
require_once '../config.php';

// List of common countries for dropdown
$countries = [
    'Bangladesh', 'United States', 'United Kingdom', 'Canada', 'Australia',
    'India', 'Germany', 'France', 'Japan', 'China', 'Singapore', 'Malaysia',
    'UAE', 'Saudi Arabia', 'South Africa', 'Brazil', 'Mexico', 'Italy', 'Spain'
];

// All 64 districts of Bangladesh
$bangladeshDistricts = [
    'Bagerhat', 'Bandarban', 'Barguna', 'Barishal', 'Bhola', 'Bogra', 'Brahmanbaria',
    'Chandpur', 'Chattogram', 'Chuadanga', 'Cox\'s Bazar', 'Cumilla', 'Dhaka', 'Dinajpur',
    'Faridpur', 'Feni', 'Gaibandha', 'Gazipur', 'Gopalganj', 'Habiganj', 'Jamalpur', 'Jashore',
    'Jhalokathi', 'Jhenaidah', 'Joypurhat', 'Khagrachhari', 'Khulna', 'Kishoreganj', 'Kurigram',
    'Kushtia', 'Lakshmipur', 'Lalmonirhat', 'Madaripur', 'Magura', 'Manikganj', 'Meherpur',
    'Moulvibazar', 'Munshiganj', 'Mymensingh', 'Naogaon', 'Narail', 'Narayanganj', 'Narsingdi',
    'Natore', 'Netrokona', 'Nilphamari', 'Noakhali', 'Pabna', 'Panchagarh', 'Patuakhali', 'Pirojpur',
    'Rajbari', 'Rajshahi', 'Rangamati', 'Rangpur', 'Satkhira', 'Shariatpur', 'Sherpur', 'Sirajganj',
    'Sunamganj', 'Sylhet', 'Tangail', 'Thakurgaon'
];

// Handle AJAX requests (unchanged, same as previous)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'add_customer') {
        $name = trim($_POST['name'] ?? '');
        $logo_url = trim($_POST['logo_url'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $order = intval($_POST['order_position'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($name) {
            $stmt = $pdo->prepare("INSERT INTO customers (customer_name, logo_url, country, district, order_position, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $logo_url, $country, $district, $order, $is_active]);
            echo json_encode(['success' => true, 'message' => 'Customer added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Customer name is required']);
        }
        exit;
    }

    if ($action === 'edit_customer') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $logo_url = trim($_POST['logo_url'] ?? '');
        $country = trim($_POST['country']);
        $district = trim($_POST['district']);
        $order = intval($_POST['order_position']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($id && $name) {
            $stmt = $pdo->prepare("UPDATE customers SET customer_name = ?, logo_url = ?, country = ?, district = ?, order_position = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $logo_url, $country, $district, $order, $is_active, $id]);
            echo json_encode(['success' => true, 'message' => 'Customer updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
        }
        exit;
    }

    if ($action === 'upload_logo') {
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/logos/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                echo json_encode(['success' => true, 'logo_url' => 'uploads/logos/' . $fileName]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        }
        exit;
    }

    if ($action === 'toggle_active') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("UPDATE customers SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'delete_customer') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("SELECT logo_url FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        $cust = $stmt->fetch();
        if ($cust && !empty($cust['logo_url']) && file_exists('../' . $cust['logo_url'])) {
            unlink('../' . $cust['logo_url']);
        }
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }
}

// Fetch all customers ordered by position
$customers = $pdo->query("SELECT * FROM customers ORDER BY order_position ASC, id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Manage Customers | NEXORA AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* ========== SAME STYLES AS PREVIOUS (unchanged) ========== */
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
        .customer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .customer-table th, .customer-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .customer-table th {
            color: var(--muted);
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .customer-table td {
            color: var(--text);
        }
        .logo-thumb {
            width: 50px;
            height: 50px;
            object-fit: contain;
            background: rgba(255,255,255,0.05);
            border-radius: 0.5rem;
            padding: 5px;
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
        .image-preview { width: 80px; height: 80px; object-fit: contain; border-radius: 0.5rem; margin-top: 0.5rem; border: 1px solid var(--border); background: rgba(255,255,255,0.05); }
        @media (max-width: 768px) {
            .sidebar { width: 80px; left: 10px; }
            .main { margin-left: 100px; }
            .customer-table th, .customer-table td { padding: 8px; font-size: 0.75rem; }
            .logo-thumb { width: 35px; height: 35px; }
            .btn-sm-custom { font-size: 0.65rem; padding: 0.2rem 0.5rem; }
        }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: var(--muted); }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="main" id="main">
    <div class="topbar">
        <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search customers..."></div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon"></i></button>
            <div class="profile-img"><i class="fas fa-user-astronaut"></i></div>
            <div><strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong><br><small style="color:var(--muted)">Admin</small></div>
        </div>
    </div>

    <div class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
            <h3><i class="fas fa-users me-2"></i> Customer Management</h3>
            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><i class="fas fa-plus me-2"></i>Add Customer</button>
        </div>
        <div class="table-responsive">
            <table class="customer-table w-100" id="customersTable">
                <thead>
                    <tr>
                        <th>Logo</th><th>ID</th><th>Customer Name</th><th>Country</th><th>District</th><th>Order</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($customers as $c): ?>
                    <tr data-id="<?php echo $c['id']; ?>">
                        <td>
                            <?php if($c['logo_url']): ?>
                                <img src="../<?php echo htmlspecialchars($c['logo_url']); ?>" class="logo-thumb" alt="logo">
                            <?php else: ?>
                                <i class="fas fa-image fa-2x text-muted"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $c['id']; ?></td>
                        <td><?php echo htmlspecialchars($c['customer_name']); ?> <br><small class="text-muted" style="color:var(--muted);"><?php echo htmlspecialchars(substr($c['country'] ?? '', 0, 20)); ?></small></td>
                        <td><?php echo htmlspecialchars($c['country']); ?></td>
                        <td><?php echo htmlspecialchars($c['district']); ?></td>
                        <td><?php echo $c['order_position']; ?></td>
                        <td><span class="status-badge <?php echo $c['is_active'] ? '' : 'inactive'; ?>"><?php echo $c['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                        <td>
                            <button class="edit-btn btn-sm-custom" data-id="<?php echo $c['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($c['customer_name']); ?>"
                                data-logo="<?php echo htmlspecialchars($c['logo_url']); ?>"
                                data-country="<?php echo htmlspecialchars($c['country']); ?>"
                                data-district="<?php echo htmlspecialchars($c['district']); ?>"
                                data-order="<?php echo $c['order_position']; ?>"
                                data-active="<?php echo $c['is_active']; ?>"><i class="fas fa-edit"></i> Edit</button>
                            <button class="toggle-active btn-sm-custom" data-id="<?php echo $c['id']; ?>"><i class="fas fa-sync-alt"></i> Toggle</button>
                            <button class="delete-btn btn-sm-custom" data-id="<?php echo $c['id']; ?>" style="border-color:var(--danger); color:var(--danger);"><i class="fas fa-trash"></i> Delete</button>
                         </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="footer">© 2025 NEXORA AI | Customer Management System</div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add New Customer</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="addCustomerForm" enctype="multipart/form-data">
                    <div class="mb-3"><label>Customer Name *</label><input type="text" id="addName" class="form-control" required></div>
                    <div class="mb-3"><label>Country</label>
                        <select id="addCountry" class="form-select">
                            <option value="">Select Country</option>
                            <?php foreach($countries as $c): ?>
                                <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="addDistrictContainer">
                        <label>District / State</label>
                        <input type="text" id="addDistrict" class="form-control" placeholder="Enter district or state">
                    </div>
                    <div class="mb-3"><label>Order Position</label><input type="number" id="addOrder" class="form-control" value="0"></div>
                    <div class="mb-3"><label>Customer Logo</label><input type="file" id="addLogo" class="form-control" accept="image/*"><img id="addLogoPreview" class="image-preview" style="display:none;"><input type="hidden" id="addLogoUrl"></div>
                    <div class="mb-3"><div class="form-check"><input type="checkbox" id="addActive" class="form-check-input" checked><label class="form-check-label">Active</label></div></div>
                    <button type="submit" class="btn btn-primary w-100">Create Customer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Customer</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="editCustomerForm" enctype="multipart/form-data">
                    <input type="hidden" id="editId">
                    <div class="mb-3"><label>Customer Name *</label><input type="text" id="editName" class="form-control" required></div>
                    <div class="mb-3"><label>Country</label>
                        <select id="editCountry" class="form-select">
                            <option value="">Select Country</option>
                            <?php foreach($countries as $c): ?>
                                <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="editDistrictContainer">
                        <label>District / State</label>
                        <div id="editDistrictField"></div>
                    </div>
                    <div class="mb-3"><label>Order Position</label><input type="number" id="editOrder" class="form-control"></div>
                    <div class="mb-3"><label>Customer Logo</label><input type="file" id="editLogo" class="form-control" accept="image/*"><img id="editLogoPreview" class="image-preview" style="display:none;"><input type="hidden" id="editLogoUrl"></div>
                    <div class="mb-3"><div class="form-check"><input type="checkbox" id="editActive" class="form-check-input"><label class="form-check-label">Active</label></div></div>
                    <button type="submit" class="btn btn-primary w-100">Update Customer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar toggle (unchanged)
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

    // Theme toggle (unchanged)
    const themeToggle = document.getElementById('themeToggle');
    if (localStorage.getItem('nexoraTheme') === 'light') document.body.classList.add('light');
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('light');
        localStorage.setItem('nexoraTheme', document.body.classList.contains('light') ? 'light' : 'dark');
        themeToggle.innerHTML = document.body.classList.contains('light') ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
        location.reload();
    });
    if(document.body.classList.contains('light')) themeToggle.innerHTML = '<i class="fas fa-moon</i>'; else themeToggle.innerHTML = '<i class="fas fa-sun"></i>';

    // Search filter (unchanged)
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#customersTable tbody tr');
        rows.forEach(row => {
            const name = row.cells[2].innerText.toLowerCase();
            row.style.display = name.includes(filter) ? '' : 'none';
        });
    });

    // Image preview helper (unchanged)
    function setupImagePreview(fileInput, previewImg, hiddenUrlInput) {
        fileInput.addEventListener('change', async function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => { previewImg.src = e.target.result; previewImg.style.display = 'block'; };
                reader.readAsDataURL(this.files[0]);
                const formData = new FormData();
                formData.append('action', 'upload_logo');
                formData.append('logo', this.files[0]);
                const res = await fetch('manage_customers.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                const data = await res.json();
                if (data.success) hiddenUrlInput.value = data.logo_url;
                else alert('Upload failed: ' + data.message);
            }
        });
    }

    setupImagePreview(document.getElementById('addLogo'), document.getElementById('addLogoPreview'), document.getElementById('addLogoUrl'));
    setupImagePreview(document.getElementById('editLogo'), document.getElementById('editLogoPreview'), document.getElementById('editLogoUrl'));

    // List of Bangladesh districts (from PHP)
    const bangladeshDistricts = <?php echo json_encode($bangladeshDistricts); ?>;

    // Function to update district field based on selected country
    function updateDistrictField(containerId, selectedCountry, currentDistrict = '') {
        const container = document.getElementById(containerId);
        if (selectedCountry === 'Bangladesh') {
            // Create select dropdown with all districts
            let html = '<select id="' + (containerId === 'addDistrictContainer' ? 'addDistrict' : 'editDistrict') + '" class="form-select">';
            html += '<option value="">Select District</option>';
            bangladeshDistricts.forEach(district => {
                const selected = (district === currentDistrict) ? 'selected' : '';
                html += `<option value="${district}" ${selected}>${district}</option>`;
            });
            html += '</select>';
            container.innerHTML = '<label>District</label>' + html;
        } else {
            // Plain text input
            container.innerHTML = '<label>District / State</label><input type="text" id="' + (containerId === 'addDistrictContainer' ? 'addDistrict' : 'editDistrict') + '" class="form-control" placeholder="Enter district or state" value="' + currentDistrict + '">';
        }
    }

    // Add modal: when country changes, update district field
    const addCountrySelect = document.getElementById('addCountry');
    addCountrySelect.addEventListener('change', function() {
        updateDistrictField('addDistrictContainer', this.value, '');
    });

    // Edit modal: when country changes, update district field
    const editCountrySelect = document.getElementById('editCountry');
    editCountrySelect.addEventListener('change', function() {
        updateDistrictField('editDistrictContainer', this.value, '');
    });

    // Add customer AJAX (unchanged, but now uses dynamic field)
    document.getElementById('addCustomerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const districtField = document.getElementById('addDistrict');
        const districtValue = districtField ? districtField.value : '';
        const formData = new FormData();
        formData.append('action', 'add_customer');
        formData.append('name', document.getElementById('addName').value);
        formData.append('country', document.getElementById('addCountry').value);
        formData.append('district', districtValue);
        formData.append('order_position', document.getElementById('addOrder').value);
        formData.append('is_active', document.getElementById('addActive').checked ? 1 : 0);
        formData.append('logo_url', document.getElementById('addLogoUrl').value);
        const res = await fetch('manage_customers.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Error: ' + data.message);
    });

    // Edit modal population (with district handling)
    const editModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const country = btn.dataset.country;
            const district = btn.dataset.district;
            const order = btn.dataset.order;
            const isActive = btn.dataset.active == 1;
            const logo = btn.dataset.logo;

            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editOrder').value = order;
            document.getElementById('editActive').checked = isActive;

            // Set country select
            const editCountry = document.getElementById('editCountry');
            editCountry.value = country;
            // Update district field based on country
            updateDistrictField('editDistrictContainer', country, district);
            // Re-attach change event to the newly created district field (already done by updateDistrictField)
            // Logo preview
            if (logo) {
                document.getElementById('editLogoPreview').src = '../' + logo;
                document.getElementById('editLogoPreview').style.display = 'block';
                document.getElementById('editLogoUrl').value = logo;
            } else {
                document.getElementById('editLogoPreview').style.display = 'none';
                document.getElementById('editLogoUrl').value = '';
            }
            editModal.show();
        });
    });

    // Edit customer AJAX (must use dynamic district field)
    document.getElementById('editCustomerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const districtField = document.getElementById('editDistrict');
        const districtValue = districtField ? districtField.value : '';
        const formData = new FormData();
        formData.append('action', 'edit_customer');
        formData.append('id', document.getElementById('editId').value);
        formData.append('name', document.getElementById('editName').value);
        formData.append('country', document.getElementById('editCountry').value);
        formData.append('district', districtValue);
        formData.append('order_position', document.getElementById('editOrder').value);
        formData.append('is_active', document.getElementById('editActive').checked ? 1 : 0);
        formData.append('logo_url', document.getElementById('editLogoUrl').value);
        const res = await fetch('manage_customers.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Error');
    });

    // Toggle active status (unchanged)
    document.querySelectorAll('.toggle-active').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const formData = new FormData();
            formData.append('action', 'toggle_active');
            formData.append('id', id);
            const res = await fetch('manage_customers.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            if (data.success) location.reload();
        });
    });

    // Delete with confirmation (unchanged)
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Delete this customer permanently?')) return;
            const id = btn.dataset.id;
            const formData = new FormData();
            formData.append('action', 'delete_customer');
            formData.append('id', id);
            const res = await fetch('manage_customers.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            const data = await res.json();
            if (data.success) location.reload();
        });
    });
</script>
</body>
</html>