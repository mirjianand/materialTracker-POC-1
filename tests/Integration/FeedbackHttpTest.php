<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/core/db.php';
require_once __DIR__ . '/../../src/controllers/FeedbackController.php';

final class FeedbackHttpTest extends TestCase
{
    public function testFeedbackFormRendered(): void
    {
        $controller = new FeedbackController();
        $html = $controller->index();

        $this->assertStringContainsString('name="subject"', $html);
        $this->assertStringContainsString('name="message"', $html);
        $this->assertStringContainsString('<form', $html);
    }
}
