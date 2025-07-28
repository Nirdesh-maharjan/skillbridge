<?php
require_once 'config/init.php';

if (!isAdmin()) {
    redirect('index.php');
}

if (!isset($_GET['id'], $_GET['action'])) {
    redirect('admin-users.php');
}

$user_id = (int)$_GET['id'];
$action = $_GET['action'];

switch ($action) {
    case 'suspend':
        $stmt = $db->prepare("UPDATE users SET is_active = 0 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['success'] = "User suspended successfully.";
        break;

    case 'activate':
        $stmt = $db->prepare("UPDATE users SET is_active = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['success'] = "User account activated.";
        break;

    case 'reset':
        $new_password = password_hash('default123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$new_password, $user_id]);
        $_SESSION['success'] = "Password reset to default123.";
        break;

    case 'delete':
        $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['success'] = "User deleted successfully.";
        break;

    default:
        $_SESSION['errors'][] = "Invalid action.";
        break;
}

redirect('admin-users.php');
