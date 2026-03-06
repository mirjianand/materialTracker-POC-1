-- SQL Dump for mat_tracker
-- Generation Time: 2026-03-01

Use mat_tracker;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+05:30";

--
-- Database: `mat_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `emp_id` VARCHAR(10) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `role` ENUM('LogisticsManager', 'CommodityManager', 'User') NOT NULL,
  `start_date` DATE,
  `end_date` DATE,
  `designation` VARCHAR(255),
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `item_categories`
--
CREATE TABLE `item_categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `item_types`
--
CREATE TABLE `item_types` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `material_types`
--
CREATE TABLE `material_types` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `item_master`
--
CREATE TABLE `item_master` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `item_code` VARCHAR(6) NOT NULL UNIQUE,
  `description` TEXT,
  `category_id` INT,
  `item_type_id` INT,
  `material_type_id` INT,
  `commodity_manager_override_id` INT,
  `quantity_type` ENUM('Number', 'Batch') NOT NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `item_categories`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`item_type_id`) REFERENCES `item_types`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`material_type_id`) REFERENCES `material_types`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`commodity_manager_override_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `commodity_manager_material_types`
--
CREATE TABLE `commodity_manager_material_types` (
  `user_id` INT NOT NULL,
  `material_type_id` INT NOT NULL,
  PRIMARY KEY (`user_id`, `material_type_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`material_type_id`) REFERENCES `material_types`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `purchase_orders`
--
CREATE TABLE `purchase_orders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `po_number` VARCHAR(255) UNIQUE,
  `pr_number` VARCHAR(255) UNIQUE,
  `created_by` INT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `inventory`
--
CREATE TABLE `inventory` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `item_master_id` INT NOT NULL,
  `po_id` INT,
  `serial_number` VARCHAR(255),
  `qa_cert_no` VARCHAR(255),
  `status` ENUM('In-QA', 'Accepted', 'Rejected', 'To-Rework', 'Lost', 'Lost-but-found', 'Transferred', 'Surrendered') NOT NULL,
  `current_owner_id` INT,
  `received_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `acknowledged_at` TIMESTAMP NULL,
  `notes` TEXT,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`item_master_id`) REFERENCES `item_master`(`id`),
  FOREIGN KEY (`po_id`) REFERENCES `purchase_orders`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`current_owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `file_attachments`
--
CREATE TABLE `file_attachments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `uploaded_by` INT,
  `uploaded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `item_transactions`
--
CREATE TABLE `item_transactions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `inventory_id` INT NOT NULL,
  `from_user_id` INT,
  `to_user_id` INT,
  `transaction_type` ENUM('Inward', 'Transfer', 'Rework', 'Surrender', 'Reject', 'Lost', 'Found', 'Transfer-Lost') NOT NULL,
  `quantity` INT NOT NULL,
  `remarks` TEXT,
  `transaction_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `file_attachment_id` INT,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`inventory_id`) REFERENCES `inventory`(`id`),
  FOREIGN KEY (`from_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`to_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`file_attachment_id`) REFERENCES `file_attachments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `email_logs`
--
CREATE TABLE `email_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `recipient_email` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255),
  `body` TEXT,
  `sent_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('sent', 'failed') NOT NULL,
  `error_message` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `approval_requests`
--
CREATE TABLE `approval_requests` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `item_transaction_id` INT NOT NULL,
  `requester_id` INT,
  `approver_id` INT,
  `status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
  `decision` ENUM('WriteOff', 'DeductFromSettlement'),
  `approver_remarks` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `decided_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`item_transaction_id`) REFERENCES `item_transactions`(`id`),
  FOREIGN KEY (`requester_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`approver_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `audit_log`
--
CREATE TABLE `audit_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT,
  `action_type` VARCHAR(255) NOT NULL,
  `target_id` INT,
  `target_type` VARCHAR(255),
  `details` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `auth_settings`
--
CREATE TABLE `auth_settings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL UNIQUE,
  `value` VARCHAR(255),
  `updated_by` INT,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
