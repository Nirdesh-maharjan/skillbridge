<aside class="sidebar">
    <div class="sidebar-header">
    <div class="sidebar-user-pic">
        <img src="<?php echo $_SESSION['profile_pic'] ?? 'assets/images/default-profile.png'; ?>" alt="Profile Picture">
    </div>
    <div class="sidebar-user-info">
        <h2><?php echo htmlspecialchars($_SESSION['name']); ?></h2>
        <p><?php echo ucfirst($_SESSION['role']); ?></p>
    </div>
</div>

    <ul class="sidebar-menu">
        <li><a href="<?php echo BASE_URL; ?>dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        
        <?php if (isStudent()): ?>
            <li><a href="<?php echo BASE_URL; ?>jobs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'jobs.php' ? 'active' : ''; ?>">
                <i class="fas fa-search"></i> Browse Jobs</a></li>
            <li><a href="<?php echo BASE_URL; ?>applications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'applications.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i> My Applications</a></li>
        <?php else: ?>
            <li><a href="<?php echo BASE_URL; ?>post-job.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'post-job.php' ? 'active' : ''; ?>">
                <i class="fas fa-plus"></i> Post Job</a></li>
            <li><a href="<?php echo BASE_URL; ?>my-jobs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'my-jobs.php' ? 'active' : ''; ?>">
                <i class="fas fa-briefcase"></i> My Jobs</a></li>
            <li><a href="<?php echo BASE_URL; ?>applications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'applications.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Applications</a></li>
        <?php endif; ?>
        
        <li><a href="<?php echo BASE_URL; ?>reviews.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : ''; ?>">
            <i class="fas fa-star"></i> Reviews</a></li>
        <li><a href="<?php echo BASE_URL; ?>profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i> Profile</a></li>
        <li><a href="<?php echo BASE_URL; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</aside>