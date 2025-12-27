<?php
include 'db.php';
include 'header.php';
requireRole(['Admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $serial = $_POST['serial_number'];
    $model = $_POST['model'];
    $location = $_POST['location'];
    $description = $_POST['details']; // Form field is 'details', DB column is 'description'
    $next_service = !empty($_POST['next_service_date']) ? $_POST['next_service_date'] : NULL;
    $team_id = $_POST['team_id'] ?? NULL; // Fix undefined key warning

    try {
        $stmt = $pdo->prepare("INSERT INTO equipment (name, serial_number, model, location, description, next_service_date, status) VALUES (?, ?, ?, ?, ?, ?, 'Operational')");
        $stmt->execute([$name, $serial, $model, $location, $description, $next_service]);
        echo "<script>window.location.href = 'equipment_list.php';</script>";
    } catch (PDOException $e) {
        $error = "Error adding asset: " . $e->getMessage();
    }
}
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-slate-800">Add New Asset</h2>
        <p class="text-slate-500 text-sm">Register new equipment into the system.</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 font-bold text-sm">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Asset Name</label>
                <input type="text" name="name" placeholder="e.g. Hydraulic Press A1" required
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-700">
            </div>

            <!-- Serial Number -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Serial Number</label>
                <input type="text" name="serial_number" placeholder="SN-XXXXX" required
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-700">
            </div>

            <!-- Model -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Model</label>
                <input type="text" name="model" placeholder="Model X-100"
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-700">
            </div>

            <!-- Location -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Location</label>
                <input type="text" name="location" placeholder="e.g. Server Room"
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-700">
            </div>

            <!-- Next Service -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Next Service Date</label>
                <input type="date" name="next_service_date"
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-700">
            </div>
        </div>

        <!-- Details -->
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Description / Specs</label>
            <textarea name="details" rows="3" placeholder="Additional details..."
                class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-700"></textarea>
        </div>

        <div class="flex justify-end gap-4 pt-4">
            <a href="equipment_list.php"
                class="px-6 py-4 rounded-2xl font-bold text-slate-500 hover:bg-slate-50 transition-colors">Cancel</a>
            <button type="submit"
                class="bg-emerald-600 text-white px-8 py-4 rounded-2xl font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition-all transform hover:-translate-y-1">
                Save Asset
            </button>
        </div>

    </form>
</div>
</main>
</div>
</body>

</html>