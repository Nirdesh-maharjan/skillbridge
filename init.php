<?php
session_start();

// Define base URL
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']));

// Database connection
require_once 'database.php';
$database = new Database();
$db = $database->connect();

// csrf token handling
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function getCsrfToken() {
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return hash_equals($_SESSION['csrf_token'], $token);
}

//  Sanitize function
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

//  Authentication helpers
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isEmployer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'employer';
}

function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = "You don't have permission to access this page";
        redirect('login.php');
    }
}

//  Status Badge
function getStatusBadge($status) {
    $status = strtolower($status);
    $classes = [
        'pending' => 'badge-info',
        'accepted' => 'badge-success',
        'rejected' => 'badge-danger',
        'open' => 'badge-info',
        'closed' => 'badge-danger',
        'completed' => 'badge-success',
        'active' => 'badge-success',
        'inactive' => 'badge-danger'
    ];
    
    $class = $classes[$status] ?? 'badge-secondary';
    return "<span class='badge $class'>" . ucfirst($status) . "</span>";
}

//  Redirect
function redirect($url) {
    header("Location: " . BASE_URL . ltrim($url, '/'));
    exit();
}

//  Logging
function logAction($message) {
    $log_file = __DIR__ . '/../logs/admin_actions.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

//  Error and success messages
if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}
if (!isset($_SESSION['success'])) {
    $_SESSION['success'] = '';
}

//  Asset path helper
function asset($path) {
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

//  Flash message handling
function flashMessages() {
    if (!empty($_SESSION['errors'])) {
        foreach ($_SESSION['errors'] as $error) {
            echo '<div class="alert alert-error">' . htmlspecialchars($error) . '</div>';
        }
        unset($_SESSION['errors']);
    }
    
    if (!empty($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
}
?>
