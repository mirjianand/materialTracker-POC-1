<?php
// scripts/migrate_workflows.php
// Run the SQL migration to add transfers and transfer_items.
// WARNING: This will ALTER your database. Backup before running.

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/core/db.php';

$sql = file_get_contents(__DIR__ . '/../migrations/20260303_workflows.sql');
if ($sql === false) {
    echo "Migration SQL not found\n";
    exit(1);
}

echo "This script will execute the migration: migrations/20260303_workflows.sql\n";
echo "Are you sure you want to proceed? Type 'yes' to continue: ";
$handle = fopen('php://stdin','r');
$line = trim(fgets($handle));
if ($line !== 'yes') {
    echo "Aborted.\n";
    exit(0);
}

$db = Database::getInstance()->getConnection();
try {
    // Execute statements sequentially (DDL may commit implicitly)
    $stmts = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($stmts as $s) {
        if ($s === '') continue;
        $db->exec($s);
    }
    echo "Migration applied successfully.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}

?>