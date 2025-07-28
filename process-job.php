<?php
require_once 'config/init.php';

if (!isEmployer()) {
    redirect('index.php');
}

if (!isset($_GET['action']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('my-jobs.php');
}

$action = $_GET['action'];
$job_id = (int)$_GET['id'];

// Verify job belongs to current employer
$stmt = $db->prepare("SELECT job_id FROM jobs WHERE job_id = ? AND posted_by = ?");
$stmt->execute([$job_id, $_SESSION['user_id']]);
$job = $stmt->fetch();

if (!$job) {
    redirect('my-jobs.php');
}

// Process action
if (in_array($action, ['open', 'close', 'complete'])) {
    $status = $action == 'close' ? 'closed' : ($action == 'complete' ? 'completed' : 'open');
    
    $stmt = $db->prepare("UPDATE jobs SET status = ? WHERE job_id = ?");
    if ($stmt->execute([$status, $job_id])) {
        $_SESSION['success'] = "Job status updated successfully!";
    } else {
        $_SESSION['errors'][] = "Failed to update job status.";
    }
}

redirect("job-details.php?id=$job_id");
?>