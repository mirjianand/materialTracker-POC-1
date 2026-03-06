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
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_master_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `po_id` int DEFAULT NULL,
  `purchase_order_item_id` int DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `qa_cert_no` varchar(255) DEFAULT NULL,
  `status` enum('In-QA','Accepted','Rejected','To-Rework','Lost','Lost-but-found','Transferred','Surrendered','In-transit','With Owner') NOT NULL,
  `current_owner_id` int DEFAULT NULL,
  `received_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `item_master_id` (`item_master_id`),
  KEY `po_id` (`po_id`),
  KEY `current_owner_id` (`current_owner_id`),
  KEY `idx_inventory_poi` (`purchase_order_item_id`),
  CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`item_master_id`) REFERENCES `item_master` (`id`),
  CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_ibfk_3` FOREIGN KEY (`current_owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
INSERT INTO `inventory` VALUES (1,1,1,NULL,NULL,'SN-91C24C',NULL,'Accepted',10,'2026-03-03 07:29:32',NULL,NULL),(2,1,1,NULL,NULL,'SN-562618',NULL,'Accepted',11,'2026-03-03 07:29:32',NULL,NULL),(3,2,1,NULL,NULL,'SN-16DFAE',NULL,'Accepted',10,'2026-03-03 07:29:32',NULL,NULL),(4,2,1,NULL,NULL,'SN-A5D5E9',NULL,'Accepted',11,'2026-03-03 07:29:32',NULL,NULL),(5,1,1,1,NULL,'SN-DR-259552',NULL,'Accepted',NULL,'2026-03-03 09:13:48',NULL,NULL),(6,1,1,1,NULL,'SN-DR-1F7648',NULL,'In-QA',NULL,'2026-03-03 09:13:48',NULL,NULL),(54,4,10,2,1,'AM-PO-1234',NULL,'Transferred',13,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 1'),(55,5,100,2,2,'AM-PO-12345',NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 2'),(56,6,15,2,3,'AM-PO-12345',NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 3'),(57,4,10,3,4,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 4'),(58,5,10,3,5,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 5'),(59,6,10,3,6,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 6'),(60,7,10,3,7,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 7'),(61,4,10,4,8,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 8'),(62,5,10,4,9,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 9'),(63,6,10,4,10,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 10'),(64,7,10,4,11,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 11'),(65,4,10,5,12,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 12'),(66,5,10,5,13,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 13'),(67,6,10,5,14,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 14'),(68,7,10,5,15,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 15'),(69,4,10,6,16,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 16'),(70,5,10,6,17,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 17'),(71,6,10,6,18,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 18'),(72,7,10,6,19,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 19'),(73,4,10,7,20,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 20'),(74,5,10,7,21,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 21'),(75,6,10,7,22,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 22'),(76,7,10,7,23,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 23'),(77,4,10,8,24,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 24'),(78,5,10,8,25,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 25'),(79,6,10,8,26,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 26'),(80,7,10,8,27,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 27'),(81,4,10,9,28,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 28'),(82,5,10,9,29,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 29'),(83,6,10,9,30,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 30'),(84,7,10,9,31,NULL,NULL,'Accepted',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 31'),(85,4,10,10,32,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 32'),(86,5,10,10,33,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 33'),(87,6,10,10,34,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 34'),(88,7,10,10,35,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 35'),(89,4,10,11,36,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 36'),(90,5,10,11,37,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 37'),(91,6,10,11,38,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 38'),(92,7,10,11,39,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 39'),(93,4,10,12,40,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 40'),(94,5,10,12,41,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 41'),(95,6,10,12,42,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 42'),(96,7,10,12,43,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 43'),(97,4,10,13,44,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 44'),(98,5,10,13,45,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 45'),(99,6,10,13,46,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 46'),(100,7,10,13,47,NULL,NULL,'In-QA',10,'2026-03-04 08:27:18',NULL,'Imported from PO_ITEM 47'),(101,16,1,NULL,NULL,'E2E-SRL-1772620804',NULL,'With Owner',14,'2026-03-04 10:40:04','2026-03-04 10:40:04','E2E created'),(104,21,5,14,48,NULL,NULL,'Accepted',10,'2026-03-06 00:52:48',NULL,'Imported from PO_ITEM 48'),(105,23,5,16,49,NULL,NULL,'Accepted',10,'2026-03-06 00:53:18',NULL,'Imported from PO_ITEM 49'),(106,24,5,17,50,NULL,NULL,'Accepted',10,'2026-03-06 00:54:29',NULL,'Imported from PO_ITEM 50'),(107,25,5,18,51,NULL,NULL,'Surrendered',3,'2026-03-06 00:57:24','2026-03-06 00:57:24','Imported from PO_ITEM 51 Sent to vendor for rework Surrendered by LM');
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-06  8:50:26
