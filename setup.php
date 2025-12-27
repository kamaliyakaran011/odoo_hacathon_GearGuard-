<?php
include 'db.php';
try {
    // 0. RESET DATABASE (DROP OLD TABLES)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS requests");
    $pdo->exec("DROP TABLE IF EXISTS equipment");
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("DROP TABLE IF EXISTS teams"); // Remove the rogue table
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // 1. Initialize Tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('Admin', 'Manager', 'Technician') NOT NULL,
            team ENUM('IT', 'Mechanical', 'Electrical', 'General') DEFAULT 'General',
            avatar VARCHAR(255) DEFAULT 'default_avatar.png'
        );

        CREATE TABLE IF NOT EXISTS equipment (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            serial_number VARCHAR(100) UNIQUE, 
            model VARCHAR(100),
            location VARCHAR(100),
            preferred_tech_id INT NULL,
            team_id INT NULL,
            status ENUM('Operational', 'Maintenance', 'Decommissioned') DEFAULT 'Operational',
            next_service_date DATE NULL,
            purchase_date DATE NULL,
            description TEXT
        );

        CREATE TABLE IF NOT EXISTS requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            equipment_id INT NOT NULL,
            subject VARCHAR(150),
            description TEXT,
            priority ENUM('High', 'Medium', 'Low') DEFAULT 'Medium',
            type ENUM('Breakdown', 'Routine') DEFAULT 'Breakdown',
            stage ENUM('New', 'In Progress', 'Waiting for Parts', 'Repaired', 'Scrap') DEFAULT 'New',
            assigned_to INT NULL,
            duration FLOAT DEFAULT 0.0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (equipment_id) REFERENCES equipment(id),
            FOREIGN KEY (assigned_to) REFERENCES users(id)
        );
    ");

    // 1.5 FORCE UPDATES (If tables existed but were empty/old)
    try {
        // Users
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT 'default_avatar.png'");
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS team ENUM('IT', 'Mechanical', 'Electrical', 'General') DEFAULT 'General'");

        // Equipment
        $pdo->exec("ALTER TABLE equipment ADD COLUMN IF NOT EXISTS status ENUM('Operational', 'Maintenance', 'Decommissioned') DEFAULT 'Operational'");
        $pdo->exec("ALTER TABLE equipment ADD COLUMN IF NOT EXISTS next_service_date DATE NULL");
        $pdo->exec("ALTER TABLE equipment ADD COLUMN IF NOT EXISTS description TEXT");

        // Requests
        $pdo->exec("ALTER TABLE requests ADD COLUMN IF NOT EXISTS priority ENUM('High', 'Medium', 'Low') DEFAULT 'Medium'");
        $pdo->exec("ALTER TABLE requests ADD COLUMN IF NOT EXISTS type ENUM('Breakdown', 'Routine') DEFAULT 'Breakdown'");
        $pdo->exec("ALTER TABLE requests ADD COLUMN IF NOT EXISTS duration FLOAT DEFAULT 0.0");
    } catch (Exception $e) {
        // Ignore column already exists errors if SQL syntax differs slightly across versions, 
        // but 'IF NOT EXISTS' usually handles it in modern MariaDB/MySQL.
    }

    // 2. Seed Users (Password: 'password123')
    $pass = password_hash('password123', PASSWORD_DEFAULT);
    $check = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    if ($check == 0) {
        $pdo->exec("
            INSERT INTO users (username, password, role, team) VALUES 
            ('admin', '$pass', 'Admin', 'General'),
            ('manager_mech', '$pass', 'Manager', 'Mechanical'),
            ('tech_alex', '$pass', 'Technician', 'Mechanical'),
            ('tech_sarah', '$pass', 'Technician', 'Electrical'),
            ('tech_mike', '$pass', 'Technician', 'IT')
        ");
        echo "<p>✅ Users seeded (admin, manager_mech, tech_alex...)</p>";
    }

    // 3. Seed Equipment
    $checkEq = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
    if ($checkEq == 0) {
        $pdo->exec("
            INSERT INTO equipment (name, serial_number, model, location, status, next_service_date, description) VALUES
            ('Hydraulic Press X1', 'SN-1001', 'X-Series', 'Factory Floor', 'Operational', DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'Main production press'),
            ('Conveyor Belt A', 'SN-1002', 'Conv-2000', 'Assembly Line', 'Maintenance', DATE_ADD(CURDATE(), INTERVAL -5 DAY), 'Assembly line belt'),
            ('Server Rack Main', 'SN-9000', 'Dell R740', 'Server Room', 'Operational', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Primary data center'),
            ('Cooling Tower B', 'SN-COOL-1', 'FrostByte', 'Roof', 'Decommissioned', NULL, 'Old cooling unit'),
            ('Forklift Z500', 'SN-FL-55', 'Toyota-Z', 'Warehouse', 'Operational', DATE_ADD(CURDATE(), INTERVAL 120 DAY), 'Warehouse forklift'),
            ('CNC Machine 01', 'SN-CNC-99', 'Haas VF-2', 'Workshop', 'Operational', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'Precision cutting'),
            ('Generator Backup', 'SN-GEN-X', 'Cat G3500', 'Basement', 'Operational', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Emergency power'),
            ('Drill Press Heavy', 'SN-DP-22', 'Bosch Pro', 'Workshop', 'Operational', DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'Heavy duty drilling'),
            ('Network Switch Core', 'SN-NET-01', 'Cisco 9300', 'Server Room', 'Operational', DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'Core network switch'),
            ('Production Robot Arm', 'SN-ROBO-X', 'Kuka KR-16', 'Assembly Line', 'Maintenance', DATE_ADD(CURDATE(), INTERVAL -1 DAY), 'Robotic assembly arm')
        ");
        echo "<p>✅ Equipment seeded.</p>";
    }

    // 4. Seed Requests
    $checkReq = $pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn();
    if ($checkReq == 0) {
        $pdo->exec("
            INSERT INTO requests (equipment_id, subject, priority, type, stage, assigned_to, duration) VALUES
            (2, 'Belt slipping', 'High', 'Breakdown', 'In Progress', 3, 4.5),
            (10, 'Calibration Error', 'Medium', 'Routine', 'Waiting for Parts', 3, 2.0),
            (1, 'Weekly Checkup', 'Low', 'Routine', 'New', NULL, 1.0),
            (6, 'Coolant Leak', 'High', 'Breakdown', 'New', NULL, 0.0),
            (4, 'Structural Failure', 'High', 'Breakdown', 'Scrap', 2, 0.0)
        ");
        echo "<p>✅ Requests seeded.</p>";
    }

    echo "<h2 style='color:green'>System Setup Complete!</h2> <a href='login.php'>Go to Login</a>";

} catch (PDOException $e) {
    die("Setup Error: " . $e->getMessage());
}
?>