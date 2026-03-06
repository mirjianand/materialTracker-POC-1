<?php
// CLI: simple E2E script to create two users, an item, transfer it and accept the transfer.
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/core/db.php';

$db = Database::getInstance()->getConnection();
echo "Starting E2E transfer/accept script\n";

// helper: ensure user exists
function ensureUser($db, $email, $name, $role = 'User') {
    $s = $db->prepare('SELECT id FROM users WHERE email = :e LIMIT 1'); $s->execute([':e'=>$email]); $r = $s->fetch(PDO::FETCH_ASSOC);
    if ($r) return (int)$r['id'];
    // emp_id must be unique; generate a simple one
    $emp = 'E2E' . rand(1000,9999);
    $ins = $db->prepare('INSERT INTO users (emp_id, name, email, role, is_active) VALUES (:emp,:n,:e,:r,1)');
    $ins->execute([':emp'=>$emp, ':n'=>$name, ':e'=>$email, ':r'=>$role]);
    return (int)$db->lastInsertId();
}

$u1 = ensureUser($db, 'e2e.sender@example.local', 'E2E Sender');
$u2 = ensureUser($db, 'e2e.receiver@example.local', 'E2E Receiver');
echo "Users: sender={$u1}, receiver={$u2}\n";

// ensure an item_master
$s = $db->prepare('SELECT id FROM item_master WHERE item_code = :c LIMIT 1'); $s->execute([':c'=>'E2E-ITEM-001']); $r = $s->fetch(PDO::FETCH_ASSOC);
if ($r) { $im = (int)$r['id']; } else {
    $db->prepare('INSERT INTO item_master (item_code, description, quantity_type, is_active) VALUES (:c, :d, :qt, 1)')->execute([':c'=>'E2E-ITEM-001', ':d'=>'E2E test item', ':qt'=>'Number']);
    $im = (int)$db->lastInsertId();
}
echo "Item master id: {$im}\n";

// create inventory row owned by sender
$db->prepare('INSERT INTO inventory (item_master_id, po_id, serial_number, status, current_owner_id, received_at, notes, quantity) VALUES (:im,NULL,:srl,:st,:owner,NOW(),:notes,1)')
    ->execute([':im'=>$im, ':srl'=>'E2E-SRL-'.time(), ':st'=>'With Owner', ':owner'=>$u1, ':notes'=>'E2E created']);
$invId = (int)$db->lastInsertId();
echo "Created inventory id: {$invId}\n";

// create transfer
$tn = 'E2E'.time().rand(10,99);
$db->prepare('INSERT INTO transfers (transfer_number, transfer_type, from_user_id, to_user_id, created_by, created_at, status, notes) VALUES (:tn,:tt,:from,:to,:cb,NOW(),"pending",:notes)')
    ->execute([':tn'=>$tn, ':tt'=>'to_employee', ':from'=>$u1, ':to'=>$u2, ':cb'=>$u1, ':notes'=>'E2E transfer']);
$transferId = (int)$db->lastInsertId();
$db->prepare('INSERT INTO transfer_items (transfer_id, inventory_id, item_master_id, quantity, status, remarks) VALUES (:tid,:inv,:im,1,"in-transit",:r)')
    ->execute([':tid'=>$transferId, ':inv'=>$invId, ':im'=>$im, ':r'=>'E2E']);
$db->prepare('UPDATE inventory SET status = "In-transit" WHERE id = :id')->execute([':id'=>$invId]);
echo "Transfer created id={$transferId}, inventory set In-transit\n";

// simulate receiver accepting
$db->beginTransaction();
try {
    $items = $db->prepare('SELECT id, inventory_id FROM transfer_items WHERE transfer_id = :tid'); $items->execute([':tid'=>$transferId]); $titems = $items->fetchAll(PDO::FETCH_ASSOC);
    foreach ($titems as $ti) {
        $db->prepare('UPDATE transfer_items SET status = "accepted" WHERE id = :id')->execute([':id'=>$ti['id']]);
        if (!empty($ti['inventory_id'])) {
            $db->prepare('UPDATE inventory SET current_owner_id = :newOwner, status = "With Owner", acknowledged_at = NOW() WHERE id = :id')->execute([':newOwner'=>$u2, ':id'=>$ti['inventory_id']]);
            $db->prepare('INSERT INTO item_transactions (inventory_id, from_user_id, to_user_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv,:from,:to,:tt,:q,:r,NOW())')
                ->execute([':inv'=>$ti['inventory_id'], ':from'=>$u1, ':to'=>$u2, ':tt'=>'Transfer', ':q'=>1, ':r'=>'E2E accept']);
        }
    }
    $db->prepare('UPDATE transfers SET status = "accepted", actioned_at = NOW(), actioned_by = :ab WHERE id = :id')->execute([':ab'=>$u2, ':id'=>$transferId]);
    $db->commit();
    echo "Transfer accepted by user {$u2}\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "Accept failed: " . $e->getMessage() . "\n";
    exit(1);
}

$owner = $db->prepare('SELECT current_owner_id, status FROM inventory WHERE id = :id'); $owner->execute([':id'=>$invId]); $o = $owner->fetch(PDO::FETCH_ASSOC);
echo "Final inventory owner={$o['current_owner_id']}, status={$o['status']}\n";

echo "E2E script completed\n";

?>
