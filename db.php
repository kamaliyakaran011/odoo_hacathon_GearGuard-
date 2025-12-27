<?php
$host = 'localhost';
$dbname = 'gearguard_db';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>