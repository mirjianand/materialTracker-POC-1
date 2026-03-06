<?php
// src/controllers/OpsController.php
require_once __DIR__ . '/BaseController.php';

class OpsController extends BaseController {
    public function transactions() {
        require_once __DIR__ . '/../core/db.php';
        $db = Database::getInstance()->getConnection();
        require_once __DIR__ . '/../core/session.php';
        require_once __DIR__ . '/../core/csrf.php';
        SessionHelper::start();
        $currentUserId = SessionHelper::userId();

        // Only non-admin 'User' role should use these operations
        require_once __DIR__ . '/../core/auth.php';
        $u = Auth::user();
        $role = $u['role'] ?? null;
        if ($role !== 'User') {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Access restricted to regular users only'];
            header('Location: ' . (function_exists('base_path') ? base_path('/') : '/'));
            exit;
        }

        // Handle POST actions: create, accept, reject
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? 'create_transfer';
            if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
                $_SESSION['flash'] = ['type'=>'danger','message'=>'Invalid CSRF token'];
                header('Location: ' . (function_exists('base_path') ? base_path('ops/transactions') : '/ops/transactions'));
                exit;
            }

            if ($action === 'create_transfer') {
                $to_user_id = !empty($_POST['to_user_id']) ? (int)$_POST['to_user_id'] : null;
                $transfer_type = $_POST['transfer_type'] ?? 'to_employee';
                $notes = trim($_POST['notes'] ?? '');
                $inventory_ids = $_POST['inventory_ids'] ?? [];
                $bulk_item_id = !empty($_POST['bulk_item_id']) ? (int)$_POST['bulk_item_id'] : null;
                $bulk_quantity = !empty($_POST['bulk_quantity']) ? (int)$_POST['bulk_quantity'] : 0;

                // Server-side ownership validation for selected inventory_ids
                $inventory_ids = array_map('intval', $inventory_ids);
                if (!empty($inventory_ids)) {
                    $placeholders = implode(',', array_fill(0, count($inventory_ids), '?'));
                    $check = $db->prepare("SELECT COUNT(*) as cnt FROM inventory WHERE id IN ($placeholders) AND current_owner_id = ?");
                    $params = $inventory_ids; $params[] = $currentUserId;
                    $check->execute($params);
                    $cnt = (int)$check->fetchColumn();
                    if ($cnt !== count($inventory_ids)) {
                        $_SESSION['flash'] = ['type'=>'danger','message'=>'One or more selected items are not owned by you'];
                        header('Location: ' . (function_exists('base_path') ? base_path('ops/transactions') : '/ops/transactions'));
                        exit;
                    }
                }

                // For bulk transfers, ensure user has enough owned inventory rows for that item
                if ($bulk_item_id && $bulk_quantity > 0) {
                    $c = $db->prepare('SELECT COUNT(*) FROM inventory WHERE item_master_id = :im AND current_owner_id = :uid');
                    $c->execute([':im'=>$bulk_item_id, ':uid'=>$currentUserId]);
                    $have = (int)$c->fetchColumn();
                    if ($have < $bulk_quantity) {
                        $_SESSION['flash'] = ['type'=>'danger','message'=>'You do not have enough quantity of the selected item for bulk transfer'];
                        header('Location: ' . (function_exists('base_path') ? base_path('ops/transactions') : '/ops/transactions'));
                        exit;
                    }
                }

                // Insert transfer
                $tn = 'T' . time() . rand(100,999);
                $stmt = $db->prepare('INSERT INTO transfers (transfer_number, transfer_type, from_user_id, to_user_id, to_role, created_by, created_at, status, notes) VALUES (:tn,:tt,:from,:to,NULL,:cb,NOW(),"pending",:notes)');
                $stmt->execute([':tn'=>$tn, ':tt'=>$transfer_type, ':from'=>$currentUserId, ':to'=>$to_user_id, ':cb'=>$currentUserId, ':notes'=>$notes]);
                $transferId = $db->lastInsertId();

                // Insert transfer_items and mark inventory in-transit for explicit selections
                $ins = $db->prepare('INSERT INTO transfer_items (transfer_id, inventory_id, item_master_id, quantity, status, remarks) VALUES (:tid,:inv,:im,:q,"in-transit",:r)');
                foreach ($inventory_ids as $invId) {
                    $invId = (int)$invId;
                    $row = $db->prepare('SELECT item_master_id FROM inventory WHERE id = :id'); $row->execute([':id'=>$invId]); $r = $row->fetch(PDO::FETCH_ASSOC);
                    $im = $r['item_master_id'] ?? null;
                    $ins->execute([':tid'=>$transferId, ':inv'=>$invId, ':im'=>$im, ':q'=>1, ':r'=>$notes]);
                    $db->prepare('UPDATE inventory SET status = "In-transit" WHERE id = :id')->execute([':id'=>$invId]);
                }

                // Handle bulk: pick N inventory rows owned by user for the item and create transfer_items for each
                if ($bulk_item_id && $bulk_quantity > 0) {
                    $select = $db->prepare('SELECT id FROM inventory WHERE item_master_id = :im AND current_owner_id = :uid LIMIT :lim');
                    // PDO doesn't accept LIMIT param directly for some drivers; bind as int on emulation
                    $select->bindValue(':im', $bulk_item_id, PDO::PARAM_INT);
                    $select->bindValue(':uid', $currentUserId, PDO::PARAM_INT);
                    $select->bindValue(':lim', $bulk_quantity, PDO::PARAM_INT);
                    $select->execute();
                    $rows = $select->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $r) {
                        $iid = (int)$r['id'];
                        $ins->execute([':tid'=>$transferId, ':inv'=>$iid, ':im'=>$bulk_item_id, ':q'=>1, ':r'=>$notes]);
                        $db->prepare('UPDATE inventory SET status = "In-transit" WHERE id = :id')->execute([':id'=>$iid]);
                    }
                }

