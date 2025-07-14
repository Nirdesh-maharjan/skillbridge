<?php
require_once 'config/init.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Get filter parameters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$budget_min = isset($_GET['budget_min']) ? (int)$_GET['budget_min'] : 0;
$budget_max = isset($_GET['budget_max']) ? (int)$_GET['budget_max'] : 999999;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$query = "SELECT j.*, u.name as employer_name FROM jobs j JOIN users u ON j.posted_by = u.user_id WHERE j.status = 'open'";
$params = [];

if (!empty($category)) {
    $query .= " AND j.category = ?";
    $params[] = $category;
}

if ($budget_min > 0) {
    $query .= " AND j.budget >= ?";
    $params[] = $budget_min;
}

if ($budget_max < 999999) {
    $query .= " AND j.budget <= ?";
    $params[] = $budget_max;
}

if (!empty($search)) {
    $query .= " AND (j.title LIKE ? OR j.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY j.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// Get categories for filter
$categories = ["Web Development", "Mobile Development", "Design", "Writing", "Marketing", "Data Entry", "Virtual Assistant", "Other"];

$page_title = 'Browse Jobs';
include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/navigation.php'; ?>

    <main class="main-content">
        <div class="dashboard-header">
            <h1><i class="fas fa-search"></i> Browse Jobs</h1>
            <p>Find the perfect freelance opportunity that matches your skills</p>
        </div>

        <div class="filters">
            <form method="GET">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="search">Search Jobs</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="Enter keywords..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" <?php echo $category == $cat ? 'selected' : ''; ?>>
                                    <?php echo $cat; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="budget_min">Min Budget</label>
                        <input type="number" id="budget_min" name="budget_min" class="form-control" placeholder="$0" 
                               value="<?php echo $budget_min > 0 ? $budget_min : ''; ?>">
                    </div>
                    <div class="filter-group">
                        <label for="budget_max">Max Budget</label>
                        <input type="number" id="budget_max" name="budget_max" class="form-control" placeholder="$999999" 
                               value="<?php echo $budget_max < 999999 ? $budget_max : ''; ?>">
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter Jobs
                        </button>
                        <a href="jobs.php" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="jobs-grid">
            <?php if (!empty($jobs)): ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="job-card">
                        <div class="job-header">
                            <div>
                                <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                                <span class="job-category"><?php echo htmlspecialchars($job['category']); ?></span>
                            </div>
                            <div class="job-budget">$<?php echo number_format($job['budget']); ?></div>
                        </div>
                        <div class="job-meta">
                            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($job['employer_name']); ?></span>
                            <span><i class="fas fa-clock"></i> Posted <?php echo date('M d, Y', strtotime($job['created_at'])); ?></span>
                        </div>
                        <div class="job-description">
                            <?php echo nl2br(htmlspecialchars(substr($job['description'], 0, 200) . '...')); ?>
                        </div>
                        <div class="job-footer">
                            <a href="job-details.php?id=<?php echo $job['job_id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-briefcase"></i>
                    <p>No jobs found matching your criteria. Try adjusting your filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

