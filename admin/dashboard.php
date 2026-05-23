<?php
require_once 'auth.php';
require_once '../config.php';

// Fetch counts
$totalSliders = $pdo->query("SELECT COUNT(*) FROM sliders")->fetchColumn();
$totalServices = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$totalPortfolios = $pdo->query("SELECT COUNT(*) FROM portfolios")->fetchColumn();
$totalTestimonials = $pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$chairmanExists = $pdo->query("SELECT COUNT(*) FROM chairman_speech WHERE is_active = 1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AR Tech Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background: #f4f7fc; }
        .sidebar { background: #1e2a3a; min-height: 100vh; }
        .sidebar a { color: #ccc; text-decoration: none; display: block; padding: 12px 20px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #2c3e50; color: white; }
        .sidebar i { width: 25px; margin-right: 10px; }
        .card-stats { border-radius: 15px; transition: 0.3s; }
        .card-stats:hover { transform: translateY(-5px); }
        .navbar-brand { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="text-center py-4">
                    <h4 class="text-white">AR Tech Admin</h4>
                    <small class="text-white-50">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></small>
                </div>
                <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage_sliders.php"><i class="fas fa-images"></i> Sliders</a>
                <a href="manage_services.php"><i class="fas fa-cogs"></i> Services</a>
                <a href="manage_portfolios.php"><i class="fas fa-folder-open"></i> Portfolios</a>
                <a href="manage_testimonials.php"><i class="fas fa-comment-dots"></i> Testimonials</a>
                <a href="manage_chairman.php"><i class="fas fa-microphone-alt"></i> Chairman Speech</a>
                <a href="manage_customers.php"><i class="fas fa-users"></i> Customers</a>
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
            
            <!-- Main content -->
            <div class="col-md-9 col-lg-10 p-4">
                <h2 class="mb-4">Dashboard Overview</h2>
                <div class="row g-4">
                    <div class="col-md-4 col-lg-3">
                        <div class="card card-stats bg-primary text-white p-3">
                            <div class="d-flex justify-content-between">
                                <div><i class="fas fa-sliders-h fa-3x"></i></div>
                                <div class="fs-2 fw-bold"><?php echo $totalSliders; ?></div>
                            </div>
                            <div>Sliders</div>
                            <a href="manage_sliders.php" class="text-white small">Manage →</a>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="card card-stats bg-success text-white p-3">
                            <div class="d-flex justify-content-between">
                                <div><i class="fas fa-cogs fa-3x"></i></div>
                                <div class="fs-2 fw-bold"><?php echo $totalServices; ?></div>
                            </div>
                            <div>Services</div>
                            <a href="manage_services.php" class="text-white small">Manage →</a>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="card card-stats bg-info text-white p-3">
                            <div class="d-flex justify-content-between">
                                <div><i class="fas fa-folder-open fa-3x"></i></div>
                                <div class="fs-2 fw-bold"><?php echo $totalPortfolios; ?></div>
                            </div>
                            <div>Portfolios</div>
                            <a href="manage_portfolios.php" class="text-white small">Manage →</a>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="card card-stats bg-warning text-white p-3">
                            <div class="d-flex justify-content-between">
                                <div><i class="fas fa-comment-dots fa-3x"></i></div>
                                <div class="fs-2 fw-bold"><?php echo $totalTestimonials; ?></div>
                            </div>
                            <div>Testimonials</div>
                            <a href="manage_testimonials.php" class="text-white small">Manage →</a>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="card card-stats bg-danger text-white p-3">
                            <div class="d-flex justify-content-between">
                                <div><i class="fas fa-users fa-3x"></i></div>
                                <div class="fs-2 fw-bold"><?php echo $totalCustomers; ?></div>
                            </div>
                            <div>Customers</div>
                            <a href="manage_customers.php" class="text-white small">Manage →</a>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="card card-stats bg-secondary text-white p-3">
                            <div class="d-flex justify-content-between">
                                <div><i class="fas fa-microphone-alt fa-3x"></i></div>
                                <div class="fs-2 fw-bold"><?php echo $chairmanExists ? 'Active' : 'Inactive'; ?></div>
                            </div>
                            <div>Chairman Speech</div>
                            <a href="manage_chairman.php" class="text-white small">Manage →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>