<?php
require_once 'config/init.php';

if (!isAdmin()) {
    $_SESSION['error'] = "You don't have permission to access this page";
    redirect('index.php'); // Changed from '../index.php'
}

// Check if job ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Job ID is missing";
    redirect('admin-jobs.php');
}

$job_id = (int)$_GET['id'];

// Get job details before deletion
$stmt = $db->prepare("SELECT j.*, u.name as employer_name 
                     FROM jobs j 
                     JOIN users u ON j.posted_by = u.user_id 
                     WHERE j.job_id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    $_SESSION['error'] = "Job not found";
    redirect('admin-jobs.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Option 1: Hard delete (permanently remove)
        $stmt = $db->prepare("DELETE FROM jobs WHERE job_id = ?");
        
        // Option 2: Soft delete (recommended - just mark as deleted)
        // $stmt = $db->prepare("UPDATE jobs SET status = 'deleted', updated_at = NOW() WHERE job_id = ?");
        
        $stmt->execute([$job_id]);

        $_SESSION['success'] = "Job '{$job['title']}' has been deleted successfully";
        redirect('admin-jobs.php');
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting job: " . $e->getMessage();
        redirect('admin-jobs.php');
    }
}

$page_title = "Delete Job";
include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/navigation.php'; ?>

    <main class="main-content">
        <div class="dashboard-header">
            <h1>Delete Job</h1>
            <p>Confirm deletion of job posting</p>
        </div>

        <div class="dashboard-section">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Warning:</strong> This action cannot be undone. The job will be permanently removed from the system.
            </div>

            <div class="job-details">
                <h3>Job Details</h3>
                <p><strong>Title:</strong> <?php echo htmlspecialchars($job['title']); ?></p>
                <p><strong>Employer:</strong> <?php echo htmlspecialchars($job['employer_name']); ?></p>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($job['category']); ?></p>
                <p><strong>Budget:</strong> $<?php echo number_format($job['budget']); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($job['status']); ?></p>
                <p><strong>Posted:</strong> <?php echo date('M d, Y', strtotime($job['created_at'])); ?></p>
            </div>

            <form method="POST" class="delete-form">
                <div class="form-group">
                    <label for="confirm">Type "DELETE" to confirm:</label>
                    <input type="text" id="confirm" name="confirm" class="form-control" required
                           pattern="DELETE" title="Please type DELETE to confirm">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Confirm Delete
                    </button>
                    <a href="admin-jobs.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>

