<?php
require_once 'config/init.php';

// Set header to return JSON
header('Content-Type: application/json');

//  Optional filters from AJAX (category, budget, keyword)
$category = $_POST['category'] ?? '';
$min_budget = $_POST['min_budget'] ?? '';
$max_budget = $_POST['max_budget'] ?? '';
$keyword = $_POST['keyword'] ?? '';

//  Base query
$query = "SELECT job_id, title, budget, category, created_at, status 
          FROM jobs 
          WHERE status = 'open'";

$params = [];

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

if (!empty($min_budget)) {
    $query .= " AND budget >= ?";
    $params[] = $min_budget;
}

if (!empty($max_budget)) {
    $query .= " AND budget <= ?";
    $params[] = $max_budget;
}

if (!empty($keyword)) {
    $query .= " AND title LIKE ?";
    $params[] = "%$keyword%";
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);

$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($jobs);
exit;
?>
