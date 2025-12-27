<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'auth.php';
requireLogin();

$role = $_SESSION['role'] ?? 'Technician';
$username = $_SESSION['username'] ?? 'User';
$avatar = $_SESSION['avatar'] ?? 'default_avatar.png'; // In a real app, from DB

// Helper for active menu
function isActive($page)
{
    return basename($_SERVER['PHP_SELF']) == $page ? 'bg-white text-black font-bold' : 'text-gray-400 hover:bg-white/10 hover:text-white';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GearGuard Pro | Asset Management</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f5f5f7;
            /* Apple's light grey background */
            color: #1d1d1f;
            /* Apple's dark grey text */
        }

        /* Solid Black Header */
        .solid-header {
            background-color: rgba(0, 0, 0, 0.9);
            /* Solid Black */
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 50;
            transition: background-color 0.3s ease;
        }

        .sidebar-link {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Smooth Fade-In Animation for Content */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        main {
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            /* Ease Out Expo */
        }

        /* Minimalist Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #555;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #333;
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-72 bg-black flex flex-col border-r border-white/10 z-20 relative">
        <!-- Logo -->
        <div class="h-24 flex items-center px-8 border-b border-white/10">
            <div
                class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-black shadow-lg shadow-white/20 mr-3 transform hover:rotate-12 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2l9 4.9V17L12 22l-9-4.9V7l9-4.9z" />
                    <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                    <path d="M12 2v10" />
                </svg>
            </div>
            <span class="font-bold text-2xl text-white tracking-tight">GearGuard</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
            <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Main Menu</p>

            <a href="dashboard.php"
                class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl font-medium sidebar-link <?= isActive('dashboard.php') ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span>Dashboard</span>
            </a>

            <?php if (hasRole(['Admin', 'Manager'])): ?>
                <a href="equipment_list.php"
                    class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl font-medium sidebar-link <?= isActive('equipment_list.php') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Assets Registry</span>
                </a>
            <?php endif; ?>


            <a href="kanban_view.php"
                class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl font-medium sidebar-link <?= isActive('kanban_view.php') ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span>Service Tasks</span>
            </a>

            <a href="calendar_view.php"
                class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl font-medium sidebar-link <?= isActive('calendar_view.php') ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Calendar</span>
            </a>

            <?php if (hasRole(['Admin', 'Manager'])): ?>
                <a href="reports.php"
                    class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl font-medium sidebar-link <?= isActive('reports.php') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                    <span>Reports</span>
                </a>
            <?php endif; ?>

            <?php if (hasRole(['Admin'])): ?>
                <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-8 mb-2">Admin</p>
                <a href="add_request.php"
                    class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl font-medium sidebar-link <?= isActive('add_request.php') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>New Request</span>
                </a>
                <a href="setup.php"
                    class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl font-medium sidebar-link <?= isActive('setup.php') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>System Setup</span>
                </a>
            <?php endif; ?>
        </nav>

        <!-- User Profile -->
        <div class="p-4 border-t border-white/10">
            <div
                class="bg-white/5 rounded-2xl p-4 flex items-center justify-between group hover:bg-white/10 transition-colors">
                <a href="profile.php" class="flex items-center space-x-3 flex-1">
                    <div
                        class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-300 font-bold text-sm border-2 border-white/10 shadow-sm">
                        <?= strtoupper(substr($username, 0, 2)) ?>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white">
                            <?= htmlspecialchars($username) ?>
                        </p>
                        <p class="text-[10px] uppercase font-bold text-gray-500 group-hover:text-gray-300">
                            <?= $role ?>
                        </p>
                    </div>
                </a>
                <a href="logout.php" title="Logout" class="text-gray-500 hover:text-red-500 transition-colors p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <!-- Top Solid Header (Black) -->
        <header class="h-16 solid-header flex items-center justify-between px-8 z-10">
            <h1 class="text-2xl font-bold text-white">
                <?php
                $page = basename($_SERVER['PHP_SELF'], ".php");
                echo ucwords(str_replace("_", " ", $page));
                ?>
            </h1>
            <div class="flex items-center space-x-6">
                <!-- Search -->
                <div class="relative hidden md:block">
                    <input type="text" placeholder="Search assets..."
                        class="pl-10 pr-4 py-2.5 bg-white/10 border border-white/10 text-white placeholder-white/50 focus:border-white/30 rounded-full text-sm outline-none shadow-sm w-64 transition-all">
                    <span class="absolute left-3.5 top-2.5 text-white/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                </div>
                <!-- Notifications -->
                <div class="relative" id="notifyContainer">
                    <button onclick="toggleNotifications()"
                        class="relative p-2 bg-white/10 text-white rounded-xl shadow-sm hover:bg-white/20 transition-all">
                        <span id="notifyBadge"
                            class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full border-2 border-black hidden"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>
                    <!-- Dropdown -->
                    <div id="notifyDropdown"
                        class="absolute right-0 top-full mt-4 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 hidden p-2 z-50 transform origin-top-right transition-all">
                        <div class="p-3 border-b border-slate-50 flex justify-between items-center">
                            <h3 class="font-bold text-slate-800 text-sm">Notifications</h3>
                            <button onclick="refreshNotifications()"
                                class="text-xs text-black font-bold hover:underline">Refresh</button>
                        </div>
                        <div id="notifyList" class="max-h-64 overflow-y-auto">
                            <div class="p-4 text-center text-slate-400 text-xs font-bold">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <script>
            function toggleNotifications() {
                const dd = document.getElementById('notifyDropdown');
                dd.classList.toggle('hidden');
                if (!dd.classList.contains('hidden')) {
                    refreshNotifications();
                }
            }

            function refreshNotifications() {
                fetch('api_notifications.php')
                    .then(r => r.json())
                    .then(data => {
                        const list = document.getElementById('notifyList');
                        const badge = document.getElementById('notifyBadge');
                        list.innerHTML = '';

                        if (data.length > 0) {
                            badge.classList.remove('hidden');
                            data.forEach(n => {
                                const item = document.createElement('a');
                                item.href = n.link;
                                item.className = 'block p-3 hover:bg-slate-50 rounded-xl transition-colors border-b border-slate-50 last:border-0';
                                item.innerHTML = `
                                    <div class="text-xs font-bold text-slate-700">${n.message}</div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase mt-1">${n.time}</div>
                                `;
                                list.appendChild(item);
                            });
                        } else {
                            badge.classList.add('hidden');
                            list.innerHTML = '<div class="p-4 text-center text-slate-400 text-xs font-bold">No new notifications</div>';
                        }
                    });
            }

            // Auto fetch on load
            document.addEventListener('DOMContentLoaded', refreshNotifications);
        </script>

        <!-- Content Area -->
        <main class="flex-1 overflow-y-auto p-8 scroll-smooth">