<?php
include 'db.php';
header('Content-Type: application/json');

$notifications = [];

try {
    // 1. Overdue Assets
    $stmt = $pdo->query("SELECT id, name, next_service_date FROM equipment WHERE next_service_date < CURDATE() AND status != 'Decommissioned'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notifications[] = [
            'type' => 'alert',
            'message' => "⚠️ Service Overdue: " . $row['name'],
            'link' => 'asset_details.php?id=' . $row['id'],
            'time' => $row['next_service_date']
        ];
    }

    // 2. High Priority Open Requests
    $stmt = $pdo->query("SELECT id, subject FROM requests WHERE priority = 'High' AND stage != 'Repaired' AND stage != 'Scrap' LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notifications[] = [
            'type' => 'info',
            'message' => "🔥 High Priority: " . $row['subject'],
            'link' => 'kanban_view.php',
            'time' => 'Action Required'
        ];
    }

} catch (Exception $e) {
    // Silent fail
}

echo json_encode($notifications);
?>