<?php
// scripts/run_test_email_to.php
// Usage: php scripts/run_test_email_to.php recipient@example.org
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/core/session.php';

SessionHelper::start();

$recipient = $argv[1] ?? (defined('TEST_NOTIFICATION_EMAIL') ? TEST_NOTIFICATION_EMAIL : null);
if (!$recipient) {
    echo "Usage: php scripts/run_test_email_to.php recipient@example.org\n";
    exit(2);
}

require_once __DIR__ . '/../src/controllers/TestController.php';
require_once __DIR__ . '/../src/core/EmailService.php';

$emailer = new EmailService();
$subject = 'Test Notification (custom) from Material Tracker';
$body = 'This is a test notification sent at ' . date('Y-m-d H:i:s') . ' to ' . $recipient;
$result = $emailer->send($recipient, $subject, $body);
if ($result['success']) {
    echo "Test email sent to $recipient\n";
} else {
    echo "Test email failed: " . ($result['error'] ?? 'unknown') . "\n";
}

// Also render controller output for consistency
$controller = new TestController();
echo $controller->sendEmail();
