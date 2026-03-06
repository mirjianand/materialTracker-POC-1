<?php
// scripts/backfill_recipients.php
// Backfill notification_recipients table from config $ROLE_RECIPIENTS
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/core/db.php';

$db = Database::getInstance()->getConnection();
$count = 0;
foreach ($ROLE_RECIPIENTS as $role => $addrs) {
    foreach ($addrs as $email) {
        // avoid duplicates
        $stmt = $db->prepare('SELECT COUNT(*) FROM notification_recipients WHERE role_name = :r AND email = :e');
        $stmt->execute([':r'=>$role, ':e'=>$email]);
        $n = (int)$stmt->fetchColumn();
        if ($n === 0) {
            $ins = $db->prepare('INSERT INTO notification_recipients (role_name, email, created_at) VALUES (:r,:e,NOW())');
            $ins->execute([':r'=>$role, ':e'=>$email]);
            $count++;
        }
    }
}

echo "Backfilled $count recipient(s)\n";

?>