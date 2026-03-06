<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/core/db.php';
$db = Database::getInstance()->getConnection();
function exists($db, $name) {
    $stmt = $db->query("SHOW TABLES LIKE '" . addslashes($name) . "'");
    return (bool)$stmt->fetch();
}

echo "transfers: " . (exists($db,'transfers') ? "exists" : "missing") . PHP_EOL;
echo "transfer_items: " . (exists($db,'transfer_items') ? "exists" : "missing") . PHP_EOL;

// inventory status column info
$stmt = $db->query("SHOW COLUMNS FROM inventory LIKE 'status'");
$col = $stmt->fetch(PDO::FETCH_ASSOC);
if ($col) {
    echo "inventory.status: " . ($col['Type'] ?? 'unknown') . PHP_EOL;
} else {
    echo "inventory.status: column missing\n";
}
?>