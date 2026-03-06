<?php
// scripts/promote_test_logistics.php
// Adds 'Admin' to users.role enum if missing and promotes test_logistics@local to Admin

require_once __DIR__ . '/../src/core/db.php';

if (PHP_SAPI !== 'cli') {
    echo "Run from CLI only.\n";
    exit(1);
}

$db = Database::getInstance()->getConnection();

try {
    // Check if enum contains Admin
    $col = $db->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch(PDO::FETCH_ASSOC);
    $type = $col['Type'] ?? '';
    if (strpos($type, "'Admin'") === false) {
        echo "Altering users.role to include 'Admin'...\n";
        // Attempt to alter enum to include Admin
        $db->exec("ALTER TABLE users MODIFY role ENUM('LogisticsManager','CommodityManager','User','Admin') NOT NULL");
        echo "Altered role enum.\n";
    } else {
        echo "users.role already contains 'Admin'.\n";
    }

    // Promote the test user
    $email = 'test_logistics@local';
    $stmt = $db->prepare('SELECT id, role FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo "User {$email} not found.\n";
        exit(1);
    }
    if ($row['role'] === 'Admin') {
        echo "User {$email} is already Admin.\n";
    } else {
        $db->prepare('UPDATE users SET role = :role WHERE id = :id')->execute([':role' => 'Admin', ':id' => $row['id']]);
        echo "Promoted {$email} (id={$row['id']}) to Admin.\n";
    }

    echo "Done. {$email} now has Admin rights and (via code) Admins bypass role checks.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
