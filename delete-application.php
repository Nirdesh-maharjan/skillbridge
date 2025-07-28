<?php
require_once 'config/init.php';

if (!isStudent()) {
    redirect('index.php');
}

// Validate application ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('my-applications.php');
}

$app_id = (int)$_GET['id'];

// Verify the application belongs to the logged-in student
$stmt = $db->prepare("SELECT app_id, job_id FROM applications WHERE app_id = ? AND user_id = ?");
$stmt->execute([$app_id, $_SESSION['user_id']]);
$app = $stmt->fetch();

if (!$app) {
    $_SESSION['errors'][] = "You are not authorized to delete this application or it doesn't exist.";
    redirect('my-applications.php');
}

// Securely delete the application
$delete = $db->prepare("DELETE FROM applications WHERE app_id = ? AND user_id = ?");
if ($delete->execute([$app_id, $_SESSION['user_id']])) {
    $_SESSION['success'] = "Your application has been withdrawn successfully.";
} else {
    $_SESSION['errors'][] = "Failed to withdraw the application. Please try again.";
}

redirect('my-applications.php');
