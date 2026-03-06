<?php
// src/controllers/POController.php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../core/session.php';

class POController extends BaseController {
    public function entries() {
        $db = Database::getInstance()->getConnection();
        SessionHelper::start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
                $_SESSION['flash'] = ['type'=>'danger','message'=>'Invalid CSRF token'];
                header('Location: ' . (function_exists('base_path') ? base_path('po/entries') : '/po/entries'));
                exit;
            }

            $po_number = trim($_POST['po_number'] ?? '');
            $pr_number = trim($_POST['pr_number'] ?? '');
            $po_type = trim($_POST['po_type'] ?? '');
            $vendor_name = trim($_POST['vendor_name'] ?? '');
            $vendor_invoice = trim($_POST['vendor_invoice'] ?? '');
            $grn_number = trim($_POST['grn_number'] ?? '');
            $grn_date = trim($_POST['grn_date'] ?? null);
            $created_by = SessionHelper::userId() ?: null;

            // Ensure purchase_order_items table exists
            $db->exec("CREATE TABLE IF NOT EXISTS purchase_order_items (
                id INT NOT NULL AUTO_INCREMENT,
                po_id INT NOT NULL,
                item_code VARCHAR(64),
                item_name VARCHAR(255),
                item_category VARCHAR(255),
                item_type VARCHAR(255),
                material_type VARCHAR(255),
                quantity INT DEFAULT 0,
                expiry_date DATE NULL,
                serial_number VARCHAR(255) NULL,
                item_status ENUM('Accepted','Rejected','In-QA') DEFAULT 'In-QA',
                PRIMARY KEY (id),
                FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Server-side validation: basic checks and transaction
            $errors = [];
            if ($po_number === '') {
                $errors[] = 'PO number is required';
            }

            $items = $_POST['item_code'] ?? [];
            if (!is_array($items)) $items = [];
            $itemCount = count($items);
            $maxItems = 200; // arbitrary safety limit for now
            if ($itemCount > $maxItems) {
                $errors[] = "Too many items (maximum is $maxItems). Please use CSV upload.";
            }

            $validStatuses = ['Accepted','Rejected','In-QA'];
            // Validate each item
            for ($i = 0; $i < $itemCount; $i++) {
                $code = trim($items[$i] ?? '');
                $name = trim($_POST['item_name'][$i] ?? '');
                $qty = $_POST['item_qty'][$i] ?? null;
                if ($code === '' && $name === '') continue; // skip empty rows
                if ($code === '') $errors[] = "Item row " . ($i+1) . ": item code is required";
                if ($name === '') $errors[] = "Item row " . ($i+1) . ": item name is required";
                if (!is_numeric($qty) || (int)$qty < 0) $errors[] = "Item row " . ($i+1) . ": qty must be a non-negative number";
                if (strlen($name) > 255) $errors[] = "Item row " . ($i+1) . ": item name too long";
                $status = trim($_POST['item_status'][$i] ?? 'In-QA');
                if (!in_array($status, $validStatuses, true)) $errors[] = "Item row " . ($i+1) . ": invalid status";
            }

            if (!empty($errors)) {
                $_SESSION['flash'] = ['type'=>'danger','message'=>implode('; ', array_slice($errors,0,5))];
                header('Location: ' . (function_exists('base_path') ? base_path('po/entries') : '/po/entries'));
                if (PHP_SAPI !== 'cli') exit;
                return;
            }

            // Insert PO and items inside transaction
            try {
                $db->beginTransaction();
                // Insert PO with extended fields (columns may or may not exist in older schema);
                // Use INSERT that ignores unknown columns by explicitly selecting ones we expect to exist.
                $insertSql = 'INSERT INTO purchase_orders (po_number, pr_number, created_by, created_at) VALUES (:po, :pr, :cb, NOW())';
                $stmt = $db->prepare($insertSql);
                $stmt->execute([':po'=>$po_number, ':pr'=>$pr_number, ':cb'=>$created_by]);
                $poId = $db->lastInsertId();

                // Now try to update extended fields if columns exist
                $cols = $db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_orders'")->fetchAll(PDO::FETCH_COLUMN);
                $updateParts = [];
                $params = [':id'=>$poId];
                if (in_array('po_type',$cols, true)) { $updateParts[] = 'po_type = :pt'; $params[':pt']=$po_type; }
                if (in_array('vendor_name',$cols, true)) { $updateParts[] = 'vendor_name = :vn'; $params[':vn']=$vendor_name; }
                if (in_array('vendor_invoice',$cols, true)) { $updateParts[] = 'vendor_invoice = :vi'; $params[':vi']=$vendor_invoice; }
                if (in_array('grn_number',$cols, true)) { $updateParts[] = 'grn_number = :grn'; $params[':grn']=$grn_number; }
                if (in_array('grn_date',$cols, true)) { $updateParts[] = 'grn_date = :grnd'; $params[':grnd']=$grn_date; }
                if (!empty($updateParts)) {
                    $db->prepare('UPDATE purchase_orders SET ' . implode(', ', $updateParts) . ' WHERE id = :id')->execute($params);
                }

                if ($itemCount > 0) {
                    $ins = $db->prepare('INSERT INTO purchase_order_items (po_id, item_code, item_name, item_category, item_type, material_type, quantity, expiry_date, serial_number, item_status) VALUES (:po,:code,:name,:cat,:itype,:mtype,:qty,:exp,:srl,:status)');
                    for ($i=0;$i<$itemCount;$i++) {
                        $code = trim($items[$i] ?? '');
                        $name = trim($_POST['item_name'][$i] ?? '');
                        if ($code === '' && $name === '') continue;
                        $cat = trim($_POST['item_category'][$i] ?? '');
                        $itype = trim($_POST['item_type'][$i] ?? '');
                        $mtype = trim($_POST['material_type'][$i] ?? '');
                        $qty = (int)($_POST['item_qty'][$i] ?? 0);
                        $exp = trim($_POST['item_expiry_date'][$i] ?? null) ?: null;
                        $srl = trim($_POST['item_srl_no'][$i] ?? null) ?: null;
                        $status = trim($_POST['item_status'][$i] ?? 'In-QA');
                        $ins->execute([':po'=>$poId, ':code'=>$code, ':name'=>$name, ':cat'=>$cat, ':itype'=>$itype, ':mtype'=>$mtype, ':qty'=>$qty, ':exp'=>$exp, ':srl'=>$srl, ':status'=>$status]);
                        // create corresponding inventory row (idempotent) so PO items appear in QA and Inventory
                        $poiId = (int)$db->lastInsertId();
                        if ($poiId > 0) {
                            // check if inventory already linked
                            $chk = $db->prepare('SELECT id FROM inventory WHERE purchase_order_item_id = :poi LIMIT 1');
                            $chk->execute([':poi'=>$poiId]);
                            if (!$chk->fetch()) {
                                // find a LogisticsManager user id to be current_owner_id
                                $lm = $db->query("SELECT id FROM users WHERE role = 'LogisticsManager' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                                $lmId = $lm['id'] ?? null;
                                // find or create item_master for this code
                                $item_master_id = null;
                                if ($code !== '') {
                                    $s = $db->prepare('SELECT id FROM item_master WHERE item_code = :code LIMIT 1');
                                    $s->execute([':code'=>$code]);
                                    $r2 = $s->fetch(PDO::FETCH_ASSOC);
                                    if ($r2) $item_master_id = $r2['id'];
                                    else {
                                        $db->prepare('INSERT INTO item_master (item_code, description, quantity_type, is_active) VALUES (:code, :desc, :qt, 1)')
                                            ->execute([':code'=>$code, ':desc'=>$name ?: null, ':qt'=>'Number']);
                                        $item_master_id = $db->lastInsertId();
                                    }
                                }
                                $stmtInv = $db->prepare('INSERT INTO inventory (item_master_id, po_id, serial_number, qa_cert_no, status, current_owner_id, received_at, notes, purchase_order_item_id, quantity) VALUES (:im, :po, :srl, NULL, :st, :owner, NOW(), :notes, :poi, :qty)');
                                $stmtInv->execute([':im'=>$item_master_id, ':po'=>$poId, ':srl'=>$srl, ':st'=>'In-QA', ':owner'=>$lmId, ':notes'=>"Imported from PO_ITEM {$poiId}", ':poi'=>$poiId, ':qty'=>$qty]);
                            }
                        }
                    }
                }

                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                $_SESSION['flash'] = ['type'=>'danger','message'=>'Failed to create PO: ' . $e->getMessage()];
                header('Location: ' . (function_exists('base_path') ? base_path('po/entries') : '/po/entries'));
                if (PHP_SAPI !== 'cli') exit;
                return;
            }

            // notify Logistics managers
            require_once __DIR__ . '/../core/EmailService.php';
            $emailer = new EmailService();
            $subject = 'New Purchase Order: ' . $po_number;
            $data = ['po_number'=>$po_number, 'pr_number'=>$pr_number, 'created_by'=>$created_by];
            $emailer->sendToRole('LogisticsManager', $subject, 'po_created', $data);

            $_SESSION['flash'] = ['type'=>'success','message'=>'PO created'];
            header('Location: ' . (function_exists('base_path') ? base_path('po/entries') : '/po/entries'));
            if (PHP_SAPI !== 'cli') exit;
            return;
        }

        // Pagination and sorting for recent POs
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = 25;
        $offset = ($page - 1) * $pageSize;
        $sort = in_array($_GET['sort'] ?? '', ['po_number','pr_number','created_at','vendor_name','po_type'], true) ? $_GET['sort'] : 'created_at';
        $order = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'ASC' : 'DESC';

        $totalRow = $db->query('SELECT COUNT(*) FROM purchase_orders')->fetchColumn();
        // Build select list dynamically to avoid referencing non-existent columns
        $cols = $db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_orders'")->fetchAll(PDO::FETCH_COLUMN);
        $selectFields = ["id", "po_number AS `PO#`", "pr_number AS `PR#`", "created_by", "created_at"];
        if (in_array('po_type', $cols, true)) $selectFields[] = 'po_type';
        if (in_array('vendor_name', $cols, true)) $selectFields[] = 'vendor_name';
        if (in_array('vendor_invoice', $cols, true)) $selectFields[] = 'vendor_invoice';
        if (in_array('grn_number', $cols, true)) $selectFields[] = 'grn_number';
        if (in_array('grn_date', $cols, true)) $selectFields[] = 'grn_date';

        $fieldsSql = implode(', ', $selectFields);
        $sql = "SELECT $fieldsSql FROM purchase_orders ORDER BY $sort $order LIMIT :lim OFFSET :off";
        $pos = $db->prepare($sql);
        $pos->bindValue(':lim', $pageSize, PDO::PARAM_INT);
        $pos->bindValue(':off', $offset, PDO::PARAM_INT);
        $pos->execute();
        $rows = $pos->fetchAll(PDO::FETCH_ASSOC);

        return $this->render('po/entries', ['title' => 'PO Entries', 'purchase_orders'=>$rows, 'total'=>$totalRow, 'page'=>$page, 'pageSize'=>$pageSize, 'sort'=>$sort, 'order'=>$order]);
    }

