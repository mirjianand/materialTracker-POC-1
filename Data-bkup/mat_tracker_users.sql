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
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('LogisticsManager','CommodityManager','User','Admin') NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `emp_id` (`emp_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'AA-0001','Test Logistics','test_logistics@local','$2y$10$RT1oVt1wY/1Tz4lwsaHD1.eIgPsrJ.IPn/Ufm0dW8yqmvAd1KXgve','Admin','2026-03-03',NULL,NULL,1),(2,'AA-0002','Test Commodity','test_commodity@local','$2y$10$agt40OD5MorkPhhNl5TZI.Za0ZcvS5n.YrvvTPX50y9gjUQcbhhMy','CommodityManager','2026-03-03',NULL,NULL,1),(3,'YY-0001','Test User','test_user@local','$2y$10$K2cwOuqw.WiiTtWv2yWRauVAk0pqoclPcM3oSS6zWKN4MwSa1nMrS','User','2026-03-03',NULL,NULL,1),(10,'YY-0002','Demo Admin','demo@local',NULL,'LogisticsManager','2026-03-03',NULL,NULL,1),(11,'AA-0003','Demo User','demo_user@example.com',NULL,'User','2026-03-03',NULL,NULL,1),(12,'AA-0004','Logistics','logistics@example.com',NULL,'LogisticsManager','2026-03-03',NULL,NULL,1),(13,'E2E6319','E2E Sender','e2e.sender@example.local',NULL,'User',NULL,NULL,NULL,1),(14,'E2E2623','E2E Receiver','e2e.receiver@example.local',NULL,'User',NULL,NULL,NULL,1),(19,'0','LM Tester','lmtester@example.test','$2y$10$CkgSgqfTUQdt6pjdNJOhSefVnuey6nsY/HFBNtV3BIPvG.SbG367y','LogisticsManager',NULL,NULL,NULL,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-06  8:50:28
