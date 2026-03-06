<?php
// CLI: sync PO items into inventory (idempotent) and validate PO numbers
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/core/db.php';

$db = Database::getInstance()->getConnection();
echo "Starting sync of purchase_order_items -> inventory\n";
// ensure log directory exists
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
$logFile = $logDir . '/sync_po_items_to_inventory.log';
file_put_contents($logFile, date('c') . " - Sync started\n", FILE_APPEND);
// create audit table if not exists
$db->exec("CREATE TABLE IF NOT EXISTS sync_audit (id INT AUTO_INCREMENT PRIMARY KEY, run_at DATETIME, created_count INT, skipped_count INT, mismatches_count INT, notes TEXT)");

// find logistics manager id
$lm = $db->query("SELECT id FROM users WHERE role = 'LogisticsManager' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$lmId = $lm['id'] ?? null;

$stmt = $db->query('SELECT p.*, po.po_number FROM purchase_order_items p LEFT JOIN purchase_orders po ON po.id = p.po_id');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$created = 0; $skipped = 0; $mismatchCount = 0;
foreach ($rows as $r) {
    $poiId = (int)$r['id'];
    $exists = $db->prepare('SELECT id, po_id FROM inventory WHERE purchase_order_item_id = :poi LIMIT 1');
    $exists->execute([':poi'=>$poiId]);
    $inv = $exists->fetch(PDO::FETCH_ASSOC);
    if ($inv) {
        // validate po_id matches
        if ((int)$inv['po_id'] !== (int)$r['po_id']) {
            echo "PO mismatch for inventory id {$inv['id']} (poi={$poiId}): inventory.po_id={$inv['po_id']} vs poi.po_id={$r['po_id']}\n";
            $mismatchCount++;
        } else {
            $skipped++;
        }
        continue;
    }
    // create inventory row
    $item_code = trim($r['item_code'] ?? '');
    $item_master_id = null;
    if ($item_code !== '') {
        $s = $db->prepare('SELECT id FROM item_master WHERE item_code = :code LIMIT 1');
        $s->execute([':code'=>$item_code]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        if ($row) $item_master_id = $row['id'];
    }
    $qty = isset($r['quantity']) ? (int)$r['quantity'] : 1;
    if ($qty <= 0) $qty = 1;
    $ins = $db->prepare('INSERT INTO inventory (item_master_id, po_id, serial_number, qa_cert_no, status, current_owner_id, received_at, notes, purchase_order_item_id, quantity) VALUES (:im, :po, :srl, NULL, :st, :owner, NOW(), :notes, :poi, :qty)');
    $ins->execute([':im'=>$item_master_id, ':po'=>$r['po_id'], ':srl'=>$r['serial_number'] ?? null, ':st'=>'In-QA', ':owner'=>$lmId, ':notes'=>'Imported from PO_ITEM '.$poiId, ':poi'=>$poiId, ':qty'=>$qty]);
    $created++;
    file_put_contents($logFile, date('c') . " - Created inventory from PO_ITEM {$poiId} (po_id={$r['po_id']})\n", FILE_APPEND);
}

 $summary = "Sync complete. Created={$created}, Skipped={$skipped}, Mismatches={$mismatchCount}\n";
 echo $summary;
 file_put_contents($logFile, date('c') . " - " . trim($summary) . "\n", FILE_APPEND);
 // insert audit row
 $insAudit = $db->prepare('INSERT INTO sync_audit (run_at, created_count, skipped_count, mismatches_count, notes) VALUES (NOW(), :c, :s, :m, :n)');
 $insAudit->execute([':c'=>$created, ':s'=>$skipped, ':m'=>$mismatchCount, ':n'=>'Imported from sync_po_items_to_inventory.php']);

?>
