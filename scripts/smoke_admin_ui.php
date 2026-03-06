<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../src/controllers/AdminController.php';

$db = Database::getInstance()->getConnection();
$inv = $db->query('SELECT id FROM inventory LIMIT 1')->fetch(PDO::FETCH_ASSOC);
if (!$inv) { echo "No inventory rows found; cannot run smoke UI test.\n"; exit(1); }
$invId = (int)$inv['id'];

// Render admin inventory view directly (simulate controller data)
$page = 1; $pageSize = 25; $q = ''; $owner = null; $status = '';
$offset = ($page - 1) * $pageSize;
$totalStmt = $db->prepare('SELECT COUNT(*) FROM inventory'); $totalStmt->execute(); $total = (int)$totalStmt->fetchColumn();
$stmt = $db->prepare('SELECT i.*, im.item_code, im.description, u.name as owner_name, po.po_number FROM inventory i LEFT JOIN item_master im ON im.id = i.item_master_id LEFT JOIN purchase_orders po ON po.id = i.po_id LEFT JOIN users u ON u.id = i.current_owner_id ORDER BY i.received_at DESC LIMIT :lim OFFSET :off');
$stmt->bindValue(':lim', $pageSize, PDO::PARAM_INT); $stmt->bindValue(':off', $offset, PDO::PARAM_INT); $stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$users = $db->query('SELECT id, name FROM users ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$statuses = $db->query('SELECT DISTINCT status FROM inventory ORDER BY status')->fetchAll(PDO::FETCH_COLUMN);
ob_start();
try { include __DIR__ . '/../src/views/admin/inventory.php'; } catch (Exception $e) { /* ignore */ }
$invHtml = ob_get_clean();

// Render transfer form for selected inventory id by including view with item data
$itemStmt = $db->prepare('SELECT i.*, im.item_code, im.description FROM inventory i LEFT JOIN item_master im ON im.id = i.item_master_id WHERE i.id = :id');
$itemStmt->execute([':id'=>$invId]);
$item = $itemStmt->fetch(PDO::FETCH_ASSOC);
$users = $db->query('SELECT id, name FROM users ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
ob_start();
try { include __DIR__ . '/../src/views/admin/transfer_form.php'; } catch (Exception $e) { /* ignore */ }
$transferHtml = ob_get_clean();

// Render rework and surrender forms as well
ob_start();
try { include __DIR__ . '/../src/views/admin/rework_form.php'; } catch (Exception $e) { }
$reworkHtml = ob_get_clean();
ob_start();
try { include __DIR__ . '/../src/views/admin/surrender_form.php'; } catch (Exception $e) { }
$surrenderHtml = ob_get_clean();

echo "Smoke UI test for Admin pages:\n";
echo "- Inventory page: ";
if (stripos($invHtml, 'Inventory (Admin)') !== false) echo "OK\n"; else echo "MISSING\n";

echo "- Transfer form for inventory id={$invId}: ";
if (stripos($transferHtml, 'Transfer Inventory Item') === false) {
    echo "MISSING\n";
    file_put_contents('smoke_inventory_output.html', $invHtml);
    file_put_contents('smoke_transfer_output.html', $transferHtml);
    echo "Saved HTML outputs to smoke_*.html for inspection.\n";
    exit(1);
}

// detect if Transfer button disabled
$disabled = stripos($transferHtml, 'disabled title="Only QA-accepted items can be transferred"') !== false || stripos($transferHtml, 'disabled>Transfer</button>') !== false;
if ($disabled) {
    echo "Rendered (Transfer disabled — item not QA-Accepted)\n";
} else {
    echo "Rendered (Transfer enabled)\n";
}

// Rework form check
echo "- Rework form: ";
if (stripos($reworkHtml, 'Already marked To-Rework') !== false) echo "Already To-Rework\n";
elseif (stripos($reworkHtml, 'Cannot rework surrendered item') !== false) echo "Cannot rework (surrendered)\n";
elseif (stripos($reworkHtml, 'Send to Rework') !== false) echo "Rendered (action enabled)\n";
else echo "Missing\n";

// Surrender form check
echo "- Surrender form: ";
if (stripos($surrenderHtml, 'Already surrendered') !== false) echo "Already surrendered\n";
elseif (stripos($surrenderHtml, 'Surrender') !== false) echo "Rendered (action enabled)\n";
else echo "Missing\n";

// success
exit(0);
