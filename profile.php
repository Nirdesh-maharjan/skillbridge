<?php
require_once 'config/init.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Get user profile
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $bio = sanitize($_POST['bio']);
    $skills = sanitize($_POST['skills']);
    $education = sanitize($_POST['education']);
    
    // For students
    $portfolio_url = isset($_POST['portfolio_url']) ? sanitize($_POST['portfolio_url']) : '';
    
    // For employers
    $company_name = isset($_POST['company_name']) ? sanitize($_POST['company_name']) : '';
    $company_website = isset($_POST['company_website']) ? sanitize($_POST['company_website']) : '';

    // Validation
    if (empty($name)) {
        $_SESSION['errors'][] = "Name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['errors'][] = "Valid email is required";
    }
    
    if (empty($bio)) {
        $_SESSION['errors'][] = "Bio is required";
    }

    // Check if email is changed and already exists
    if ($email != $user['email']) {
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['errors'][] = "Email already registered by another user";
        }
    }

    // Handle file upload (profile picture)
    $profile_pic = $user['profile_pic'];
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_pic']['type'];
        $file_size = $_FILES['profile_pic']['size'];
        
        if (in_array($file_type, $allowed_types)) {
            if ($file_size <= 2 * 1024 * 1024) { // 2MB max
                $upload_dir = 'uploads/profile_pics/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                $file_name = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $file_path)) {
                    // Delete old profile pic if exists
                    if ($profile_pic && file_exists($profile_pic)) {
                        unlink($profile_pic);
                    }
                    $profile_pic = $file_path;
                } else {
                    $_SESSION['errors'][] = "Failed to upload profile picture";
                }
            } else {
                $_SESSION['errors'][] = "Profile picture must be less than 2MB";
            }
        } else {
            $_SESSION['errors'][] = "Only JPG, PNG, and GIF images are allowed";
        }
    }

    if (empty($_SESSION['errors'])) {
        if (isStudent()) {
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, bio = ?, skills = ?, education = ?, portfolio_url = ?, profile_pic = ? WHERE user_id = ?");
            $stmt->execute([$name, $email, $bio, $skills, $education, $portfolio_url, $profile_pic, $_SESSION['user_id']]);
        } else {
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, bio = ?, company_name = ?, company_website = ?, profile_pic = ? WHERE user_id = ?");
            $stmt->execute([$name, $email, $bio, $company_name, $company_website, $profile_pic, $_SESSION['user_id']]);
        }
        
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['profile_pic'] = $profile_pic;
        $_SESSION['success'] = "Profile updated successfully!";
        redirect('profile.php');
    }
}

$page_title = 'My Profile';
include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/navigation.php'; ?>

    <main class="main-content">
        <div class="dashboard-header">
            <h1>My Profile</h1>
            <p>Manage your profile information</p>
        </div>

        <div class="dashboard-section">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="profile_pic">Profile Picture</label>
                        <div class="profile-pic-container">
                            <img src="<?php echo $user['profile_pic'] ? htmlspecialchars($user['profile_pic']) : 'assets/images/default-profile.png'; ?>" 
                                 alt="Profile Picture" class="profile-pic" id="profilePicPreview">
                            <div class="profile-pic-upload-text">Click to change</div>
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                        </div>
                        <div id="fileValidation" class="file-validation"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required 
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($user['name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($user['email']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" class="form-control" rows="3" required><?php 
                        echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : htmlspecialchars($user['bio']); 
                    ?></textarea>
                </div>
                
                <?php if (isStudent()): ?>
                    <div class="form-group">
                        <label for="skills">Skills (comma separated)</label>
                        <input type="text" id="skills" name="skills" class="form-control" 
                               value="<?php echo isset($_POST['skills']) ? htmlspecialchars($_POST['skills']) : htmlspecialchars($user['skills']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="education">Education</label>
                        <textarea id="education" name="education" class="form-control" rows="2"><?php 
                            echo isset($_POST['education']) ? htmlspecialchars($_POST['education']) : htmlspecialchars($user['education']); 
                        ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="portfolio_url">Portfolio URL</label>
                        <input type="url" id="portfolio_url" name="portfolio_url" class="form-control" 
                               value="<?php echo isset($_POST['portfolio_url']) ? htmlspecialchars($_POST['portfolio_url']) : htmlspecialchars($user['portfolio_url']); ?>">
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <input type="text" id="company_name" name="company_name" class="form-control" 
                               value="<?php echo isset($_POST['company_name']) ? htmlspecialchars($_POST['company_name']) : htmlspecialchars($user['company_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="company_website">Company Website</label>
                        <input type="url" id="company_website" name="company_website" class="form-control" 
                               value="<?php echo isset($_POST['company_website']) ? htmlspecialchars($_POST['company_website']) : htmlspecialchars($user['company_website']); ?>">
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </main>
</div>
