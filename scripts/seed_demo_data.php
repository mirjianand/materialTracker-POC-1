<?php
// scripts/seed_demo_data.php
// CLI script to seed demo users and inventory for local development

require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../config/config.php';

if (PHP_SAPI !== 'cli') {
    echo "This script is intended to be run from the command line.\n";
    exit(1);
}

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();

    // Create demo users (if not exists)
    $users = [
        ['emp_id' => 'demo', 'name' => 'Demo Admin', 'email' => DEV_DEMO_USER, 'role' => 'LogisticsManager'],
        ['emp_id' => 'demo2', 'name' => 'Demo User', 'email' => 'demo_user@example.com', 'role' => 'User'],
        ['emp_id' => 'log1', 'name' => 'Logistics', 'email' => 'logistics@example.com', 'role' => 'LogisticsManager'],
    ];

    $insertUser = $db->prepare("INSERT INTO users (emp_id, name, email, role, is_active, start_date) VALUES (:emp_id, :name, :email, :role, 1, NOW())");
    $selectUser = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');

    $created = [];
    foreach ($users as $u) {
        $selectUser->execute([':email' => $u['email']]);
        $row = $selectUser->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $created[$u['email']] = $row['id'];
            continue;
        }

        $insertUser->execute([
            ':emp_id' => $u['emp_id'],
            ':name' => $u['name'],
            ':email' => $u['email'],
            ':role' => $u['role'],
        ]);
        $created[$u['email']] = $db->lastInsertId();
    }

    // Create some item_master entries
    $items = [
        ['item_code' => 'ITM001', 'description' => 'Demo Widget A'],
        ['item_code' => 'ITM002', 'description' => 'Demo Widget B'],
    ];

    $selectItem = $db->prepare('SELECT id FROM item_master WHERE item_code = :code LIMIT 1');
    $insertItem = $db->prepare('INSERT INTO item_master (item_code, description, quantity_type, is_active) VALUES (:code, :description, :quantity_type, 1)');

    $itemIds = [];
    foreach ($items as $it) {
        $selectItem->execute([':code' => $it['item_code']]);
        $r = $selectItem->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            $itemIds[] = $r['id'];
            continue;
        }
        $insertItem->execute([':code' => $it['item_code'], ':description' => $it['description'], ':quantity_type' => 'Number']);
        $itemIds[] = $db->lastInsertId();
    }

    // Create inventory rows assigned to demo user
    $demoOwnerId = $created[DEV_DEMO_USER] ?? null;
    $demoUser2 = $created['demo_user@example.com'] ?? null;

    if (!$demoOwnerId || !$demoUser2) {
        throw new Exception('Expected demo users created');
    }

    $insertInv = $db->prepare('INSERT INTO inventory (item_master_id, serial_number, status, current_owner_id, received_at) VALUES (:item_master_id, :serial, :status, :owner, NOW())');

    // Add one item for each item id to demo user and one to demo_user2
    foreach ($itemIds as $idx => $mid) {
        $insertInv->execute([':item_master_id' => $mid, ':serial' => 'SN-' . strtoupper(bin2hex(random_bytes(3))), ':status' => 'Accepted', ':owner' => $demoOwnerId]);
        $insertInv->execute([':item_master_id' => $mid, ':serial' => 'SN-' . strtoupper(bin2hex(random_bytes(3))), ':status' => 'Accepted', ':owner' => $demoUser2]);
    }

    $db->commit();

    echo "Demo data seeded successfully.\n";
    echo "Demo login: " . DEV_DEMO_USER . " / " . DEV_DEMO_PASS . "\n";
    echo "You can sign in using the demo credentials when APP_ENV is 'development'.\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "Failed to seed demo data: " . $e->getMessage() . "\n";
    exit(1);
}

?>
