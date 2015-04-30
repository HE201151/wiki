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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (0,'admin','d078a412258074c64516d41274720c502c66c6bdc7e02b73a95951ed018413e236a48dd69d0aeb624ccba78d76c22708c13d7a47abb786baa3d38461436a10a6','2015-04-29 16:33:50','youri.mout@gmail.com','2015-04-30 16:10:13','admin','2015-04-29 16:34:39','upload/avatar/admin_1426036538530.jpg'),(17,'youri','17f96f0b7bf4369b091e0f9c321b4461967e323e53cc2f23226a05fea54237cda0a36260b505a93666aa3ce5519738e30d1f10c7192135d48e7d5a729e9c5072','2015-04-28 13:35:51','c@c.c','2015-04-30 16:10:03','member','2015-04-30 04:04:24','upload/avatar/17_4bb.png'),(18,'test','d078a412258074c64516d41274720c502c66c6bdc7e02b73a95951ed018413e236a48dd69d0aeb624ccba78d76c22708c13d7a47abb786baa3d38461436a10a6','2015-04-30 04:21:29','test@mail.com','0000-00-00 00:00:00','registered','0000-00-00 00:00:00','upload/avatar/18_mini.jpg'),(19,'pepe','d078a412258074c64516d41274720c502c66c6bdc7e02b73a95951ed018413e236a48dd69d0aeb624ccba78d76c22708c13d7a47abb786baa3d38461436a10a6','2015-04-30 04:28:01','t@m.com','0000-00-00 00:00:00','registered','0000-00-00 00:00:00','upload/avatar/19_10932388_423147147837341_576471532_n.jpg');
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

-- Dump completed on 2015-04-30 17:20:56
