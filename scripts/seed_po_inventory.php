<?php
// scripts/seed_po_inventory.php
// Create a PO, an item (if needed), and two inventory rows in 'In-QA' status for testing QA flow.

require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../config/config.php';

if (PHP_SAPI !== 'cli') {
    echo "Run from CLI only.\n";
    exit(1);
}

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();

    // Create PO
    $poNumber = 'PO-DR-' . time();
    $prNumber = 'PR-DR-' . rand(100,999);
    $stmt = $db->prepare('INSERT INTO purchase_orders (po_number, pr_number, created_by, created_at) VALUES (:po, :pr, NULL, NOW())');
    $stmt->execute([':po'=>$poNumber, ':pr'=>$prNumber]);
    $poId = $db->lastInsertId();

    // Use first item_master if exists, otherwise create a demo item
    $itm = $db->query('SELECT id FROM item_master LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    if ($itm) {
        $itemId = $itm['id'];
    } else {
        $db->prepare('INSERT INTO item_master (item_code, description, quantity_type, is_active) VALUES (:code, :desc, :qt, 1)')
            ->execute([':code'=>'DEMO100', ':desc'=>'Demo seeded item', ':qt'=>'Number']);
        $itemId = $db->lastInsertId();
    }

    // Insert two inventory rows with status In-QA
    $insertInv = $db->prepare('INSERT INTO inventory (item_master_id, po_id, serial_number, status, current_owner_id, received_at) VALUES (:im, :po, :sn, :st, NULL, NOW())');
    $serials = ['SN-DR-' . strtoupper(bin2hex(random_bytes(3))), 'SN-DR-' . strtoupper(bin2hex(random_bytes(3)))];
    $createdIds = [];
    foreach ($serials as $s) {
        $insertInv->execute([':im'=>$itemId, ':po'=>$poId, ':sn'=>$s, ':st'=>'In-QA']);
        $createdIds[] = $db->lastInsertId();
    }

    $db->commit();

    echo "Seeded PO id={$poId} ({$poNumber}), item_id={$itemId}, inventory_ids=" . implode(',', $createdIds) . "\n";
    echo "Use Commodity Manager to visit /qa and accept these items.\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage() . "\n";
    exit(1);
}

?>
