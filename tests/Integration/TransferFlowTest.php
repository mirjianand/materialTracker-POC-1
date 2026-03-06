<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../bootstrap.php';

class TransferFlowTest extends TestCase {
    protected $db;

    protected function setUp(): void {
        require_once __DIR__ . '/../../src/core/db.php';
        $this->db = Database::getInstance()->getConnection();
        $this->db->beginTransaction();
    }

    protected function tearDown(): void {
        $this->db->rollBack();
    }

    public function testCreateAndAcceptTransfer() {
        // create users
        $u1 = $this->ensureUser('test.sender@example.local', 'Test Sender');
        $u2 = $this->ensureUser('test.receiver@example.local', 'Test Receiver');

        // create item master
        $im = $this->ensureItemMaster('TF-ITEM-001', 'Transfer test item');

        // create inventory row
        $this->db->prepare('INSERT INTO inventory (item_master_id, status, current_owner_id, received_at, quantity) VALUES (:im, "With Owner", :owner, NOW(), 1)')
            ->execute([':im'=>$im, ':owner'=>$u1]);
        $invId = (int)$this->db->lastInsertId();

        // create transfer header
        $tn = 'TUNIT' . time();
        $this->db->prepare('INSERT INTO transfers (transfer_number, transfer_type, from_user_id, to_user_id, created_by, created_at, status) VALUES (:tn,:tt,:from,:to,:cb,NOW(),"pending")')
            ->execute([':tn'=>$tn, ':tt'=>'to_employee', ':from'=>$u1, ':to'=>$u2, ':cb'=>$u1]);
        $transferId = (int)$this->db->lastInsertId();

        $this->db->prepare('INSERT INTO transfer_items (transfer_id, inventory_id, item_master_id, quantity, status) VALUES (:tid,:inv,:im,1,"in-transit")')
            ->execute([':tid'=>$transferId, ':inv'=>$invId, ':im'=>$im]);

        // mark inventory in-transit
        $this->db->prepare('UPDATE inventory SET status = "In-transit" WHERE id = :id')->execute([':id'=>$invId]);

        // accept transfer (simulate receiver)
        $items = $this->db->prepare('SELECT id, inventory_id FROM transfer_items WHERE transfer_id = :tid'); $items->execute([':tid'=>$transferId]); $titems = $items->fetchAll(PDO::FETCH_ASSOC);
        foreach ($titems as $ti) {
            $this->db->prepare('UPDATE transfer_items SET status = "accepted" WHERE id = :id')->execute([':id'=>$ti['id']]);
            if (!empty($ti['inventory_id'])) {
                $this->db->prepare('UPDATE inventory SET current_owner_id = :newOwner, status = "With Owner", acknowledged_at = NOW() WHERE id = :id')->execute([':newOwner'=>$u2, ':id'=>$ti['inventory_id']]);
                $this->db->prepare('INSERT INTO item_transactions (inventory_id, from_user_id, to_user_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv,:from,:to,:tt,:q,:r,NOW())')
                    ->execute([':inv'=>$ti['inventory_id'], ':from'=>$u1, ':to'=>$u2, ':tt'=>'Transfer', ':q'=>1, ':r'=>'unit test']);
            }
        }
        $this->db->prepare('UPDATE transfers SET status = "accepted", actioned_at = NOW(), actioned_by = :ab WHERE id = :id')->execute([':ab'=>$u2, ':id'=>$transferId]);

        // assert final owner
        $stmt = $this->db->prepare('SELECT current_owner_id, status FROM inventory WHERE id = :id'); $stmt->execute([':id'=>$invId]); $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals($u2, (int)$row['current_owner_id']);
        $this->assertEquals('With Owner', $row['status']);

        // assert item_transactions created
        $t = $this->db->prepare('SELECT COUNT(*) FROM item_transactions WHERE inventory_id = :id AND transaction_type = "Transfer"'); $t->execute([':id'=>$invId]);
        $count = (int)$t->fetchColumn();
        $this->assertGreaterThanOrEqual(1, $count);
    }

    protected function ensureUser($email, $name) {
        $s = $this->db->prepare('SELECT id FROM users WHERE email = :e LIMIT 1'); $s->execute([':e'=>$email]); $r = $s->fetch(PDO::FETCH_ASSOC);
        if ($r) return (int)$r['id'];
        $emp = 'TU' . rand(1000,9999);
        $ins = $this->db->prepare('INSERT INTO users (emp_id, name, email, role, is_active) VALUES (:emp,:n,:e,:r,1)');
        $ins->execute([':emp'=>$emp, ':n'=>$name, ':e'=>$email, ':r'=>'User']);
        return (int)$this->db->lastInsertId();
    }

    protected function ensureItemMaster($code, $desc) {
        $s = $this->db->prepare('SELECT id FROM item_master WHERE item_code = :c LIMIT 1'); $s->execute([':c'=>$code]); $r = $s->fetch(PDO::FETCH_ASSOC);
        if ($r) return (int)$r['id'];
        $this->db->prepare('INSERT INTO item_master (item_code, description, quantity_type, is_active) VALUES (:c,:d,:qt,1)')->execute([':c'=>$code, ':d'=>$desc, ':qt'=>'Number']);
        return (int)$this->db->lastInsertId();
    }
}
