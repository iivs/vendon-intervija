-- MySQL dump 10.13  Distrib 5.7.9, for Win32 (AMD64)
--
-- Host: 127.0.0.1    Database: vendon_intervija
-- ------------------------------------------------------
-- Server version    5.6.21

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
-- Table structure for table `answers`
--

DROP TABLE IF EXISTS `answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `answers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) NOT NULL,
  `answer` text CHARACTER SET utf8,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_questions_c1` (`question_id`) USING BTREE,
  CONSTRAINT `fk_questions` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `answers`
--

LOCK TABLES `answers` WRITE;
/*!40000 ALTER TABLE `answers` DISABLE KEYS */;
INSERT INTO `answers` VALUES (1,1,'A deadly virus.',0,1),(2,1,'A plan to depopulate the Earth.',1,2),(3,2,'Yes, it is. And the government is helping to prevent it by making new laws, restictions etc.',0,1),(4,2,'No, the panemic is fake made up by the New World Order, the \"elites\". They do nothing that would reflect a real pandemic. It\'s the biggest hoax the world has ever seen.',1,2),(5,3,'They are 3% accurate.',1,1),(6,3,'They are 35% accurate.',0,2),(7,3,'They are 50% accurate.',0,3),(8,3,'They are 78% accurate.',0,4),(9,3,'They are 99% accurate.',0,5),(10,3,'They are 100% accurate.',0,6),(11,4,'Yes, they protect me from other people that have Covid-19.',0,1),(12,4,'Yes, they protect you from me in case I have the non-symptomatic type of Covid-19.',0,2),(13,4,'No, they restrict oxygen being delivered to cells, lower brain waves which makes people more susceptible to government implemented ideas that eventually can cause destruction of humanity, slows down learning process, causes fungus in mouth and other various skin diseases.',1,3),(14,5,'Yes, it helps stopping the virus spread.',0,1),(15,5,'No, it disconnects people and restricts from changing energy from aura causing negative energies to take over leading to sadness, depression and suicide.',1,2),(16,6,'To keep people in the dark, destroy small and middle business, microchip peopple, keep people in the dosile state, and depopulate the Earth by eliminating 2/3 of the population.',1,1),(17,6,'To keep people safe from virus and help by not spreading it around.',0,2),(18,7,'Yes, because they are approved by FDA.',0,1),(19,7,'No, there are countless cases of deaths caused by vaccines and many more cases of permanent mental and physical damage. They cause tumors, cancer, imptence, fertility issues, blood clots and many more illneses. They completely wipe out dopamine. They intervene with human DNA causing to lower the frequency for human energetic field so that people vibrate in lower frequencies which causes sudded illnesses and death and prevents people from once again reincarnating here on Earth. Futher more, the contents are non-vegan, contain heavy metals, a mutated virus made in laboratory that can indeed spread around and cause various side effects and symptoms to other people who are in close range of a vaccinated person.',1,2),(20,8,'indoors.',1,1),(21,8,'outdoors.',0,2),(22,9,'8-10',0,1),(23,9,'12-16',1,2),(24,9,'20-24',0,3),(25,10,'that the cat is agressive.',0,1),(26,10,'that the cat is friendly.',0,2),(27,10,'that the cat feels threathened.',1,3),(28,11,'He\'s being flayful.',0,1),(29,11,'Better not push it more. It\'s a warning.',1,2),(30,12,'he\'s just rude.',0,1),(31,12,'he\'s feels superior.',0,2),(32,12,'he\'s being friendly.',1,3),(33,13,'it\'s an invitation to belly rub.',0,1),(34,13,'he\'s relaxed and showing his trust to you.',1,2),(35,14,'defensive.',1,1),(36,14,'aggressive.',0,2);
/*!40000 ALTER TABLE `answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progress`
--

DROP TABLE IF EXISTS `progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `progress` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `test_id` bigint(20) NOT NULL,
  `question_id` bigint(20) NOT NULL,
  `answer_id` bigint(20) NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_users_c1` (`user_id`) USING BTREE,
  KEY `fk_tests_c1` (`test_id`) USING BTREE,
  KEY `fK_questions_c1` (`question_id`) USING BTREE,
  KEY `fk_answers_c1` (`answer_id`),
  CONSTRAINT `fK_questions_constraint` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `fk_answers_constraint` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `fk_tests_constraint` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_constraint` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progress`
--

LOCK TABLES `progress` WRITE;
/*!40000 ALTER TABLE `progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `test_id` bigint(20) NOT NULL,
  `question` text COLLATE utf8_bin NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tests_c1` (`test_id`) USING BTREE,
  CONSTRAINT `fk_tests` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES (1,1,'What is Covid-19?',1),(2,1,'Is the Covid-19 pandemic real?',2),(3,1,'How accurate are PCR tests?',3),(4,1,'Are masks healthy?',4),(5,1,'Is social distancing healthy?',5),(6,1,'What are lockdowns for?',6),(7,1,'Are vaccines safe?',7),(8,2,'Cats live longer:',1),(9,2,'How many hours cats sleep in a day?',2),(10,2,'A direct eye contact to a cat to them it could mean:',3),(11,2,'What means when cat wags his tail?',4),(12,2,'When a cat sticks his butt to your face, it means:',5),(13,2,'When a cat exposes his belly, it means:',6),(14,2,'Hissing is:',7),(15,4,'How many known alien races have humans contacted?',1);
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `results`
--

DROP TABLE IF EXISTS `results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `results` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `test_id` bigint(20) NOT NULL,
  `correct` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `fk_users_c1` (`user_id`) USING BTREE,
  KEY `fk_tests_c1` (`test_id`) USING BTREE,
  CONSTRAINT `fk_tests_c2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_c2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `results`
--

LOCK TABLES `results` WRITE;
/*!40000 ALTER TABLE `results` DISABLE KEYS */;
/*!40000 ALTER TABLE `results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tests`
--

DROP TABLE IF EXISTS `tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tests` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`) USING BTREE,
  UNIQUE KEY `id_UNIQUE` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tests`
--

LOCK TABLES `tests` WRITE;
/*!40000 ALTER TABLE `tests` DISABLE KEYS */;
INSERT INTO `tests` VALUES (1,'Covid-19',1),(2,'Some things about cats',2),(3,'Test with no questions',3),(4,'Test with question and no answers',4);
/*!40000 ALTER TABLE `tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `test_id` bigint(20) NOT NULL,
  `sessionid` varchar(255) COLLATE utf8_bin NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `k_sessionid` (`sessionid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
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

-- Dump completed on 2021-08-22 17:41:36
