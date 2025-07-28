<?php
require_once 'config/init.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT notification_id, message, created_at as time FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
$stmt->execute([$user_id]);

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($notifications);
?>