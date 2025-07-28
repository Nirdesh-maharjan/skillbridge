<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Skill Bridge' : 'Skill Bridge - Student Freelance Portal'; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Notification Styles */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ff4757;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
        }

        .notification-dropdown {
            position: absolute;
            right: 0;
            top: 100%;
            width: 350px;
            max-height: 400px;
            overflow-y: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            z-index: 1000;
            display: none;
            padding: 10px 0;
        }

        .notification-dropdown.show {
            display: block;
        }

        .notification-item {
            padding: 12px 15px;
            border-bottom: 1px solid #1b1515ff;
            transition: background 0.2s;
            cursor: pointer;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item.unread {
            background: #f8f9ff;
        }

        .notification-message {
            font-size: 14px;
            color: black;
            margin-bottom: 5px;
        }

        .notification-time {
            font-size: 12px;
            color: #6c757d;
        }

        .notification-popup {
            position: fixed;
            top: 80px;
            right: 20px;
            background: #fff;
            color: #333;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 9999;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            border-left: 4px solid #4e73df;
            max-width: 300px;
            transform: translateX(120%);
            transition: transform 0.3s ease-out;
            cursor: pointer;
        }

        .notification-popup.show {
            transform: translateX(0);
        }

        .notification-popup .close-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: none;
            border: none;
            font-size: 12px;
            cursor: pointer;
            color: #000000ff;
        }

        .notification-header {
            padding: 10px 15px;
            border-bottom: 1px solid #030303ff;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header .mark-all-read {
            font-size: 12px;
            color: #4e73df;
            cursor: pointer;
        }

        .notification-item.empty {
            color: #6c757d;
            text-align: center;
            padding: 20px;
        }
    </style>
</head>
<body>
<header>
    <div class="container">
        <nav>
            <a href="<?php echo BASE_URL; ?>" class="logo">
                <i class="fas fa-bridge"></i> Skill Bridge
            </a>
            <ul class="nav-links">
                <li class="nav-item">
                    <div class="notification-icon">
                        <a href="#" id="notificationsMenu">
                            <i class="fas fa-bell"></i>
                            <span id="notifCount" class="notification-badge" style="display:none;">0</span>
                        </a>
                        <div class="notification-dropdown" id="notificationsDropdown">
                            <div class="notification-header">
                                Notifications
                                <span class="mark-all-read">Mark all as read</span>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <div class="notification-item empty">
                                    No new notifications
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                <li><a href="<?php echo BASE_URL; ?>jobs.php">Browse Jobs</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="<?php echo BASE_URL; ?>dashboard.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>login.php">Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>register.php">Register</a></li>
                <?php endif; ?>
            </ul>
            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </nav>

        <script>
            $(document).ready(function() {
                let notificationDropdown = $('#notificationsDropdown');
                let notificationList = $('#notificationList');
                
                // Toggle dropdown
                $('#notificationsMenu').on('click', function(e) {
                    e.preventDefault();
                    notificationDropdown.toggleClass('show');
                    fetchNotifications();
                });
                
                // Close dropdown when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.notification-icon').length) {
                        notificationDropdown.removeClass('show');
                    }
                });
                
                // Mark all as read
                $('.mark-all-read').on('click', function(e) {
                    e.stopPropagation();
                    $.post('mark_notifications_read.php', function() {
                        fetchNotifications();
                        $('#notifCount').hide();
                    });
                });
                
                // Fetch notifications
                function fetchNotifications() {
                    $.getJSON('fetch_notifications.php', function(data) {
                        updateNotificationsUI(data);
                    });
                }
                
                // Update UI with notifications
                function updateNotificationsUI(data) {
                    notificationList.empty();
                    
                    if (data.length === 0) {
                        notificationList.append(`
                            <div class="notification-item empty">
                                No new notifications
                            </div>
                        `);
                        $('#notifCount').hide();
                        return;
                    }
                    
                    data.forEach(function(notif) {
                        notificationList.append(`
                            <div class="notification-item ${notif.read ? '' : 'unread'}" data-id="${notif.id}">
                                <div class="notification-message">${notif.message}</div>
                                <div class="notification-time">${notif.time}</div>
                            </div>
                        `);
                    });
                    
                    $('#notifCount').text(data.filter(n => !n.read).length).toggle(data.filter(n => !n.read).length > 0);
                }
                
                // Check for new notifications periodically
                setInterval(fetchNotifications, 30000);
                
                // Show popup notification if needed
                <?php if (isLoggedIn() && !isset($_SESSION['notified'])): ?>
                    $_SESSION['notified'] = true;
                    $.getJSON('fetch_notifications.php', function(data) {
                        const unreadNotifications = data.filter(n => !n.read);
                        if (unreadNotifications.length > 0) {
                            showPopupNotification(unreadNotifications[0]);
                        }
                    });
                <?php endif; ?>
            });
            
            function showPopupNotification(notification) {
                const popup = $(`
                    <div class="notification-popup show">
                        <button class="close-btn">&times;</button>
                        <div class="notification-message">${notification.message}</div>
                        <div class="notification-time">${notification.time}</div>
                    </div>
                `).appendTo('body');
                
                popup.on('click', function() {
                    $(this).removeClass('show');
                    setTimeout(() => $(this).remove(), 300);
                });
                
                setTimeout(() => {
                    popup.removeClass('show');
                    setTimeout(() => popup.remove(), 300);
                }, 5000);
            }
        </script>
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
</main>