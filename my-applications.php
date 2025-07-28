<?php
require_once 'config/init.php';

if (!isStudent()) {
    redirect('index.php');  // Redirect to home or login if not student
}

$page_title = "My Applications";
include 'includes/header.php';

// Get applications submitted by the logged-in student
$stmt = $db->prepare("
    SELECT a.*, j.title AS job_title, j.status AS job_status
    FROM applications a
    JOIN jobs j ON a.job_id = j.job_id
    WHERE a.user_id = ?
    ORDER BY a.date_applied DESC
");
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll();
?>

<div class="dashboard">
    <?php include 'includes/navigation.php'; // This includes the sidebar ?>

    <main class="main-content">
        <div class="container mt-4">
            <h2 class="mb-4">My Applications</h2>

            <?php if (count($applications) === 0): ?>
                <div class="alert alert-info">You haven't applied to any jobs yet.</div>
            <?php else: ?>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Proposal</th>
                            <th>Status</th>
                            <th>Date Applied</th>
                            <th>Job Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?= htmlspecialchars($app['job_title']) ?></td>
                                <td><?= nl2br(htmlspecialchars($app['proposal'])) ?></td>
                                <td><span class="badge badge-<?= htmlspecialchars($app['status']) ?>"><?= ucfirst(htmlspecialchars($app['status'])) ?></span></td>
                                <td><?= htmlspecialchars($app['date_applied']) ?></td>
                                <td><?= ucfirst(htmlspecialchars($app['job_status'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?>
