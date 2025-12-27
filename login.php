<?php
session_start();
include 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        // Debug Check: Is DB initialized?
        $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($count == 0) {
            $error = "System not initialized! Please run <a href='setup.php' class='underline'>setup.php</a> first.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = "User '$username' not found.";
            } elseif ($user['role'] !== $_POST['role']) { // Validate Role
                $error = "Access Denied: You are not authorized as " . htmlspecialchars($_POST['role']);
            } elseif (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['team'] = $user['team'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Incorrect Password for '$username'.";
            }
        }
    } catch (Exception $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login | GearGuard Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body
    class="bg-slate-50 flex items-center justify-center h-screen bg-[url('https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&q=80')] bg-cover bg-center">
    <div class="absolute inset-0 bg-emerald-900/40 backdrop-blur-sm"></div>

    <form method="POST"
        class="relative bg-white/90 backdrop-blur-md p-10 rounded-[2rem] shadow-2xl w-96 border border-white/50">
        <div class="text-center mb-8">
            <div
                class="w-16 h-16 bg-emerald-600 rounded-2xl mx-auto flex items-center justify-center text-white text-3xl shadow-lg shadow-emerald-600/30 mb-4">
                🛡️
            </div>
            <h2 class="text-3xl font-bold text-slate-800">Welcome Back</h2>
            <p class="text-slate-500 text-sm mt-2">Sign in to GearGuard</p>
        </div>

        <?php if ($error): ?>
            <div
                class="bg-red-100 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-6 text-sm font-bold text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Login As</label>
                <select name="role"
                    class="w-full p-4 bg-white border border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all font-bold text-slate-700">
                    <option value="Admin">Admin</option>
                    <option value="Manager">Manager</option>
                    <option value="Technician">Technician</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Username</label>
                <input type="text" name="username" placeholder="admin"
                    class="w-full p-4 bg-white border border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all font-bold text-slate-700"
                    required>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Password</label>
                <input type="password" name="password" placeholder="password123"
                    class="w-full p-4 bg-white border border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all font-bold text-slate-700"
                    required>
            </div>
        </div>

        <button
            class="w-full mt-8 bg-emerald-600 text-white p-4 rounded-2xl font-bold hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/30 transition-all transform hover:-translate-y-1">
            Access Dashboard
        </button>

        <p class="text-center mt-6 text-xs text-slate-400 font-medium">
            Demo: admin / password123
        </p>
    </form>
</body>

</html>