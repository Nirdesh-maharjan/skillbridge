<?php
require_once 'config/init.php';

$category = isset($_POST['category']) ? sanitize($_POST['category']) : '';
$budget_min = isset($_POST['budget_min']) ? (int)$_POST['budget_min'] : 0;
$budget_max = isset($_POST['budget_max']) ? (int)$_POST['budget_max'] : 999999;

$query = "SELECT * FROM jobs WHERE status = 'open'";
$params = [];

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

if ($budget_min > 0) {
    $query .= " AND budget >= ?";
    $params[] = $budget_min;
}

if ($budget_max > 0) {
    $query .= " AND budget <= ?";
    $params[] = $budget_max;
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

if ($jobs):
    foreach ($jobs as $job): ?>
        <div class="job-card">
            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
            <p><?php echo htmlspecialchars(substr($job['description'], 0, 100)); ?>...</p>
            <p><strong>Budget:</strong> $<?php echo number_format($job['budget']); ?></p>
            <a href="job-details.php?id=<?php echo $job['job_id']; ?>" class="btn btn-primary">View Details</a>
        </div>
    <?php endforeach;
else: ?>
    <p>No jobs found matching your filters.</p>
<?php endif; ?>
