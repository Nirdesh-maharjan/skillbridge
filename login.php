<?php
require_once 'config/init.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['errors'][] = "Please fill in all fields";
    } else {
        $stmt = $db->prepare("SELECT user_id, name, email, password, role, profile_pic FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                // Check if user is pretending to be admin
                if ($user['email'] === 'admin@yourdomain.com' && $user['role'] !== 'admin') {
                    $_SESSION['errors'][] = "Admin access denied";
                    redirect('login.php');
                }

                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_pic'] = $user['profile_pic'];

                // Handle Remember Me
                if (isset($_POST['remember_me'])) {
                    $token = bin2hex(random_bytes(16));
                    $stmt = $db->prepare("UPDATE users SET remember_token = ? WHERE user_id = ?");
                    $stmt->execute([$token, $user['user_id']]);
                    setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
                } else {
                    $stmt = $db->prepare("UPDATE users SET remember_token = NULL WHERE user_id = ?");
                    $stmt->execute([$user['user_id']]);
                }

                redirect('dashboard.php');
            } else {
                $_SESSION['errors'][] = "Invalid password";
            }
        } else {
            $_SESSION['errors'][] = "Account not found";
        }
    }
} else {
    // Clear session and remember_token cookie if not a POST request
    session_destroy();
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

$page_title = 'Login';
include 'includes/header.php';
?>

<div class="login-container">
    <div class="login-header">
        <h1><i class="fas fa-sign-in-alt"></i> Welcome Back</h1>
        <p>Sign in to your Skill Bridge account</p>
    </div>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; unset($_SESSION['errors']); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="login-form">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <div class="form-group form-check">
            <input type="checkbox" name="remember_me" id="remember_me" class="form-check-input">
            <label for="remember_me" class="form-check-label">Remember Me</label>
        </div>

        <div class="form-group">
            <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
        </div>

        <button type="submit" class="btn btn-primary">Sign In</button>
    </form>

    <div class="register-link">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
