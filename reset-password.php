<?php
require_once 'config/init.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

if (!isset($_GET['token'])) {
    redirect('login.php');
}

$token = sanitize($_GET['token']);

// Verify token
$stmt = $db->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$reset_request = $stmt->fetch();

if (!$reset_request) {
    $_SESSION['errors'][] = "Invalid or expired reset token.";
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($password) || strlen($password) < 6) {
        $_SESSION['errors'][] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['errors'][] = "Passwords do not match";
    }

    if (empty($_SESSION['errors'])) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        if ($stmt->execute([$hashed_password, $reset_request['user_id']])) {
            // Delete reset token
            $db->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);
            
            $_SESSION['success'] = "Password reset successfully! Please login with your new password.";
            redirect('login.php');
        } else {
            $_SESSION['errors'][] = "Failed to reset password. Please try again.";
        }
    }
}

$page_title = 'Reset Password';
include 'includes/header.php';
?>

<div class="login-container">
    <div class="login-header">
        <h1><i class="fas fa-key"></i> Reset Password</h1>
        <p>Enter your new password</p>
    </div>

    <form method="POST">
        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>