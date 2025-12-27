<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function hasRole($roles)
{
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    return isset($_SESSION['role']) && in_array($_SESSION['role'], $roles);
}

function requireRole($roles)
{
    requireLogin();
    if (!hasRole($roles)) {
        die("<div style='color:red; font-family:sans-serif; text-align:center; padding:50px;'>
            <h1>🚫 Access Denied</h1>
            <p>You do not have permission to view this page.</p>
            <a href='dashboard.php'>Return to Dashboard</a>
        </div>");
    }
}
?>