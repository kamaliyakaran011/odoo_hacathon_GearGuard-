<?php
include 'db.php';
include 'header.php';

// 1. HANDLE STAGE UPDATES & SCRAP LOGIC
if (isset($_GET['move']) && isset($_GET['id'])) {
    $new_stage = $_GET['move'];
    $req_id = $_GET['id'];

    // Only allow technicians to move their owntasks, or Admin/Manager to move any
    // Simplified for now: just update
    $stmt = $pdo->prepare("UPDATE requests SET stage = ? WHERE id = ?");
    $stmt->execute([$new_stage, $req_id]);

    // PDF Requirement: Automated Scrap Logic
    if ($new_stage == 'Scrap') {
        $r_data = $pdo->prepare("SELECT equipment_id FROM requests WHERE id = ?");
        $r_data->execute([$req_id]);
        $eq = $r_data->fetch();
        if ($eq) {
            $pdo->prepare("UPDATE equipment SET status = 'Decommissioned' WHERE id = ?")->execute([$eq['equipment_id']]);
        }
    }
    // Redirect to strip GET params
    echo "<script>window.location.href='kanban_view.php';</script>";
}

// 2. FETCH DATA BASED ON ROLE
// If technician, only show assigned or unassigned? 
// The spec says "Technician: Restricted view. Can only see and update status on tasks specifically assigned to them."
if (hasRole('Technician')) {
    $stmt = $pdo->prepare("
        SELECT r.*, e.name as ename, u.username as tech_name, u.avatar 
        FROM requests r 
        JOIN equipment e ON r.equipment_id = e.id 
        LEFT JOIN users u ON r.assigned_to = u.id 
        WHERE r.assigned_to = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $reqs = $stmt->fetchAll();
} else {
    $reqs = $pdo->query("
        SELECT r.*, e.name as ename, u.username as tech_name, u.avatar 
        FROM requests r 
        JOIN equipment e ON r.equipment_id = e.id 
        LEFT JOIN users u ON r.assigned_to = u.id
    ")->fetchAll();
}

$stages = ['New', 'In Progress', 'Waiting for Parts', 'Repaired', 'Scrap'];
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h2 class="text-3xl font-bold text-slate-800">Maintenance Board</h2>
        <p class="text-slate-500 text-sm">Managing tasks for role: <span
                class="text-emerald-600 font-bold"><?= $_SESSION['role'] ?></span></p>
    </div>
</div>

<div class="overflow-x-auto pb-8">
    <div class="flex gap-6 min-w-max">
        <?php foreach ($stages as $s): ?>
            <div class="w-80 flex-shrink-0">
                <!-- Column Header -->
                <div
                    class="flex items-center justify-between mb-4 bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                    <h3 class="font-bold text-slate-700 text-sm flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <?= $s ?>
                    </h3>
                    <span class="bg-slate-100 text-slate-500 text-xs font-bold px-2 py-1 rounded-lg">
                        <?= count(array_filter($reqs, fn($r) => $r['stage'] == $s)) ?>
                    </span>
                </div>

                <!-- Cards Container -->
                <div class="space-y-4 min-h-[50vh]">
                    <?php foreach ($reqs as $r): ?>
                        <?php if ($r['stage'] == $s): ?>
                            <?php
                            // Priority Visuals
                            $borderClass = match ($r['priority']) {
                                'High' => 'border-l-4 border-l-red-500',
                                'Medium' => 'border-l-4 border-l-orange-400',
                                'Low' => 'border-l-4 border-l-emerald-400',
                                default => 'border-l-4 border-l-slate-200'
                            };
                            ?>
                            <div
                                class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 group hover:shadow-md transition-all relative overflow-hidden <?= $borderClass ?>">

                                <!-- Header -->
                                <div class="flex justify-between items-start mb-3">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">#<?= $r['id'] ?> •
                                        <?= $r['type'] ?></span>
                                    <div class="flex items-center space-x-1">
                                        <span
                                            class="text-[10px] bg-slate-50 text-slate-500 px-2 py-1 rounded-md font-bold whitespace-nowrap">
                                            ⏱ <?= $r['duration'] ?>h
                                        </span>
                                    </div>
                                </div>

                                <!-- Content -->
                                <h4 class="font-bold text-slate-800 text-sm mb-1 leading-tight">
                                    <?= htmlspecialchars($r['subject']) ?></h4>
                                <p class="text-xs text-slate-500 mb-4 font-medium flex items-center gap-1">
                                    <span>🏭</span> <?= htmlspecialchars($r['ename']) ?>
                                </p>

                                <!-- Footer -->
                                <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                                    <!-- Tech Avatar -->
                                    <?php if ($r['tech_name']): ?>
                                        <div class="flex items-center -space-x-2">
                                            <div class="w-6 h-6 rounded-full bg-emerald-100 border-2 border-white flex items-center justify-center text-[8px] font-bold text-emerald-700"
                                                title="<?= $r['tech_name'] ?>">
                                                <?= strtoupper(substr($r['tech_name'], 0, 2)) ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-[10px] text-slate-300 font-bold italic">Unassigned</span>
                                    <?php endif; ?>

                                    <!-- Actions -->

                                </div>

                                <!-- Quick Move (Visible on Hover) -->
                                <div
                                    class="absolute inset-x-0 bottom-0 bg-white/95 backdrop-blur px-4 py-2 translate-y-full group-hover:translate-y-0 transition-transform duration-200 border-t border-slate-100 flex gap-2 overflow-x-auto no-scrollbar">
                                    <?php foreach ($stages as $target):
                                        if ($target != $s): ?>
                                            <a href="?move=<?= $target ?>&id=<?= $r['id'] ?>"
                                                class="text-[9px] font-bold text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded border border-slate-200 hover:border-emerald-200 whitespace-nowrap">
                                                <?= $target ?>
                                            </a>
                                        <?php endif; endforeach; ?>
                                </div>

                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</main>
</div>
</body>

</html>