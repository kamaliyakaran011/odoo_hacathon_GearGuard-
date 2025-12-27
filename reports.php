<?php
include 'db.php';
include 'header.php';

// Fetch Report Data
try {
    // 1. Request Status Distribution
    $status_sql = "SELECT stage, COUNT(*) as count FROM requests GROUP BY stage";
    $status_data = $pdo->query($status_sql)->fetchAll(PDO::FETCH_KEY_PAIR);

    $statuses = array_keys($status_data);
    $status_counts = array_values($status_data);

    // 2. Incident Type Breakdown
    $type_sql = "SELECT type, COUNT(*) as count FROM requests GROUP BY type";
    $type_data = $pdo->query($type_sql)->fetchAll(PDO::FETCH_KEY_PAIR);

    $types = array_keys($type_data);
    $type_counts = array_values($type_data);

    // 3. Asset Reliability (Most Frequent Breakdowns)
    $reliability_sql = "
        SELECT e.name, COUNT(r.id) as failures 
        FROM requests r
        JOIN equipment e ON r.equipment_id = e.id
        WHERE r.type = 'Breakdown'
        GROUP BY e.name
        ORDER BY failures DESC LIMIT 5
    ";
    $reliability = $pdo->query($reliability_sql)->fetchAll(PDO::FETCH_ASSOC);

    $rel_names = array_column($reliability, 'name');
    $rel_counts = array_column($reliability, 'failures');

} catch (Exception $e) {
    $statuses = [];
    $status_counts = [];
    $types = [];
    $type_counts = [];
    $rel_names = [];
    $rel_counts = [];
}
?>

<div class="space-y-8">
    <!-- Header Stats -->
    <div
        class="bg-gradient-to-r from-emerald-600 to-emerald-800 rounded-[2rem] p-8 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-3xl font-bold mb-2">System Analytics & Reports</h2>
            <p class="text-emerald-100">Deep dive into your maintenance performance metrics.</p>
        </div>
        <div class="absolute right-0 top-0 h-full w-1/3 bg-white/10 skew-x-12"></div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Status Distribution -->
        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-800 text-lg mb-6">Request Status Distribution</h3>
            <div class="h-64">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Issue Types -->
        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-800 text-lg mb-6">Incident Types</h3>
            <div class="h-64 flex items-center justify-center">
                <canvas id="typeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Reliability Chart -->
    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-lg mb-6">Top 5 Most Reliable/Unreliable Assets (Breakdowns)</h3>
        <div class="h-64">
            <canvas id="relChart"></canvas>
        </div>
    </div>
</div>

<script>
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#94a3b8';

    // Status Chart (Bar)
    new Chart(document.getElementById('statusChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($statuses) ?>,
            datasets: [{
                label: 'Requests',
                data: <?= json_encode($status_counts) ?>,
                backgroundColor: '#10b981',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [4, 4] } },
                x: { grid: { display: false } }
            }
        }
    });

    // Type Chart (Doughnut)
    new Chart(document.getElementById('typeChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($types) ?>,
            datasets: [{
                data: <?= json_encode($type_counts) ?>,
                backgroundColor: ['#ef4444', '#3b82f6', '#f59e0b'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            },
            cutout: '70%'
        }
    });

    // Reliability Chart (Horizontal Bar)
    new Chart(document.getElementById('relChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($rel_names) ?>,
            datasets: [{
                label: 'Breakdowns',
                data: <?= json_encode($rel_counts) ?>,
                backgroundColor: '#f43f5e',
                borderRadius: 5,
                barThickness: 30
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { borderDash: [4, 4] } }
            }
        }
    });
</script>

</main>
</div>
</body>

</html>