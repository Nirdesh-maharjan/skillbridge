<?php
require_once 'config/init.php';

if (!isAdmin()) {
    redirect('index.php');
}

if (!isset($_GET['id'], $_GET['action'])) {
    redirect('admin-jobs.php');
}

$job_id = (int)$_GET['id'];
$action = $_GET['action'];

if ($action === 'delete') {
    // Secure delete with prepared statement
    $stmt = $db->prepare("DELETE FROM jobs WHERE job_id = ?");
    $stmt->execute([$job_id]);
    $_SESSION['success'] = "Job deleted successfully.";
} elseif ($action === 'approve') {
    // Mark job as approved (remove policy flag)
    $stmt = $db->prepare("UPDATE jobs SET violates_policy = 0 WHERE job_id = ?");
    $stmt->execute([$job_id]);
    $_SESSION['success'] = "Job approved and restored.";
} else {
    $_SESSION['errors'][] = "Invalid action.";
}

redirect('admin-jobs.php');
