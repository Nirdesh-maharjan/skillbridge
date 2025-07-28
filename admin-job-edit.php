<?php
require_once 'config/init.php';

if (!isAdmin()) {
    $_SESSION['error'] = "You don't have permission to access this page";
    redirect('../index.php');
}

// Check if job ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Job ID is missing";
    redirect('admin-jobs.php');
}

$job_id = (int)$_GET['id'];

// Get job details
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

// Get categories
$categories = $db->query("SELECT DISTINCT category FROM jobs WHERE category IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $category = sanitize($_POST['category']);
    $budget = (float)$_POST['budget'];
    $status = sanitize($_POST['status']);
    $deadline = sanitize($_POST['deadline']);

    try {
        $stmt = $db->prepare("UPDATE jobs SET 
                            title = ?,
                            description = ?,
                            category = ?,
                            budget = ?,
                            status = ?,
                            deadline = ?,
                            updated_at = NOW()
                            WHERE job_id = ?");
        
        $stmt->execute([$title, $description, $category, $budget, $status, $deadline, $job_id]);

        $_SESSION['success'] = "Job updated successfully";
        redirect('admin-jobs.php');
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating job: " . $e->getMessage();
    }
}

$page_title = "Edit Job";
include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/navigation.php'; ?>

    <main class="main-content">
        <div class="dashboard-header">
            <h1>Edit Job</h1>
            <p>Update job details</p>
        </div>

        <div class="dashboard-section">
            <form method="POST" class="job-form">
                <div class="form-group">
                    <label for="title">Job Title</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           value="<?php echo htmlspecialchars($job['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Job Description</label>
                    <textarea id="description" name="description" class="form-control" 
                              rows="5" required><?php echo htmlspecialchars($job['description']); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="category">Category</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" 
                                    <?php echo $job['category'] == $cat ? 'selected' : ''; ?>>
                                    <?php echo $cat; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="budget">Budget ($)</label>
                        <input type="number" id="budget" name="budget" class="form-control" 
                               value="<?php echo $job['budget']; ?>" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="open" <?php echo $job['status'] == 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="closed" <?php echo $job['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                            <option value="completed" <?php echo $job['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="deadline">Deadline</label>
                        <input type="date" id="deadline" name="deadline" class="form-control" 
                               value="<?php echo $job['deadline'] ? htmlspecialchars($job['deadline']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <p><strong>Posted by:</strong> <?php echo htmlspecialchars($job['employer_name']); ?></p>
                    <p><strong>Created at:</strong> <?php echo date('M d, Y', strtotime($job['created_at'])); ?></p>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Job</button>
                    <a href="admin-jobs.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>

