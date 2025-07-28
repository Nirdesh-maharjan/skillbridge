<?php
require_once 'config/init.php';

if (!isEmployer()) {
    redirect('index.php');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('my-jobs.php');
}

$job_id = (int)$_GET['id'];

// Get job details
$stmt = $db->prepare("SELECT * FROM jobs WHERE job_id = ? AND posted_by = ?");
$stmt->execute([$job_id, $_SESSION['user_id']]);
$job = $stmt->fetch();

if (!$job) {
    redirect('my-jobs.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $category = sanitize($_POST['category']);
    $budget = (float)$_POST['budget'];
    $description = sanitize($_POST['description']);
    $requirements = sanitize($_POST['requirements']);
    $status = sanitize($_POST['status']);

    // Validation
    if (empty($title)) {
        $_SESSION['errors'][] = "Job title is required";
    }
    
    if (empty($category)) {
        $_SESSION['errors'][] = "Category is required";
    }
    
    if ($budget <= 0) {
        $_SESSION['errors'][] = "Budget must be greater than 0";
    }
    
    if (empty($description)) {
        $_SESSION['errors'][] = "Description is required";
    }
    
    if (empty($requirements)) {
        $_SESSION['errors'][] = "Requirements are required";
    }

    if (empty($_SESSION['errors'])) {
        $stmt = $db->prepare("UPDATE jobs SET title = ?, category = ?, budget = ?, description = ?, requirements = ?, status = ? WHERE job_id = ?");
        
        if ($stmt->execute([$title, $category, $budget, $description, $requirements, $status, $job_id])) {
            $_SESSION['success'] = "Job updated successfully!";
            redirect("job-details.php?id=$job_id");
        } else {
            $_SESSION['errors'][] = "Failed to update job. Please try again.";
        }
    }
}

$categories = ["Web Development", "Mobile Development", "Design", "Writing", "Marketing", "Data Entry", "Virtual Assistant", "Other"];
$statuses = ["open", "closed", "completed"];

$page_title = 'Edit Job';
include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/navigation.php'; ?>

    <main class="main-content">
        <div class="dashboard-header">
            <h1>Edit Job</h1>
            <p>Update your job listing</p>
        </div>

        <div class="dashboard-section">
            <form method="POST">
                <div class="form-group">
                    <label for="title">Job Title</label>
                    <input type="text" id="title" name="title" class="form-control" required 
                           value="<?php echo htmlspecialchars($job['title']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo $job['category'] == $cat ? 'selected' : ''; ?>>
                                <?php echo $cat; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="budget">Budget ($)</label>
                    <input type="number" id="budget" name="budget" class="form-control" step="0.01" min="1" required 
                           value="<?php echo htmlspecialchars($job['budget']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <?php foreach ($statuses as $stat): ?>
                            <option value="<?php echo $stat; ?>" <?php echo $job['status'] == $stat ? 'selected' : ''; ?>>
                                <?php echo ucfirst($stat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description">Job Description</label>
                    <textarea id="description" name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($job['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="requirements">Requirements</label>
                    <textarea id="requirements" name="requirements" class="form-control" rows="5" required><?php echo htmlspecialchars($job['requirements']); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Job</button>
                <a href="my-jobs.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </main>
</div>

