<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Skill Bridge' : 'Skill Bridge - Student Freelance Portal'; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="<?php echo BASE_URL; ?>" class="logo">
                    <i class="fas fa-bridge"></i> Skill Bridge
                </a>
                <ul class="nav-links">
                    <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>jobs.php">Browse Jobs</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?php echo BASE_URL; ?>dashboard.php">Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>profile.php">Profile</a></li>
                        <li><a href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>login.php">Login</a></li>
                        <li><a href="<?php echo BASE_URL; ?>register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
                <div class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <div class="mobile-menu">
        <ul>
            <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
            <li><a href="<?php echo BASE_URL; ?>jobs.php">Browse Jobs</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="<?php echo BASE_URL; ?>dashboard.php">Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>profile.php">Profile</a></li>
                <li><a href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo BASE_URL; ?>login.php">Login</a></li>
                <li><a href="<?php echo BASE_URL; ?>register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <main class="container">
        <?php flashMessages(); ?>