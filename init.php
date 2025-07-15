<?php
session_start();

// Define base URL
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']));

// Database connection
require_once 'database.php';
$database = new Database();
$db = $database->connect();

// Helper functions
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

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

function redirect($url) {
    header("Location: " . BASE_URL . ltrim($url, '/'));
    exit();
}

// Error and success messages
if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}
if (!isset($_SESSION['success'])) {
    $_SESSION['success'] = '';
}

// Path helper function
function asset($path) {
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

// Flash message handling
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