<?php
// public/index.php - Front controller (minimal)

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/core/session.php';

// Start session early
SessionHelper::start();

// Basic autoload for controllers and models
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../src/controllers/' . $class . '.php',
        __DIR__ . '/../src/models/' . $class . '.php',
        __DIR__ . '/../src/core/' . $class . '.php',
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Very small router: map / to HomeController@index
// Simple routing with base path detection so app works in subfolders
$rawUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rawUri = str_replace('\\', '/', $rawUri);
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

// Normalize base path (script directory) and strip it from the request URI
$base = rtrim($scriptName, '/');
$uri = $rawUri;
if ($base !== '' && strpos($uri, $base) === 0) {
    $uri = substr($uri, strlen($base));
}

$uri = rtrim($uri, '/');
if ($uri === '') {
    $uri = '/';
}

// Public routes (no auth required)
$publicRoutes = ['/login', '/auth/login'];

// If route is not public, require authentication
if (!in_array($uri, $publicRoutes, true) && !SessionHelper::isAuthenticated()) {
    $loginUrl = function_exists('base_path') ? base_path('login') : '/login';
    header('Location: ' . $loginUrl);
    exit;
}

// Lightweight API endpoints for AJAX (user/vendor search)
if ($uri === '/api/users') {
    require_once __DIR__ . '/../src/core/db.php';
    $q = trim($_GET['q'] ?? '');
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT id, name, email, emp_id FROM users WHERE name LIKE :q OR email LIKE :q OR emp_id LIKE :q LIMIT 50');
    $stmt->execute([':q'=>'%'.$q.'%']);
    header('Content-Type: application/json');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
if ($uri === '/api/vendors') {
    require_once __DIR__ . '/../src/core/db.php';
    $q = trim($_GET['q'] ?? '');
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT id, vendor_name FROM purchase_orders WHERE vendor_name LIKE :q GROUP BY vendor_name LIMIT 50');
    $stmt->execute([':q'=>'%'.$q.'%']);
    header('Content-Type: application/json');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Routes
if ($uri === '/' ) {
    $controller = new HomeController();
    echo $controller->index();
    exit;
}
// Simple route map for scaffolded pages
$routes = [
    '/login' => ['AuthController','loginForm'],
    '/auth/login' => ['AuthController','login'],
    '/logout' => ['AuthController','logout'],

    '/items/create' => ['ItemsController','create'],
    '/items' => ['ItemsController','index'],
    '/items/edit' => ['ItemsController','edit'],
    '/po/entries' => ['POController','entries'],
    '/po/view' => ['POController','detail'],
    '/users' => ['UsersController','index'],
    '/users/edit' => ['UsersController','edit'],
    '/users/update' => ['UsersController','update'],

    '/admin/process/lost' => ['AdminController','processLost'],
    '/admin/process/rework' => ['AdminController','processRework'],
    '/admin/process/surrender' => ['AdminController','processSurrender'],
    // Notification recipients admin
    '/admin/notification_recipients' => ['AdminController','notificationRecipients'],
    '/admin/notification_recipients/add' => ['AdminController','addNotificationRecipient'],
    '/admin/notification_recipients/delete' => ['AdminController','deleteNotificationRecipient'],
    '/admin/inventory' => ['AdminController','inventory'],
    '/admin/inventory/bulk-action' => ['AdminController','bulkAction'],
    '/admin/inventory/transfer-form' => ['AdminController','transferForm'],
    '/admin/inventory/rework-form' => ['AdminController','reworkForm'],
    '/admin/inventory/surrender-form' => ['AdminController','surrenderForm'],
    '/admin/inventory/transfer' => ['AdminController','transferItem'],
    '/admin/inventory/rework' => ['AdminController','reworkItem'],
    '/admin/inventory/surrender' => ['AdminController','surrenderItem'],
    '/qa' => ['QAController','index'],

    '/ops/transactions' => ['OpsController','transactions'],
    '/ops/generate/lost' => ['OpsController','generateLost'],
    '/ops/generate/surrender' => ['OpsController','generateSurrender'],

    '/reports' => ['ReportsController','index'],
    '/feedback' => ['FeedbackController','index'],
    '/feedback/send' => ['FeedbackController','send'],
    '/test/email' => ['TestController','sendEmail'],
    '/forbidden' => ['ErrorController','forbidden'],
];

if (isset($routes[$uri])) {
    $c = $routes[$uri][0];
    $m = $routes[$uri][1];
    $controller = new $c();
    echo $controller->$m();
    exit;
}

// Additional routing can be added here
http_response_code(404);
echo 'Not Found';

?>