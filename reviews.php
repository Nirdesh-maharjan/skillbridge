<?php
require_once 'config/init.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$page_title = 'Reviews';
include 'includes/header.php';

// Get reviews for the current user
$stmt = $db->prepare("SELECT r.*, u.name as reviewer_name, u.profile_pic as reviewer_pic, j.title as job_title
                     FROM reviews r
                     JOIN users u ON r.reviewer_id = u.user_id
                     JOIN jobs j ON r.job_id = j.job_id
                     WHERE r.reviewee_id = ?
                     ORDER BY r.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$reviews = $stmt->fetchAll();

// Get average rating
$stmt = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE reviewee_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$rating_data = $stmt->fetch();
$avg_rating = $rating_data['avg_rating'];
$total_reviews = $rating_data['total_reviews'];

// Only show "Leave Review" section for students
$can_review_jobs = [];
if (isStudent()) {
    $stmt = $db->prepare("SELECT j.job_id, j.title, u.user_id as employer_id, u.name as employer_name
                         FROM jobs j
                         JOIN users u ON j.posted_by = u.user_id
                         WHERE j.status = 'completed' 
                         AND j.job_id IN (
                             SELECT job_id FROM job_applications 
                             WHERE student_id = ? AND status = 'completed'
                         )
                         AND j.job_id NOT IN (
                             SELECT job_id FROM reviews 
                             WHERE reviewer_id = ?
                         )");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $can_review_jobs = $stmt->fetchAll();
}
?>

<div class="dashboard">
    <?php include 'includes/navigation.php'; ?>

    <main class="main-content">
        <div class="dashboard-header">
            <h1>Reviews</h1>
            <p>Feedback from <?php echo isStudent() ? 'employers' : 'students'; ?></p>
        </div>

        <div class="reviews-summary">
            <div class="average-rating">
                <div class="rating-number"><?php echo number_format($avg_rating, 1); ?></div>
                <div class="rating-stars">
                    <?php
                    $full_stars = floor($avg_rating);
                    $half_star = ($avg_rating - $full_stars) >= 0.5;
                    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                    
                    for ($i = 0; $i < $full_stars; $i++) {
                        echo '<i class="fas fa-star"></i>';
                    }
                    
                    if ($half_star) {
                        echo '<i class="fas fa-star-half-alt"></i>';
                    }
                    
                    for ($i = 0; $i < $empty_stars; $i++) {
                        echo '<i class="far fa-star"></i>';
                    }
                    ?>
                </div>
                <div class="rating-count">Based on <?php echo $total_reviews; ?> reviews</div>
            </div>
        </div>

        <?php if (isStudent() && !empty($can_review_jobs)): ?>
        <div class="dashboard-section">
            <h2 class="section-title">Leave Reviews</h2>
            <div class="jobs-to-review">
                <?php foreach ($can_review_jobs as $job): ?>
                <div class="job-card">
                    <h4><?php echo htmlspecialchars($job['title']); ?></h4>
                    <p>Employer: <?php echo htmlspecialchars($job['employer_name']); ?></p>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#reviewModal" 
                            data-jobid="<?php echo $job['job_id']; ?>" data-employerid="<?php echo $job['employer_id']; ?>">
                        Leave Review
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="dashboard-section">
            <h2 class="section-title">All Reviews</h2>
            
            <?php if (!empty($reviews)): ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <img src="<?php echo $review['reviewer_pic'] ? htmlspecialchars($review['reviewer_pic']) : 'assets/images/default-profile.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($review['reviewer_name']); ?>" class="profile-pic-sm">
                                <div class="reviewer-info">
                                    <h4><?php echo htmlspecialchars($review['reviewer_name']); ?></h4>
                                    <div class="review-rating">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $review['rating']) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <p class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></p>
                                </div>
                            </div>
                            <div class="review-content">
                                <h5>Job: <?php echo htmlspecialchars($review['job_title']); ?></h5>
                                <p><?php echo htmlspecialchars($review['comments']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-star"></i>
                    <p>No reviews yet. <?php echo isStudent() ? 'Complete jobs to receive reviews.' : 'Hire students to leave reviews.'; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

