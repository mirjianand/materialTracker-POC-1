<?php
// scripts/import_po_items_to_inventory.php
// Import purchase_order_items into inventory (idempotent).

require_once __DIR__ . '/../src/core/db.php';

if (PHP_SAPI !== 'cli') {
    echo "Run from CLI only.\n";
    exit(1);
}

$argv = $_SERVER['argv'] ?? [];
$doReset = in_array('--reset', $argv, true) || in_array('-r', $argv, true);

$db = Database::getInstance()->getConnection();

try {
    echo "Checking inventory schema...\n";
    // add purchase_order_item_id if missing
    $col = $db->query("SHOW COLUMNS FROM inventory LIKE 'purchase_order_item_id'")->fetch();
    if (!$col) {
        echo "Adding purchase_order_item_id column to inventory...\n";
        $db->exec("ALTER TABLE inventory ADD COLUMN purchase_order_item_id INT NULL AFTER po_id");
        $db->exec("CREATE INDEX idx_inventory_poi ON inventory(purchase_order_item_id)");
    } else {
        echo "purchase_order_item_id column exists.\n";
    }

    // add quantity column if missing
    $col2 = $db->query("SHOW COLUMNS FROM inventory LIKE 'quantity'")->fetch();
    if (!$col2) {
        echo "Adding quantity column to inventory...\n";
        $db->exec("ALTER TABLE inventory ADD COLUMN quantity INT NOT NULL DEFAULT 1 AFTER item_master_id");
    } else {
        echo "quantity column exists.\n";
    }

    // find LogisticsManager user id
    $lm = $db->prepare("SELECT id FROM users WHERE role = 'LogisticsManager' LIMIT 1");
    $lm->execute();
    $lmRow = $lm->fetch(PDO::FETCH_ASSOC);
    $lmId = $lmRow['id'] ?? null;
    if ($lmId) echo "Using LogisticsManager user id={$lmId} as current_owner_id.\n";
    else echo "No LogisticsManager user found; will set current_owner_id = NULL.\n";

    // optionally reset previous imports
    if ($doReset) {
        echo "Reset requested: removing previously imported inventory rows (purchase_order_item_id IS NOT NULL)...\n";
        $db->exec('DELETE FROM inventory WHERE purchase_order_item_id IS NOT NULL');
    }

    // fetch purchase_order_items to import (only Accepted items)
    echo "Querying purchase_order_items to import (status = Accepted)...\n";
    $poiStmt = $db->prepare("SELECT p.*, po.po_number FROM purchase_order_items p LEFT JOIN purchase_orders po ON po.id = p.po_id WHERE p.item_status = 'Accepted'");
    $poiStmt->execute();
    $rows = $poiStmt->fetchAll(PDO::FETCH_ASSOC);
    $count = 0; $skipped = 0; $created = 0;
    foreach ($rows as $r) {
        $count++;
        $poiId = (int)$r['id'];
        // skip if already imported
        $chk = $db->prepare('SELECT id FROM inventory WHERE purchase_order_item_id = :poi LIMIT 1');
        $chk->execute([':poi'=>$poiId]);
        if ($chk->fetch()) { $skipped++; continue; }

        // find item_master by item_code
        $item_code = trim($r['item_code'] ?? '');
        $item_master_id = null;
        if ($item_code !== '') {
            $s = $db->prepare('SELECT id FROM item_master WHERE item_code = :code LIMIT 1');
            $s->execute([':code'=>$item_code]);
            $row = $s->fetch(PDO::FETCH_ASSOC);
            if ($row) $item_master_id = $row['id'];
            else {
                // create item_master fallback
                $ins = $db->prepare('INSERT INTO item_master (item_code, description, quantity_type, is_active) VALUES (:code, :desc, :qt, 1)');
                $ins->execute([':code'=>$item_code, ':desc'=>$r['item_name'] ?? null, ':qt'=>'Number']);
                $item_master_id = $db->lastInsertId();
                echo "Created item_master for code={$item_code} id={$item_master_id}\n";
            }
        }

        $po_id = $r['po_id'] ?? null;
        $serial = $r['serial_number'] ?? null;
        $qty = isset($r['quantity']) ? (int)$r['quantity'] : 1;
        if ($qty <= 0) $qty = 1;

        // insert inventory row (single row representing the PO entry with quantity)
        $ins = $db->prepare('INSERT INTO inventory (item_master_id, po_id, serial_number, qa_cert_no, status, current_owner_id, received_at, notes, purchase_order_item_id, quantity) VALUES (:im, :po, :srl, NULL, :st, :owner, NOW(), :notes, :poi, :qty)');
        $ins->execute([':im'=>$item_master_id, ':po'=>$po_id, ':srl'=>$serial, ':st'=>'In-QA', ':owner'=>$lmId, ':notes'=>"Imported from PO_ITEM {$poiId}", ':poi'=>$poiId, ':qty'=>$qty]);
        $created++;
    }

    echo "Processed {$count} PO items: created {$created}, skipped {$skipped}.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
