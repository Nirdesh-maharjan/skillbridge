<?php
require_once 'config/init.php';

// Only allow logged-in employers
if (!isEmployer()) {
    redirect('index.php');
}

$page_title = 'My Jobs';
include 'includes/header.php';

// Get jobs posted by current employer
$stmt = $db->prepare("SELECT * FROM jobs WHERE posted_by = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$jobs = $stmt->fetchAll();
?>

<div class="dashboard">
    <?php include 'includes/navigation.php'; ?>

    <main class="main-content">
        <div class="dashboard-header">
            <h1>My Posted Jobs</h1>
            <p>Manage your job listings</p>
        </div>

        <!-- Success & Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['errors'])): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; unset($_SESSION['errors']); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">All Jobs</h2>
                <a href="post-job.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Post New Job
                </a>
            </div>

            <?php if (!empty($jobs)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Category</th>
                            <th>Budget</th>
                            <th>Posted Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($job['title']); ?></td>
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
                                    <a href="job-details.php?id=<?php echo $job['job_id']; ?>" class="btn btn-sm">View</a>
                                    <a href="edit-job.php?id=<?php echo $job['job_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="view-applications.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-sm btn-secondary">Applications</a>
                                    <a href="delete-job.php?id=<?php echo $job['job_id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this job?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-briefcase"></i>
                    <p>No jobs posted yet. <a href="post-job.php">Post your first job</a> to find talented students!</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
