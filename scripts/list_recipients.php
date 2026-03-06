<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/core/db.php';
$db = Database::getInstance()->getConnection();
$rows = $db->query('SELECT id, role_name, email FROM notification_recipients ORDER BY role_name, email')->fetchAll();
foreach ($rows as $r) {
    echo "{$r['id']} {$r['role_name']} {$r['email']}\n";
}
?>