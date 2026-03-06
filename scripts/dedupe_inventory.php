<?php
// scripts/dedupe_inventory.php
// Remove duplicate inventory rows for the same purchase_order_item_id, keeping the earliest id

require_once __DIR__ . '/../src/core/db.php';

if (PHP_SAPI !== 'cli') {
    echo "Run from CLI only.\n";
    exit(1);
}

$db = Database::getInstance()->getConnection();
try {
    echo "Finding duplicate inventory rows by purchase_order_item_id...\n";
    $rows = $db->query("SELECT purchase_order_item_id, COUNT(*) AS cnt FROM inventory WHERE purchase_order_item_id IS NOT NULL GROUP BY purchase_order_item_id HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
    $totalDeleted = 0;
    foreach ($rows as $r) {
        $poi = $r['purchase_order_item_id'];
        echo "Cleaning duplicates for PO item {$poi}...\n";
        // keep the earliest id
        $keep = $db->query("SELECT id FROM inventory WHERE purchase_order_item_id = " . intval($poi) . " ORDER BY id ASC LIMIT 1")->fetchColumn();
        if (!$keep) continue;
        $del = $db->exec("DELETE FROM inventory WHERE purchase_order_item_id = " . intval($poi) . " AND id <> " . intval($keep));
        $totalDeleted += $del;
        echo "  Kept {$keep}, deleted {$del}\n";
    }
    echo "Done. Total deleted: {$totalDeleted}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
