<?php
// scripts/seed_sample_po_items.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/core/db.php';

$db = Database::getInstance()->getConnection();

// create purchase_orders table if missing (basic)
$db->exec("CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT NOT NULL AUTO_INCREMENT,
    po_number VARCHAR(64) NULL,
    pr_number VARCHAR(64) NULL,
    created_by INT NULL,
    created_at DATETIME NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ensure items table exists (same schema as controller)
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

$poNumber = 'PO-' . time();
$prNumber = 'PR-' . rand(1000,9999);

$stmt = $db->prepare('INSERT INTO purchase_orders (po_number, pr_number, created_by, created_at) VALUES (:po, :pr, :cb, NOW())');
$stmt->execute([':po'=>$poNumber, ':pr'=>$prNumber, ':cb'=>null]);
$poId = $db->lastInsertId();

$ins = $db->prepare('INSERT INTO purchase_order_items (po_id, item_code, item_name, item_category, item_type, material_type, quantity, expiry_date, serial_number, item_status) VALUES (:po,:code,:name,:cat,:itype,:mtype,:qty,:exp,:srl,:status)');
for ($i=1;$i<=4;$i++) {
    $code = sprintf('I-%06d', $i);
    $name = 'Sample Item ' . $i;
    $ins->execute([':po'=>$poId, ':code'=>$code, ':name'=>$name, ':cat'=>'General', ':itype'=>'Component', ':mtype'=>'Raw', ':qty'=>10, ':exp'=>null, ':srl'=>null, ':status'=>'In-QA']);
}

echo "Seeded sample PO id={$poId} with 4 items (codes I-000001..I-000004)\n";
