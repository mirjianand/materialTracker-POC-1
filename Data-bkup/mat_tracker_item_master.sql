-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: localhost    Database: mat_tracker
-- ------------------------------------------------------
-- Server version	8.0.36

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `item_master`
--

DROP TABLE IF EXISTS `item_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_master` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_code` varchar(64) NOT NULL,
  `description` text,
  `category_id` int DEFAULT NULL,
  `item_type_id` int DEFAULT NULL,
  `material_type_id` int DEFAULT NULL,
  `commodity_manager_override_id` int DEFAULT NULL,
  `quantity_type` enum('Number','Batch') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_code` (`item_code`),
  UNIQUE KEY `item_code_2` (`item_code`),
  KEY `category_id` (`category_id`),
  KEY `item_type_id` (`item_type_id`),
  KEY `material_type_id` (`material_type_id`),
  KEY `commodity_manager_override_id` (`commodity_manager_override_id`),
  CONSTRAINT `item_master_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `item_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `item_master_ibfk_2` FOREIGN KEY (`item_type_id`) REFERENCES `item_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `item_master_ibfk_3` FOREIGN KEY (`material_type_id`) REFERENCES `material_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `item_master_ibfk_4` FOREIGN KEY (`commodity_manager_override_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_master`
--

LOCK TABLES `item_master` WRITE;
/*!40000 ALTER TABLE `item_master` DISABLE KEYS */;
INSERT INTO `item_master` VALUES (1,'ITM001','Demo Widget A',NULL,NULL,NULL,NULL,'Number',1),(2,'ITM002','Demo Widget B',NULL,NULL,NULL,NULL,'Number',1),(3,'I-000005','Item-5',9,9,5,NULL,'Number',1),(4,'I-000001','item-1',5,5,5,NULL,'Number',1),(5,'I-000002','item-2',6,6,6,NULL,'Number',1),(6,'I-000003','item-3',7,7,6,NULL,'Number',1),(7,'I-000004','item-4',8,8,5,NULL,'Number',1),(8,'I-000006','item-6',10,5,5,NULL,'Number',1),(9,'I-000007','item-7',9,9,5,NULL,'Number',1),(10,'I-000008','item-8',8,7,6,NULL,'Number',1),(11,'I-000009','item-9',7,7,6,NULL,'Number',1),(16,'E2E-ITEM-001','E2E test item',NULL,NULL,NULL,NULL,'Number',1),(21,'TW-8368','Test Workflow Item',NULL,NULL,NULL,NULL,'Number',1),(22,'TW-8383','Test Workflow Item',NULL,NULL,NULL,NULL,'Number',1),(23,'TW-8398','Test Workflow Item',NULL,NULL,NULL,NULL,'Number',1),(24,'TW-8469','Test Workflow Item',NULL,NULL,NULL,NULL,'Number',1),(25,'TW-8644','Test Workflow Item',NULL,NULL,NULL,NULL,'Number',1);
/*!40000 ALTER TABLE `item_master` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-06  8:50:30
