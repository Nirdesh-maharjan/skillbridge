<?php
require_once 'config/init.php';

if (!isLoggedIn() || $_SESSION['role'] != 'admin') {
    redirect('login.php');
}

// Handle export request
if (isset($_POST['export_format'])) {
    $format = $_POST['export_format'];

    // Fetch all applications
    $stmt = $db->query("SELECT a.app_id, a.date_applied, a.status, 
                               u.name AS student_name, j.title AS job_title, j.budget 
                        FROM applications a 
                        JOIN users u ON a.user_id = u.user_id 
                        JOIN jobs j ON a.job_id = j.job_id 
                        ORDER BY a.date_applied DESC");
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($format == 'xml') {
        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="applications.xml"');

        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<applications>\n";
        foreach ($applications as $app) {
            echo "  <application>\n";
            echo "    <id>{$app['app_id']}</id>\n";
            echo "    <date_applied>{$app['date_applied']}</date_applied>\n";
            echo "    <status>{$app['status']}</status>\n";
            echo "    <job_title>" . htmlspecialchars($app['job_title']) . "</job_title>\n";
            echo "    <budget>{$app['budget']}</budget>\n";
            echo "    <student_name>" . htmlspecialchars($app['student_name']) . "</student_name>\n";
            echo "  </application>\n";
        }
        echo "</applications>";
        exit;
    }

    if ($format == 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="applications.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Date Applied', 'Status', 'Job Title', 'Budget', 'Student Name']);
        foreach ($applications as $app) {
            fputcsv($output, $app);
        }
        fclose($output);
        exit;
    }

    if ($format == 'json') {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="applications.json"');
        echo json_encode($applications, JSON_PRETTY_PRINT);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Export Applications</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .export-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        select, button {
            padding: 10px;
            margin: 10px 0;
            width: 80%;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="export-container">
        <h2>Export Job Applications</h2>
        <form method="POST">
            <select name="export_format" required>
                <option value="">-- Select Format --</option>
                <option value="xml">XML</option>
                <option value="csv">CSV</option>
                <option value="json">JSON</option>
            </select>
            <br>
            <button type="submit" class="btn btn-primary">Export</button>
        </form>
    </div>
</body>
</html>
