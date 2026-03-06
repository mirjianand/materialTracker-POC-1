<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../bootstrap.php';

class ItemCreationTest extends TestCase {
    protected PDO $db;

    public function setUp(): void {
        // ensure DB access
        require_once __DIR__ . '/../../src/core/db.php';
        $this->db = Database::getInstance()->getConnection();
        // start clean transaction for test isolation
        $this->db->beginTransaction();
    }

    public function tearDown(): void {
        // rollback any changes
        $this->db->rollBack();
    }

    public function testCreateItemDirectInsert(): void {
        $code = 'TST' . rand(10000,99999);
        $stmt = $this->db->prepare('INSERT INTO item_master (item_code, description, quantity_type, is_active) VALUES (:code, :desc, :qt, 1)');
        $res = $stmt->execute([':code'=>$code, ':desc'=>'Test item', ':qt'=>'Number']);
        $this->assertTrue($res, 'Insert should succeed');

        $row = $this->db->prepare('SELECT * FROM item_master WHERE item_code = :code');
        $row->execute([':code'=>$code]);
        $f = $row->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($f, 'Inserted item should be retrievable');
        $this->assertEquals($code, $f['item_code']);
    }

    public function testCreateItemViaController(): void {
        // Simulate POST to ItemsController::create
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '',
            'item_code' => 'TST' . rand(10000,99999),
            'description' => 'Created by controller test',
            'quantity_type' => 'Number'
        ];

        // Ensure CSRF token exists
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            $_POST['csrf_token'] = $_SESSION['csrf_token'];
        }

        require_once __DIR__ . '/../../src/controllers/ItemsController.php';
        $c = new ItemsController();
        // Capture output to avoid headers
        ob_start();
        $c->create();
        $out = ob_get_clean();

        // After controller runs should have inserted item
        $stmt = $this->db->prepare('SELECT * FROM item_master WHERE description = :d');
        $stmt->execute([':d'=>'Created by controller test']);
        $f = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($f, 'Controller should insert item');
        $this->assertEquals('Created by controller test', $f['description']);
    }
}
