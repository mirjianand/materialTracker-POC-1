-- migrations/20260302_roles_permissions.sql
-- Adds a simple permission mapping table to map role names to permission names

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(100) NOT NULL,
  `permission_name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_perm_unique` (`role_name`,`permission_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Example inserts (run once manually to seed)
-- INSERT INTO `role_permissions` (role_name, permission_name) VALUES
-- ('LogisticsManager','items.create'),
-- ('LogisticsManager','items.manage'),
-- ('CommodityManager','approvals.process'),
-- ('User','ops.generate');
