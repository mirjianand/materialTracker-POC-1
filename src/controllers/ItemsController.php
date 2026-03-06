<?php
// src/controllers/ItemsController.php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../core/csrf.php';

class ItemsController extends BaseController {
    public function create() {
        SessionHelper::start();
        // handle POST -> create item_master
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid CSRF token'];
                header('Location: ' . (function_exists('base_path') ? base_path('items/create') : '/items/create'));
                exit;
            }

            $db = Database::getInstance()->getConnection();
            $item_code = strtoupper(trim($_POST['item_code'] ?? ''));
            $description = trim($_POST['description'] ?? '');
            $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $item_type_id = !empty($_POST['item_type_id']) ? (int)$_POST['item_type_id'] : null;
            $material_type_id = !empty($_POST['material_type_id']) ? (int)$_POST['material_type_id'] : null;
            $quantity_type = $_POST['quantity_type'] ?? 'Number';

            $errors = [];
            if ($item_code === '') $errors[] = 'Item code is required';
            if ($description === '') $errors[] = 'Description is required';

            // Ensure item_code column length is sufficient; alter to VARCHAR(64) in dev when needed
            try {
                $col = $db->prepare("SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'item_master' AND COLUMN_NAME = 'item_code'");
                $col->execute();
                $len = (int)$col->fetchColumn();
                $desired = 64;
                if ($len > 0 && $len < $desired) {
                    $db->exec("ALTER TABLE item_master MODIFY COLUMN item_code VARCHAR(64) NOT NULL UNIQUE");
                }
            } catch (Exception $e) {
                // ignore alter failures in production; will validate length below
            }

            // Enforce max length to avoid SQL truncation errors
            $maxLen = 64;
            if (strlen($item_code) > $maxLen) $errors[] = "Item code too long (max $maxLen characters)";

            // unique item_code
            if ($item_code !== '') {
                $cstm = $db->prepare('SELECT COUNT(*) FROM item_master WHERE item_code = :code');
                $cstm->execute([':code'=>$item_code]);
                if ($cstm->fetchColumn() > 0) $errors[] = 'Item code already exists';
            }

            if (!empty($errors)) {
                $_SESSION['flash'] = ['type'=>'danger','message'=>implode('; ',$errors)];
                header('Location: ' . (function_exists('base_path') ? base_path('items/create') : '/items/create'));
                exit;
            }

            $stmt = $db->prepare('INSERT INTO item_master (item_code, description, category_id, item_type_id, material_type_id, quantity_type, is_active) VALUES (:code, :desc, :cat, :it, :mt, :qt, 1)');
            $stmt->execute([':code'=>$item_code, ':desc'=>$description, ':cat'=>$category_id, ':it'=>$item_type_id, ':mt'=>$material_type_id, ':qt'=>$quantity_type]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Item created successfully'];

            // Send notification email about new item to Commodity Managers
            require_once __DIR__ . '/../core/EmailService.php';
            $emailer = new EmailService();
            // try to fetch friendly names for category/material
            $catName = null; $mtName = null;
            if ($category_id) {
                $s = $db->prepare('SELECT name FROM item_categories WHERE id = :id'); $s->execute([':id'=>$category_id]); $row = $s->fetch(); $catName = $row['name'] ?? null;
            }
            if ($material_type_id) {
                $s = $db->prepare('SELECT name FROM material_types WHERE id = :id'); $s->execute([':id'=>$material_type_id]); $row = $s->fetch(); $mtName = $row['name'] ?? null;
            }
            $subject = 'New Item Created: ' . $item_code;
            $data = ['item_code'=>$item_code, 'description'=>$description, 'category_name'=>$catName, 'material_type_name'=>$mtName];
            $emailer->sendToRole('CommodityManager', $subject, 'item_created', $data);
            header('Location: ' . (function_exists('base_path') ? base_path('items/create') : '/items/create'));
            exit;
        }

        // GET: show form
        // fetch categories/types for selects
        $db = Database::getInstance()->getConnection();
        $cats = $db->query('SELECT id,name FROM item_categories')->fetchAll(PDO::FETCH_ASSOC);
        $types = $db->query('SELECT id,name FROM item_types')->fetchAll(PDO::FETCH_ASSOC);
        $mts = $db->query('SELECT id,name FROM material_types')->fetchAll(PDO::FETCH_ASSOC);
        // fetch recent items to show on the create page (paginated, filterable, sortable)
        $itemPage = max(1, (int)($_GET['item_page'] ?? 1));
        $itemPageSize = max(5, min(100, (int)($_GET['item_page_size'] ?? 25)));
        $itemOffset = ($itemPage - 1) * $itemPageSize;

