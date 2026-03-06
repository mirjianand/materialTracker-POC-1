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
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `po_id` int NOT NULL,
  `item_code` varchar(64) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `item_category` varchar(255) DEFAULT NULL,
  `item_type` varchar(255) DEFAULT NULL,
  `material_type` varchar(255) DEFAULT NULL,
  `quantity` int DEFAULT '0',
  `expiry_date` date DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `item_status` enum('Accepted','Rejected','In-QA') DEFAULT 'In-QA',
  PRIMARY KEY (`id`),
  KEY `po_id` (`po_id`),
  CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
INSERT INTO `purchase_order_items` VALUES (1,2,'I-000001','Item-name-1','cat-1','type-1','Consum',10,'2026-03-03','AM-PO-1234','In-QA'),(2,2,'I-000002','Item-name2','cat-2','type-2','NonConsum',100,'2026-03-03','AM-PO-12345','In-QA'),(3,2,'I-000003','Item-name3','cat-3','type-3','Consum',15,'2026-03-03','AM-PO-12345','In-QA'),(4,3,'I-000001','Sample Item 1','General','Component','Raw',10,NULL,NULL,'In-QA'),(5,3,'I-000002','Sample Item 2','General','Component','Raw',10,NULL,NULL,'In-QA'),(6,3,'I-000003','Sample Item 3','General','Component','Raw',10,NULL,NULL,'In-QA'),(7,3,'I-000004','Sample Item 4','General','Component','Raw',10,NULL,NULL,'In-QA'),(8,4,'I-000001','Sample Item 1','General','Component','Raw',10,NULL,NULL,'In-QA'),(9,4,'I-000002','Sample Item 2','General','Component','Raw',10,NULL,NULL,'In-QA'),(10,4,'I-000003','Sample Item 3','General','Component','Raw',10,NULL,NULL,'In-QA'),(11,4,'I-000004','Sample Item 4','General','Component','Raw',10,NULL,NULL,'In-QA'),(12,5,'I-000001','Sample Item 1','General','Component','Raw',10,NULL,NULL,'In-QA'),(13,5,'I-000002','Sample Item 2','General','Component','Raw',10,NULL,NULL,'In-QA'),(14,5,'I-000003','Sample Item 3','General','Component','Raw',10,NULL,NULL,'In-QA'),(15,5,'I-000004','Sample Item 4','General','Component','Raw',10,NULL,NULL,'In-QA'),(16,6,'I-000001','Sample Item 1','General','Component','Raw',10,NULL,NULL,'In-QA'),(17,6,'I-000002','Sample Item 2','General','Component','Raw',10,NULL,NULL,'In-QA'),(18,6,'I-000003','Sample Item 3','General','Component','Raw',10,NULL,NULL,'In-QA'),(19,6,'I-000004','Sample Item 4','General','Component','Raw',10,NULL,NULL,'In-QA'),(20,7,'I-000001','Sample Item 1','General','Component','Raw',10,NULL,NULL,'In-QA'),(21,7,'I-000002','Sample Item 2','General','Component','Raw',10,NULL,NULL,'In-QA'),(22,7,'I-000003','Sample Item 3','General','Component','Raw',10,NULL,NULL,'In-QA'),(23,7,'I-000004','Sample Item 4','General','Component','Raw',10,NULL,NULL,'In-QA'),(24,8,'I-000001','Sample Item 1','General','Component','Raw',10,NULL,NULL,'In-QA'),(25,8,'I-000002','Sample Item 2','General','Component','Raw',10,NULL,NULL,'In-QA'),(26,8,'I-000003','Sample Item 3','General','Component','Raw',10,NULL,NULL,'In-QA'),(27,8,'I-000004','Sample Item 4','General','Component','Raw',10,NULL,NULL,'In-QA'),(28,9,'I-000001','Sample Item 1','General','Component','Raw',10,NULL,NULL,'In-QA'),(29,9,'I-000002','Sample Item 2','General','Component','Raw',10,NULL,NULL,'In-QA'),(30,9,'I-000003','Sample Item 3','General','Component','Raw',10,NULL,NULL,'In-QA'),(31,9,'I-000004','Sample Item 4','General','Component','Raw',10,NULL,NULL,'In-QA'),(32,10,'I-000001','Sample Item 1','General','Component','Raw',10,NULL,NULL,'In-QA'),(33,10,'I-000002','Sample Item 2','General','Component','Raw',10,NULL,NULL,'In-QA'),(34,10,'I-000003','Sample Item 3','General','Component','Raw',10,NULL,NULL,'In-QA'),(35,10,'I-000004','Sample Item 4','General','Component','Raw',10,NULL,NULL,'In-QA'),(36,11,'I-000001','Sample Item 1','General','Component','Raw',10,NULL,NULL,'In-QA'),(37,11,'I-000002','Sample Item 2','General','Component','Raw',10,NULL,NULL,'In-QA'),(38,11,'I-000003','Sample Item 3','General','Component','Raw',10,NULL,NULL,'In-QA'),(39,11,'I-000004','Sample Item 4','General','Component','Raw',10,NULL,NULL,'In-QA'),(40,12,'I-000001','Sample Item 1','General','Component','Raw',10,NULL,NULL,'In-QA'),(41,12,'I-000002','Sample Item 2','General','Component','Raw',10,NULL,NULL,'In-QA'),(42,12,'I-000003','Sample Item 3','General','Component','Raw',10,NULL,NULL,'In-QA'),(43,12,'I-000004','Sample Item 4','General','Component','Raw',10,NULL,NULL,'In-QA'),(44,13,'I-000001','Sample Item 1','General','Component','Raw',10,NULL,NULL,'In-QA'),(45,13,'I-000002','Sample Item 2','General','Component','Raw',10,NULL,NULL,'In-QA'),(46,13,'I-000003','Sample Item 3','General','Component','Raw',10,NULL,NULL,'In-QA'),(47,13,'I-000004','Sample Item 4','General','Component','Raw',10,NULL,NULL,'In-QA'),(48,14,'TW-8368','Test Workflow Item','General','Component','Raw',5,NULL,NULL,'In-QA'),(49,16,'TW-8398','Test Workflow Item','General','Component','Raw',5,NULL,NULL,'In-QA'),(50,17,'TW-8469','Test Workflow Item','General','Component','Raw',5,NULL,NULL,'In-QA'),(51,18,'TW-8644','Test Workflow Item','General','Component','Raw',5,NULL,NULL,'In-QA');
/*!40000 ALTER TABLE `purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-06  8:50:27
