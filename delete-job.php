<?php
require_once 'config/init.php';

if (!isEmployer()) {
    redirect('index.php');
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $job_id = intval($_GET['id']);
    $employer_id = $_SESSION['user_id'];

    // Check ownership
    $stmt = $db->prepare("SELECT * FROM jobs WHERE job_id = ? AND posted_by = ?");
    $stmt->execute([$job_id, $employer_id]);

    if ($stmt->rowCount() === 1) {
        try {
            // Begin transaction
            $db->beginTransaction();

            // Delete applications first
            $deleteApplications = $db->prepare("DELETE FROM applications WHERE job_id = ?");
            $deleteApplications->execute([$job_id]);

            // Now delete the job
            $deleteJob = $db->prepare("DELETE FROM jobs WHERE job_id = ?");
            $deleteJob->execute([$job_id]);

            // Commit
            $db->commit();
            $_SESSION['success'] = "Job and related applications deleted successfully.";

        } catch (PDOException $e) {
            $db->rollBack();
            $_SESSION['errors'][] = "Failed to delete job: " . $e->getMessage();
        }
    } else {
        $_SESSION['errors'][] = "Unauthorized or job not found.";
    }
} else {
    $_SESSION['errors'][] = "Invalid job ID.";
}

redirect('my-jobs.php');
