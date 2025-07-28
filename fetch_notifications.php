<?php
require_once 'config/init.php';

if (!isLoggedIn()) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch unread notifications
$stmt = $db->prepare("SELECT notification_id, message, DATE_FORMAT(created_at, '%b %d %Y %H:%i') as time 
                      FROM notifications 
                      WHERE user_id = ? AND is_read = 0 
                      ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return JSON
header('Content-Type: application/json');
echo json_encode($notifications);

?>