                // Notify recipient
                if ($to_user_id) {
                    $u = $db->prepare('SELECT email FROM users WHERE id = :id'); $u->execute([':id'=>$to_user_id]); $urow = $u->fetch(PDO::FETCH_ASSOC);
                    if (!empty($urow['email'])) {
                        require_once __DIR__ . '/../core/EmailService.php';
                        $emailer = new EmailService();
                        $subject = 'Transfer: You have items pending acceptance';
                        $data = ['transfer_id'=>$transferId, 'notes'=>$notes, 'from_user_id'=>$currentUserId];
                        $emailer->send($urow['email'], $subject, $emailer->renderTemplate('transaction_recorded', $data));
                    }
                }

                $_SESSION['flash'] = ['type'=>'success','message'=>'Transfer created'];
                header('Location: ' . (function_exists('base_path') ? base_path('ops/transactions') : '/ops/transactions'));
                exit;
            }

            if ($action === 'accept') {
                $transfer_id = !empty($_POST['transfer_id']) ? (int)$_POST['transfer_id'] : 0;
                // ensure current user is recipient
                $s = $db->prepare('SELECT * FROM transfers WHERE id = :id'); $s->execute([':id'=>$transfer_id]); $t = $s->fetch(PDO::FETCH_ASSOC);
                if (!$t) {
                    $_SESSION['flash'] = ['type'=>'danger','message'=>'Transfer not found'];
                    header('Location: ' . (function_exists('base_path') ? base_path('ops/transactions') : '/ops/transactions'));
                    exit;
                }
                if ((int)$t['to_user_id'] !== (int)$currentUserId) {
                    $_SESSION['flash'] = ['type'=>'danger','message'=>'Not authorized to act on this transfer'];
                    header('Location: ' . (function_exists('base_path') ? base_path('ops/transactions') : '/ops/transactions'));
                    exit;
                }

                // update items and inventory
                $items = $db->prepare('SELECT id, inventory_id FROM transfer_items WHERE transfer_id = :tid'); $items->execute([':tid'=>$transfer_id]); $titems = $items->fetchAll(PDO::FETCH_ASSOC);
                foreach ($titems as $ti) {
                    $db->prepare('UPDATE transfer_items SET status = "accepted" WHERE id = :id')->execute([':id'=>$ti['id']]);
                    // transfer ownership
                    if (!empty($ti['inventory_id'])) {
                        $db->prepare('UPDATE inventory SET current_owner_id = :newOwner, status = "With Owner", acknowledged_at = NOW() WHERE id = :id')->execute([':newOwner'=>$currentUserId, ':id'=>$ti['inventory_id']]);
                        // log transaction
                        $db->prepare('INSERT INTO item_transactions (inventory_id, from_user_id, to_user_id, transaction_type, quantity, remarks, transaction_date) VALUES (:inv,:from,:to,:tt,:q,:r,NOW())')
                            ->execute([':inv'=>$ti['inventory_id'], ':from'=>$t['from_user_id'], ':to'=>$currentUserId, ':tt'=>'Transfer', ':q'=>1, ':r'=>null]);
                    }
                }
                $newStatus = 'accepted';
                $db->prepare('UPDATE transfers SET status = :st, actioned_at = NOW(), actioned_by = :ab WHERE id = :id')->execute([':st'=>$newStatus, ':ab'=>$currentUserId, ':id'=>$transfer_id]);

                // notify sender
                $u = $db->prepare('SELECT email FROM users WHERE id = :id'); $u->execute([':id'=>$t['from_user_id']]); $urow = $u->fetch(PDO::FETCH_ASSOC);
                require_once __DIR__ . '/../core/EmailService.php';
                $emailer = new EmailService();
                $subject = 'Transfer ' . strtoupper($newStatus) . ': #' . $t['transfer_number'];
                $bodyData = ['transfer_id'=>$transfer_id, 'status'=>$newStatus];
                if (!empty($urow['email'])) $emailer->send($urow['email'], $subject, $emailer->renderTemplate('transaction_recorded', $bodyData));

                $_SESSION['flash'] = ['type'=>'success','message'=>'Transfer ' . $newStatus];
                header('Location: ' . (function_exists('base_path') ? base_path('ops/transactions') : '/ops/transactions'));
                exit;
            }
        }

        // GET: gather data for the UI
        $owned = $db->prepare("SELECT i.id, i.item_master_id, im.item_code, im.description, i.status, COALESCE(i.quantity,1) AS quantity, i.serial_number, (SELECT transaction_date FROM item_transactions WHERE inventory_id = i.id AND transaction_type = 'Transfer' ORDER BY transaction_date DESC LIMIT 1) AS trans_date, i.acknowledged_at AS accept_date FROM inventory i JOIN item_master im ON im.id = i.item_master_id WHERE i.current_owner_id = :uid ORDER BY i.received_at DESC");
        $owned->execute([':uid'=>$currentUserId]); $ownedRows = $owned->fetchAll(PDO::FETCH_ASSOC);

        $incoming = $db->prepare('SELECT ti.id AS transfer_item_id, ti.transfer_id, ti.inventory_id, ti.item_master_id, ti.quantity AS ti_quantity, ti.status AS ti_status, ti.remarks, i.serial_number, im.item_code, im.description, t.created_at AS trans_date, t.transfer_number, u.name AS from_name FROM transfer_items ti JOIN transfers t ON ti.transfer_id = t.id LEFT JOIN users u ON u.id = t.from_user_id LEFT JOIN inventory i ON i.id = ti.inventory_id LEFT JOIN item_master im ON im.id = ti.item_master_id WHERE t.to_user_id = :uid AND t.status = "pending" ORDER BY t.created_at DESC');
        $incoming->execute([':uid'=>$currentUserId]); $incomingRows = $incoming->fetchAll(PDO::FETCH_ASSOC);

        $outgoing = $db->prepare('SELECT t.*, u.name as to_name FROM transfers t LEFT JOIN users u ON u.id = t.to_user_id WHERE t.from_user_id = :uid AND t.status = "pending" ORDER BY t.created_at DESC');
        $outgoing->execute([':uid'=>$currentUserId]); $outgoingRows = $outgoing->fetchAll(PDO::FETCH_ASSOC);

        $txs = $db->query('SELECT id, inventory_id, transaction_type, quantity, transaction_date FROM item_transactions ORDER BY transaction_date DESC LIMIT 50')->fetchAll();
        $users = $db->query('SELECT id, name FROM users ORDER BY name')->fetchAll();

        return $this->render('ops/transactions', ['title' => 'Item Transactions', 'transactions'=>$txs, 'owned'=>$ownedRows, 'incoming'=>$incomingRows, 'outgoing'=>$outgoingRows, 'users'=>$users]);
    }

    public function generateLost() {
        return $this->render('ops/generate_lost', ['title' => 'Generate Lost Request']);
    }

    public function generateSurrender() {
        return $this->render('ops/generate_surrender', ['title' => 'Generate Surrender Request']);
    }
}

?>