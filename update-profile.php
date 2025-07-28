<?php
require_once 'config/init.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

// Simple validation
if (empty($name) || empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Name and email are required']);
    exit;
}

$stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
$updated = $stmt->execute([$name, $email, $user_id]);

if ($updated) {
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
}
if{
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
}