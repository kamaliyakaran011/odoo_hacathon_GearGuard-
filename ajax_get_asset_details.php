<?php
include 'db.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No ID provided']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $asset = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($asset) {
        // Smart Logic: Determine Team
        // If team_id is set (future proofing), use it. For now, guess based on name if not set.
        $team = 'General';
        if (!empty($asset['team_id'])) {
            // Fetch team name logic here if we had a teams table, but we use ENUMs.
            // For now, let's stick to the keyword guessing or if we stored ENUM in team_id (which is INT, so maybe not).
            // Let's rely on the robust guessing for now for the "Upgrade" feel without over-engineering relation tables yet.
        }

        $n = strtolower($asset['name']);
        if (strpos($n, 'press') !== false || strpos($n, 'conveyor') !== false || strpos($n, 'machine') !== false)
            $team = 'Mechanical';
        if (strpos($n, 'server') !== false || strpos($n, 'computer') !== false || strpos($n, 'switch') !== false)
            $team = 'IT';
        if (strpos($n, 'generator') !== false || strpos($n, 'wire') !== false || strpos($n, 'cooling') !== false)
            $team = 'Electrical';

        // Check preventative schedule
        $warning = null;
        if ($asset['next_service_date']) {
            $today = new DateTime();
            $svc = new DateTime($asset['next_service_date']);
            $diff = $today->diff($svc)->format("%r%a");

            if ($diff < 0)
                $warning = "⚠️ Overdue for service since " . $asset['next_service_date'];
            elseif ($diff < 7)
                $warning = "⚠️ Scheduled service due in $diff days";
        }

        // Check if Scrapped
        if ($asset['status'] === 'Decommissioned') {
            $warning = "⛔ ASSET IS SCRAPPED. DO NOT CREATE REQUEST.";
        }

        echo json_encode([
            'success' => true,
            'team' => $team,
            'preferred_tech_id' => $asset['preferred_tech_id'],
            'location' => $asset['location'],
            'model' => $asset['model'],
            'status' => $asset['status'],
            'next_service' => $asset['next_service_date'],
            'warning' => $warning
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>