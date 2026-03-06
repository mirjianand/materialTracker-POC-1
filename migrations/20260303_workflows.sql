-- Migration: Add transfers and transfer_items, extend inventory.status

ALTER TABLE `inventory`
  MODIFY COLUMN `status` ENUM('In-QA', 'Accepted', 'Rejected', 'To-Rework', 'Lost', 'Lost-but-found', 'Transferred', 'Surrendered', 'In-transit', 'With Owner') NOT NULL;

-- Create transfers table
CREATE TABLE `transfers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `transfer_number` VARCHAR(100) NOT NULL UNIQUE,
  `transfer_type` ENUM('to_employee','surrender','rework') NOT NULL DEFAULT 'to_employee',
  `from_user_id` INT NULL,
  `to_user_id` INT NULL,
  `to_role` VARCHAR(50) NULL,
  `created_by` INT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending','accepted','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',
  `actioned_at` DATETIME NULL,
  `actioned_by` INT NULL,
  `notes` TEXT,
  PRIMARY KEY (`id`),
  INDEX (`from_user_id`),
  INDEX (`to_user_id`),
  INDEX (`created_by`),
  FOREIGN KEY (`from_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`to_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`actioned_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create transfer_items table
CREATE TABLE `transfer_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `transfer_id` INT NOT NULL,
  `inventory_id` INT NULL,
  `item_master_id` INT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `status` ENUM('in-transit','accepted','rejected') NOT NULL DEFAULT 'in-transit',
  `remarks` TEXT,
  PRIMARY KEY (`id`),
  INDEX (`transfer_id`),
  INDEX (`inventory_id`),
  INDEX (`item_master_id`),
  FOREIGN KEY (`transfer_id`) REFERENCES `transfers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`inventory_id`) REFERENCES `inventory`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`item_master_id`) REFERENCES `item_master`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add helpful index to item_transactions for reporting
ALTER TABLE `item_transactions`
  ADD INDEX `idx_item_transactions_from_to` (`from_user_id`, `to_user_id`, `transaction_date`);

-- Optional: create view for current ownership (one row per inventory)
CREATE OR REPLACE VIEW `v_current_ownership` AS
SELECT i.id AS inventory_id, i.item_master_id, im.item_code, im.description, i.current_owner_id AS owner_user_id, i.status, i.received_at
FROM inventory i
JOIN item_master im ON im.id = i.item_master_id;

-- End migration
