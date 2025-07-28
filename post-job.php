<?php
require_once 'config/init.php';

if (!isEmployer()) {
    redirect('index.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $category = sanitize($_POST['category']);
    $budget = (float)$_POST['budget'];
    $description = sanitize($_POST['description']);
    $requirements = sanitize($_POST['requirements']);

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
        $stmt = $db->prepare("INSERT INTO jobs (title, category, budget, description, requirements, posted_by, status) 
                             VALUES (?, ?, ?, ?, ?, ?, 'open')");
        
        if ($stmt->execute([$title, $category, $budget, $description, $requirements, $_SESSION['user_id']])) {
            $_SESSION['success'] = "Job posted successfully!";
            redirect('my-jobs.php');
        } else {
            $_SESSION['errors'][] = "Failed to post job. Please try again.";
        }
    }
}

$categories = ["Web Development", "Mobile Development", "Design", "Writing", "Marketing", "Data Entry", "Virtual Assistant", "Other"];

$page_title = 'Post Job';
include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/navigation.php'; ?>

    <main class="main-content">
        <div class="dashboard-header">
            <h1><i class="fas fa-plus"></i> Post a New Job</h1>
            <p>Create a job listing to find talented students for your project</p>
        </div>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="title">Job Title</label>
                    <input type="text" id="title" name="title" class="form-control" required 
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" placeholder="e.g. Website Developer Needed">
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo isset($_POST['category']) && $_POST['category'] == $cat ? 'selected' : ''; ?>>
                                <?php echo $cat; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="budget">Budget ($)</label>
                    <input type="number" id="budget" name="budget" class="form-control" step="0.01" min="1" required 
                           value="<?php echo isset($_POST['budget']) ? htmlspecialchars($_POST['budget']) : ''; ?>" placeholder="e.g. 100">
                </div>
                
                <div class="form-group">
                    <label for="description">Job Description</label>
                    <textarea id="description" name="description" class="form-control" rows="5" required placeholder="Describe the job in detail..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="requirements">Requirements</label>
                    <textarea id="requirements" name="requirements" class="form-control" rows="5" required placeholder="List the skills and qualifications needed..."><?php echo isset($_POST['requirements']) ? htmlspecialchars($_POST['requirements']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Post Job</button>
                <a href="my-jobs.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </main>
</div>

