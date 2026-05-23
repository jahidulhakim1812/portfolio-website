<?php
require_once 'auth.php';
require_once '../config.php';

$speech = $pdo->query("SELECT * FROM chairman_speech WHERE is_active = 1 LIMIT 1")->fetch();
if (!$speech) {
    // Insert empty default
    $pdo->exec("INSERT INTO chairman_speech (speech_text, chairman_name, title, image_url, signature_url, is_active) VALUES ('', 'Dr. Ahmed Rahim', 'Founder & Chairman', '', '', 1)");
    $speech = $pdo->query("SELECT * FROM chairman_speech WHERE is_active = 1 LIMIT 1")->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE chairman_speech SET speech_text=?, chairman_name=?, title=?, image_url=?, signature_url=? WHERE id=?");
    $stmt->execute([
        $_POST['speech_text'], $_POST['chairman_name'], $_POST['title'],
        $_POST['image_url'], $_POST['signature_url'], $speech['id']
    ]);
    header('Location: manage_chairman.php?updated=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Chairman Speech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>body{background:#f4f7fc;}.sidebar{background:#1e2a3a;min-height:100vh;}.sidebar a{color:#ccc;display:block;padding:12px 20px;}.sidebar a:hover{background:#2c3e50;color:white;}.sidebar i{width:25px;}</style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 px-0 sidebar">
            <div class="text-center py-4"><h4 class="text-white">AR Tech Admin</h4></div>
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="manage_sliders.php"><i class="fas fa-images"></i> Sliders</a>
            <a href="manage_services.php"><i class="fas fa-cogs"></i> Services</a>
            <a href="manage_portfolios.php"><i class="fas fa-folder-open"></i> Portfolios</a>
            <a href="manage_testimonials.php"><i class="fas fa-comment-dots"></i> Testimonials</a>
            <a href="manage_chairman.php" class="active"><i class="fas fa-microphone-alt"></i> Chairman Speech</a>
            <a href="manage_customers.php"><i class="fas fa-users"></i> Customers</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="col-md-9 col-lg-10 p-4">
            <h2>Edit Chairman Speech</h2>
            <?php if(isset($_GET['updated'])) echo '<div class="alert alert-success">Updated successfully!</div>'; ?>
            <form method="POST">
                <div class="mb-3"><label>Speech Text</label><textarea name="speech_text" rows="8" class="form-control" required><?php echo htmlspecialchars($speech['speech_text']); ?></textarea></div>
                <div class="mb-3"><label>Chairman Name</label><input type="text" name="chairman_name" class="form-control" value="<?php echo htmlspecialchars($speech['chairman_name']); ?>" required></div>
                <div class="mb-3"><label>Title</label><input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($speech['title']); ?>"></div>
                <div class="mb-3"><label>Image URL (profile photo)</label><input type="url" name="image_url" class="form-control" value="<?php echo htmlspecialchars($speech['image_url']); ?>"></div>
                <div class="mb-3"><label>Signature URL</label><input type="url" name="signature_url" class="form-control" value="<?php echo htmlspecialchars($speech['signature_url']); ?>"></div>
                <button type="submit" class="btn btn-primary">Update Speech</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>