<?php
include 'db.php';
include 'header.php';

$success = '';
$error = '';
$user_id = $_SESSION['user_id'];

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if ($new_pass !== $confirm_pass) {
        $error = "New passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $hash = $stmt->fetchColumn();

        if (password_verify($current_pass, $hash)) {
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$new_hash, $user_id]);
            $success = "Password updated successfully.";
        } else {
            $error = "Incorrect current password.";
        }
    }
}

// Fetch User Info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-slate-800">My Profile</h2>
        <p class="text-slate-500">Manage your account settings and preferences.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Profile Card -->
        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 text-center h-fit">
            <div
                class="w-24 h-24 bg-emerald-100 rounded-full flex items-center justify-center text-4xl mx-auto mb-4 text-emerald-600 font-bold border-4 border-white shadow-lg">
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
            </div>
            <h3 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($user['username']) ?></h3>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 mt-2">
                <?= $user['role'] ?> • <?= $user['team'] ?>
            </span>
        </div>

        <!-- Settings Form -->
        <div class="md:col-span-2 bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
            <h3 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-50 pb-4">Security Settings</h3>

            <?php if ($success): ?>
                <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 text-sm font-bold flex items-center">
                    <span class="mr-2">✅</span> <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 text-sm font-bold flex items-center">
                    <span class="mr-2">⚠️</span> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Current Password</label>
                    <input type="password" name="current_password" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">New Password</label>
                        <input type="password" name="new_password" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Confirm Password</label>
                        <input type="password" name="confirm_password" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all">
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit"
                        class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-200">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</main>
</div>
</body>

</html>