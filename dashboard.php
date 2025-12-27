<?php
include 'db.php';
include 'header.php';

// Fetch Stats
try {
    $total_assets = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn() ?: 0;
    $active_reqs = $pdo->query("SELECT COUNT(*) FROM requests WHERE stage NOT IN ('Repaired', 'Scrap')")->fetchColumn() ?: 0;
    $scrapped = $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'Decommissioned'")->fetchColumn() ?: 0;

    // Fetch Recent Activity
    $recent = $pdo->query("
        SELECT r.*, e.name as asset_name, u.username as tech_name 
        FROM requests r 
        JOIN equipment e ON r.equipment_id = e.id 
        LEFT JOIN users u ON r.assigned_to = u.id 
        ORDER BY r.created_at DESC LIMIT 5
    ")->fetchAll();

    // Chart Data 1: Last 7 Days Completed Tasks
    $trend_sql = "
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM requests 
        WHERE stage = 'Repaired' 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
        GROUP BY DATE(created_at) 
        ORDER BY date ASC
    ";
    $trend_data = $pdo->query($trend_sql)->fetchAll(PDO::FETCH_KEY_PAIR);

    // Fill unrelated days with 0
    $last_7_days = [];
    $chart_counts = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $short_d = date('D', strtotime("-$i days"));
        $last_7_days[] = $short_d;
        $chart_counts[] = $trend_data[$d] ?? 0;
    }

    // Chart Data 2: Workload by Team
    $workload_sql = "
        SELECT u.team, COUNT(r.id) as count
        FROM requests r
        JOIN users u ON r.assigned_to = u.id
        WHERE r.stage NOT IN ('Repaired', 'Scrap')
        GROUP BY u.team
    ";
    $workload_raw = $pdo->query($workload_sql)->fetchAll(PDO::FETCH_ASSOC);

    $teams = [];
    $team_counts = [];
    foreach ($workload_raw as $row) {
        $teams[] = $row['team'];
        $team_counts[] = $row['count'];
    }

} catch (Exception $e) {
    // Fallback if tables don't exist yet
    $total_assets = 0;
    $active_reqs = 0;
    $scrapped = 0;
    $recent = [];
    $last_7_days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $chart_counts = [0, 0, 0, 0, 0, 0, 0];
    $teams = ['N/A'];
    $team_counts = [0];
}
?>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Card 1 -->
    <div
        class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-lg transition-all">
        <div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Total Assets</p>
            <h3 class="text-3xl font-black text-slate-800"><?= $total_assets ?></h3>
        </div>
        <div
            class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 text-xl group-hover:scale-110 transition-transform">
            🏭
        </div>
    </div>

    <!-- Card 2 -->
    <div
        class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-lg transition-all">
        <div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Active Tasks</p>
            <h3 class="text-3xl font-black text-slate-800"><?= $active_reqs ?></h3>
        </div>
        <div
            class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 text-xl group-hover:scale-110 transition-transform">
            ⚡
        </div>
    </div>

    <!-- Card 3 -->
    <div
        class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-lg transition-all">
        <div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Decommissioned</p>
            <h3 class="text-3xl font-black text-slate-800"><?= $scrapped ?></h3>
        </div>
        <div
            class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center text-red-600 text-xl group-hover:scale-110 transition-transform">
            🗑️
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Left Column: Charts -->
    <div class="lg:col-span-2 space-y-8">
        <!-- Maintenance Trends -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-800 text-lg mb-4">Maintenance Trends</h3>
            <div class="h-64">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Team Workload -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-800 text-lg mb-4">Team Workload</h3>
            <div class="h-48">
                <canvas id="workloadChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Right Column: Activity -->
    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 h-fit">
        <h3 class="font-bold text-slate-800 text-lg mb-6">Recent Activity</h3>
        <div class="relative pl-4 border-l-2 border-slate-100 space-y-8">
            <?php foreach ($recent as $r): ?>
                <div class="relative">
                    <!-- Timeline Dot -->
                    <?php
                    $dotColor = ($r['priority'] ?? 'Medium') == 'High' ? 'bg-red-500 shadow-red-200' : 'bg-emerald-500 shadow-emerald-200';
                    ?>
                    <div
                        class="absolute -left-[21px] top-1 w-3 h-3 rounded-full border-2 border-white <?= $dotColor ?> shadow-md">
                    </div>

                    <p class="text-xs text-slate-400 font-bold mb-1"><?= date('M d, H:i', strtotime($r['created_at'])) ?>
                    </p>
                    <h4 class="text-sm font-bold text-slate-800"><?= htmlspecialchars($r['subject']) ?></h4>
                    <p class="text-xs text-slate-500 mt-1">
                        <span class="font-bold text-slate-700"><?= htmlspecialchars($r['asset_name']) ?></span>
                        • <?= $r['stage'] ?>
                    </p>
                    <?php if ($r['tech_name']): ?>
                        <div class="mt-2 flex items-center space-x-2">
                            <div
                                class="w-5 h-5 rounded-full bg-slate-200 text-[8px] flex items-center justify-center font-bold text-slate-600">
                                <?= strtoupper(substr($r['tech_name'], 0, 1)) ?>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400"><?= $r['tech_name'] ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (empty($recent)): ?>
                <div class="text-center py-8">
                    <p class="text-slate-400 text-sm">No recent activity.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Chart Configs
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#94a3b8';

    // Trend Chart
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($last_7_days) ?>,
            datasets: [{
                label: 'Tasks Completed',
                data: <?= json_encode($chart_counts) ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.05)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#10b981',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { borderDash: [4, 4], color: '#f1f5f9' }, beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    // Workload Chart
    new Chart(document.getElementById('workloadChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($teams) ?>,
            datasets: [{
                label: 'Active Tasks',
                data: <?= json_encode($team_counts) ?>,
                backgroundColor: ['#f59e0b', '#3b82f6', '#ec4899', '#8b5cf6'],
                borderRadius: 8,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false },
                x: { grid: { display: false } }
            }
        }
    });
</script>

</main>
</div>
</body>

</html>