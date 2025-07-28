<?php
require_once 'config/init.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['errors'][] = "Valid email is required";
        redirect('forgot-password.php');
    }

    // Check if email exists
    $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Delete any existing tokens for this user
        $db->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['user_id']]);
        
        // Store token
        $stmt = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        if ($stmt->execute([$user['user_id'], $token, $expires_at])) {
            // Send email with reset link (in a real app, you would implement this)
            $reset_link = BASE_URL . "reset-password.php?token=$token";
            
            // For demo purposes, we'll just show the link
            $_SESSION['success'] = "Password reset link generated. In a real app, this would be emailed to you.<br>For demo: <a href='$reset_link'>$reset_link</a>";
        } else {
            $_SESSION['errors'][] = "Failed to generate reset link. Please try again.";
        }
    } else {
        $_SESSION['errors'][] = "Email not found in our system.";
    }
}

redirect('forgot-password.php');
?>