        $itemQ = trim($_GET['item_q'] ?? '');
        $itemCategory = !empty($_GET['item_category']) ? (int)$_GET['item_category'] : null;
        $itemTypeFilter = !empty($_GET['item_type']) ? (int)$_GET['item_type'] : null;
        $itemMaterialFilter = !empty($_GET['item_material']) ? (int)$_GET['item_material'] : null;
        $itemSort = $_GET['item_sort'] ?? 'item_code';
        $itemOrder = (isset($_GET['item_order']) && strtolower($_GET['item_order']) === 'asc') ? 'ASC' : 'DESC';

        // sanitize sort column allowed list
        $allowedSorts = ['item_code','description','category_name','item_type_name','material_type_name'];
        if (!in_array($itemSort, $allowedSorts, true)) $itemSort = 'item_code';

        $where = [];
        $params = [];
        if ($itemQ !== '') {
            $where[] = "(im.item_code LIKE :iq OR im.description LIKE :iq2)";
            $params[':iq'] = '%' . $itemQ . '%';
            $params[':iq2'] = '%' . $itemQ . '%';
        }
        if ($itemCategory) {
            $where[] = 'im.category_id = :icat';
            $params[':icat'] = $itemCategory;
        }
        if ($itemTypeFilter) {
            $where[] = 'im.item_type_id = :itype';
            $params[':itype'] = $itemTypeFilter;
        }
        if ($itemMaterialFilter) {
            $where[] = 'im.material_type_id = :imaterial';
            $params[':imaterial'] = $itemMaterialFilter;
        }
        $whereSql = '';
        if (!empty($where)) $whereSql = 'WHERE ' . implode(' AND ', $where);

        $totalStmt = $db->prepare('SELECT COUNT(*) FROM item_master im ' . $whereSql);
        $totalStmt->execute($params);
        $itemTotal = (int)$totalStmt->fetchColumn();

