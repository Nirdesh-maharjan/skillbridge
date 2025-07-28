<?php
require_once 'config/init.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Get user statistics
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role == 'student') {
    $stmt = $db->prepare("SELECT COUNT(*) as total_applications FROM applications WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_applications = $stmt->fetch()['total_applications'];

    $stmt = $db->prepare("SELECT COUNT(*) as active_jobs FROM applications a JOIN jobs j ON a.job_id = j.job_id WHERE a.user_id = ? AND a.status = 'accepted'");
    $stmt->execute([$user_id]);
    $active_jobs = $stmt->fetch()['active_jobs'];

    $stmt = $db->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE reviewee_id = ?");
    $stmt->execute([$user_id]);
    $avg_rating = $stmt->fetch()['avg_rating'];

    $stmt = $db->prepare("SELECT j.title, j.budget, a.date_applied, a.status FROM applications a JOIN jobs j ON a.job_id = j.job_id WHERE a.user_id = ? ORDER BY a.date_applied DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_applications = $stmt->fetchAll();

} elseif ($role == 'employer') {
    $stmt = $db->prepare("SELECT COUNT(*) as total_jobs FROM jobs WHERE posted_by = ?");
    $stmt->execute([$user_id]);
    $total_jobs = $stmt->fetch()['total_jobs'];

    $stmt = $db->prepare("SELECT COUNT(*) as total_applications FROM applications a JOIN jobs j ON a.job_id = j.job_id WHERE j.posted_by = ?");
    $stmt->execute([$user_id]);
    $total_applications = $stmt->fetch()['total_applications'];

    $stmt = $db->prepare("SELECT COUNT(*) as active_jobs FROM jobs WHERE posted_by = ? AND status = 'open'");
    $stmt->execute([$user_id]);
    $active_jobs = $stmt->fetch()['active_jobs'];

    $stmt = $db->prepare("SELECT COUNT(*) as completed_jobs FROM jobs WHERE posted_by = ? AND status = 'completed'");
    $stmt->execute([$user_id]);
    $completed_jobs = $stmt->fetch()['completed_jobs'];

    $stmt = $db->prepare("SELECT job_id, title, budget, created_at, status FROM jobs WHERE posted_by = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_jobs = $stmt->fetchAll();

} elseif ($role == 'admin') {
    $users_count = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    $students_count = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'")->fetch()['count'];
    $employers_count = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'employer'")->fetch()['count'];
    $admins_count = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch()['count'];
    $jobs_count = $db->query("SELECT COUNT(*) as count FROM jobs")->fetch()['count'];
    $active_jobs = $db->query("SELECT COUNT(*) as count FROM jobs WHERE status = 'open'")->fetch()['count'];
    $applications_count = $db->query("SELECT COUNT(*) as count FROM applications")->fetch()['count'];

    $recent_users = $db->query("SELECT user_id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

    $recent_jobs = $db->query("SELECT j.job_id, j.title, j.budget, j.status, u.name as employer_name 
                              FROM jobs j JOIN users u ON j.posted_by = u.user_id 
                              ORDER BY j.created_at DESC LIMIT 5")->fetchAll();

    $user_roles = $db->query("SELECT role, COUNT(*) as total FROM users GROUP BY role")->fetchAll();
    $job_status = $db->query("SELECT status, COUNT(*) as total FROM jobs GROUP BY status")->fetchAll();
}

$page_title = 'Dashboard';
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title><?php echo $page_title; ?></title>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>

        <main class="main-content">
            <div class="dashboard-header">
                <h1>Dashboard Overview</h1>
                <p>Welcome back! Here's what's happening with your account.</p>
            </div>

            <div class="stats-grid">
                <?php if ($role == 'student'): ?>
                    <div class="stat-card primary"><i class="fas fa-file-alt"></i><div class="stat-number"><?php echo $total_applications; ?></div><div class="stat-label">Total Applications</div></div>
                    <div class="stat-card success"><i class="fas fa-briefcase"></i><div class="stat-number"><?php echo $active_jobs; ?></div><div class="stat-label">Active Jobs</div></div>
                    <div class="stat-card warning"><i class="fas fa-dollar-sign"></i><div class="stat-number">0</div><div class="stat-label">Total Earnings</div></div>
                    <div class="stat-card danger"><i class="fas fa-star"></i><div class="stat-number"><?php echo number_format($avg_rating, 1); ?></div><div class="stat-label">Average Rating</div></div>

                <?php elseif ($role == 'employer'): ?>
                    <div class="stat-card primary"><i class="fas fa-briefcase"></i><div class="stat-number"><?php echo $total_jobs; ?></div><div class="stat-label">Jobs Posted</div></div>
                    <div class="stat-card success"><i class="fas fa-users"></i><div class="stat-number"><?php echo $total_applications; ?></div><div class="stat-label">Applications Received</div></div>
                    <div class="stat-card warning"><i class="fas fa-clock"></i><div class="stat-number"><?php echo $active_jobs; ?></div><div class="stat-label">Active Projects</div></div>
                    <div class="stat-card danger"><i class="fas fa-check-circle"></i><div class="stat-number"><?php echo $completed_jobs; ?></div><div class="stat-label">Completed Projects</div></div>

                <?php elseif ($role == 'admin'): ?>
                    <div class="stat-card primary"><i class="fas fa-users"></i><div class="stat-number"><?php echo $users_count; ?></div><div class="stat-label">Total Users</div></div>
                    <div class="stat-card success"><i class="fas fa-graduation-cap"></i><div class="stat-number"><?php echo $students_count; ?></div><div class="stat-label">Students</div></div>
                    <div class="stat-card warning"><i class="fas fa-briefcase"></i><div class="stat-number"><?php echo $employers_count; ?></div><div class="stat-label">Employers</div></div>
                    <div class="stat-card danger"><i class="fas fa-user-shield"></i><div class="stat-number"><?php echo $admins_count; ?></div><div class="stat-label">Admins</div></div>
                    <div class="stat-card info"><i class="fas fa-tasks"></i><div class="stat-number"><?php echo $jobs_count; ?></div><div class="stat-label">Total Jobs</div></div>
                    <div class="stat-card secondary"><i class="fas fa-check-circle"></i><div class="stat-number"><?php echo $active_jobs; ?></div><div class="stat-label">Active Jobs</div></div>
                    <div class="stat-card dark"><i class="fas fa-file-alt"></i><div class="stat-number"><?php echo $applications_count; ?></div><div class="stat-label">Applications</div></div>
                <?php endif; ?>
            </div>

            <div class="dashboard-section">
                <?php if ($role == 'admin'): ?>
    <div class="charts-row">
        <div class="chart-container">
            <canvas id="userRolesChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="jobStatusChart"></canvas>
        </div>
        <a href="admin-export-applications.php" class="btn btn-secondary">Export Applications</a>
        <a href="export_jobs.php" class="btn btn-secondary">Export Jobs</a>
    </div>

    <style>
        .charts-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .chart-container {
            width: 400px;
            height: 400px;
        }
        canvas {
            width: 100% !important;
            height: 100% !important;
        }
    </style>

    <script>
        const rolesData = <?php echo json_encode($user_roles); ?>;
        const jobData = <?php echo json_encode($job_status); ?>;
        const roleLabels = rolesData.map(r => r.role);
        const roleCounts = rolesData.map(r => r.total);
        const jobLabels = jobData.map(j => j.status);
        const jobCounts = jobData.map(j => j.total);

        new Chart(document.getElementById('userRolesChart'), {
            type: 'pie',
            data: { 
                labels: roleLabels, 
                datasets: [{ 
                    data: roleCounts, 
                    backgroundColor: ['#4e73df','#1cc88a','#36b9cc','#f6c23e'] 
                }] 
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        new Chart(document.getElementById('jobStatusChart'), {
            type: 'bar',
            data: { 
                labels: jobLabels, 
                datasets: [{ 
                    label: 'Jobs', 
                    data: jobCounts, 
                    backgroundColor: '#36b9cc' 
                }] 
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
<?php endif; ?>


                <?php if ($role == 'student'): ?>
                <div class="section-header">
                    <h2 class="section-title">Recent Applications</h2>
                    <a href="jobs.php" class="btn btn-success">Browse Jobs</a>
                </div>

                <?php if (!empty($recent_applications)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Budget</th>
                                <th>Applied Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_applications as $app): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($app['title']); ?></td>
                                    <td>$<?php echo number_format($app['budget']); ?></td>
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
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-clipboard-list"></i>
                        <p>No applications yet. <a href="jobs.php">Browse jobs</a> to get started!</p>
                    </div>
                <?php endif; ?>

            <?php elseif ($role == 'employer'): ?>
                <div class="section-header">
                    <h2 class="section-title">Recent Jobs</h2>
                    <a href="post-job.php" class="btn btn-success">Post New Job</a>
                </div>

                <?php if (!empty($recent_jobs)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Budget</th>
                                <th>Posted Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_jobs as $job): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($job['title']); ?></td>
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

            <?php elseif ($role == 'admin'): ?>
                <div class="section-row">
                    <div class="section-col">
                        <div class="section-header">
                            <h2 class="section-title">Recent Users</h2>
                            <a href="admin-users.php" class="btn btn-sm">View All</a>
                        </div>

                        <?php if (!empty($recent_users)): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $user['role'] == 'admin' ? 'badge-danger' : ($user['role'] == 'employer' ? 'badge-warning' : 'badge-success'); ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <a href="admin-user-edit.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="admin-user-delete.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="no-data">
                                <i class="fas fa-users"></i>
                                <p>No users found</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="section-col">
                        <div class="section-header">
                            <h2 class="section-title">Recent Jobs</h2>
                            <a href="admin-jobs.php" class="btn btn-sm">View All</a>
                        </div>

                        <?php if (!empty($recent_jobs)): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Employer</th>
                                        <th>Budget</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_jobs as $job): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($job['title']); ?></td>
                                            <td><?php echo htmlspecialchars($job['employer_name']); ?></td>
                                            <td>$<?php echo number_format($job['budget']); ?></td>
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
                                                <a href="admin-job-edit.php?id=<?php echo $job['job_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="admin-job-delete.php?id=<?php echo $job['job_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="no-data">
                                <i class="fas fa-briefcase"></i>
                                <p>No jobs found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

