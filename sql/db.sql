-- MySQL dump 10.13  Distrib 5.5.41, for osx10.10 (x86_64)
--
-- Host: localhost    Database: pmm_projet
-- ------------------------------------------------------
-- Server version	5.6.22

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activations`
--

DROP TABLE IF EXISTS `activations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activations` (
  `users_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `activationCode` char(128) NOT NULL,
  PRIMARY KEY (`users_id`),
  CONSTRAINT `activations_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activations`
--

LOCK TABLES `activations` WRITE;
/*!40000 ALTER TABLE `activations` DISABLE KEYS */;
/*!40000 ALTER TABLE `activations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(128) NOT NULL,
  `email` varchar(64) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `date` datetime NOT NULL,
  `message` text,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (4,'Projet PMM - test','youri.mout@gmail.com',0,NULL,'0000-00-00 00:00:00',NULL,0),(5,'Projet PMM - anon test','test@mail.com',NULL,NULL,'0000-00-00 00:00:00',NULL,0),(6,'Projet PMM - youritest','c@c.c',17,NULL,'0000-00-00 00:00:00',NULL,0),(7,'Projet PMM - test','youri.mout@gmail.com',0,NULL,'2015-05-01 16:07:17','testtest',0),(8,'Projet PMM - testest','c@c.c',17,NULL,'2015-05-01 16:24:37',':D:D\r\n',0),(9,'Projet PMM - testest','c@c.c',0,NULL,'2015-05-01 17:15:27','test',0),(10,'Projet PMM - testest','c@c.c',0,NULL,'2015-05-01 17:15:44','retest',0),(11,'Projet PMM - testest','c@c.c',0,8,'2015-05-01 17:17:01','replytest',0),(12,'Projet PMM - testest','c@c.c',0,8,'2015-05-01 17:18:11','yet another answser',0),(13,'Projet PMM - testest','c@c.c',0,8,'2015-05-01 17:22:28','this is getting good',0),(14,'Projet PMM - testest','c@c.c',0,8,'2015-05-01 17:22:33','another one?',0),(15,'Projet PMM - testest','c@c.c',0,8,'2015-05-01 17:24:30','yes',0),(16,'Projet PMM - testest','c@c.c',0,8,'2015-05-01 17:42:36','ok',1),(17,'Projet PMM - testest','c@c.c',0,16,'2015-05-02 10:43:31','hallo',1),(18,'Projet PMM - testest','c@c.c',0,17,'2015-05-02 10:44:40','test',0);
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` char(128) NOT NULL,
  `created` datetime NOT NULL,
  `email` varchar(50) NOT NULL,
  `lastconnect` datetime NOT NULL,
  `status` char(128) NOT NULL,
  `activated` datetime NOT NULL,
  `avatar` char(128) DEFAULT NULL,
  `secret_question` char(128) DEFAULT NULL,
  `secret_question_answer` char(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (0,'admin','d078a412258074c64516d41274720c502c66c6bdc7e02b73a95951ed018413e236a48dd69d0aeb624ccba78d76c22708c13d7a47abb786baa3d38461436a10a6','2015-04-29 16:33:50','test@mail.comc','2015-05-03 23:43:40','admin','2015-04-29 16:34:39','upload/avatar/admin_1426036538530.jpg',NULL,NULL),(17,'youri','17f96f0b7bf4369b091e0f9c321b4461967e323e53cc2f23226a05fea54237cda0a36260b505a93666aa3ce5519738e30d1f10c7192135d48e7d5a729e9c5072','2015-04-28 13:35:51','c@c.c','2015-05-01 16:24:29','member','2015-04-30 04:04:24','upload/avatar/17_4bb.png',NULL,NULL),(18,'test','05a1db76c6f36dd66b61967b67fdfdae8b6050f42908c1b40836d5235dae621ef147e30dadb2a63354ca9d3a15495dfe95048e3cf3694716f6682638298ae467','2015-04-30 04:21:29','youri.mout@gmail.com','2015-05-03 19:32:13','member','2015-05-02 13:28:55','upload/avatar/18_mini.jpg','test','ee26b0dd4af7e749aa1a8ee3c10ae9923f618980772e473f8819a5d4940e0db27ac185f8a0e1d5f84f88bc887fd67b143732c304cc5fa9ad8e6f57f50028a8ff'),(19,'pepe','d078a412258074c64516d41274720c502c66c6bdc7e02b73a95951ed018413e236a48dd69d0aeb624ccba78d76c22708c13d7a47abb786baa3d38461436a10a6','2015-04-30 04:28:01','t@m.com','0000-00-00 00:00:00','registered','2015-05-02 12:11:45','upload/avatar/19_10932388_423147147837341_576471532_n.jpg','test','ee26b0dd4af7e749aa1a8ee3c10ae9923f618980772e473f8819a5d4940e0db27ac185f8a0e1d5f84f88bc887fd67b143732c304cc5fa9ad8e6f57f50028a8ff'),(20,'dwarf','d078a412258074c64516d41274720c502c66c6bdc7e02b73a95951ed018413e236a48dd69d0aeb624ccba78d76c22708c13d7a47abb786baa3d38461436a10a6','2015-05-02 11:32:42','yrmt@edgebsd.org','0000-00-00 00:00:00','registered','2015-05-02 12:08:24',NULL,'test','ee26b0dd4af7e749aa1a8ee3c10ae9923f618980772e473f8819a5d4940e0db27ac185f8a0e1d5f84f88bc887fd67b143732c304cc5fa9ad8e6f57f50028a8ff'),(21,'re','d078a412258074c64516d41274720c502c66c6bdc7e02b73a95951ed018413e236a48dd69d0aeb624ccba78d76c22708c13d7a47abb786baa3d38461436a10a6','2015-05-02 12:06:52','test@mail.comm','0000-00-00 00:00:00','registered','2015-05-02 12:07:11',NULL,NULL,NULL);
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

-- Dump completed on 2015-05-03 23:53:57
