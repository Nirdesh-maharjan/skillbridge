<?php
require_once 'config/init.php';

if (!isAdmin()) {
    redirect('index.php');
}

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'date_applied DESC';

// Build query
$query = "SELECT a.*, j.title as job_title, u.name as student_name, e.name as employer_name 
          FROM applications a 
          JOIN jobs j ON a.job_id = j.job_id 
          JOIN users u ON a.user_id = u.user_id 
          JOIN users e ON j.posted_by = e.user_id 
          WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (j.title LIKE ? OR u.name LIKE ? OR e.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status) && in_array($status, ['pending', 'accepted', 'rejected'])) {
    $query .= " AND a.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY $sort";

// Get all applications
$stmt = $db->prepare($query);
$stmt->execute($params);
$applications = $stmt->fetchAll();

$page_title = 'Manage Applications';
include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/navigation.php'; ?>

    <main class="main-content">
        <div class="dashboard-header">
            <h1>Manage Applications</h1>
            <p>View and manage all job applications</p>
        </div>

        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Search applications..." class="form-control" 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="accepted" <?php echo $status == 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                        <option value="rejected" <?php echo $status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="sort" class="form-control">
                        <option value="date_applied DESC" <?php echo $sort == 'date_applied DESC' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="date_applied ASC" <?php echo $sort == 'date_applied ASC' ? 'selected' : ''; ?>>Oldest First</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="admin-applications.php" class="btn btn-secondary">Reset</a>
            </form>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">All Applications</h2>
            </div>

            <?php if (!empty($applications)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Student</th>
                            <th>Employer</th>
                            <th>Applied</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                                <td><?php echo htmlspecialchars($app['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($app['employer_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($app['date_applied'])); ?></td>
                                <td>
                                    <?php
                                    $status_class = 'badge-info';
                                    if ($app['status'] == 'accepted') $status_class = 'badge-success';
                                    if ($app['status'] == 'rejected') $status_class = 'badge-danger';
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="application-details.php?id=<?php echo $app['app_id']; ?>" class="btn btn-sm">View</a>
                                    <a href="admin-application-delete.php?id=<?php echo $app['app_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this application?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-file-alt"></i>
                    <p>No applications found matching your criteria</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

