<?php
// CLI worker: process one bulk_jobs entry (queued) and apply actions
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/core/db.php';

$db = Database::getInstance()->getConnection();
echo "Looking for queued bulk jobs...\n";
$job = $db->query("SELECT * FROM bulk_jobs WHERE status IN ('queued','pending') ORDER BY created_at ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$job) { echo "No queued jobs.\n"; exit(0); }
echo "Processing job id={$job['id']} action={$job['action']}\n";
$db->beginTransaction();
try {
    $payload = json_decode($job['payload'], true);
    $ids = $payload['ids'] ?? [];
    $action = $job['action'];
    if (empty($ids) || !in_array($action, ['transfer','rework','surrender'])) {
        throw new Exception('Invalid job payload');
    }
    if ($action === 'transfer') {
        $toUser = $payload['params']['to_user'] ?? $payload['params']['to_user_id'] ?? null;
        $remarks = $payload['params']['remarks'] ?? 'Bulk transfer (worker)';
        $transferDate = $payload['params']['transfer_date'] ?? null;
        if ($transferDate === null || $transferDate === '') $transferDate = date('Y-m-d H:i:s'); else $transferDate = date('Y-m-d H:i:s', strtotime($transferDate));
        foreach ($ids as $id) {
            $cur = $db->prepare('SELECT current_owner_id FROM inventory WHERE id = :id LIMIT 1'); $cur->execute([':id'=>$id]); $curRow = $cur->fetch(PDO::FETCH_ASSOC);
            $fromUser = $curRow['current_owner_id'] ?? null;
            $db->prepare('UPDATE inventory SET current_owner_id = :newOwner, status = "Transferred" WHERE id = :id')->execute([':newOwner'=>$toUser, ':id'=>$id]);
            $db->prepare('INSERT INTO item_transactions (inventory_id, from_user_id, to_user_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv, :from, :to, :tt, :q, :r, :td)')
                ->execute([':inv'=>$id, ':from'=>$fromUser, ':to'=>$toUser, ':tt'=>'Transfer', ':q'=>1, ':r'=>$remarks, ':td'=>$transferDate]);
        }
    } elseif ($action === 'rework') {
        $remarks = $payload['params']['remarks'] ?? 'Bulk rework (worker)';
        foreach ($ids as $id) {
            $db->prepare("UPDATE inventory SET status = 'To-Rework', notes = CONCAT(COALESCE(notes, ''), :note) WHERE id = :id")->execute([':note'=>$remarks, ':id'=>$id]);
            $db->prepare('INSERT INTO item_transactions (inventory_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv, :tt, :q, :r, NOW())')
                ->execute([':inv'=>$id, ':tt'=>'Rework', ':q'=>1, ':r'=>$remarks]);
        }
    } elseif ($action === 'surrender') {
        $remarks = $payload['params']['remarks'] ?? 'Bulk surrender (worker)';
        foreach ($ids as $id) {
            $db->prepare("UPDATE inventory SET status = 'Surrendered', notes = CONCAT(COALESCE(notes, ''), :note) WHERE id = :id")->execute([':note'=>$remarks, ':id'=>$id]);
            $db->prepare('INSERT INTO item_transactions (inventory_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv, :tt, :q, :r, NOW())')
                ->execute([':inv'=>$id, ':tt'=>'Surrender', ':q'=>1, ':r'=>$remarks]);
        }
    }

    $db->prepare('UPDATE bulk_jobs SET status = :st, updated_at = NOW() WHERE id = :id')->execute([':st'=>'completed', ':id'=>$job['id']]);
    $db->commit();
    echo "Job {$job['id']} processed successfully.\n";
} catch (Exception $e) {
    $db->rollBack();
    $db->prepare('UPDATE bulk_jobs SET status = :st, updated_at = NOW() WHERE id = :id')->execute([':st'=>'failed', ':id'=>$job['id']]);
    echo "Job failed: " . $e->getMessage() . "\n";
    exit(1);
}

?>
