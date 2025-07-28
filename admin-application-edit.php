<?php
require_once 'config/init.php';

if (!isAdmin()) {
    die("Access denied");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid application ID.");
}

$app_id = (int)$_GET['id'];

// Fetch application details
$stmt = $db->prepare("SELECT a.*, u.email, u.name, j.title AS job_title 
                      FROM applications a
                      JOIN users u ON a.user_id = u.user_id
                      JOIN jobs j ON a.job_id = j.job_id
                      WHERE a.app_id = ?");
$stmt->execute([$app_id]);
$app = $stmt->fetch();

if (!$app) {
    die("Application not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'];

    if (!in_array($new_status, ['pending', 'accepted', 'rejected'])) {
        die("Invalid status.");
    }

    // Update application status
    $update = $db->prepare("UPDATE applications SET status = ? WHERE app_id = ?");
    $update->execute([$new_status, $app_id]);

    // Send email notification to student
    $to = $app['email'];
    $subject = "Application Update for Job: " . $app['job_title'];
    $message = "Dear " . $app['name'] . ",\n\n";
    if ($new_status == 'accepted') {
        $message .= "Congratulations! Your application for the job \"" . $app['job_title'] . "\" has been accepted.\n";
    } elseif ($new_status == 'rejected') {
        $message .= "We regret to inform you that your application for the job \"" . $app['job_title'] . "\" has been rejected.\n";
    } else {
        $message .= "Your application status for the job \"" . $app['job_title'] . "\" is now set to 'pending'.\n";
    }
    $message .= "\nThank you for using Skill Bridge.";

    // Use PHP's mail() function or integrate with SMTP later
    mail($to, $subject, $message);

    $_SESSION['success'] = "Application status updated and email sent.";
    redirect('admin-applications.php');
}
?>

<?php include 'includes/header.php'; ?>
<div class="container mt-4">
    <h2>Edit Application Status</h2>
    <form method="post">
        <div class="form-group">
            <label>Job Title</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($app['job_title']) ?>" disabled>
        </div>
        <div class="form-group">
            <label>Student</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($app['name']) ?> (<?= htmlspecialchars($app['email']) ?>)" disabled>
        </div>
        <div class="form-group">
            <label>Proposal</label>
            <textarea class="form-control" rows="5" disabled><?= htmlspecialchars($app['proposal']) ?></textarea>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="pending" <?= $app['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="accepted" <?= $app['status'] == 'accepted' ? 'selected' : '' ?>>Accepted</option>
                <option value="rejected" <?= $app['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Update Status</button>
        <a href="admin-applications.php" class="btn btn-secondary">Back</a>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
