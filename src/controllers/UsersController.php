<?php
// src/controllers/UsersController.php
require_once __DIR__ . '/BaseController.php';

class UsersController extends BaseController {
    public function index() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('Admin');
        require_once __DIR__ . '/../core/db.php';
        require_once __DIR__ . '/../core/csrf.php';

        $db = Database::getInstance()->getConnection();

        // Handle POST -> create new user
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid CSRF token'];
                header('Location: ' . (function_exists('base_path') ? base_path('users') : '/users'));
                exit;
            }

            $emp_id = trim($_POST['emp_id'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = trim($_POST['role'] ?? 'User');
            $designation = trim($_POST['designation'] ?? '');
            $start_date = trim($_POST['start_date'] ?? '');
            $end_date = trim($_POST['end_date'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $password = trim($_POST['password'] ?? '');

            $errors = [];
            if ($emp_id === '') $errors[] = 'Employee ID required';
            if ($name === '') $errors[] = 'Name required';
            if ($email === '') $errors[] = 'Email required';

            // check unique email/emp_id
            $chk = $db->prepare('SELECT COUNT(*) FROM users WHERE email = :email OR emp_id = :emp');
            $chk->execute([':email'=>$email, ':emp'=>$emp_id]);
            if ($chk->fetchColumn() > 0) $errors[] = 'Email or Employee ID already exists';

            if (!empty($errors)) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => implode('; ', $errors)];
                header('Location: ' . (function_exists('base_path') ? base_path('users') : '/users'));
                exit;
            }

            // detect if password_hash column exists
            $hasPass = (bool)$db->query("SHOW COLUMNS FROM users LIKE 'password_hash'")->fetch();
            $pwHash = null;
            if ($password !== '') {
                $pwHash = password_hash($password, PASSWORD_DEFAULT);
            }

            if ($hasPass) {
                $stmt = $db->prepare('INSERT INTO users (emp_id, name, email, role, start_date, end_date, designation, is_active, password_hash) VALUES (:emp, :name, :email, :role, :sd, :ed, :des, :active, :ph)');
                $stmt->execute([':emp'=>$emp_id, ':name'=>$name, ':email'=>$email, ':role'=>$role, ':sd'=>$start_date ?: null, ':ed'=>$end_date ?: null, ':des'=>$designation, ':active'=>$is_active, ':ph'=>$pwHash]);
            } else {
                $stmt = $db->prepare('INSERT INTO users (emp_id, name, email, role, start_date, end_date, designation, is_active) VALUES (:emp, :name, :email, :role, :sd, :ed, :des, :active)');
                $stmt->execute([':emp'=>$emp_id, ':name'=>$name, ':email'=>$email, ':role'=>$role, ':sd'=>$start_date ?: null, ':ed'=>$end_date ?: null, ':des'=>$designation, ':active'=>$is_active]);
            }

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'User created'];
            header('Location: ' . (function_exists('base_path') ? base_path('users') : '/users'));
            exit;
        }

        // GET -> show form + embedded list
        $q = trim($_GET['q'] ?? '');
        $roleFilter = trim($_GET['role'] ?? '');
        $activeFilter = isset($_GET['is_active']) && $_GET['is_active'] !== '' ? (int)$_GET['is_active'] : null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = max(5, min(200, (int)($_GET['page_size'] ?? 25)));
        $offset = ($page - 1) * $pageSize;

        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = '(name LIKE :q OR email LIKE :q2 OR emp_id LIKE :q3)';
            $params[':q'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
            $params[':q3'] = '%' . $q . '%';
        }
        if ($roleFilter !== '') { $where[] = 'role = :role'; $params[':role'] = $roleFilter; }
        if ($activeFilter !== null) { $where[] = 'is_active = :active'; $params[':active'] = $activeFilter; }

        $whereSql = '';
        if (!empty($where)) $whereSql = 'WHERE ' . implode(' AND ', $where);

        $totalStmt = $db->prepare('SELECT COUNT(*) FROM users ' . $whereSql);
        $totalStmt->execute($params);
        $total = (int)$totalStmt->fetchColumn();

        $sql = 'SELECT id, emp_id, name, email, role, designation, start_date, end_date, is_active FROM users ' . $whereSql . ' ORDER BY name ASC LIMIT :lim OFFSET :off';
        $stmt = $db->prepare($sql);
        foreach ($params as $k=>$v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // roles list
        $roles = ['LogisticsManager','CommodityManager','User','Admin'];

        return $this->render('users/index', ['title'=>'User Management', 'users'=>$rows, 'total'=>$total, 'page'=>$page, 'pageSize'=>$pageSize, 'q'=>$q, 'roleFilter'=>$roleFilter, 'activeFilter'=>$activeFilter, 'roles'=>$roles]);
    }

    // Render edit form
    public function edit() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('Admin');
        require_once __DIR__ . '/../core/db.php';
        $db = Database::getInstance()->getConnection();
        $id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
        $user = null;
        if ($id) {
            $stmt = $db->prepare('SELECT id, emp_id, name, email, role, designation, start_date, end_date, is_active FROM users WHERE id = :id');
            $stmt->execute([':id'=>$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        $roles = ['LogisticsManager','CommodityManager','User','Admin'];
        return $this->render('users/edit', ['user'=>$user, 'roles'=>$roles]);
    }

    // Handle update
    public function update() {
        require_once __DIR__ . '/../core/authorize.php';
        Authorize::requireRole('Admin');
        require_once __DIR__ . '/../core/db.php';
        require_once __DIR__ . '/../core/csrf.php';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CSRF::validate($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Invalid request']; header('Location: ' . (function_exists('base_path') ? base_path('users') : '/users')); exit;
        }
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : 0;
        $emp_id = trim($_POST['emp_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'User');
        $designation = trim($_POST['designation'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        if (!$id) { $_SESSION['flash']=['type'=>'danger','message'=>'Invalid id']; header('Location: ' . (function_exists('base_path') ? base_path('users') : '/users')); exit; }
        $db = Database::getInstance()->getConnection();
        // unique checks
        $chk = $db->prepare('SELECT COUNT(*) FROM users WHERE (email = :email OR emp_id = :emp) AND id <> :id');
        $chk->execute([':email'=>$email, ':emp'=>$emp_id, ':id'=>$id]);
        if ($chk->fetchColumn() > 0) { $_SESSION['flash']=['type'=>'danger','message'=>'Email or Employee ID conflicts']; header('Location: ' . (function_exists('base_path') ? base_path('users') : '/users')); exit; }
        $stmt = $db->prepare('UPDATE users SET emp_id = :emp, name = :name, email = :email, role = :role, designation = :des, is_active = :active WHERE id = :id');
        $stmt->execute([':emp'=>$emp_id, ':name'=>$name, ':email'=>$email, ':role'=>$role, ':des'=>$designation, ':active'=>$is_active, ':id'=>$id]);
        $_SESSION['flash']=['type'=>'success','message'=>'User updated']; header('Location: ' . (function_exists('base_path') ? base_path('users') : '/users')); exit;
    }
}

?>