    public function detail() {
        $db = Database::getInstance()->getConnection();
        SessionHelper::start();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo 'Invalid PO id';
            return;
        }

        $po = $db->prepare('SELECT * FROM purchase_orders WHERE id = :id');
        $po->execute([':id'=>$id]);
        $poData = $po->fetch(PDO::FETCH_ASSOC);
        if (!$poData) {
            http_response_code(404);
            echo 'PO not found';
            return;
        }

        // Ensure purchase_order_items table exists (avoid fatal when older schema lacks it)
        $db->exec("CREATE TABLE IF NOT EXISTS purchase_order_items (
            id INT NOT NULL AUTO_INCREMENT,
            po_id INT NOT NULL,
            item_code VARCHAR(64),
            item_name VARCHAR(255),
            item_category VARCHAR(255),
            item_type VARCHAR(255),
            material_type VARCHAR(255),
            quantity INT DEFAULT 0,
            expiry_date DATE NULL,
            serial_number VARCHAR(255) NULL,
            item_status ENUM('Accepted','Rejected','In-QA') DEFAULT 'In-QA',
            PRIMARY KEY (id),
            FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Server-side paging for items
        $itemPage = max(1, (int)($_GET['item_page'] ?? 1));
        $itemPageSize = max(5, min(200, (int)($_GET['item_page_size'] ?? 25)));
        $itemOffset = ($itemPage - 1) * $itemPageSize;

        $countStmt = $db->prepare('SELECT COUNT(*) FROM purchase_order_items WHERE po_id = :id');
        $countStmt->execute([':id'=>$id]);
        $itemTotal = (int)$countStmt->fetchColumn();

        $itemsStmt = $db->prepare('SELECT * FROM purchase_order_items WHERE po_id = :id ORDER BY id ASC LIMIT :lim OFFSET :off');
        $itemsStmt->bindValue(':id', $id, PDO::PARAM_INT);
        $itemsStmt->bindValue(':lim', $itemPageSize, PDO::PARAM_INT);
        $itemsStmt->bindValue(':off', $itemOffset, PDO::PARAM_INT);
        $itemsStmt->execute();
        $itemRows = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->render('po/detail', [
            'title'=>'PO Detail',
            'po'=>$poData,
            'items'=>$itemRows,
            'itemTotal'=>$itemTotal,
            'itemPage'=>$itemPage,
            'itemPageSize'=>$itemPageSize,
        ]);
    }
}

?>