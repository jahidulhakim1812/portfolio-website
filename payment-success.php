<?php
// payment-success.php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$enrollment_id = isset($_GET['enrollment_id']) ? intval($_GET['enrollment_id']) : 0;
$demo = isset($_GET['demo']);
$message = '';

if ($enrollment_id && $demo) {
    $stmt = $pdo->prepare("UPDATE enrollments SET payment_status = 'completed' WHERE id = ?");
    $stmt->execute([$enrollment_id]);
    
    // Update course enrolled_students count
    $stmt2 = $pdo->prepare("UPDATE courses SET enrolled_students = enrolled_students + 1 WHERE id = (SELECT course_id FROM enrollments WHERE id = ?)");
    $stmt2->execute([$enrollment_id]);
    
    $message = "Enrollment successful! You are now registered for the course.";
} else {
    $message = "Payment processed successfully (demo mode).";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success | AR Tech Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
        .success-card { background: white; border-radius: 28px; padding: 2rem; box-shadow: 0 20px 40px rgba(0,0,0,0.1); text-align: center; }
        @media (max-width: 768px) { .success-card { margin: 1rem; } }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="success-card">
                <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                <h2 class="mb-3">Payment Successful!</h2>
                <p><?php echo htmlspecialchars($message); ?></p>
                <a href="index.php" class="btn btn-primary mt-3">Go to Homepage</a>
                <a href="course-details.php?id=<?php echo isset($_GET['course_id']) ? $_GET['course_id'] : ''; ?>" class="btn btn-outline-secondary mt-3 ms-2">View Course</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>