        $sql = 'SELECT im.id, im.item_code, im.description, im.quantity_type, c.name AS category_name, it.name AS item_type_name, mt.name AS material_type_name FROM item_master im LEFT JOIN item_categories c ON c.id = im.category_id LEFT JOIN item_types it ON it.id = im.item_type_id LEFT JOIN material_types mt ON mt.id = im.material_type_id ' . $whereSql . " ORDER BY $itemSort $itemOrder LIMIT :lim OFFSET :off";
        $stmt = $db->prepare($sql);
        foreach ($params as $k=>$v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $itemPageSize, PDO::PARAM_INT);
        $stmt->bindValue(':off', $itemOffset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->render('items/create', [
            'title' => 'Item Creation',
            'categories'=>$cats,
            'item_types'=>$types,
            'material_types'=>$mts,
            'items'=>$items,
            'itemTotal'=>$itemTotal,
            'itemPage'=>$itemPage,
            'itemPageSize'=>$itemPageSize,
            'itemQ'=>$itemQ,
            'itemCategory'=>$itemCategory,
            'itemSort'=>$itemSort,
            'itemOrder'=>$itemOrder,
        ]);
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        SessionHelper::start();
        $q = trim($_GET['q'] ?? '');
        $category = !empty($_GET['category']) ? (int)$_GET['category'] : null;
        $itemType = !empty($_GET['item_type']) ? (int)$_GET['item_type'] : null;
        $materialType = !empty($_GET['material_type']) ? (int)$_GET['material_type'] : null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = max(5, min(100, (int)($_GET['page_size'] ?? 25)));
        $offset = ($page-1)*$pageSize;

        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = "(im.item_code LIKE :q OR im.description LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }
        if ($category) { $where[] = 'im.category_id = :cat'; $params[':cat'] = $category; }
        if ($itemType) { $where[] = 'im.item_type_id = :it'; $params[':it'] = $itemType; }
        if ($materialType) { $where[] = 'im.material_type_id = :mt'; $params[':mt'] = $materialType; }
        $whereSql = '';
        if (!empty($where)) $whereSql = 'WHERE ' . implode(' AND ', $where);

        $total = $db->prepare("SELECT COUNT(*) FROM item_master im $whereSql");
        $total->execute($params);
        $totalRows = (int)$total->fetchColumn();

        $sql = "SELECT im.*, c.name AS category_name, it.name AS item_type_name, mt.name AS material_type_name FROM item_master im LEFT JOIN item_categories c ON c.id = im.category_id LEFT JOIN item_types it ON it.id = im.item_type_id LEFT JOIN material_types mt ON mt.id = im.material_type_id $whereSql ORDER BY im.item_code ASC LIMIT :lim OFFSET :off";
        $stmt = $db->prepare($sql);
        foreach ($params as $k=>$v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // fetch categories/types/materials for filters
        $categories = $db->query('SELECT id,name FROM item_categories')->fetchAll(PDO::FETCH_ASSOC);
        $item_types = $db->query('SELECT id,name FROM item_types')->fetchAll(PDO::FETCH_ASSOC);
        $material_types = $db->query('SELECT id,name FROM material_types')->fetchAll(PDO::FETCH_ASSOC);

        return $this->render('items/index', ['title'=>'Items', 'items'=>$rows, 'total'=>$totalRows, 'page'=>$page, 'pageSize'=>$pageSize, 'q'=>$q, 'categories'=>$categories, 'item_types'=>$item_types, 'material_types'=>$material_types]);
    }

    public function edit() {
        $db = Database::getInstance()->getConnection();
        SessionHelper::start();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Invalid item id'];
            header('Location: ' . (function_exists('base_path') ? base_path('items') : '/items'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
                $_SESSION['flash'] = ['type'=>'danger','message'=>'Invalid CSRF token'];
                header('Location: ' . (function_exists('base_path') ? base_path('items/edit?id='.$id) : '/items/edit?id='.$id));
                exit;
            }
            $item_code = strtoupper(trim($_POST['item_code'] ?? ''));
            $description = trim($_POST['description'] ?? '');
            $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $item_type_id = !empty($_POST['item_type_id']) ? (int)$_POST['item_type_id'] : null;
            $material_type_id = !empty($_POST['material_type_id']) ? (int)$_POST['material_type_id'] : null;
            $quantity_type = $_POST['quantity_type'] ?? 'Number';

            $errors = [];
            if ($item_code === '') $errors[] = 'Item code is required';
            if ($description === '') $errors[] = 'Description is required';

            // check uniqueness excluding current id
            $cstm = $db->prepare('SELECT COUNT(*) FROM item_master WHERE item_code = :code AND id != :id');
            $cstm->execute([':code'=>$item_code, ':id'=>$id]);
            if ($cstm->fetchColumn() > 0) $errors[] = 'Item code already exists';

            if (!empty($errors)) {
                $_SESSION['flash'] = ['type'=>'danger','message'=>implode('; ',$errors)];
                header('Location: ' . (function_exists('base_path') ? base_path('items/edit?id='.$id) : '/items/edit?id='.$id));
                exit;
            }

            $stmt = $db->prepare('UPDATE item_master SET item_code = :code, description = :desc, category_id = :cat, item_type_id = :it, material_type_id = :mt, quantity_type = :qt WHERE id = :id');
            $stmt->execute([':code'=>$item_code, ':desc'=>$description, ':cat'=>$category_id, ':it'=>$item_type_id, ':mt'=>$material_type_id, ':qt'=>$quantity_type, ':id'=>$id]);
            $_SESSION['flash'] = ['type'=>'success','message'=>'Item updated'];
            header('Location: ' . (function_exists('base_path') ? base_path('items') : '/items'));
            exit;
        }

        // GET: fetch item and show edit form
        $stmt = $db->prepare('SELECT * FROM item_master WHERE id = :id');
        $stmt->execute([':id'=>$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$item) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Item not found'];
            header('Location: ' . (function_exists('base_path') ? base_path('items') : '/items'));
            exit;
        }

        $cats = $db->query('SELECT id,name FROM item_categories')->fetchAll(PDO::FETCH_ASSOC);
        $types = $db->query('SELECT id,name FROM item_types')->fetchAll(PDO::FETCH_ASSOC);
        $mts = $db->query('SELECT id,name FROM material_types')->fetchAll(PDO::FETCH_ASSOC);
        return $this->render('items/edit', ['title'=>'Edit Item', 'item'=>$item, 'categories'=>$cats, 'item_types'=>$types, 'material_types'=>$mts]);
    }
}

?>