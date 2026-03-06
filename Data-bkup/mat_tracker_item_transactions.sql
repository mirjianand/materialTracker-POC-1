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
-- Table structure for table `item_transactions`
--

DROP TABLE IF EXISTS `item_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inventory_id` int NOT NULL,
  `from_user_id` int DEFAULT NULL,
  `to_user_id` int DEFAULT NULL,
  `transaction_type` enum('Inward','Transfer','Rework','Surrender','Reject','Lost','Found','Transfer-Lost') NOT NULL,
  `quantity` int NOT NULL,
  `remarks` text,
  `transaction_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `file_attachment_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_id` (`inventory_id`),
  KEY `to_user_id` (`to_user_id`),
  KEY `file_attachment_id` (`file_attachment_id`),
  KEY `idx_item_transactions_from_to` (`from_user_id`,`to_user_id`,`transaction_date`),
  CONSTRAINT `item_transactions_ibfk_1` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`),
  CONSTRAINT `item_transactions_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `item_transactions_ibfk_3` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `item_transactions_ibfk_4` FOREIGN KEY (`file_attachment_id`) REFERENCES `file_attachments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_transactions`
--

LOCK TABLES `item_transactions` WRITE;
/*!40000 ALTER TABLE `item_transactions` DISABLE KEYS */;
INSERT INTO `item_transactions` VALUES (1,5,NULL,NULL,'Inward',1,'QA accepted','2026-03-03 09:23:24',NULL),(2,101,13,14,'Transfer',1,'E2E accept','2026-03-04 10:40:04',NULL),(5,54,NULL,NULL,'Inward',1,'QA accepted','2026-03-05 13:05:05',NULL),(6,54,10,13,'Transfer',1,'Transfered by Anand for Test','2026-03-05 07:35:00',NULL),(7,107,NULL,NULL,'Inward',5,'QA accepted','2026-03-06 00:57:24',NULL),(8,107,NULL,NULL,'Rework',5,'Sent to vendor','2026-03-06 00:57:24',NULL),(9,107,NULL,NULL,'Surrender',5,'Surrendered','2026-03-06 00:57:24',NULL),(10,106,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:46:58',NULL),(11,104,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:02',NULL),(12,60,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:05',NULL),(13,59,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:07',NULL),(14,61,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:10',NULL),(15,62,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:11',NULL),(16,63,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:13',NULL),(17,64,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:14',NULL),(18,65,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:14',NULL),(19,105,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:16',NULL),(20,55,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:20',NULL),(21,55,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:20',NULL),(22,55,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:21',NULL),(23,55,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:21',NULL),(24,55,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:21',NULL),(25,55,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:21',NULL),(26,56,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:23',NULL),(27,82,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:24',NULL),(28,81,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:29',NULL),(29,81,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:29',NULL),(30,83,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:29',NULL),(31,80,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:30',NULL),(32,84,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:31',NULL),(33,79,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:31',NULL),(34,57,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:32',NULL),(35,58,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:32',NULL),(36,66,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:33',NULL),(37,67,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:34',NULL),(38,68,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:35',NULL),(39,69,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:35',NULL),(40,71,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:37',NULL),(41,72,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:39',NULL),(42,72,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:40',NULL),(43,72,NULL,NULL,'Inward',1,'QA accepted','2026-03-06 01:47:40',NULL);
/*!40000 ALTER TABLE `item_transactions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-06  8:50:25
