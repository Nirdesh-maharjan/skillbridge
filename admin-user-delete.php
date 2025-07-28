<?php
require_once 'config/init.php';

// Only allow admins to access this page
if (!isAdmin()) {
    $_SESSION['error'] = "You don't have permission to access this page";
    redirect('index.php');
}

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "User ID is missing";
    redirect('admin-users.php');
}

$user_id = (int)$_GET['id'];

// Prevent admin from deleting themselves
if ($user_id === $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account";
    redirect('admin-users.php');
}

// Get user data before deleting (optional, for logging purposes)
$stmt = $db->prepare("SELECT name, email FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = "User not found";
    redirect('admin-users.php');
}

// Handle the deletion when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $db->beginTransaction();

        // Option 1: Soft delete (recommended - sets is_active to 0)
        // $stmt = $db->prepare("UPDATE users SET is_active = 0 WHERE user_id = ?");
        
        // Option 2: Hard delete (permanently removes record)
        $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
        
        $stmt->execute([$user_id]);

        // Log the deletion (optional)
        $log_message = "Admin {$_SESSION['user_id']} deleted user {$user_id} ({$user['email']})";
        logAction($log_message); // You would need to implement this function

        $db->commit();

        $_SESSION['success'] = "User {$user['name']} has been deleted successfully";
        redirect('admin-users.php');
    } catch (PDOException $e) {
        $db->rollBack();
        $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
        redirect('admin-users.php');
    }
}

$page_title = "Delete User";
include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/navigation.php'; ?>

    <main class="main-content">
        <div class="dashboard-header">
            <h1>Delete User</h1>
            <p>Confirm deletion of user account</p>
        </div>

        <div class="dashboard-section">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Warning:</strong> This action cannot be undone. The user will be permanently removed from the system.
            </div>

            <div class="user-details">
                <h3>User Details</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>User ID:</strong> <?php echo $user_id; ?></p>
            </div>

            <form method="POST" class="delete-form">
                <div class="form-group">
                    <label for="confirm">Type "DELETE" to confirm:</label>
                    <input type="text" id="confirm" name="confirm" class="form-control" required
                           pattern="DELETE" title="Please type DELETE to confirm">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Confirm Delete
                    </button>
                    <a href="admin-users.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>

