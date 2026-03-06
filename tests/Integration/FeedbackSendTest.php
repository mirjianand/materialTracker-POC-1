<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/core/db.php';
require_once __DIR__ . '/../../src/core/EmailService.php';
require_once __DIR__ . '/../../src/controllers/FeedbackController.php';

final class FeedbackSendTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = Database::getInstance()->getConnection();
        // session already started in tests/bootstrap.php
    }

    public function testSendUsesEmailServiceAndLogs(): void
    {
        $this->db->beginTransaction();

        // Prepare POST data
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['subject'] = 'PHPUnit Feedback Send ' . uniqid();
        $_POST['message'] = 'This is a test message.';

        // Create a mock EmailService that returns success
        $mock = $this->createMock(EmailService::class);
        $mock->method('send')->willReturn(['success' => true, 'error' => null]);

        $controller = new FeedbackController($mock);
        $controller->send();

        // Verify flash set
        $this->assertArrayHasKey('flash', $_SESSION);
        $this->assertEquals('success', $_SESSION['flash']['type']);

        // Verify DB log exists
        $stmt = $this->db->prepare('SELECT id, recipient_email, subject, body, status FROM email_logs WHERE subject = :s ORDER BY id DESC LIMIT 1');
        $stmt->execute([':s' => $_POST['subject']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($row);
        $this->assertEquals($_POST['subject'], $row['subject']);
        $this->assertEquals('sent', $row['status']);

        $this->db->rollBack();
    }

    protected function tearDown(): void
    {
        // clear session flash
        if (isset($_SESSION['flash'])) unset($_SESSION['flash']);
    }
}
