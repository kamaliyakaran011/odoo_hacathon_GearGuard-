<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include 'db.php';
    echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
    echo "<h1 style='color:green;'>✅ Database Connected Successfully!</h1>";
    echo "<p>Connected to database: <b>$dbname</b></p>";

    // Check tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tables found: " . implode(", ", $tables) . "</p>";

    // Check user count
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "<p>Users in database: <b>$count</b></p>";

    echo "<br><a href='login.php' style='background:green; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Go to Login</a>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
    echo "<h1 style='color:red;'>❌ Connection Failed</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>