<?php
include 'db.php';
include 'header.php';
requireRole(['Admin', 'Manager']);

// Fetch Assets safely
try {
    $assets = $pdo->query("SELECT * FROM equipment ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $assets = [];
}
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Asset Registry</h2>
        <p class="text-slate-500 text-sm">Manage all industrial machinery and status.</p>
    </div>
    <div class="flex space-x-3">
        <!-- Live Search Input -->
        <input type="text" id="assetSearch" placeholder="Filter assets..."
            class="border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:border-emerald-500 w-64 shadow-sm bg-white">

        <?php if (hasRole(['Admin'])): ?>
            <a href="add_asset.php"
                class="bg-emerald-600 text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-all text-sm">
                + Add Asset
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="p-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Asset Name</th>
                <th class="p-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="p-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Next Service</th>
                <th class="p-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Details</th>
            </tr>
        </thead>
        <tbody id="assetTableBody" class="divide-y divide-slate-50">
            <?php foreach ($assets as $a): ?>
                <?php
                // Logic for badges
                $statusColor = match ($a['status']) {
                    'Operational' => 'bg-emerald-100 text-emerald-600',
                    'Maintenance' => 'bg-orange-100 text-orange-600',
                    'Decommissioned' => 'bg-slate-100 text-slate-500',
                    default => 'bg-slate-100 text-slate-600'
                };

                // Logic for Service Alert
                $alert = '';
                if ($a['status'] == 'Operational' && !empty($a['next_service_date'])) {
                    $today = new DateTime();
                    $serviceDate = new DateTime($a['next_service_date']);
                    $diff = $today->diff($serviceDate)->format("%r%a"); // Signed integer of days
            
                    if ($diff < 0)
                        $alert = '<span class="ml-2 text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-bold">OVERDUE</span>';
                    elseif ($diff <= 7)
                        $alert = '<span class="ml-2 text-[10px] bg-yellow-100 text-yellow-600 px-2 py-0.5 rounded-full font-bold">DUE SOON</span>';
                }
                ?>
                <tr class="hover:bg-slate-50/50 transition-colors group">
                    <td class="p-6">
                        <div class="font-bold text-slate-800 text-sm asset-name">
                            <a href="asset_details.php?id=<?= $a['id'] ?>" class="hover:text-emerald-600 transition-colors">
                                <?= htmlspecialchars($a['name']) ?>
                            </a>
                        </div>
                        <div class="text-[10px] text-slate-400 font-mono tracking-wider">SN:
                            <?= $a['serial_number'] ?? 'N/A' ?>
                        </div>
                        <div class="text-xs text-slate-500 mt-1">
                            <span
                                class="bg-slate-100 px-1.5 py-0.5 rounded text-[10px] font-bold"><?= htmlspecialchars($a['model'] ?? 'Std') ?></span>
                            <span class="text-slate-400">• <?= htmlspecialchars($a['location'] ?? 'Unknown') ?></span>
                        </div>
                    </td>
                    <td class="p-6">
                        <span class="px-3 py-1 rounded-lg text-xs font-bold border border-transparent <?= $statusColor ?>">
                            <?= $a['status'] ?>
                        </span>
                    </td>
                    <td class="p-6">
                        <div class="text-sm font-bold text-slate-600 flex items-center">
                            <?= $a['next_service_date'] ? date('M d, Y', strtotime($a['next_service_date'])) : '-' ?>
                            <?= $alert ?>
                        </div>
                    </td>
                    <td class="p-6">
                        <p class="text-xs text-slate-500 truncate max-w-xs"><?= htmlspecialchars($a['description']) ?></p>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($assets)): ?>
        <div class="p-10 text-center text-slate-400 font-bold">No assets found.</div>
    <?php endif; ?>
</div>

<script>
    // Live Search Script
    document.getElementById('assetSearch').addEventListener('keyup', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#assetTableBody tr');

        rows.forEach(row => {
            let name = row.querySelector('.asset-name').innerText.toLowerCase();
            let meta = row.innerText.toLowerCase(); // Searches entire row text (includes SN, location, model)
            if (name.includes(filter) || meta.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

</main>
</div>
</body>

</html>