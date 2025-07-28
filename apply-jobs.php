<?php
require_once 'config/init.php';

if (!isStudent()) {
    redirect('index.php');
}

//  Detect AJAX request
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //  CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        if ($is_ajax) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF Validation Failed']);
            exit;
        }
        $_SESSION['errors'][] = "Security token mismatch!";
        redirect('jobs.php');
    }

    $job_id = (int)$_POST['job_id'];
    $proposal = sanitize($_POST['proposal'] ?? '');

    $user_id = $_SESSION['user_id'];

    //  Verify job exists and is open
    $stmt = $db->prepare("SELECT job_id FROM jobs WHERE job_id = ? AND status = 'open'");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch();

    if (!$job) {
        $error_msg = "Job not available for application.";
        if ($is_ajax) {
            echo json_encode(['status' => 'error', 'message' => $error_msg]);
            exit;
        }
        $_SESSION['errors'][] = $error_msg;
        redirect('jobs.php');
    }

    //  Check if already applied
    $stmt = $db->prepare("SELECT app_id FROM applications WHERE job_id = ? AND user_id = ?");
    $stmt->execute([$job_id, $user_id]);
    if ($stmt->rowCount() > 0) {
        $error_msg = "You have already applied for this job.";
        if ($is_ajax) {
            echo json_encode(['status' => 'error', 'message' => $error_msg]);
            exit;
        }
        $_SESSION['errors'][] = $error_msg;
        redirect("job-details.php?id=$job_id");
    }

    //  Submit application
    $stmt = $db->prepare("INSERT INTO applications (job_id, user_id, proposal, date_applied, status) VALUES (?, ?, ?, NOW(), 'pending')");
    $success = $stmt->execute([$job_id, $user_id, $proposal]);

    //  Add Notification for Employer
    if ($success) {
        $emp_stmt = $db->prepare("SELECT posted_by FROM jobs WHERE job_id = ?");
        $emp_stmt->execute([$job_id]);
        $employer = $emp_stmt->fetchColumn();

        $notif_stmt = $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notif_stmt->execute([$employer, 'A student has applied for your job: '.$job_id]);
    }

    //  AJAX Response
    if ($is_ajax) {
        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Application submitted successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to submit application. Please try again.']);
        }
        exit;
    }

    //  Normal response
    if ($success) {
        $_SESSION['success'] = "Application submitted successfully!";
    } else {
        $_SESSION['errors'][] = "Failed to submit application. Please try again.";
    }

    redirect("job-details.php?id=$job_id");
} else {
    redirect('jobs.php');
}
?>
