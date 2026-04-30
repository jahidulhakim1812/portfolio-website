<?php
// config.php - Database Configuration
session_start();

$host = 'localhost';
$dbname = 'ar_portfolio';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper function to get active menu class
function isActive($page) {
    $currentFile = basename($_SERVER['PHP_SELF'], '.php');
    return ($currentFile == $page) ? 'active' : '';
}
?>