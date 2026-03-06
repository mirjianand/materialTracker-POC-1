<?php
// scripts/create_test_users.php
// Create test users with password hashes for local password-based authentication

require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../config/config.php';

if (PHP_SAPI !== 'cli') {
    echo "Run from CLI only.\n";
    exit(1);
}

$db = Database::getInstance()->getConnection();

try {
    // Ensure password_hash column exists
    $colRes = $db->query("SHOW COLUMNS FROM users LIKE 'password_hash'")->fetch();
    if (!$colRes) {
        echo "Adding password_hash column to users table...\n";
        $db->exec("ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) DEFAULT NULL AFTER email");
    }

    $testUsers = [
        ['emp_id' => 'lgmgr', 'name' => 'Test Logistics', 'email' => 'test_logistics@local', 'role' => 'LogisticsManager', 'password' => 'Logistics123!'],
        ['emp_id' => 'commgr', 'name' => 'Test Commodity', 'email' => 'test_commodity@local', 'role' => 'CommodityManager', 'password' => 'Commodity123!'],
        ['emp_id' => 'tuser', 'name' => 'Test User', 'email' => 'test_user@local', 'role' => 'User', 'password' => 'User123!'],
    ];

    $select = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $insert = $db->prepare('INSERT INTO users (emp_id, name, email, role, is_active, start_date, password_hash) VALUES (:emp_id, :name, :email, :role, 1, NOW(), :password_hash)');
    $update = $db->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');

    foreach ($testUsers as $u) {
        $select->execute([':email' => $u['email']]);
        $row = $select->fetch(PDO::FETCH_ASSOC);
        $hash = password_hash($u['password'], PASSWORD_DEFAULT);
        if ($row) {
            $update->execute([':password_hash' => $hash, ':id' => $row['id']]);
            echo "Updated password for existing user {$u['email']}\n";
        } else {
            $insert->execute([':emp_id' => $u['emp_id'], ':name' => $u['name'], ':email' => $u['email'], ':role' => $u['role'], ':password_hash' => $hash]);
            echo "Inserted user {$u['email']} with password {$u['password']}\n";
        }
    }

    echo "Test users created/updated.\n";
    echo "Credentials:\n";
    foreach ($testUsers as $u) {
        echo " - {$u['email']} / {$u['password']} (role: {$u['role']})\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
