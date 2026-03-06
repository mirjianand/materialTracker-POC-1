<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/core/db.php';

$db = Database::getInstance()->getConnection();

function ensureUser($db, $name, $role) {
    // Prefer an existing user with the desired role
    $s = $db->prepare('SELECT id FROM users WHERE role = :role LIMIT 1');
    $s->execute([':role'=>$role]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
    if ($r) return $r['id'];
    // Fallback: return any existing user id
    $s2 = $db->query('SELECT id FROM users LIMIT 1');
    $r2 = $s2->fetch(PDO::FETCH_ASSOC);
    if ($r2) return $r2['id'];
    throw new Exception("No users present in the database. Create at least one user to run workflow tests.");
}

echo "Starting workflow test...\n";
$lmId = ensureUser($db, 'LM Tester', 'LogisticsManager');
$qaId = ensureUser($db, 'QA Tester', 'CommodityManager');
$empId = ensureUser($db, 'Employee Tester', 'User');

// create item_master
$code = 'TW-' . time()%10000;
$desc = 'Test Workflow Item';
$exists = $db->prepare('SELECT id FROM item_master WHERE item_code = :c LIMIT 1'); $exists->execute([':c'=>$code]);
if ($r = $exists->fetch(PDO::FETCH_ASSOC)) {
    $imId = $r['id'];
} else {
    $db->prepare('INSERT INTO item_master (item_code, description, quantity_type, is_active) VALUES (:c,:d,:qt,1)')->execute([':c'=>$code, ':d'=>$desc, ':qt'=>'Number']);
    $imId = $db->lastInsertId();
}

// create PO and PO items, and inventory rows with status In-QA
try {
    echo "[STEP] creating purchase_orders table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS purchase_orders (id INT NOT NULL AUTO_INCREMENT, po_number VARCHAR(64), pr_number VARCHAR(64), created_by INT NULL, created_at DATETIME NULL, PRIMARY KEY(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[STEP] creating purchase_order_items table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS purchase_order_items (id INT NOT NULL AUTO_INCREMENT, po_id INT NOT NULL, item_code VARCHAR(64), item_name VARCHAR(255), item_category VARCHAR(255), item_type VARCHAR(255), material_type VARCHAR(255), quantity INT DEFAULT 0, expiry_date DATE NULL, serial_number VARCHAR(255) NULL, item_status ENUM('Accepted','Rejected','In-QA') DEFAULT 'In-QA', PRIMARY KEY(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[STEP] creating inventory table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS inventory (id INT NOT NULL AUTO_INCREMENT, item_master_id INT NULL, po_id INT NULL, serial_number VARCHAR(255) NULL, qa_cert_no VARCHAR(255) NULL, status VARCHAR(64) NOT NULL DEFAULT 'In-QA', current_owner_id INT NULL, received_at DATETIME NULL, notes TEXT, purchase_order_item_id INT NULL, quantity INT DEFAULT 1, PRIMARY KEY(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Insert rows inside a transaction (do not include DDL inside the transaction)
    $db->beginTransaction();
    echo "[STEP] inserting purchase_orders row...\n";
    $stmt = $db->prepare('INSERT INTO purchase_orders (po_number, pr_number, created_by, created_at) VALUES (:po,:pr,:cb,NOW())');
    $poNum = 'PO-TW-' . rand(1000,9999);
    $prNum = 'PR-TW-' . time() . rand(10,99);
    $stmt->execute([':po'=>$poNum, ':pr'=>$prNum, ':cb'=>$lmId]);
    $poId = $db->lastInsertId();

    echo "[STEP] inserting purchase_order_items row...\n";
    $ins = $db->prepare('INSERT INTO purchase_order_items (po_id, item_code, item_name, item_category, item_type, material_type, quantity, expiry_date, serial_number, item_status) VALUES (:po,:code,:name,:cat,:itype,:mtype,:qty,:exp,:srl,:status)');
    $ins->execute([':po'=>$poId, ':code'=>$code, ':name'=>$desc, ':cat'=>'General', ':itype'=>'Component', ':mtype'=>'Raw', ':qty'=>5, ':exp'=>null, ':srl'=>null, ':status'=>'In-QA']);
    $poiId = $db->lastInsertId();

    echo "[STEP] inserting inventory row...\n";
    $stmtInv = $db->prepare('INSERT INTO inventory (item_master_id, po_id, serial_number, qa_cert_no, status, current_owner_id, received_at, notes, purchase_order_item_id, quantity) VALUES (:im, :po, :srl, NULL, :st, :owner, NOW(), :notes, :poi, :qty)');
    $stmtInv->execute([':im'=>$imId, ':po'=>$poId, ':srl'=>null, ':st'=>'In-QA', ':owner'=>$lmId, ':notes'=>"Imported from PO_ITEM {$poiId}", ':poi'=>$poiId, ':qty'=>5]);
    $invId = $db->lastInsertId();

    $db->commit();
} catch (Exception $e) {
    if ($db->inTransaction()) {
        try { $db->rollBack(); } catch (Exception $_) { /* ignore rollback failure */ }
    }
    echo "Failed to seed PO/inventory: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Created PO {$poNum}, purchase_item {$poiId}, inventory id={$invId} (status In-QA)\n";

// QA Acceptance check: list In-QA items
$qaList = $db->query("SELECT id, purchase_order_item_id, status FROM inventory WHERE status = 'In-QA'")->fetchAll(PDO::FETCH_ASSOC);
echo "QA list (In-QA) count: " . count($qaList) . "\n";

// Simulate QA accept: change status to Accepted and insert item_transactions
$db->prepare('UPDATE inventory SET status = "Accepted" WHERE id = :id')->execute([':id'=>$invId]);
$db->prepare('INSERT INTO item_transactions (inventory_id, from_user_id, to_user_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv, NULL, NULL, :tt, :q, :r, NOW())')->execute([':inv'=>$invId, ':tt'=>'Inward', ':q'=>5, ':r'=>'QA accepted']);

echo "After QA accept: inventory id={$invId} status=" . $db->query('SELECT status FROM inventory WHERE id = '.(int)$invId)->fetchColumn() . "\n";

// Attempt transfer by LogisticsManager via creating a transfer (Ops style): create transfers and transfer_items and mark inventory In-transit
$db->prepare('INSERT INTO transfers (transfer_number, transfer_type, from_user_id, to_user_id, to_role, created_by, created_at, status, notes) VALUES (:tn,:tt,:from,:to,NULL,:cb,NOW(),"pending",:notes)')->execute([':tn'=>'T' . time(), ':tt'=>'to_employee', ':from'=>$lmId, ':to'=>$empId, ':cb'=>$lmId, ':notes'=>'Test transfer']);
$transferId = $db->lastInsertId();
$db->prepare('INSERT INTO transfer_items (transfer_id, inventory_id, item_master_id, quantity, status, remarks) VALUES (:tid,:inv,:im,:q,"in-transit",:r)')->execute([':tid'=>$transferId, ':inv'=>$invId, ':im'=>$imId, ':q'=>5, ':r'=>'Test transfer']);
$db->prepare('UPDATE inventory SET status = "In-transit" WHERE id = :id')->execute([':id'=>$invId]);

echo "Transfer created id={$transferId}, inventory id={$invId} status now " . $db->query('SELECT status FROM inventory WHERE id = '.(int)$invId)->fetchColumn() . "\n";

// Simulate recipient acceptance: update transfer_items status, inventory current_owner_id and status With Owner
$items = $db->prepare('SELECT id FROM transfer_items WHERE transfer_id = :tid'); $items->execute([':tid'=>$transferId]); $titems = $items->fetchAll(PDO::FETCH_ASSOC);
foreach ($titems as $ti) {
    $db->prepare('UPDATE transfer_items SET status = "accepted" WHERE id = :id')->execute([':id'=>$ti['id']]);
}
$db->prepare('UPDATE inventory SET current_owner_id = :newOwner, status = "With Owner", acknowledged_at = NOW() WHERE id = :id')->execute([':newOwner'=>$empId, ':id'=>$invId]);
$db->prepare('UPDATE transfers SET status = "accepted", actioned_at = NOW(), actioned_by = :ab WHERE id = :id')->execute([':ab'=>$empId, ':id'=>$transferId]);

echo "After transfer accept: inventory id={$invId} owner=" . $db->query('SELECT current_owner_id FROM inventory WHERE id = '.(int)$invId)->fetchColumn() . ", status=" . $db->query('SELECT status FROM inventory WHERE id = '.(int)$invId)->fetchColumn() . "\n";

// Rework process: LM sends to vendor -> status To-Rework
$db->prepare("UPDATE inventory SET status = 'To-Rework', notes = CONCAT(COALESCE(notes, ''), :note) WHERE id = :id")->execute([':note'=>' Sent to vendor for rework', ':id'=>$invId]);
$db->prepare('INSERT INTO item_transactions (inventory_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv, :tt, :q, :r, NOW())')->execute([':inv'=>$invId, ':tt'=>'Rework', ':q'=>5, ':r'=>'Sent to vendor']);

echo "After rework: inventory id={$invId} status=" . $db->query('SELECT status FROM inventory WHERE id = '.(int)$invId)->fetchColumn() . "\n";

// Surrender process: LM surrenders -> status Surrendered
$db->prepare("UPDATE inventory SET status = 'Surrendered', notes = CONCAT(COALESCE(notes, ''), :note) WHERE id = :id")->execute([':note'=>' Surrendered by LM', ':id'=>$invId]);
$db->prepare('INSERT INTO item_transactions (inventory_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv, :tt, :q, :r, NOW())')->execute([':inv'=>$invId, ':tt'=>'Surrender', ':q'=>5, ':r'=>'Surrendered']);

echo "After surrender: inventory id={$invId} status=" . $db->query('SELECT status FROM inventory WHERE id = '.(int)$invId)->fetchColumn() . "\n";

echo "Workflow test completed.\n";

?>