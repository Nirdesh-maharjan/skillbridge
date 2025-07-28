<?php
require_once 'config/init.php';

if (!isAdmin()) {
    redirect('index.php');
}

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'created_at DESC';

// Build query
$query = "SELECT j.*, u.name as employer_name 
          FROM jobs j 
          JOIN users u ON j.posted_by = u.user_id 
          WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (j.title LIKE ? OR j.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND j.category = ?";
    $params[] = $category;
}

if (!empty($status) && in_array($status, ['open', 'closed', 'completed'])) {
    $query .= " AND j.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY $sort";

// Fetch jobs
$stmt = $db->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get distinct categories
$categories = $db->query("SELECT DISTINCT category FROM jobs WHERE category IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);

$page_title = 'Manage Jobs';
include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/navigation.php'; ?>

    <main class="main-content">
        <div class="dashboard-header">
            <h1>Manage Jobs</h1>
            <p>View, edit, approve, or delete job postings</p>
        </div>

        <!-- Filter Section -->
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Search jobs..." class="form-control" 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category == $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="open" <?php echo $status == 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="closed" <?php echo $status == 'closed' ? 'selected' : ''; ?>>Closed</option>
                        <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="sort" class="form-control">
                        <option value="created_at DESC" <?php echo $sort == 'created_at DESC' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="created_at ASC" <?php echo $sort == 'created_at ASC' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="title ASC" <?php echo $sort == 'title ASC' ? 'selected' : ''; ?>>Title (A-Z)</option>
                        <option value="title DESC" <?php echo $sort == 'title DESC' ? 'selected' : ''; ?>>Title (Z-A)</option>
                        <option value="budget DESC" <?php echo $sort == 'budget DESC' ? 'selected' : ''; ?>>Highest Budget</option>
                        <option value="budget ASC" <?php echo $sort == 'budget ASC' ? 'selected' : ''; ?>>Lowest Budget</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="admin-jobs.php" class="btn btn-secondary">Reset</a>
            </form>
        </div>

        <!-- Jobs Table -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">All Jobs</h2>
            </div>

            <?php if (!empty($jobs)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Employer</th>
                            <th>Category</th>
                            <th>Budget</th>
                            <th>Posted</th>
                            <th>Status</th>
                            <th>Flag</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                            <tr <?php echo $job['violates_policy'] ? 'style="background:#fff3cd;"' : ''; ?>>
                                <td><?php echo htmlspecialchars($job['title']); ?></td>
                                <td><?php echo htmlspecialchars($job['employer_name']); ?></td>
                                <td><?php echo htmlspecialchars($job['category']); ?></td>
                                <td>$<?php echo number_format($job['budget']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                <td>
                                    <?php
                                    $status_class = 'badge-info';
                                    if ($job['status'] == 'completed') $status_class = 'badge-success';
                                    if ($job['status'] == 'closed') $status_class = 'badge-danger';
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst($job['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($job['violates_policy']): ?>
                                        <span class="badge badge-danger">Flagged</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Clean</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="job-details.php?id=<?php echo $job['job_id']; ?>" class="btn btn-sm">View</a>
                                    <a href="admin-job-edit.php?id=<?php echo $job['job_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <?php if ($job['violates_policy']): ?>
                                        <a href="moderate-job.php?action=approve&id=<?php echo $job['job_id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                    <?php endif; ?>
                                    <a href="moderate-job.php?action=delete&id=<?php echo $job['job_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? This will delete the job permanently.')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-briefcase"></i>
                    <p>No jobs found matching your criteria</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
