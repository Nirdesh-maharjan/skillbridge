<?php
require_once 'config/init.php';

if (!isEmployer()) redirect('index.php');
if (!isset($_GET['job_id']) || !is_numeric($_GET['job_id'])) redirect('my-jobs.php');

$job_id = (int)$_GET['job_id'];

//  Verify job ownership
$stmt = $db->prepare("SELECT title FROM jobs WHERE job_id = ? AND posted_by = ?");
$stmt->execute([$job_id, $_SESSION['user_id']]);
$job = $stmt->fetch();
if (!$job) redirect('my-jobs.php');

//  Status filter
$status_filter = $_GET['status'] ?? 'pending';
$valid_status = ['pending', 'accepted', 'rejected'];
if (!in_array($status_filter, $valid_status)) $status_filter = 'pending';

//  Fetch applications
$stmt = $db->prepare("SELECT a.*, u.name as student_name, u.email as student_email, 
                             u.profile_pic as student_pic, u.skills as student_skills 
                      FROM applications a 
                      JOIN users u ON a.user_id = u.user_id 
                      WHERE a.job_id = ? AND a.status = ?
                      ORDER BY a.date_applied DESC");
$stmt->execute([$job_id, $status_filter]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Job Applications';
include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/navigation.php'; ?>
    <main class="main-content">
        <h1>Applications for: <?php echo htmlspecialchars($job['title']); ?></h1>

        <form method="GET" class="filter-form">
            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
            <select name="status" onchange="this.form.submit()">
                <option value="pending" <?php if($status_filter=='pending') echo 'selected'; ?>>Pending</option>
                <option value="accepted" <?php if($status_filter=='accepted') echo 'selected'; ?>>Accepted</option>
                <option value="rejected" <?php if($status_filter=='rejected') echo 'selected'; ?>>Rejected</option>
            </select>
        </form>

        <div class="dashboard-section">
            <?php if ($applications): ?>
                <div class="applications-list">
                    <?php foreach ($applications as $app): ?>
                        <div class="application-card" data-app-id="<?php echo $app['app_id']; ?>">
                            <div class="application-header">
                                <img src="<?php echo $app['student_pic'] ?: 'assets/images/default-profile.png'; ?>" 
                                     alt="" class="profile-pic-sm">
                                <div class="application-info">
                                    <h3><?php echo htmlspecialchars($app['student_name']); ?></h3>
                                    <p><?php echo htmlspecialchars($app['student_email']); ?></p>
                                    <p class="skills">Skills: <?php echo htmlspecialchars($app['student_skills']); ?></p>
                                    <p class="application-date">Applied on <?php echo date('M d, Y', strtotime($app['date_applied'])); ?></p>
                                </div>
                                <span class="badge status-badge <?php echo $app['status'] == 'accepted' ? 'badge-success' : ($app['status']=='rejected'?'badge-danger':'badge-info'); ?>">
                                    <?php echo ucfirst($app['status']); ?>
                                </span>
                            </div>
                            <div class="application-content">
                                <h4>Proposal</h4>
                                <p><?php echo nl2br(htmlspecialchars($app['proposal'])); ?></p>
                            </div>
                            <div class="application-actions">
                                <?php if ($app['status'] == 'pending'): ?>
                                    <button class="btn btn-success update-status" data-action="accept">Accept</button>
                                    <button class="btn btn-danger update-status" data-action="reject">Reject</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No applications found.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
const csrfToken = "<?php echo $_SESSION['csrf_token']; ?>"; //  Embed CSRF token in JS

document.querySelectorAll('.update-status').forEach(btn => {
    btn.addEventListener('click', function(){
        const action = this.dataset.action;
        const card = this.closest('.application-card');
        const appId = card.dataset.appId;

        fetch('process-applications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `id=${appId}&action=${action}&csrf_token=${csrfToken}` //  Send CSRF token
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                const badge = card.querySelector('.status-badge');
                badge.textContent = data.status;
                badge.className = 'badge status-badge ' + (data.status === 'accepted' ? 'badge-success' : 'badge-danger');
                card.querySelector('.application-actions').innerHTML = '<span class="btn disabled">Processed</span>';
            }
        });
    });
});
</script>
