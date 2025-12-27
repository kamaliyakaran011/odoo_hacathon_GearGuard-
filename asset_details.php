<?php
include 'db.php';
include 'header.php';

$id = $_GET['id'] ?? 0;
try {
    // Fetch Asset Details
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
    $stmt->execute([$id]);
    $asset = $stmt->fetch();

    if (!$asset) {
        throw new Exception("Asset not found");
    }

    // Fetch History
    $hist_stmt = $pdo->prepare("
        SELECT r.*, u.username as tech_name 
        FROM requests r 
        LEFT JOIN users u ON r.assigned_to = u.id 
        WHERE r.equipment_id = ? 
        ORDER BY r.created_at DESC
    ");
    $hist_stmt->execute([$id]);
    $history = $hist_stmt->fetchAll();

} catch (Exception $e) {
    echo "<div class='p-8'><div class='bg-red-100 text-red-600 p-4 rounded-xl'>Error: " . $e->getMessage() . "</div></div>";
    exit;
}
?>

<!-- Asset Header -->
<div class="flex justify-between items-center mb-8">
    <div>
        <a href="equipment_list.php"
            class="text-slate-400 font-bold text-xs uppercase hover:text-emerald-600 mb-2 block">← Back to Registry</a>
        <h2 class="text-3xl font-bold text-slate-800"><?= htmlspecialchars($asset['name']) ?></h2>
        <span class="inline-block px-3 py-1 rounded-lg text-xs font-bold bg-slate-200 text-slate-600 mt-2">
            ID: #<?= str_pad($asset['id'], 4, '0', STR_PAD_LEFT) ?>
        </span>
    </div>

    <div class="flex space-x-3">
        <?php if (hasRole(['Admin', 'Manager'])): ?>
            <a href="add_request.php?asset_id=<?= $asset['id'] ?>"
                class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-all transform hover:-translate-y-1">
                + New Service Request
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Column: Specs -->
    <div class="lg:col-span-1 space-y-8">
        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-800 text-lg mb-6 border-b border-slate-50 pb-4">Specifications</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Current Status</label>
                    <?php
                    $statusColor = match ($asset['status']) {
                        'Operational' => 'text-emerald-600 bg-emerald-50',
                        'Maintenance' => 'text-orange-600 bg-orange-50',
                        'Decommissioned' => 'text-red-500 bg-red-50',
                        default => 'text-slate-600 bg-slate-50'
                    };
                    ?>
                    <span class="px-3 py-1 rounded-lg text-sm font-bold <?= $statusColor ?>">
                        <?= $asset['status'] ?>
                    </span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Asset Information</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase tracking-widest">Serial No.</span>
                            <p class="font-bold text-slate-700 font-mono text-xs">
                                <?= $asset['serial_number'] ?? 'N/A' ?></p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase tracking-widest">Model</span>
                            <p class="font-bold text-slate-700 text-sm"><?= $asset['model'] ?? 'Standard' ?></p>
                        </div>
                        <div class="col-span-2">
                            <span class="text-[10px] text-slate-400 uppercase tracking-widest">Location</span>
                            <p class="font-bold text-slate-700 text-sm flex items-center gap-2">
                                📍 <?= $asset['location'] ?? 'Unknown' ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Next Service Due</label>
                    <p class="font-bold text-slate-700">
                        <?= $asset['next_service_date'] ? date('M d, Y', strtotime($asset['next_service_date'])) : 'Not Scheduled' ?>
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Purchase Date</label>
                    <p class="font-bold text-slate-700">
                        <?= $asset['purchase_date'] ? date('M d, Y', strtotime($asset['purchase_date'])) : 'N/A' ?>
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Description</label>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        <?= nl2br(htmlspecialchars($asset['description'])) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: History -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-8 border-b border-slate-50">
                <h3 class="font-bold text-slate-800 text-lg">Maintenance History</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="p-6 text-xs font-bold text-slate-400 uppercase">Date</th>
                            <th class="p-6 text-xs font-bold text-slate-400 uppercase">Issue</th>
                            <th class="p-6 text-xs font-bold text-slate-400 uppercase">Type</th>
                            <th class="p-6 text-xs font-bold text-slate-400 uppercase">Tech</th>
                            <th class="p-6 text-xs font-bold text-slate-400 uppercase">Stage</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach ($history as $h): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-6 text-sm font-bold text-slate-600">
                                    <?= date('M d, Y', strtotime($h['created_at'])) ?>
                                </td>
                                <td class="p-6">
                                    <span
                                        class="font-bold text-slate-800 block text-sm"><?= htmlspecialchars($h['subject']) ?></span>
                                    <span class="text-xs text-slate-400">Duration: <?= $h['duration'] ?>h</span>
                                </td>
                                <td class="p-6">
                                    <span
                                        class="px-2 py-1 rounded text-xs font-bold 
                                        <?= $h['type'] == 'Breakdown' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600' ?>">
                                        <?= $h['type'] ?>
                                    </span>
                                </td>
                                <td class="p-6 text-sm text-slate-600">
                                    <?= $h['tech_name'] ?? '<span class="text-slate-300">Unassigned</span>' ?>
                                </td>
                                <td class="p-6 text-xs font-bold text-slate-500">
                                    <?= $h['stage'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($history)): ?>
                            <tr>
                                <td colspan="5" class="p-10 text-center text-slate-400">No history found for this asset.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</main>
</div>
</body>

</html>