<?php
include 'db.php';
header('Content-Type: application/json');

$events = [];

try {
    // 1. Fetch Preventive Maintenance Schedules (From Equipment)
    $stmt = $pdo->query("SELECT id, name, next_service_date FROM equipment WHERE next_service_date IS NOT NULL");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $events[] = [
            'id' => 'eq_' . $row['id'],
            'title' => '🔧 Service: ' . $row['name'],
            'start' => $row['next_service_date'],
            'color' => '#3b82f6', // Blue for planned maintenance
            'extendedProps' => [
                'type' => 'Maintenance',
                'detail' => 'Scheduled service due.'
            ]
        ];
    }

    // 2. Fetch Active Requests (From Requests)
    $stmt = $pdo->query("
        SELECT r.id, r.subject, r.created_at, r.priority, r.stage, e.name as asset_name 
        FROM requests r
        JOIN equipment e ON r.equipment_id = e.id
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Determine color based on Priority
        $color = match ($row['priority']) {
            'High' => '#ef4444', // Red
            'Medium' => '#f59e0b', // Orange
            'Low' => '#10b981', // Green
            default => '#64748b'
        };

        // If completed, grey it out
        if ($row['stage'] === 'Repaired' || $row['stage'] === 'Scrap') {
            $color = '#94a3b8';
        }

        $events[] = [
            'id' => 'req_' . $row['id'],
            'title' => '⚠️ ' . $row['subject'] . ' (' . $row['asset_name'] . ')',
            'start' => date('Y-m-d', strtotime($row['created_at'])), // Simple date for now
            'color' => $color,
            'extendedProps' => [
                'type' => 'Request (' . $row['priority'] . ')',
                'detail' => 'Stage: ' . $row['stage']
            ]
        ];
    }

} catch (Exception $e) {
    // Return empty or error block if needed, but for calendar just empty array is safer to prevent crash
}

echo json_encode($events);
?>