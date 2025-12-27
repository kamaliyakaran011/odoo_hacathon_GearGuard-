<?php
include 'db.php';
include 'header.php';
requireRole(['Admin']);

// Handle Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $eq_id = $_POST['equipment_id'];
    $subj = $_POST['subject'];
    $desc = $_POST['description'];
    $prio = $_POST['priority'];
    $type = $_POST['type'];
    $tech = $_POST['technician_id'] ?: NULL;
    $dur = $_POST['duration'];

    $stmt = $pdo->prepare("INSERT INTO requests (equipment_id, subject, description, priority, type, assigned_to, duration) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$eq_id, $subj, $desc, $prio, $type, $tech, $dur]);

    echo "<script>window.location.href = 'kanban_view.php';</script>";
}

$equipment = $pdo->query("SELECT * FROM equipment WHERE status != 'Decommissioned'")->fetchAll();
$techs = $pdo->query("SELECT * FROM users WHERE role = 'Technician'")->fetchAll();
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-slate-800">New Task</h2>
        <p class="text-slate-500 text-sm">Create a maintenance request or routine check.</p>
    </div>

    <form method="POST" class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 space-y-6">

        <!-- Asset Select -->
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Select Asset</label>
            <select name="equipment_id" id="assetSelect"
                class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-700"
                required>
                <option value="">-- Choose Equipment --</option>
                <?php foreach ($equipment as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div id="serviceWarning" class="mt-2 text-xs font-bold text-orange-500 hidden"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Subject -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Subject</label>
                <input type="text" name="subject" placeholder="e.g. Broken Conveyor Belt"
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-700"
                    required>
            </div>

            <!-- Priority -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Priority</label>
                <select name="priority"
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-700">
                    <option value="Low">Low</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="High">High (Urgent)</option>
                </select>
            </div>

            <!-- Type -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Type</label>
                <select name="type"
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-700">
                    <option value="Breakdown">Breakdown</option>
                    <option value="Routine">Routine Check</option>
                </select>
            </div>

            <!-- Predicted Team (Read Only) -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Suggested Team</label>
                <input type="text" id="suggestedTeam" readonly
                    class="w-full p-4 bg-emerald-50/50 text-emerald-600 border-none rounded-2xl outline-none font-bold cursor-not-allowed"
                    value="-">
            </div>

            <!-- Technician -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Assign Technician</label>
                <select name="technician_id" id="techSelect"
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-700">
                    <option value="">-- Unassigned --</option>
                    <?php foreach ($techs as $t): ?>
                        <option value="<?= $t['id'] ?>" data-team="<?= $t['team'] ?>">
                            <?= htmlspecialchars($t['username']) ?> (<?= $t['team'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Est Duration -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Est. Hours</label>
                <input type="number" step="0.5" name="duration" value="1.0"
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-700">
            </div>
        </div>

        <!-- Description -->
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Description</label>
            <textarea name="description" rows="4"
                class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-700"></textarea>
        </div>

        <button type="submit"
            class="w-full bg-emerald-600 text-white p-4 rounded-2xl font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition-all transform hover:-translate-y-1">
            Create Task
        </button>
    </form>
</div>

<script>
    // Smart Form Logic
    document.getElementById('assetSelect').addEventListener('change', function () {
        const id = this.value;
        if (!id) return;

        fetch('ajax_get_asset_details.php?id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Update Suggested Team
                    document.getElementById('suggestedTeam').value = data.team;

                    // Show Service Warning or Scrap Error
                    const warnEl = document.getElementById('serviceWarning');
                    if (data.warning) {
                        warnEl.textContent = data.warning;
                        warnEl.classList.remove('hidden');
                        if (data.status === 'Decommissioned') {
                            warnEl.classList.add('text-red-600');
                            document.querySelector('button[type="submit"]').disabled = true;
                        } else {
                            warnEl.classList.remove('text-red-600');
                            document.querySelector('button[type="submit"]').disabled = false;
                        }
                    } else {
                        warnEl.classList.add('hidden');
                        document.querySelector('button[type="submit"]').disabled = false;
                    }

                    // Auto-Assign Technician
                    const techSelect = document.getElementById('techSelect');
                    if (data.preferred_tech_id) {
                        techSelect.value = data.preferred_tech_id;
                    } else {
                        // Else, try to select first tech of that team
                        let found = false;
                        for (let i = 0; i < techSelect.options.length; i++) {
                            let opt = techSelect.options[i];
                            if (opt.getAttribute('data-team') == data.team) {
                                techSelect.selectedIndex = i;
                                found = true;
                                break;
                            }
                        }
                        if (!found) techSelect.value = "";
                    }
                }
            });
    });
</script>

</main>
</div>
</body>

</html>