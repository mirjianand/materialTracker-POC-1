<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/core/db.php';

final class FeedbackTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function testFeedbackInsertRollback(): void
    {
        $this->db->beginTransaction();

        $subject = 'PHPUnit Test ' . date('Y-m-d H:i:s');
        $body = 'Integration test body from PHPUnit';
        $recipient = 'admin@example.org';
        $now = date('Y-m-d H:i:s');

        $stmt = $this->db->prepare('INSERT INTO email_logs (recipient_email, subject, body, sent_at, status, error_message) VALUES (:r,:s,:b,:t,:st,:err)');
        $stmt->execute([':r' => $recipient, ':s' => $subject, ':b' => $body, ':t' => $now, ':st' => 'sent', ':err' => null]);

        $id = (int)$this->db->lastInsertId();
        $this->assertGreaterThan(0, $id, 'Insert should return an id');

        $sel = $this->db->prepare('SELECT recipient_email, subject, body, status FROM email_logs WHERE id = :id');
        $sel->execute([':id' => $id]);
        $row = $sel->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($row);
        $this->assertEquals($recipient, $row['recipient_email']);
        $this->assertEquals($subject, $row['subject']);
        $this->assertEquals($body, $row['body']);
        $this->assertEquals('sent', $row['status']);

        $this->db->rollBack();
    }
}
