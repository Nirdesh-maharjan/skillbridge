<?php
require_once 'config/init.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize($_POST['role']);

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (!in_array($role, ['student', 'employer'])) {
        $errors[] = "Invalid role selected";
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already registered";
        }
    }

    // Insert user if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $email, $hashed_password, $role])) {
            $_SESSION['success'] = "Registration successful! Please login.";
            redirect('login.php');
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}

$selected_role = isset($_GET['type']) && in_array($_GET['type'], ['student', 'employer']) ? $_GET['type'] : 'student';
$page_title = 'Register';
include 'includes/header.php';
?>

<div class="register-container">
    <div class="register-header">
        <h1><i class="fas fa-user-plus"></i> Join Skill Bridge</h1>
        <p>Create your account and start your freelance journey</p>
    </div>

    <form method="POST">
        <div class="form-group">
            <label>I want to:</label>
            <div class="role-selection">
                <div class="role-card <?php echo $selected_role == 'student' ? 'selected' : ''; ?>" onclick="selectRole('student')">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>Find Work</h3>
                    <p>As a Student</p>
                </div>
                <div class="role-card <?php echo $selected_role == 'employer' ? 'selected' : ''; ?>" onclick="selectRole('employer')">
                    <i class="fas fa-briefcase"></i>
                    <h3>Hire Students</h3>
                    <p>As an Employer</p>
                </div>
            </div>
            <input type="hidden" name="role" id="role" value="<?php echo $selected_role; ?>">
        </div>

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-control" required 
                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
            <div class="password-strength">
                <div class="password-strength-bar"></div>
            </div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Create Account</button>
    </form>

    <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

<script>
function selectRole(role) {
    document.getElementById('role').value = role;
    document.querySelectorAll('.role-card').forEach(card => {
        card.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
}
</script>

<?php include 'includes/footer.php'; ?>