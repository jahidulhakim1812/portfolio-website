<?php
require_once 'auth.php';
require_once '../config.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM sliders WHERE id = ?")->execute([$id]);
    header('Location: manage_sliders.php');
    exit();
}

// Handle add/edit
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit = $pdo->prepare("SELECT * FROM sliders WHERE id = ?");
    $edit->execute([$id]);
    $edit = $edit->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $subtitle = trim($_POST['subtitle']);
    $image_url = trim($_POST['image_url']);
    $button_text = trim($_POST['button_text']);
    $button_link = trim($_POST['button_link']);
    $order_position = (int)$_POST['order_position'];
    $status = isset($_POST['status']) ? 1 : 0;
    
    if (isset($_POST['id']) && $_POST['id'] > 0) {
        // Update
        $stmt = $pdo->prepare("UPDATE sliders SET title=?, subtitle=?, image_url=?, button_text=?, button_link=?, order_position=?, status=? WHERE id=?");
        $stmt->execute([$title, $subtitle, $image_url, $button_text, $button_link, $order_position, $status, $_POST['id']]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO sliders (title, subtitle, image_url, button_text, button_link, order_position, status) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$title, $subtitle, $image_url, $button_text, $button_link, $order_position, $status]);
    }
    header('Location: manage_sliders.php');
    exit();
}

$sliders = $pdo->query("SELECT * FROM sliders ORDER BY order_position ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sliders - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background: #f4f7fc; }
        .sidebar { background: #1e2a3a; min-height: 100vh; }
        .sidebar a { color: #ccc; text-decoration: none; display: block; padding: 12px 20px; }
        .sidebar a:hover, .sidebar a.active { background: #2c3e50; color: white; }
        .sidebar i { width: 25px; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 px-0 sidebar">
            <div class="text-center py-4"><h4 class="text-white">AR Tech Admin</h4></div>
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="manage_sliders.php" class="active"><i class="fas fa-images"></i> Sliders</a>
            <a href="manage_services.php"><i class="fas fa-cogs"></i> Services</a>
            <a href="manage_portfolios.php"><i class="fas fa-folder-open"></i> Portfolios</a>
            <a href="manage_testimonials.php"><i class="fas fa-comment-dots"></i> Testimonials</a>
            <a href="manage_chairman.php"><i class="fas fa-microphone-alt"></i> Chairman Speech</a>
            <a href="manage_customers.php"><i class="fas fa-users"></i> Customers</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <div class="col-md-9 col-lg-10 p-4">
            <h2>Manage Sliders</h2>
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#sliderModal" onclick="clearForm()">+ Add New Slider</button>
            <table class="table table-bordered bg-white">
                <thead><tr><th>Order</th><th>Title</th><th>Image URL</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($sliders as $s): ?>
                    <tr>
                        <td><?php echo $s['order_position']; ?></td>
                        <td><?php echo htmlspecialchars($s['title']); ?></td>
                        <td><?php echo htmlspecialchars($s['image_url']); ?></td>
                        <td><?php echo $s['status'] ? 'Active' : 'Inactive'; ?></td>
                        <td>
                            <a href="?edit=<?php echo $s['id']; ?>" class="btn btn-sm btn-info" onclick="editSlider(<?php echo htmlspecialchars(json_encode($s)); ?>)">Edit</a>
                            <a href="?delete=<?php echo $s['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="sliderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5 class="modal-title">Slider Form</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="sliderId">
                    <div class="mb-2"><label>Title</label><input type="text" name="title" id="title" class="form-control" required></div>
                    <div class="mb-2"><label>Subtitle</label><textarea name="subtitle" id="subtitle" class="form-control"></textarea></div>
                    <div class="mb-2"><label>Image URL</label><input type="url" name="image_url" id="image_url" class="form-control" required></div>
                    <div class="mb-2"><label>Button Text</label><input type="text" name="button_text" id="button_text" class="form-control"></div>
                    <div class="mb-2"><label>Button Link</label><input type="text" name="button_link" id="button_link" class="form-control"></div>
                    <div class="mb-2"><label>Order Position</label><input type="number" name="order_position" id="order_position" class="form-control"></div>
                    <div class="mb-2"><label><input type="checkbox" name="status" id="status"> Active</label></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function clearForm() {
    document.getElementById('sliderId').value = '';
    document.getElementById('title').value = '';
    document.getElementById('subtitle').value = '';
    document.getElementById('image_url').value = '';
    document.getElementById('button_text').value = '';
    document.getElementById('button_link').value = '';
    document.getElementById('order_position').value = '';
    document.getElementById('status').checked = true;
}
function editSlider(data) {
    document.getElementById('sliderId').value = data.id;
    document.getElementById('title').value = data.title;
    document.getElementById('subtitle').value = data.subtitle;
    document.getElementById('image_url').value = data.image_url;
    document.getElementById('button_text').value = data.button_text;
    document.getElementById('button_link').value = data.button_link;
    document.getElementById('order_position').value = data.order_position;
    document.getElementById('status').checked = data.status == 1;
    new bootstrap.Modal(document.getElementById('sliderModal')).show();
}
<?php if($edit): ?>
document.addEventListener('DOMContentLoaded', function() {
    editSlider(<?php echo json_encode($edit); ?>);
});
<?php endif; ?>
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>