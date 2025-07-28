<?php
require_once 'config/init.php';

if (!isAdmin()) {
    redirect('index.php');
}

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$role = isset($_GET['role']) ? sanitize($_GET['role']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'created_at DESC';

// Build query
$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($role) && in_array($role, ['student', 'employer', 'admin'])) {
    $query .= " AND role = ?";
    $params[] = $role;
}

$query .= " ORDER BY $sort";

// Get all users
$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

$page_title = 'Manage Users';
include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/navigation.php'; ?>

    <main class="main-content">
        <div class="dashboard-header">
            <h1>Manage Users</h1>
            <p>View, suspend, activate, reset, or delete user accounts</p>
        </div>

        <!-- Filter Form -->
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Search users..." class="form-control" 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group">
                    <select name="role" class="form-control">
                        <option value="">All Roles</option>
                        <option value="student" <?php echo $role == 'student' ? 'selected' : ''; ?>>Students</option>
                        <option value="employer" <?php echo $role == 'employer' ? 'selected' : ''; ?>>Employers</option>
                        <option value="admin" <?php echo $role == 'admin' ? 'selected' : ''; ?>>Admins</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="sort" class="form-control">
                        <option value="created_at DESC" <?php echo $sort == 'created_at DESC' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="created_at ASC" <?php echo $sort == 'created_at ASC' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="name ASC" <?php echo $sort == 'name ASC' ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="name DESC" <?php echo $sort == 'name DESC' ? 'selected' : ''; ?>>Name (Z-A)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="admin-users.php" class="btn btn-secondary">Reset</a>
            </form>
        </div>

        <!-- Users Table -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">All Users</h2>
            </div>

            <?php if (!empty($users)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <img src="<?php echo $user['profile_pic'] ? htmlspecialchars($user['profile_pic']) : 'assets/images/default-profile.png'; ?>" 
                                             class="profile-pic-sm">
                                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['role'] == 'admin' ? 'badge-danger' : ($user['role'] == 'employer' ? 'badge-warning' : 'badge-success'); ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $user['is_active'] ? 'Active' : 'Suspended'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <a href="manage-user.php?action=suspend&id=<?php echo $user['user_id']; ?>" class="btn btn-warning btn-sm">Suspend</a>
                                    <?php else: ?>
                                        <a href="manage-user.php?action=activate&id=<?php echo $user['user_id']; ?>" class="btn btn-success btn-sm">Activate</a>
                                    <?php endif; ?>
                                    <a href="manage-user.php?action=reset&id=<?php echo $user['user_id']; ?>" class="btn btn-primary btn-sm">Reset Password</a>
                                    <a href="admin-user-delete.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? This cannot be undone.')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-users"></i>
                    <p>No users found matching your criteria</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
