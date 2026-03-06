<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/core/db.php';
require_once __DIR__ . '/../../src/controllers/ItemsController.php';

final class ItemsControllerTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function testCreateFormContainsInsertedSelects(): void
    {
        $this->db->beginTransaction();

        $catName = 'TestCat_' . uniqid();
        $typeName = 'TestType_' . uniqid();
        $mtName = 'TestMT_' . uniqid();

        $ins = $this->db->prepare('INSERT INTO item_categories (name) VALUES (:n)');
        $ins->execute([':n' => $catName]);
        $ins = $this->db->prepare('INSERT INTO item_types (name) VALUES (:n)');
        $ins->execute([':n' => $typeName]);
        $ins = $this->db->prepare('INSERT INTO material_types (name) VALUES (:n)');
        $ins->execute([':n' => $mtName]);

        // Ensure server method defaults to GET for controller rendering
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new ItemsController();
        $html = $controller->create();

        $this->assertStringContainsString($catName, $html);
        $this->assertStringContainsString($typeName, $html);
        $this->assertStringContainsString($mtName, $html);

        $this->db->rollBack();
    }
}
