<?php
require_once 'config/init.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$page_title = 'Forgot Password';
include 'includes/header.php';
?>

<div class="login-container">
    <div class="login-header">
        <h1><i class="fas fa-key"></i> Forgot Password</h1>
        <p>Enter your email to receive a password reset link</p>
    </div>

    <form method="POST" action="send-reset-link.php">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Send Reset Link</button>
    </form>

    <div class="login-link">
        Remember your password? <a href="login.php">Login here</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>