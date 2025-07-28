<?php
require_once 'config/init.php';
header('Content-Type: application/xml; charset=utf-8');
header('Content-Disposition: attachment; filename="jobs.xml"');

$stmt = $db->query("SELECT job_id, title, budget FROM jobs");
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<jobs>\n";
foreach ($jobs as $job) {
    echo "  <job>\n";
    echo "    <id>{$job['job_id']}</id>\n";
    echo "    <title>".htmlspecialchars($job['title'])."</title>\n";
    echo "    <budget>{$job['budget']}</budget>\n";
    echo "  </job>\n";
}
echo "</jobs>";
