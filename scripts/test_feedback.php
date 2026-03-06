<?php
// scripts/test_feedback.php
// Simple CLI test that inserts a feedback record into email_logs and verifies it.

require_once __DIR__ . '/../src/core/db.php';

echo "Starting feedback DB test...\n";

try {
    $db = Database::getInstance()->getConnection();

    $subject = 'CLI Test Feedback ' . date('Y-m-d H:i:s');
    $body = 'This is a test feedback inserted by scripts/test_feedback.php';
    $recipient = 'admin@example.org';
    $now = date('Y-m-d H:i:s');

    $stmt = $db->prepare('INSERT INTO email_logs (recipient_email, subject, body, sent_at, status, error_message) VALUES (:r,:s,:b,:t,:st,:err)');
    $stmt->execute([':r' => $recipient, ':s' => $subject, ':b' => $body, ':t' => $now, ':st' => 'sent', ':err' => null]);

    $id = $db->lastInsertId();
    echo "Inserted email_logs id=$id\n";

    $sel = $db->prepare('SELECT id, recipient_email, subject, body, sent_at, status, error_message FROM email_logs WHERE id = :id');
    $sel->execute([':id' => $id]);
    $row = $sel->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo "Verified row:\n";
        foreach ($row as $k => $v) {
            echo "  $k: " . ($v === null ? 'NULL' : $v) . "\n";
        }
        echo "Test succeeded.\n";
        exit(0);
    } else {
        echo "Failed to verify inserted row.\n";
        exit(2);
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    exit(3);
}
