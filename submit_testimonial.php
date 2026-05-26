<?php
// submit_testimonial.php
require_once 'config.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}
$name = trim($_POST['client_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$text = trim($_POST['testimonial_text'] ?? '');
$rating = intval($_POST['rating'] ?? 5);
$image = trim($_POST['image_url'] ?? '');
if (empty($name) || empty($email) || empty($text)) {
    echo json_encode(['success' => false, 'message' => 'All fields required']);
    exit;
}
$stmt = $pdo->prepare("INSERT INTO testimonials (client_name, email, testimonial_text, rating, image_url, status, is_verified) VALUES (?, ?, ?, ?, ?, 0, 1)");
$stmt->execute([$name, $email, $text, $rating, $image]);
echo json_encode(['success' => true, 'message' => 'Thank you! Your testimonial will appear after admin approval.']);
?>