<?php
// src/controllers/AdminController.php
require_once __DIR__ . '/BaseController.php';

class AdminController extends BaseController {
    public function processLost() {
        return $this->render('admin/process_lost', ['title' => 'Process Item Lost Requests']);
    }

    // Bulk action handler for inventory (transfer / rework / surrender)
    public function bulkAction() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('LogisticsManager');
        require_once __DIR__ . '/../core/db.php';
        require_once __DIR__ . '/../core/csrf.php';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'));
            exit;
        }
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Invalid CSRF token'];
            header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'));
            exit;
        }

        $ids = [];
        if (!empty($_POST['ids'])) {
            if (is_array($_POST['ids'])) {
                $ids = array_map('intval', $_POST['ids']);
            } else {
                $ids = array_filter(array_map('intval', explode(',', $_POST['ids'])));
            }
        }
        $action = $_POST['action'] ?? '';
        if (empty($ids) || !in_array($action, ['transfer','rework','surrender'])) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'No items selected or invalid action'];
            header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'));
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $success = 0; $failed = 0;

        // If very large batch, enqueue for background processing
        if (count($ids) > 500) {
            $db->exec("CREATE TABLE IF NOT EXISTS bulk_jobs (id INT AUTO_INCREMENT PRIMARY KEY, action VARCHAR(64), payload TEXT, created_by INT, status VARCHAR(32), created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
            $payload = json_encode(['ids'=>$ids,'action'=>$action,'params'=>$_POST]);
            $createdBy = $_SESSION['user_id'] ?? null;
            $ins = $db->prepare('INSERT INTO bulk_jobs (action, payload, created_by, status) VALUES (:a,:p,:c,:s)');
            $ins->execute([':a'=>$action, ':p'=>$payload, ':c'=>$createdBy, ':s'=>'pending']);
            $_SESSION['flash'] = ['type'=>'info','message'=>'Bulk action queued for background processing.'];
            header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'));
            exit;
        }

        try {
            $db->beginTransaction();
            if ($action === 'transfer') {
                $toUser = !empty($_POST['to_user']) ? (int)$_POST['to_user'] : null;
                if ($toUser) {
                    $chk = $db->prepare('SELECT id FROM users WHERE id = :id LIMIT 1'); $chk->execute([':id'=>$toUser]);
                    if (!$chk->fetch()) { throw new Exception('Target user not found'); }
                }
                $remarks = trim($_POST['remarks'] ?? 'Bulk transfer');
                $transferDate = trim($_POST['transfer_date'] ?? '');
                if ($transferDate === '') $transferDate = date('Y-m-d H:i:s'); else $transferDate = date('Y-m-d H:i:s', strtotime($transferDate));
                foreach ($ids as $id) {
                    $cur = $db->prepare('SELECT current_owner_id, status FROM inventory WHERE id = :id LIMIT 1'); $cur->execute([':id'=>$id]); $curRow = $cur->fetch(PDO::FETCH_ASSOC);
                    $fromUser = $curRow['current_owner_id'] ?? null;
                    $curStatus = $curRow['status'] ?? null;
                    // Only allow transfer of QA-accepted items
                    if ($curStatus !== 'Accepted') { $failed++; continue; }
                    $db->prepare('UPDATE inventory SET current_owner_id = :newOwner, status = "Transferred" WHERE id = :id')
                        ->execute([':newOwner'=>$toUser, ':id'=>$id]);
                    $db->prepare('INSERT INTO item_transactions (inventory_id, from_user_id, to_user_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv, :from, :to, :tt, :q, :r, :td)')
                        ->execute([':inv'=>$id, ':from'=>$fromUser, ':to'=>$toUser, ':tt'=>'Transfer', ':q'=>1, ':r'=>$remarks, ':td'=>$transferDate]);
                    $success++;
                }
            } elseif ($action === 'rework') {
                $vendor = trim($_POST['vendor'] ?? '');
                $remarks = trim($_POST['remarks'] ?? 'Bulk rework');
                $upd = $db->prepare("UPDATE inventory SET status = 'To-Rework', notes = CONCAT(COALESCE(notes, ''), :note) WHERE id = :id");
                foreach ($ids as $id) {
                    $note = trim($remarks . ' Sent to vendor: ' . $vendor);
                    if ($upd->execute([':note'=>$note, ':id'=>$id])) {
                        $db->prepare('INSERT INTO item_transactions (inventory_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv, :tt, :q, :r, NOW())')
                            ->execute([':inv'=>$id, ':tt'=>'Rework', ':q'=>1, ':r'=>$note]);
                        $success++;
                    } else { $failed++; }
                }
            } elseif ($action === 'surrender') {
                $reason = trim($_POST['reason'] ?? '');
                $remarks = trim($_POST['remarks'] ?? 'Bulk surrender');
                $upd = $db->prepare("UPDATE inventory SET status = 'Surrendered', notes = CONCAT(COALESCE(notes, ''), :note) WHERE id = :id");
                foreach ($ids as $id) {
                    $note = trim($remarks . ' Surrendered: ' . $reason);
                    if ($upd->execute([':note'=>$note, ':id'=>$id])) {
                        $db->prepare('INSERT INTO item_transactions (inventory_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv, :tt, :q, :r, NOW())')
                            ->execute([':inv'=>$id, ':tt'=>'Surrender', ':q'=>1, ':r'=>$note]);
                        $success++;
                    } else { $failed++; }
                }
            }
            $db->commit();
            $_SESSION['flash'] = ['type'=>'success','message'=>"Bulk action completed: {$success} succeeded, {$failed} failed."];
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Bulk action failed: ' . $e->getMessage()];
        }

        header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'));
        exit;
    }

    public function processRework() {
        return $this->render('admin/process_rework', ['title' => 'Process Item Reworks']);
    }

    public function processSurrender() {
        return $this->render('admin/process_surrender', ['title' => 'Process Surrendered Items']);
    }

    // List notification recipients and show add form
    public function notificationRecipients() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('Admin');
        require_once __DIR__ . '/../core/db.php';
        $db = Database::getInstance()->getConnection();
        $rows = $db->query('SELECT id, role_name, email, created_at FROM notification_recipients ORDER BY role_name, email')->fetchAll();
        return $this->render('admin/notification_recipients', ['title' => 'Notification Recipients', 'recipients' => $rows]);
    }

    // Admin inventory view to inspect items across users
    public function inventory() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('LogisticsManager');
        require_once __DIR__ . '/../core/db.php';

        $db = Database::getInstance()->getConnection();
        // Filters and pagination similar to ItemsController.index
        $q = trim($_GET['q'] ?? '');
        $owner = !empty($_GET['owner_id']) ? (int)$_GET['owner_id'] : null;
        $status = trim($_GET['status'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = max(5, min(200, (int)($_GET['page_size'] ?? 25)));
        $offset = ($page - 1) * $pageSize;

        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = "(im.item_code LIKE :q OR im.description LIKE :q2)";
            $params[':q'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
        }
        if ($owner) { $where[] = 'i.current_owner_id = :owner'; $params[':owner'] = $owner; }
        if ($status !== '') { $where[] = 'i.status = :status'; $params[':status'] = $status; }

        $whereSql = '';
        if (!empty($where)) $whereSql = 'WHERE ' . implode(' AND ', $where);

        $totalStmt = $db->prepare('SELECT COUNT(*) FROM inventory i JOIN item_master im ON im.id = i.item_master_id ' . $whereSql);
        $totalStmt->execute($params);
        $totalRows = (int)$totalStmt->fetchColumn();

           $sql = 'SELECT i.*, im.item_code, im.description, c.name AS category_name, it.name AS item_type_name, mt.name AS material_type_name, u.name as owner_name, po.po_number '
               . 'FROM inventory i JOIN item_master im ON im.id = i.item_master_id '
               . 'LEFT JOIN purchase_orders po ON po.id = i.po_id '
             . 'LEFT JOIN item_categories c ON c.id = im.category_id '
             . 'LEFT JOIN item_types it ON it.id = im.item_type_id '
             . 'LEFT JOIN material_types mt ON mt.id = im.material_type_id '
             . 'LEFT JOIN users u ON u.id = i.current_owner_id '
             . $whereSql . ' ORDER BY i.received_at DESC LIMIT :lim OFFSET :off';

        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = $db->query('SELECT id, name FROM users ORDER BY name')->fetchAll();
        // get distinct statuses for filter
        $statuses = $db->query('SELECT DISTINCT status FROM inventory ORDER BY status')->fetchAll(PDO::FETCH_COLUMN);

        return $this->render('admin/inventory', ['title'=>'Inventory (Admin)', 'items'=>$rows, 'users'=>$users, 'owner'=>$owner, 'q'=>$q, 'status'=>$status, 'page'=>$page, 'pageSize'=>$pageSize, 'total'=>$totalRows, 'statuses'=>$statuses]);
    }

    // Transfer an inventory item to an employee (simple form)
    public function transferItem() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('LogisticsManager');
        require_once __DIR__ . '/../core/db.php';
        require_once __DIR__ . '/../core/csrf.php';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'));
            exit;
        }
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Invalid CSRF token'];
            header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'));
            exit;
        }
        $invId = !empty($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : 0;
        $toUser = !empty($_POST['to_user']) ? (int)$_POST['to_user'] : null;
        if ($invId <= 0) { $_SESSION['flash'] = ['type'=>'danger','message'=>'Invalid inventory id']; header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory')); exit; }

        $db = Database::getInstance()->getConnection();
        try {
            // get current owner
            $cur = $db->prepare('SELECT current_owner_id FROM inventory WHERE id = :id LIMIT 1'); $cur->execute([':id'=>$invId]); $curRow = $cur->fetch(PDO::FETCH_ASSOC);
            $fromUser = $curRow['current_owner_id'] ?? null;
            // ensure item is QA-Accepted before transferring
            $curStatusStmt = $db->prepare('SELECT status FROM inventory WHERE id = :id LIMIT 1'); $curStatusStmt->execute([':id'=>$invId]); $curStatus = $curStatusStmt->fetchColumn();
            if ($curStatus !== 'Accepted') {
                throw new Exception('Item must be QA-Accepted before transfer');
            }
            if ($toUser) {
                $chk = $db->prepare('SELECT id FROM users WHERE id = :id LIMIT 1'); $chk->execute([':id'=>$toUser]);
                if (!$chk->fetch()) { throw new Exception('Target user not found'); }
            }
            $db->prepare("UPDATE inventory SET current_owner_id = :newOwner, status = 'Transferred' WHERE id = :id")->execute([':newOwner'=>$toUser, ':id'=>$invId]);
            $remarks = trim($_POST['remarks'] ?? 'Admin transfer');
            $transferDate = trim($_POST['transfer_date'] ?? '');
            if ($transferDate === '') $transferDate = date('Y-m-d H:i:s'); else $transferDate = date('Y-m-d H:i:s', strtotime($transferDate));
            $db->prepare('INSERT INTO item_transactions (inventory_id, from_user_id, to_user_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv, :from, :to, :tt, :q, :r, :td)')
                ->execute([':inv'=>$invId, ':from'=>$fromUser, ':to'=>$toUser, ':tt'=>'Transfer', ':q'=>1, ':r'=>$remarks, ':td'=>$transferDate]);
            $_SESSION['flash'] = ['type'=>'success','message'=>'Inventory transferred'];
        } catch (Exception $e) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Transfer failed: ' . $e->getMessage()];
        }
        header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'));
        exit;
    }

    // Send an inventory item for rework (simple)
    public function reworkItem() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('LogisticsManager');
        require_once __DIR__ . '/../core/db.php';
        require_once __DIR__ . '/../core/csrf.php';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'));
            exit;
        }
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Invalid CSRF token'];
            header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'));
            exit;
        }
        $invId = !empty($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : 0;
        $vendor = trim($_POST['vendor'] ?? '');
        if ($invId <= 0) { $_SESSION['flash'] = ['type'=>'danger','message'=>'Invalid inventory id']; header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory')); exit; }

        $db = Database::getInstance()->getConnection();
        try {
            $notes = trim($_POST['remarks'] ?? '') . ' Sent to vendor: ' . $vendor;
            $db->prepare("UPDATE inventory SET status = 'To-Rework', notes = CONCAT(COALESCE(notes, ''), :note) WHERE id = :id")->execute([':note'=>$notes, ':id'=>$invId]);
            // log transaction
            $cur = $db->prepare('SELECT current_owner_id FROM inventory WHERE id = :id LIMIT 1'); $cur->execute([':id'=>$invId]); $curRow = $cur->fetch(PDO::FETCH_ASSOC);
            $fromUser = $curRow['current_owner_id'] ?? null;
            $db->prepare('INSERT INTO item_transactions (inventory_id, from_user_id, to_user_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv, :from, NULL, :tt, :q, :r, NOW())')
                ->execute([':inv'=>$invId, ':from'=>$fromUser, ':tt'=>'Rework', ':q'=>1, ':r'=>$notes]);
            $_SESSION['flash'] = ['type'=>'success','message'=>'Inventory marked for rework'];
        } catch (Exception $e) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Rework failed: ' . $e->getMessage()];
        }
        header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'));
        exit;
    }

    // Surrender (scrap) an inventory item (simple)
    public function surrenderItem() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('LogisticsManager');
        require_once __DIR__ . '/../core/db.php';
        require_once __DIR__ . '/../core/csrf.php';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'));
            exit;
        }
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Invalid CSRF token'];
            header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'));
            exit;
        }
        $invId = !empty($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : 0;
        $reason = trim($_POST['reason'] ?? '');
        if ($invId <= 0) { $_SESSION['flash'] = ['type'=>'danger','message'=>'Invalid inventory id']; header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory')); exit; }

        $db = Database::getInstance()->getConnection();
        try {
            $db->prepare("UPDATE inventory SET status = 'Surrendered', notes = CONCAT(COALESCE(notes, ''), :note) WHERE id = :id")->execute([':note'=>' Surrendered: ' . $reason, ':id'=>$invId]);
            // log transaction
            $cur = $db->prepare('SELECT current_owner_id FROM inventory WHERE id = :id LIMIT 1'); $cur->execute([':id'=>$invId]); $curRow = $cur->fetch(PDO::FETCH_ASSOC);
            $fromUser = $curRow['current_owner_id'] ?? null;
            $db->prepare('INSERT INTO item_transactions (inventory_id, from_user_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv, :from, :tt, :q, :r, NOW())')
                ->execute([':inv'=>$invId, ':from'=>$fromUser, ':tt'=>'Surrender', ':q'=>1, ':r'=>'Surrendered: ' . $reason]);
            $_SESSION['flash'] = ['type'=>'success','message'=>'Inventory marked as surrendered'];
        } catch (Exception $e) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Surrender failed: ' . $e->getMessage()];
        }
        header('Location: ' . (function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'));
        exit;
    }

    // Render transfer form for single item
    public function transferForm() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('LogisticsManager');
        require_once __DIR__ . '/../core/db.php';
        $db = Database::getInstance()->getConnection();
        $invId = !empty($_GET['inventory_id']) ? (int)$_GET['inventory_id'] : 0;
        $item = null;
        if ($invId) {
            $stmt = $db->prepare('SELECT i.*, im.item_code, im.description FROM inventory i LEFT JOIN item_master im ON im.id = i.item_master_id WHERE i.id = :id');
            $stmt->execute([':id'=>$invId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        $users = $db->query('SELECT id, name FROM users ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
        return $this->render('admin/transfer_form', ['item'=>$item, 'users'=>$users]);
    }

    public function reworkForm() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('LogisticsManager');
        require_once __DIR__ . '/../core/db.php';
        $db = Database::getInstance()->getConnection();
        $invId = !empty($_GET['inventory_id']) ? (int)$_GET['inventory_id'] : 0;
        $item = null;
        if ($invId) {
            $stmt = $db->prepare('SELECT i.*, im.item_code, im.description FROM inventory i LEFT JOIN item_master im ON im.id = i.item_master_id WHERE i.id = :id');
            $stmt->execute([':id'=>$invId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $this->render('admin/rework_form', ['item'=>$item]);
    }

    public function surrenderForm() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('LogisticsManager');
        require_once __DIR__ . '/../core/db.php';
        $db = Database::getInstance()->getConnection();
        $invId = !empty($_GET['inventory_id']) ? (int)$_GET['inventory_id'] : 0;
        $item = null;
        if ($invId) {
            $stmt = $db->prepare('SELECT i.*, im.item_code, im.description FROM inventory i LEFT JOIN item_master im ON im.id = i.item_master_id WHERE i.id = :id');
            $stmt->execute([':id'=>$invId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $this->render('admin/surrender_form', ['item'=>$item]);
    }

    // Handle add POST
    public function addNotificationRecipient() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('Admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (function_exists('base_path') ? base_path('admin/notification_recipients') : '/admin/notification_recipients'));
            exit;
        }
        require_once __DIR__ . '/../core/csrf.php';
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid CSRF token'];
            header('Location: ' . (function_exists('base_path') ? base_path('admin/notification_recipients') : '/admin/notification_recipients'));
            exit;
        }
        $role = trim($_POST['role_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($role === '' || $email === '') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Role and email are required'];
            header('Location: ' . (function_exists('base_path') ? base_path('admin/notification_recipients') : '/admin/notification_recipients'));
            exit;
        }
        require_once __DIR__ . '/../core/db.php';
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('INSERT INTO notification_recipients (role_name, email, created_at) VALUES (:r,:e,NOW())');
        $stmt->execute([':r'=>$role,':e'=>$email]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Recipient added'];
        header('Location: ' . (function_exists('base_path') ? base_path('admin/notification_recipients') : '/admin/notification_recipients'));
        exit;
    }

    // Handle delete POST
    public function deleteNotificationRecipient() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('Admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (function_exists('base_path') ? base_path('admin/notification_recipients') : '/admin/notification_recipients'));
            exit;
        }
        require_once __DIR__ . '/../core/csrf.php';
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid CSRF token'];
            header('Location: ' . (function_exists('base_path') ? base_path('admin/notification_recipients') : '/admin/notification_recipients'));
            exit;
        }
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid id'];
            header('Location: ' . (function_exists('base_path') ? base_path('admin/notification_recipients') : '/admin/notification_recipients'));
            exit;
        }
        require_once __DIR__ . '/../core/db.php';
        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare('DELETE FROM notification_recipients WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Recipient deleted'];
        } catch (Exception $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Delete failed: ' . $e->getMessage()];
        }
        header('Location: ' . (function_exists('base_path') ? base_path('admin/notification_recipients') : '/admin/notification_recipients'));
        exit;
    }

}