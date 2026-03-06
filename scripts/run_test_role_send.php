<?php
// scripts/run_test_role_send.php
// Usage: php scripts/run_test_role_send.php [RoleName]
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/core/session.php';
SessionHelper::start();

$role = $argv[1] ?? 'CommodityManager';
require_once __DIR__ . '/../src/core/EmailService.php';
$emailer = new EmailService();
$subject = "Test Role Notification to $role from Material Tracker";
$data = ['body' => "This is a test role-based notification for role $role sent at " . date('Y-m-d H:i:s')];

$result = $emailer->sendToRole($role, $subject, 'item_created', $data);

echo "sendToRole result:\n";
print_r($result);

// Optionally show last 10 email_logs
try {
    require_once __DIR__ . '/../src/core/db.php';
    $db = Database::getInstance()->getConnection();
    $rows = $db->query('SELECT id, recipient_email, subject, sent_at, status, error_message FROM email_logs ORDER BY id DESC LIMIT 10')->fetchAll();
    echo "\nLast email_logs entries:\n";
    foreach ($rows as $r) {
        echo "#{$r['id']} {$r['recipient_email']} {$r['status']} {$r['sent_at']}" . PHP_EOL;
        if (!empty($r['error_message'])) echo "  ERROR: {$r['error_message']}\n";
    }
} catch (Exception $e) {
    echo "Could not read email_logs: " . $e->getMessage() . "\n";
}

?>