<?php
require_once 'config/init.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

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
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_pic'] = $user['profile_pic'];
                
                redirect('dashboard.php');
            } else {
                $_SESSION['errors'][] = "Invalid password";
            }
        } else {
            $_SESSION['errors'][] = "Account not found";
        }
    }
}

$page_title = 'Login';
include 'includes/header.php';
?>

<div class="login-container">
    <div class="login-header">
        <h1><i class="fas fa-sign-in-alt"></i> Welcome Back</h1>
        <p>Sign in to your Skill Bridge account</p>
    </div>

    <form method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
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