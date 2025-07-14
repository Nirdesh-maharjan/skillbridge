<?php
require_once 'config/init.php';

$page_title = 'Home';
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <section class="hero">
    <div class="container">
        <h1>Connect Students with Opportunities</h1>
        <p>The premier freelance platform designed exclusively for students to showcase their skills and find flexible work opportunities.</p>
        <div class="cta-buttons">
            <a href="register.php?type=student" class="btn btn-primary">Join as Student</a>
            <a href="register.php?type=employer" class="btn btn-secondary">Hire Students</a>
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <h2 class="section-title">Why Choose Skill Bridge?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Student-Focused</h3>
                <p>Designed specifically for students with flexible schedules and growing skill sets.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>Real Experience</h3>
                <p>Gain practical experience while building your portfolio and earning money.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3>Fair Pricing</h3>
                <p>Competitive rates that work for both students and budget-conscious employers.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Secure Platform</h3>
                <p>Safe and secure environment with verified users and secure payment processing.</p>
            </div>
        </div>
    </div>
</section>
</body>
</html>

<?php include 'includes/footer.php'; ?>