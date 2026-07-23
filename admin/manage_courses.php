<?php
// admin/manage_courses.php – List all courses with edit/delete actions
require_once 'auth.php';
require_once '../config.php';

// Delete a course (including all child records) if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $pdo->beginTransaction();

        // 1. Delete module children (reference module_id)
        $moduleChildTables = ['course_module_breakdown', 'course_module_highlights', 'course_module_projects', 'course_classes'];
        foreach ($moduleChildTables as $table) {
            // Delete from these tables by joining with course_modules to get module IDs
            // Or simpler: delete from course_modules with CASCADE, but we'll do manual for safety
            $stmt = $pdo->prepare("DELETE `$table` FROM `$table`
                JOIN course_modules ON `$table`.module_id = course_modules.id
                WHERE course_modules.course_id = ?");
            $stmt->execute([$id]);
        }

        // 2. Delete modules (have course_id)
        $stmt = $pdo->prepare("DELETE FROM course_modules WHERE course_id = ?");
        $stmt->execute([$id]);

        // 3. Delete all other direct children (have course_id)
        $directTables = [
            'course_features', 'course_highlights', 'course_careers',
            'course_prereq_items', 'course_audience', 'course_software_items',
            'course_delivery_modes', 'course_pricing', 'course_gallery',
            'course_faqs'
        ];
        foreach ($directTables as $table) {
            $stmt = $pdo->prepare("DELETE FROM `$table` WHERE course_id = ?");
            $stmt->execute([$id]);
        }

        // 4. Delete the course itself
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        $deleteSuccess = true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $deleteError = 'Delete failed: ' . $e->getMessage();
    }
}

// Fetch all courses
$courses = $pdo->query("SELECT id, title, status, is_popular, price, created_at FROM courses ORDER BY order_position ASC, title ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses | ARTECH Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root{
            --bg:#050816; --panel:#0f172a; --primary:#7c3aed; --text:#ffffff; --muted:#94a3b8; --border:rgba(255,255,255,0.08);
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--text);}
        .main{margin-left:310px;padding:20px;transition:margin .3s cubic-bezier(.4,0,.2,1);}
        .main.expand{margin-left:120px;}
        .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;flex-wrap:wrap;gap:12px;}
        .panel{background:rgba(255,255,255,.03);border-radius:1.6rem;padding:1.5rem;border:1px solid var(--border);}
        .table-custom{color:var(--text);}
        .table-custom th{color:var(--muted);font-weight:500;border-color:var(--border);}
        .table-custom td{vertical-align:middle;border-color:var(--border);}
        .badge-status{background:var(--primary);color:#fff;}
        .badge-popular{background:#f59e0b;color:#000;}
        .btn-action{background:rgba(255,255,255,.05);border:none;color:var(--text);padding:.3rem .7rem;border-radius:.5rem;}
        .btn-action:hover{background:rgba(255,255,255,.15);}
        .btn-danger-action{color:#ef4444;}
        .btn-danger-action:hover{background:rgba(239,68,68,.2);}
        @media(max-width:768px){.main{margin-left:100px;}}
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>
<div class="main" id="main">
    <div class="topbar">
        <h3><i class="fas fa-list me-2"></i>Manage Courses</h3>
        <a href="add_course.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add New Course</a>
    </div>

    <?php if (isset($deleteSuccess)): ?>
        <div class="alert alert-success alert-custom">Course deleted successfully.</div>
    <?php endif; ?>
    <?php if (isset($deleteError)): ?>
        <div class="alert alert-danger alert-custom"><?= htmlspecialchars($deleteError) ?></div>
    <?php endif; ?>

    <div class="panel">
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Popular</th>
                        <th>Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['title']) ?></td>
                        <td>$<?= number_format($c['price'], 2) ?></td>
                        <td><span class="badge badge-status"><?= $c['status'] ? 'Active' : 'Inactive' ?></span></td>
                        <td><?= $c['is_popular'] ? '<i class="fas fa-star text-warning"></i>' : '' ?></td>
                        <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                        <td class="text-center">
                            <a href="edit_course.php?id=<?= $c['id'] ?>" class="btn-action me-1"><i class="fas fa-edit"></i></a>
                            <a href="?delete=<?= $c['id'] ?>" class="btn-action btn-danger-action" onclick="return confirm('Delete this course and all its data?')"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($courses)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No courses found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>