<?php
// src/controllers/QAController.php
require_once __DIR__ . '/BaseController.php';

class QAController extends BaseController {
    public function index() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('CommodityManager');
        require_once __DIR__ . '/../core/db.php';
        require_once __DIR__ . '/../core/csrf.php';
        $db = Database::getInstance()->getConnection();

        // Handle POST accept/reject
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
                $_SESSION['flash'] = ['type'=>'danger','message'=>'Invalid CSRF token'];
                header('Location: ' . (function_exists('base_path') ? base_path('qa') : '/qa'));
                exit;
            }
            $action = $_POST['action'] ?? '';
            $inv_id = !empty($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : 0;
            if ($inv_id <= 0) {
                $_SESSION['flash'] = ['type'=>'danger','message'=>'Invalid inventory id'];
                header('Location: ' . (function_exists('base_path') ? base_path('qa') : '/qa'));
                exit;
            }
            if ($action === 'accept') {
                $db->prepare('UPDATE inventory SET status = "Accepted" WHERE id = :id')->execute([':id'=>$inv_id]);
                $db->prepare('INSERT INTO item_transactions (inventory_id, from_user_id, to_user_id, transaction_type, quantity, remarks) VALUES (:inv, NULL, NULL, :tt, 1, :r)')
                    ->execute([':inv'=>$inv_id, ':tt'=>'Inward', ':r'=>'QA accepted']);
                $_SESSION['flash'] = ['type'=>'success','message'=>'Item accepted'];
            } else {
                $db->prepare('UPDATE inventory SET status = "Rejected" WHERE id = :id')->execute([':id'=>$inv_id]);
                $db->prepare('INSERT INTO item_transactions (inventory_id, from_user_id, to_user_id, transaction_type, quantity, remarks) VALUES (:inv, NULL, NULL, :tt, 1, :r)')
                    ->execute([':inv'=>$inv_id, ':tt'=>'Reject', ':r'=>'QA rejected']);
                $_SESSION['flash'] = ['type'=>'warning','message'=>'Item rejected'];
            }
            header('Location: ' . (function_exists('base_path') ? base_path('qa') : '/qa'));
            exit;
        }

        $rows = $db->query("SELECT i.id, im.item_code, im.description AS item_name, po.po_number, i.quantity, i.serial_number, i.status FROM inventory i JOIN item_master im ON im.id = i.item_master_id LEFT JOIN purchase_orders po ON po.id = i.po_id WHERE i.status = 'In-QA' ORDER BY i.received_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        return $this->render('qa/index', ['title'=>'QA Acceptance', 'items'=>$rows]);
    }
}

?>
