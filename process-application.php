<?php
require_once 'config/init.php';

//  Only employers can process applications
if (!isEmployer()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

//  Ensure it's an AJAX POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'xmlhttprequest') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

//  CSRF validation
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'CSRF Validation Failed']);
    exit;
}

$app_id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

//  Validate action
$new_status = $action === 'accept' ? 'accepted' : ($action === 'reject' ? 'rejected' : '');
if (!$new_status) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

//  Fetch application and verify job ownership
$stmt = $db->prepare("SELECT a.*, u.user_id AS student_id, j.job_id, j.posted_by 
                      FROM applications a 
                      JOIN users u ON a.user_id = u.user_id 
                      JOIN jobs j ON a.job_id = j.job_id 
                      WHERE a.app_id = ?");
$stmt->execute([$app_id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    echo json_encode(['success' => false, 'message' => 'Application not found']);
    exit;
}

//  Ensure employer owns the job
if ((int)$app['posted_by'] !== (int)$_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

//  Update application status
$stmt = $db->prepare("UPDATE applications SET status = ? WHERE app_id = ?");
$updated = $stmt->execute([$new_status, $app_id]);

if ($updated) {
    //  Insert notification for the student
    $notif_stmt = $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notif_stmt->execute([$app['student_id'], 'Your application for Job #' . $app['job_id'] . ' has been ' . $new_status]);

    echo json_encode([
        'success' => true,
        'status' => $new_status,
        'message' => "Application {$new_status} successfully"
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}
?>
