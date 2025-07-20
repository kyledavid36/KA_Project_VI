-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: elevator
-- ------------------------------------------------------
-- Server version	8.0.42-0ubuntu0.24.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `CAN_subNetwork`
--

DROP TABLE IF EXISTS `CAN_subNetwork`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `CAN_subNetwork` (
  `CAN_nodeID` int unsigned NOT NULL,
  `CAN_status` tinyint NOT NULL,
  `CAN_currentFloor` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CAN_subNetwork`
--

LOCK TABLES `CAN_subNetwork` WRITE;
/*!40000 ALTER TABLE `CAN_subNetwork` DISABLE KEYS */;
/*!40000 ALTER TABLE `CAN_subNetwork` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elevatorNetwork`
--

DROP TABLE IF EXISTS `elevatorNetwork`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elevatorNetwork` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL DEFAULT (curdate()),
  `time` time NOT NULL DEFAULT (curtime()),
  `nodeID` int unsigned NOT NULL,
  `status` tinyint NOT NULL,
  `currentFloor` tinyint NOT NULL,
  `requestedFloor` tinyint NOT NULL,
  `otherInfo` text,
  `eventType` varchar(50) DEFAULT 'GUI_CALL',
  `processed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elevatorNetwork`
--

LOCK TABLES `elevatorNetwork` WRITE;
/*!40000 ALTER TABLE `elevatorNetwork` DISABLE KEYS */;
INSERT INTO `elevatorNetwork` VALUES (1,'2025-07-18','15:53:40',256,1,2,2,'GUI call (alsaSteamGUI)','FLOOR_REQUEST',0),(2,'2025-07-18','15:53:48',256,1,3,3,'GUI call (alsaSteamGUI)','FLOOR_REQUEST',0),(3,'2025-07-18','15:54:17',256,1,1,1,'GUI call (alsaSteamGUI)','FLOOR_REQUEST',0),(4,'2025-07-18','15:54:40',256,1,2,2,'GUI call (alsaSteamGUI)','FLOOR_REQUEST',0);
/*!40000 ALTER TABLE `elevatorNetwork` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_map`
--

DROP TABLE IF EXISTS `node_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `node_map` (
  `nodeID` int NOT NULL,
  `nodeName` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`nodeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_map`
--

LOCK TABLES `node_map` WRITE;
/*!40000 ALTER TABLE `node_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_flags`
--

DROP TABLE IF EXISTS `system_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_flags` (
  `id` int NOT NULL,
  `sabbath_mode` tinyint(1) DEFAULT '0',
  `lockout_mode` tinyint(1) DEFAULT '0',
  `lastModified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_flags`
--

LOCK TABLES `system_flags` WRITE;
/*!40000 ALTER TABLE `system_flags` DISABLE KEYS */;
INSERT INTO `system_flags` VALUES (1,0,0,'2025-07-18 04:42:10');
/*!40000 ALTER TABLE `system_flags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'alanhpm','alanhpm@gmail.com','alan','$2y$10$qc0oRaWNrp./m7I6esNgM.8ZIWxDK1w8LKTenX3OaE4Df2ERRkRMW');
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

-- Dump completed on 2025-07-20 15:30:39
