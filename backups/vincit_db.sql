-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: vincit_db
-- ------------------------------------------------------
-- Server version	8.4.3

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
-- Current Database: `vincit_db`
--

/*!40000 DROP DATABASE IF EXISTS `vincit_db`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `vincit_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `vincit_db`;

--
-- Table structure for table `admin_notifications`
--

DROP TABLE IF EXISTS `admin_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admin_notifications_read` (`is_read`),
  KEY `idx_admin_notifications_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_notifications`
--

LOCK TABLES `admin_notifications` WRITE;
/*!40000 ALTER TABLE `admin_notifications` DISABLE KEYS */;
INSERT INTO `admin_notifications` VALUES (1,'Nova encomenda','Pedido VNC-20260403-A0A34F — aguarda pagamento.','http://localhost/vincit/site/admin/encomendas/1',0,'2026-04-03 15:24:10'),(2,'Nova encomenda','Pedido VNC-20260404-F530BC — aguarda pagamento.','http://localhost/vincit/site/admin/encomendas/editar/2',0,'2026-04-04 22:34:23'),(3,'Nova encomenda','Pedido VNC-20260404-302FA0 — aguarda pagamento.','http://localhost/vincit/site/admin/encomendas/editar/3',0,'2026-04-04 22:46:43'),(4,'Nova encomenda','Pedido VNC-20260405-80E76B — aguarda pagamento.','http://localhost/vincit/site/admin/encomendas/editar/4',0,'2026-04-05 12:38:32'),(5,'Nova encomenda','Pedido VNC-20260411-B3DADE — aguarda pagamento.','http://localhost/vincit/site/admin/encomendas/5',0,'2026-04-11 16:43:55'),(6,'Encomenda paga','Pagamento confirmado para VNC-20260411-B3DADE.','http://localhost/vincit/site/admin/encomendas/5',0,'2026-04-11 16:45:02'),(7,'Nova encomenda','Pedido VNC-20260810-C8F213 — aguarda pagamento.','http://localhost/vincit/site/admin/encomendas/editar/6',0,'2026-08-10 09:26:36'),(8,'Encomenda paga','Pagamento confirmado para VNC-20260810-C8F213.','http://localhost/vincit/site/admin/encomendas/editar/6',0,'2026-08-10 09:26:59'),(9,'Nova encomenda','Pedido VNC-20260810-12B3B5 — aguarda pagamento.','http://localhost/vincit/site/admin/encomendas/editar/7',0,'2026-08-10 09:35:13'),(10,'Nova encomenda','Pedido VNC-20260817-F63CC2 — aguarda pagamento.','http://localhost/vincit/site/admin/encomendas/editar/8',0,'2026-08-17 09:22:07'),(11,'Encomenda paga','Pagamento confirmado para VNC-20260817-F63CC2.','http://localhost/vincit/site/admin/encomendas/editar/8',0,'2026-08-17 09:22:27');
/*!40000 ALTER TABLE `admin_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `session_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` int NOT NULL,
  `product_size_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_session` (`session_id`),
  KEY `idx_product` (`product_id`),
  KEY `fk_cart_product_size` (`product_size_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_product_size` FOREIGN KEY (`product_size_id`) REFERENCES `product_sizes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
INSERT INTO `cart` VALUES (11,3,'v5okh6e4arnp19mes8tgjhevjh',42,32,2,'2026-04-11 16:46:19','2026-04-11 16:46:21'),(14,NULL,'6qt3naqnft4e5ulgc43p6glhal',48,70,1,'2026-08-17 09:21:14','2026-08-17 09:21:14');
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_parent` (`parent_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Homem','homem','Vestu?rio masculino','69cd9bde4859e_f0a303dadacb992b9e574f537ae0d6b5.jpg',NULL,'active','2026-04-01 22:56:55','2026-04-01 23:27:42'),(2,'Mulher','mulher','Vestu?rio feminino','69cd9c05a9a47_5f9b4c3f43a2e2449c0923ff1ec8d976.jpg',NULL,'active','2026-04-01 22:56:55','2026-04-01 23:28:21'),(3,'Camisolas','camisolas','Camisolas e t-shirts','69cd9bc5ac7a4_25ee62d0ce01c72a7dc8a5038b5b84ed.jpg',NULL,'active','2026-04-01 22:56:55','2026-04-01 23:27:17'),(4,'Calças','cal-as','Calças e jeans','69cd9ba8b7926_e7a6fbdc8ba9928c5115474b6791705a.jpg',NULL,'active','2026-04-01 22:56:55','2026-04-01 23:26:48'),(5,'Sapatos','sapatos','Cal?ado','69cd9c26488fb_9f6a2a60ffa5917db5563e1374d4ca45.jpg',NULL,'active','2026-04-01 22:56:55','2026-04-01 23:28:54'),(6,'Acessorios','acessorios','Acessorios de moda','69cd9b761af5c_49348b47bc3369398ac9faf6768a082e.jpg',NULL,'active','2026-04-01 22:56:55','2026-04-01 23:25:58');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorites` (
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`product_id`),
  KEY `idx_favorites_product` (`product_id`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_size_id` int DEFAULT NULL,
  `product_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_label` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_product` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,4,35,1,'Ganga mom','M',2,76.00,152.00,'2026-04-05 12:38:32'),(2,5,41,31,'Tote canvas \"Press Kit\"','Único (42 × 38 cm)',1,26.90,26.90,'2026-04-11 16:43:55'),(5,8,36,3,'Casaco \"Prime\"','S',1,149.90,149.90,'2026-08-17 09:22:07'),(6,8,47,66,'Camisola Vincit \"Porto\"','S',1,29.90,29.90,'2026-08-17 09:22:07');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `cart_session_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_cost` decimal(10,2) DEFAULT '0.00',
  `tax` decimal(10,2) DEFAULT '0.00',
  `status` enum('pending','processing','shipped','delivered','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_payment_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_checkout_session_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `shipping_city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_postal_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tracking_code` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_user` (`user_id`),
  KEY `idx_order_number` (`order_number`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_status` (`payment_status`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,2,NULL,'VNC-20260403-A0A34F',304.00,304.00,0.00,0.00,'shipped','paid','stripe','sim_69cfcd8a0b3f2',NULL,'Admin','jpopereira9@gmail.com','+351964895643','rua pero de alenquer fanzeres','baguim','4435-776','Portugal','aa',NULL,'2026-04-03 15:24:10','2026-04-04 22:02:29'),(2,2,NULL,'VNC-20260404-F530BC',76.00,76.00,0.00,0.00,'processing','paid','stripe','sim_69d183df62fb0',NULL,'Admin','jpopereira9@gmail.com','+351964895643','Rua Pêro de Alenquer','Gondomar','4435-716','Portugal','',NULL,'2026-04-04 22:34:23','2026-04-04 22:34:23'),(3,2,NULL,'VNC-20260404-302FA0',152.00,152.00,0.00,0.00,'pending','pending','stripe',NULL,'cs_test_a1tzOvpDEi2iI4SKxhoQHV2RO9RtlT7OQEXru9VNonrs178chBp3vSUPai','Admin','jpopereira9@gmail.com','+351964895643','Rua da Quinta dos Freixos','Sobral de Monte Agraço','2590-425','Portugal','papa',NULL,'2026-04-04 22:46:43','2026-04-04 22:46:44'),(4,2,NULL,'VNC-20260405-80E76B',152.00,152.00,0.00,0.00,'processing','paid','stripe','pi_3TIp1SCFZGHrwPbx12bGDzwe','cs_test_a1hBMcMKqCtk6JCEvNQeuMOioq1cvITKE4AzuxtH4NQpz3BQ0eX7HKxlK8','Admin','jpopereira9@gmail.com','+351964895643','Rua Pêro de Alenquer n81','Gondomar','4435-716','Portugal','',NULL,'2026-04-05 12:38:32','2026-04-05 12:39:24'),(5,3,NULL,'VNC-20260411-B3DADE',32.90,26.90,6.00,0.00,'delivered','paid','stripe','pi_3TL3iTCFZGHrwPbx1ChETV7x','cs_test_b14orndgS7x2KzMgub4uXsorJDzhwflG9hsfizLSHZGO4SA0r2d29aUiuV','joao Pereira','jpopereira10@gmail.com','+351964895643','Rua Pero de Alenquer','Loures','2660-376','Portugal','',NULL,'2026-04-11 16:43:55','2026-04-11 17:03:59'),(8,4,NULL,'VNC-20260817-F63CC2',179.80,179.80,0.00,0.00,'processing','paid','stripe','pi_3U5MkFCFZGHrwPbx1a9xDT6A','cs_test_b1OvyJk1kJdT1Cz1nGOXl92muPVIskX5DzxYhrgMAHB3f0zELK2pH3nvGi','Admin','admin@vincit.pt','+351964895643','Rua do Atum','Armação de Pêra','8635','Portugal','',NULL,'2026-08-17 09:22:07','2026-08-17 09:22:23');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_password_resets_token` (`token`),
  KEY `idx_password_resets_user` (`user_id`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_main` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_main` (`is_main`),
  KEY `idx_sort` (`sort_order`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_sizes`
--

DROP TABLE IF EXISTS `product_sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_sizes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `size_label` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_product_size` (`product_id`,`size_label`),
  KEY `idx_product_sizes_product` (`product_id`),
  CONSTRAINT `product_sizes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=160 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_sizes`
--

LOCK TABLES `product_sizes` WRITE;
/*!40000 ALTER TABLE `product_sizes` DISABLE KEYS */;
INSERT INTO `product_sizes` VALUES (1,35,'M',0,0),(2,35,'L',0,1),(3,36,'S',3,0),(4,36,'M',4,1),(5,36,'L',4,2),(6,36,'XL',3,3),(7,36,'XXL',3,4),(8,37,'XXS',5,0),(9,37,'XS',5,1),(10,37,'S',5,2),(11,37,'M',5,3),(12,37,'L',5,4),(13,37,'XL',5,5),(14,38,'80',7,0),(15,38,'85',7,1),(16,38,'90',7,2),(17,38,'95',7,3),(18,38,'100',7,4),(19,38,'105 cm',7,5),(20,39,'S',5,0),(21,39,'M',5,1),(22,39,'L',5,2),(23,39,'XL',5,3),(24,39,'XXL',4,4),(25,40,'34',4,0),(26,40,'36',4,1),(27,40,'38',3,2),(28,40,'40',3,3),(29,40,'42',3,4),(30,40,'44',3,5),(31,41,'Único (42 × 38 cm)',47,0),(32,42,'Único',33,0),(33,28,'37',3,0),(34,28,'44',3,1),(66,47,'S',14,1),(67,47,'M',20,2),(68,47,'L',15,3),(69,47,'XL',10,4),(70,48,'S',8,1),(71,48,'M',12,2),(72,48,'L',12,3),(73,48,'XL',8,4),(74,49,'38',5,1),(75,49,'39',6,2),(76,49,'40',7,3),(77,49,'41',7,4),(78,49,'42',6,5),(79,49,'43',4,6),(80,50,'S',12,1),(81,50,'M',15,2),(82,50,'L',13,3),(83,50,'XL',10,4),(84,51,'36',4,0),(85,51,'37',4,1),(86,51,'38',4,2),(87,51,'39',4,3),(88,51,'40',3,4),(89,51,'41',3,5),(90,51,'42',3,6),(91,51,'43',3,7),(92,51,'44',3,8),(93,51,'45',3,9),(94,52,'S',14,0),(95,52,'M',14,1),(96,52,'L',14,2),(97,52,'XL',13,3),(98,53,'XS',20,0),(99,53,'S',20,1),(100,53,'M',20,2),(101,53,'L',20,3),(102,53,'XL',20,4),(103,53,'XXL',20,5),(104,54,'S',12,0),(105,54,'M',11,1),(106,54,'L',11,2),(107,54,'XL',11,3),(108,55,'39',7,0),(109,55,'40',7,1),(110,55,'41',7,2),(111,55,'42',7,3),(112,55,'43',6,4),(113,55,'44',6,5),(114,56,'39',10,0),(115,56,'40',10,1),(116,56,'41',10,2),(117,56,'42',10,3),(118,56,'43',10,4),(119,57,'40',6,0),(120,57,'41',6,1),(121,57,'42',6,2),(122,57,'43',6,3),(123,57,'44',6,4),(124,58,'Único',100,0),(125,59,'Único',80,0),(126,60,'Único',75,0),(127,61,'XS',8,0),(128,61,'S',8,1),(129,61,'M',8,2),(130,61,'L',8,3),(131,61,'XL',8,4),(132,62,'S',14,0),(133,62,'M',14,1),(134,62,'L',14,2),(135,62,'XL',13,3),(136,63,'32',4,0),(137,63,'34',4,1),(138,63,'36',4,2),(139,63,'38',4,3),(140,63,'40',4,4),(141,63,'42',4,5),(142,63,'44',3,6),(143,64,'38',12,0),(144,64,'40',12,1),(145,64,'42',12,2),(146,64,'44',12,3),(147,64,'46',12,4),(148,65,'38',18,0),(149,65,'40',18,1),(150,65,'42',17,2),(151,65,'44',17,3),(152,66,'XS',23,0),(153,66,'S',23,1),(154,66,'M',22,2),(155,66,'L',22,3),(156,67,'S',17,0),(157,67,'M',16,1),(158,67,'L',16,2),(159,67,'XL',16,3);
/*!40000 ALTER TABLE `product_sizes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `short_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `stock` int DEFAULT '0',
  `category_id` int DEFAULT NULL,
  `brand` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `material` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('homem','mulher','unissex') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'unissex',
  `featured` tinyint(1) DEFAULT '0',
  `status` enum('active','inactive','out_of_stock') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `meta_title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_category` (`category_id`),
  KEY `idx_gender` (`gender`),
  KEY `idx_featured` (`featured`),
  KEY `idx_status` (`status`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (13,'Hoodie \"Rua à Noite\"','hoodie-rua-a-noite','Hoodie unissex em mistura de algodão pesado. Corte relaxado inspirado no streetwear lisboeta. Peça fictícia de demonstração da loja.','Capuz largo, interior felpudo. Para dias de estúdio ou saídas à cidade.',89.90,NULL,'demo-vnc-hood-rua.jpg',NULL,40,3,'Vincit','XS, S, M, L, XL, XXL','Preto','80% algodão, 20% poliéster','unissex',1,'active','Hoodie Rua à Noite | Vincit','Hoodie streetwear unissex — demonstração.','2026-04-01 23:06:43','2026-04-01 23:06:43'),(14,'T-shirt \"Versos na Veia\"','t-shirt-versos-na-veia','T-shirt de manga curta com grafismo inspirado na cena rap nacional. Leve e versátil. Produto fictício para preencher a loja.','Algodão orgânico, estética minimal. Base perfeita para qualquer outfit.',39.90,29.90,'demo-vnc-ts-vers.jpg','demo-vnc-ts-vers-b.jpg,demo-vnc-ts-vers-c.jpg',80,3,'Vincit','XXS, XS, S, M, L, XL, XXL','Branco sujo','100% algodão','unissex',1,'active','T-shirt Versos na Veia | Vincit','T-shirt rap tuga — demonstração.','2026-04-01 23:06:44','2026-04-01 23:06:44'),(15,'Calças cargo \"Quebrada\"','calcas-cargo-quebrada','Cargo em sarja com inspiração no hip-hop dos anos 90 e no que se ouve hoje nas bairros. Fictício — para mostrar a loja.','Bolsos laterais, corte recto. Resistentes ao dia a dia.',79.90,NULL,'demo-vnc-cargo-qbr.jpg',NULL,35,4,'Vincit','28, 30, 32, 34, 36, 38, 40','Verde oliva','97% algodão, 3% elastano','homem',1,'active','Cargo Quebrada | Vincit','Calças cargo street — demonstração.','2026-04-01 23:06:46','2026-04-01 23:06:46'),(16,'Sweatshirt \"Slow Jam Sessions\"','sweatshirt-slow-jam-sessions','Sweatshirt com bordado discreto. Referência às sessões de slow jams e às playlists de madrugada. Peça de demonstração.','Tom pastel, vibe after-hours. Para quem curte o lado soul e R&B.',64.90,NULL,'demo-vnc-sw-slow.jpg','demo-vnc-sw-slow-b.jpg',28,3,'Vincit','XS, S, M, L, XL, XXL','Lilás acinzentado','Algodão french terry','unissex',1,'active','Sweatshirt Slow Jam | Vincit','Sweatshirt slow jam — demonstração.','2026-04-01 23:06:46','2026-04-01 23:06:46'),(17,'Boné \"Batida Lusitana\"','bone-batida-lusitana','Boné de 6 painéis em sarja. Homenagem às batidas e à língua que rimamos. Produto fictício.','Aba curva, fecho clássico. Logo bordado.',28.90,NULL,'demo-vnc-bon-bat.jpg','demo-vnc-bon-bat-b.jpg',60,6,'Vincit','Único (ajustável 54–62 cm)','Preto / branco','Sarja de algodão','unissex',1,'active','Boné Batida Lusitana | Vincit','Boné street — demonstração.','2026-04-01 23:06:47','2026-04-01 23:06:47'),(18,'Top crop \"Lisboa Groove\"','top-crop-lisboa-groove','Top para combinar com calças largas ou saia. Estética club e batidas electrónicas. Fictício.','Corte cropped, tecido macio. Dança ou concerto.',32.90,NULL,'demo-vnc-top-lis.jpg','demo-vnc-top-lis-b.jpg,demo-vnc-top-lis-c.jpg',45,2,'Vincit','XXS, XS, S, M, L, XL','Bordô','Viscose e elastano','mulher',0,'active','Top Lisboa Groove | Vincit','Top crop — demonstração.','2026-04-01 23:06:48','2026-04-01 23:06:48'),(19,'Joggers \"Estúdio 808\"','joggers-estudio-808','Calças de fato de treino com nome a brincar com o som da caixa. Demonstração — não é merch oficial de nenhum artista.','Cós elástico, punhos apertados. Para gravar ou relaxar.',59.90,NULL,'demo-vnc-jog-808.jpg',NULL,50,4,'Vincit','XS, S, M, L, XL, XXL','Cinzento mescla','Poliéster e algodão','homem',0,'active','Joggers Estúdio 808 | Vincit','Joggers street — demonstração.','2026-04-01 23:06:49','2026-04-01 23:06:49'),(20,'Sneakers \"Break\"','sneakers-break','Sneakers de estilo lifestyle para combinar com denim ou cargo. Inspirados na cultura break e dança urbana. Fictício.','Sola espessa, silhueta chunky. Urbano e confortável.',119.90,NULL,'demo-vnc-sn-brk.jpg','demo-vnc-sn-brk-c.jpg,demo-vnc-sn-brk-d.jpg',22,5,'Vincit','36, 37, 38, 39, 40, 41, 42, 43, 44, 45','Preto / sola creme','Couro sintético, sola em borracha','unissex',1,'active','Sneakers Break | Vincit','Sneakers urbanos — demonstração.','2026-04-01 23:06:51','2026-04-01 23:06:51'),(23,'Mochila \"Cypher\"','mochila-cypher','Mochila urbana para o dia a dia — escola, estúdio ou festival. Nome a referir a rodas de improviso. Fictício.','Compartimento para portátil, bolsos frontais.',45.90,NULL,'demo-vnc-moc-cyp.jpg','demo-vnc-moc-cyp-b.jpg',25,6,'Vincit','Único (capacidade ~22 L)','Cáqui','Poliéster reciclado','unissex',0,'active','Mochila Cypher | Vincit','Mochila street — demonstração.','2026-04-01 23:06:56','2026-04-01 23:06:56'),(24,'Meias \"Kick & Snare\"','meias-kick-snare','Meias com padrão subtil a lembrar o essencial da bateria. Pack fictício para completar o look.','Pack de dois pares. Cano médio.',12.90,NULL,'demo-vnc-mei-ks.jpg',NULL,100,6,'Vincit','35–38, 39–42, 43–46','Preto / cinzento','Algodão e poliamida','unissex',0,'active','Meias Kick & Snare | Vincit','Meias pack — demonstração.','2026-04-01 23:06:57','2026-04-01 23:06:57'),(25,'Jaqueta de ganga \"Blue Note\"','jaqueta-ganga-blue-note','Jaqueta de ganga com bolsos de peito e punhos com botão. Visual inspirado no jazz e na cidade à noite — produto fictício.','Denim médio, cortes clássicos. Sobreposição com hoodies.',98.00,84.90,'demo-vnc-dnm-bln.jpg','demo-vnc-dnm-bln-b.jpg,demo-vnc-dnm-bln-c.jpg',26,1,'Vincit Lab','XS, S, M, L, XL, XXL','Azul médio lavado','100% algodão denim','homem',1,'active','Jaqueta ganga Blue Note | Vincit','Jaqueta denim — demonstração.','2026-04-01 23:06:59','2026-04-01 23:06:59'),(26,'Polo piqué \"Sample Clear\"','polo-pique-sample-clear','Polo em piqué respirável. Nome ironiza samples limpos antes do master — peça de demonstração.','Gola com botões, corte regular. Do escritório ao after.',48.90,NULL,'demo-vnc-pl-smp.jpg',NULL,55,3,'Vincit','S, M, L, XL, XXL','Verde musgo','Algodão piqué','homem',0,'active','Polo Sample Clear | Vincit','Polo piqué — demonstração.','2026-04-01 23:07:00','2026-04-01 23:07:00'),(27,'Shorts mesh \"Arena\"','shorts-mesh-arena','Shorts com forro interior e painéis em mesh. Coleção fictícia inspirada em palcos ao ar livre.','Tecido respirável, cós com cordão. Treino ou verão urbano.',44.90,NULL,'demo-vnc-sh-arn.jpg',NULL,38,1,'Vincit','S, M, L, XL, XXL','Preto / cinza','Poliéster mesh','homem',0,'active','Shorts Arena | Vincit','Shorts desporto — demonstração.','2026-04-01 23:07:02','2026-04-01 23:07:02'),(28,'Ténis court','t-nis-court','Sneakers estilo court branco — par versátil para streetwear ou look mais limpo. Produto fictício de demonstração.','Silhueta baixa, sola em borracha. Branco puro para qualquer fit.',89.90,74.90,'demo-vnc-tn-frs.jpg','demo-vnc-tn-frs-c.jpg',6,5,'Vincit Lab','37, 44','Branco / sola creme','Couro sintético','unissex',1,'active','Ténis Fresh Press | Vincit','Ténis brancos court — demonstração.','2026-04-01 23:07:04','2026-04-11 17:34:05'),(30,'Casaco polar \"Cold Tape\"','casaco-polar-cold-tape','Casaco técnico estilo fleece para camadas. Nome brinca com mixtapes e noites frias — fictício.','Tecido polar quente, fecho integral. Ideal para inverno.',72.00,NULL,'demo-vnc-flc-ctp.jpg','demo-vnc-flc-ctp-b.jpg,demo-vnc-flc-ctp-c.jpg',31,3,'Vincit','XS, S, M, L, XL, XXL','Creme','Poliéster polar reciclado','unissex',0,'active','Casaco polar Cold Tape | Vincit','Fleece street — demonstração.','2026-04-01 23:07:07','2026-04-01 23:07:07'),(33,'Tote canvas','tote-canvas','Saco tote em canvas para o dia a dia criativo. Demonstração com fotos de lifestyle.','Alças largas, lona resistente. Cabe vinyl e portátil.',26.90,NULL,'demo-vnc-tot-pk.jpg','demo-vnc-tot-pk-b.jpg',48,6,'Vincit','Único (42 × 38 cm)','Natural cru','Lona de algodão','unissex',0,'active','Tote Press Kit | Vincit','Tote bag — demonstração.','2026-04-01 23:07:12','2026-04-03 15:10:35'),(35,'Ganga mom','ganga-mom','Calças de ganga corte mom para mulher. Nome evoca cassetes e nostalgia — demonstração.','Cintura alta, perna afunilada. Lavagem anos 90.',76.00,NULL,'demo-vnc-gng-tdk.jpg','demo-vnc-gng-tdk-b.jpg',0,4,'Vincit','M, L','Azul vintage','98% algodão, 2% elastano','mulher',1,'active','Ganga mom Tape Deck | Vincit','Ganga mulher — demonstração.','2026-04-01 23:07:15','2026-04-05 12:39:24'),(36,'Casaco \"Prime\"','casaco-prime','Casaco curto com fecho e bolsos laterais. Linha mais premium da coleção fictícia.','Acolchoado leve, gola alta. Entre estações.',149.90,NULL,'demo-vnc-cas-prm.jpg','demo-vnc-jkt-prm-c.jpg',17,1,'Vincit','S, M, L, XL, XXL','Azul noite','Nylon e enchimento sintético','homem',0,'active','Casaco Prime | Vincit','Casaco homem — demonstração.','2026-04-10 10:15:37','2026-08-17 09:33:17'),(37,'Body \"Microfone\"','body-microfone','Body de manga longa com detalhe gráfico minimal. Produto de demonstração com vibe performance.','Segunda pele, decote subido. Palco ou street.',42.90,NULL,'demo-vnc-bdy-mic.jpg',NULL,30,2,'Vincit','XXS, XS, S, M, L, XL','Preto','Malha de algodão','mulher',0,'active','Body Microfone | Vincit','Body mulher — demonstração.','2026-04-10 10:15:39','2026-08-17 09:33:18'),(38,'Cinto couro \"Loop\"','cinto-couro-loop','Cinto unissex para jeans ou cargo. Demonstração com fotos de produto em estúdio.','Fivela minimalista, couro cheio. Acabamento mate.',34.90,NULL,'demo-vnc-cnt-loop.jpg',NULL,42,6,'Vincit','80, 85, 90, 95, 100, 105 cm','Preto','Couro bovino','unissex',0,'active','Cinto Loop | Vincit','Cinto couro — demonstração.','2026-04-10 10:15:40','2026-08-17 09:33:19'),(39,'Overshirt xadrez \"Sampling\"','overshirt-xadrez-sampling','Overshirt em flanela para sobrepor t-shirts. Referência ao sampling na produção — peça de demo.','Camada intermédia, xadrez clássico. Aberto ou fechado.',69.00,NULL,'demo-vnc-ovs-smp.jpg','demo-vnc-osh-smp-c.jpg',24,1,'Vincit Lab','S, M, L, XL, XXL','Azul / vermelho','Algodão flanela','homem',1,'active','Overshirt Sampling | Vincit','Overshirt xadrez — demonstração.','2026-04-10 10:15:41','2026-08-17 09:33:18'),(40,'Saia plissada \"Neon Bairro\"','saia-plissada-neon-bairro','Saia plissada para combinar com sneakers ou botas. Visual nocturno urbano — produto fictício.','Comprimento médio, plissado fino. Movimento no passo.',54.90,NULL,'demo-vnc-saia-neon.jpg','demo-vnc-sai-nbn-b.jpg',20,2,'Vincit','34, 36, 38, 40, 42, 44','Preto','Poliéster plissado','mulher',0,'active','Saia Neon Bairro | Vincit','Saia plissada — demonstração.','2026-04-10 10:15:42','2026-08-17 09:33:18'),(41,'Tote canvas \"Press Kit\"','tote-canvas-press-kit','Saco tote em canvas para o dia a dia criativo. Demonstração com fotos de lifestyle.','Alças largas, lona resistente. Cabe vinyl e portátil.',26.90,NULL,'demo-vnc-tot-pk.jpg','demo-vnc-tot-pk-b.jpg',47,6,'Vincit','Único (42 × 38 cm)','Natural cru','Lona de algodão','unissex',0,'active','Tote Press Kit | Vincit','Tote bag — demonstração.','2026-04-10 10:15:42','2026-04-11 16:45:00'),(42,'Óculos de sol \"Gold Chain\"','oculos-sol-gold-chain','Óculos de sol unissex com estética retro-hip-hop. Produto fictício; fotos de produto genéricas.','Armação acetato, lentes escuras. Protecção UV400.',59.00,49.90,'demo-vnc-oc-gch.jpg','demo-vnc-oc-gch-b.jpg',33,6,'Vincit Lab','Único','Tartaruga / dourado','Acetato, lentes policarbonato','unissex',1,'active','Óculos Gold Chain | Vincit','Óculos de sol — demonstração.','2026-04-10 10:15:42','2026-04-10 10:15:42'),(47,'Camisola Vincit \"Porto\"','camisola-pap-porto','Camisola de algodao macio, corte regular. Edicao demonstracao PAP.','Algodao 100%, confortavel para o dia a dia.',29.90,NULL,'demo-vnc-ts-vers.jpg','demo-vnc-ts-vers.jpg',59,3,'Vincit','S, M, L, XL','Preto','Algodao','unissex',1,'active',NULL,NULL,'2026-08-10 09:38:48','2026-08-17 09:22:23'),(48,'Calcas Cargo Vincit','calcas-cargo-pap','Calcas cargo com bolsos laterais. Estetica urbana. Demo PAP.','Tecido resistente, corte largo.',44.90,39.90,'demo-vnc-cargo-qbr.jpg','demo-vnc-cargo-qbr.jpg',40,4,'Vincit','S, M, L, XL','Verde','Algodao e elastano','homem',1,'active',NULL,NULL,'2026-08-10 09:38:48','2026-08-10 09:38:48'),(49,'Tenis Runner Vincit','tenis-runner-pap','Tenis leves para uso diario. Sola em borracha. Demo PAP.','Leves e confortaveis, bom para andar.',59.90,NULL,'demo-vnc-tn-frs.jpg','demo-vnc-tn-frs.jpg',35,5,'Vincit','38, 39, 40, 41, 42, 43','Branco','Malha e borracha','unissex',0,'active',NULL,NULL,'2026-08-10 09:38:48','2026-08-10 09:38:48'),(50,'Hoodie Vincit Oversize','hoodie-pap-oversize','Camisola com capuz, corte oversize e interior em felpa. Demo PAP.','Quente e folgada, capuz forrado.',49.90,NULL,'demo-vnc-hood-rua.jpg','demo-vnc-hood-rua.jpg',50,3,'Vincit','S, M, L, XL','Cinza','Algodao e poliester','unissex',1,'active',NULL,NULL,'2026-08-10 09:38:48','2026-08-10 09:38:48'),(51,'Ténis court \"Fresh Press\"','tenis-court-fresh-press','Sneakers estilo court branco — par versátil para streetwear ou look mais limpo. Produto fictício de demonstração.','Silhueta baixa, sola em borracha. Branco puro para qualquer fit.',89.90,74.90,'demo-vnc-tn-frs.jpg','demo-vnc-tn-frs-c.jpg',34,5,'Vincit Lab','36, 37, 38, 39, 40, 41, 42, 43, 44, 45','Branco / sola creme','Couro sintético','unissex',1,'active','Ténis Fresh Press | Vincit','Ténis brancos court — demonstração.','2026-08-17 09:27:57','2026-08-17 09:27:57'),(52,'Camisola de gola alta \"Névoa\"','camisola-gola-alta-nevoa','Camisola unissex em malha canelada, gola alta ajustada. Peça fictícia de demonstração.','Malha canelada quentinha, gola alta. Para o frio da cidade.',54.90,44.90,'demo-vnc-x01.jpg','demo-vnc-x01b.jpg',55,3,'Vincit','S, M, L, XL','Cinza','70% algodão, 30% lã','unissex',0,'active','Camisola gola alta Névoa | Vincit','Camisola canelada — demonstração.','2026-08-17 09:27:58','2026-08-17 09:27:58'),(53,'T-shirt oversize \"Eco\"','t-shirt-oversize-eco','T-shirt oversize de algodão orgânico, costuras reforçadas. Produto fictício de demonstração.','Corte largo, algodão orgânico. Conforto total.',24.90,NULL,'demo-vnc-x02.jpg','demo-vnc-x02b.jpg',120,3,'Vincit','XS, S, M, L, XL, XXL','Bege','100% algodão orgânico','unissex',1,'active','T-shirt oversize Eco | Vincit','T-shirt oversize — demonstração.','2026-08-17 09:27:58','2026-08-17 09:27:58'),(54,'Sweat com fecho \"Trilho\"','sweat-fecho-trilho','Sweatshirt com fecho integral e dois bolsos laterais. Peça fictícia de demonstração.','Sweat com fecho e bolsos. Vibe outdoor urbana.',64.90,NULL,'demo-vnc-x03.jpg','demo-vnc-x03b.jpg',45,3,'Vincit','S, M, L, XL','Verde-musgo','80% algodão, 20% poliéster','homem',0,'active','Sweat com fecho Trilho | Vincit','Sweat com fecho — demonstração.','2026-08-17 09:27:58','2026-08-17 09:27:58'),(55,'Sneakers \"Asfalto\"','sneakers-asfalto','Sneakers de silhueta chunky em pele sintética. Produto fictício de demonstração.','Ténis urbanos, sola chunky. Para andar o dia todo.',99.90,84.90,'demo-vnc-x08.jpg','demo-vnc-x08b.jpg',40,5,'Vincit','39, 40, 41, 42, 43, 44','Branco','Pele sintética / borracha','unissex',1,'active','Sneakers Asfalto | Vincit','Sneakers urbanos — demonstração.','2026-08-17 09:27:58','2026-08-17 09:27:58'),(56,'Ténis running \"Vento\"','tenis-running-vento','Ténis de corrida leves com entressola em espuma. Peça fictícia de demonstração.','Leves e com boa amortização. Para correr ou passear.',79.90,NULL,'demo-vnc-x09.jpg','demo-vnc-x09b.jpg',50,5,'Vincit','39, 40, 41, 42, 43','Azul','Malha técnica / borracha','unissex',0,'active','Ténis running Vento | Vincit','Ténis de corrida — demonstração.','2026-08-17 09:27:59','2026-08-17 09:27:59'),(57,'Botas \"Cascalho\"','botas-cascalho','Botas de cano médio em pele sintética com sola tratorada. Produto fictício de demonstração.','Botas robustas para o inverno urbano.',109.90,NULL,'demo-vnc-x10.jpg','demo-vnc-x10b.jpg',30,5,'Vincit','40, 41, 42, 43, 44','Castanho','Pele sintética / borracha','homem',0,'active','Botas Cascalho | Vincit','Botas de inverno — demonstração.','2026-08-17 09:27:59','2026-08-17 09:27:59'),(58,'Gorro de malha \"Bruma\"','gorro-malha-bruma','Gorro em malha canelada, dobra frontal. Peça fictícia de demonstração.','Gorro quentinho, malha macia. Toque final do outfit.',16.90,12.90,'demo-vnc-x11.jpg','demo-vnc-x11b.jpg',100,6,'Vincit','Único','Preto','100% acrílico','unissex',0,'active','Gorro de malha Bruma | Vincit','Gorro de malha — demonstração.','2026-08-17 09:27:59','2026-08-17 09:27:59'),(59,'Óculos de sol \"Eclipse\"','oculos-sol-eclipse','Óculos de sol com armação redonda e proteção UV400. Produto fictício de demonstração.','Armação redonda, lentes escuras. Estilo garantido.',29.90,NULL,'demo-vnc-x12.jpg','demo-vnc-x12b.jpg',80,6,'Vincit','Único','Preto','Acetato / vidro','unissex',1,'active','Óculos de sol Eclipse | Vincit','Óculos de sol — demonstração.','2026-08-17 09:28:00','2026-08-17 09:28:00'),(60,'Carteira de couro \"Nó\"','carteira-couro-no','Carteira de couro com compartimentos para cartões e notas. Peça fictícia de demonstração.','Carteira compacta, couro genuíno. Cabe no bolso.',34.90,27.90,'demo-vnc-x13.jpg','demo-vnc-x13b.jpg',75,6,'Vincit','Único','Castanho','Couro genuíno','unissex',0,'active','Carteira de couro Nó | Vincit','Carteira de couro — demonstração.','2026-08-17 09:28:00','2026-08-17 09:28:00'),(61,'Vestido midi \"Maré\"','vestido-midi-mare','Vestido midi em viscose fluida, cintura marcada. Produto fictício de demonstração.','Vestido fluido de comprimento midi. Leve e elegante.',59.90,NULL,'demo-vnc-x14.jpg','demo-vnc-x14b.jpg',40,2,'Vincit','XS, S, M, L, XL','Verde-água','100% viscose','mulher',1,'active','Vestido midi Maré | Vincit','Vestido midi — demonstração.','2026-08-17 09:28:00','2026-08-17 09:28:00'),(62,'Camisa de linho \"Costa\"','camisa-linho-costa','Camisa de manga comprida em linho leve, corte regular. Peça fictícia de demonstração.','Camisa de linho fresca. Perfeita para o verão.',47.90,39.90,'demo-vnc-x15.jpg','demo-vnc-x15b.jpg',55,1,'Vincit','S, M, L, XL','Branco','100% linho','homem',0,'active','Camisa de linho Costa | Vincit','Camisa de linho — demonstração.','2026-08-17 09:28:00','2026-08-17 09:28:00'),(63,'Ganga mom \"Tape Deck\"','ganga-mom-tape-deck','Calças de ganga corte mom para mulher. Nome evoca cassetes e nostalgia — demonstração.','Cintura alta, perna afunilada. Lavagem anos 90.',76.00,NULL,'demo-vnc-gng-tdk.jpg','demo-vnc-gng-tdk-b.jpg',27,4,'Vincit','32, 34, 36, 38, 40, 42, 44','Azul vintage','98% algodão, 2% elastano','mulher',1,'active','Ganga mom Tape Deck | Vincit','Ganga mulher — demonstração.','2026-08-17 09:28:52','2026-08-17 09:28:52'),(64,'Jeans slim \"Meia-Noite\"','jeans-slim-meia-noite','Jeans de corte slim em ganga elástica azul-escura. Produto fictício de demonstração.','Ganga escura, corte slim. Combina com tudo.',69.90,54.90,'demo-vnc-x04.jpg','demo-vnc-x04b.jpg',60,4,'Vincit','38, 40, 42, 44, 46','Azul-escuro','98% algodão, 2% elastano','homem',1,'active','Jeans slim Meia-Noite | Vincit','Jeans slim — demonstração.','2026-08-17 09:28:52','2026-08-17 09:28:52'),(65,'Calças chino \"Domingo\"','calcas-chino-domingo','Calças chino em sarja leve, corte reto. Peça fictícia de demonstração.','Chino leve e casual. Do escritório ao café.',49.90,NULL,'demo-vnc-x05.jpg','demo-vnc-x05b.jpg',70,4,'Vincit','38, 40, 42, 44','Caqui','100% algodão','homem',0,'active','Calças chino Domingo | Vincit','Chino casual — demonstração.','2026-08-17 09:28:52','2026-08-17 09:28:52'),(66,'Leggings \"Fluxo\"','leggings-fluxo','Leggings de cintura alta em tecido técnico respirável. Produto fictício de demonstração.','Leggings de treino, tecido respirável.',34.90,27.90,'demo-vnc-x06.jpg','demo-vnc-x06b.jpg',90,4,'Vincit','XS, S, M, L','Preto','88% poliéster, 12% elastano','mulher',0,'active','Leggings Fluxo | Vincit','Leggings de treino — demonstração.','2026-08-17 09:28:52','2026-08-17 09:28:52'),(67,'Fato de treino \"Bloco\"','joggers-bloco','Joggers em felpa com punhos e cintura elásticos. Peça fictícia de demonstração.','Joggers com punhos elásticos. Conforto de estúdio.',44.90,NULL,'demo-vnc-x07.jpg','demo-vnc-x07b.jpg',65,4,'Vincit','S, M, L, XL','Cinza-mesclado','80% algodão, 20% poliéster','unissex',0,'active','Joggers Bloco | Vincit','Joggers de estúdio — demonstração.','2026-08-17 09:28:52','2026-08-17 09:28:52');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_chk_1` CHECK (((`rating` >= 1) and (`rating` <= 5)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_name','Vincito','2026-04-01 22:56:55','2026-04-04 22:05:07'),(2,'site_email','Info@Vincit.pt','2026-04-01 22:56:55','2026-08-10 09:21:24'),(3,'site_phone','+351 123 456 789','2026-04-01 22:56:55','2026-04-01 22:56:55'),(4,'site_address','Porto, Portugal','2026-04-01 22:56:55','2026-08-10 09:19:22'),(5,'currency','EUR','2026-04-01 22:56:55','2026-04-01 22:56:55'),(6,'currency_symbol','€','2026-04-01 22:56:55','2026-04-04 22:35:55'),(7,'shipping_cost','6','2026-04-01 22:56:55','2026-04-11 16:33:31'),(8,'free_shipping_threshold','50','2026-04-01 22:56:55','2026-04-04 22:05:07'),(9,'stripe_public_key','','2026-04-01 22:56:55','2026-04-01 22:56:55'),(10,'stripe_secret_key','Andre#2020','2026-04-01 22:56:55','2026-04-04 22:05:46'),(11,'stripe_webhook_secret','','2026-04-01 22:56:55','2026-04-01 22:56:55'),(12,'vincit_schema_revision','1','2026-04-11 17:30:20','2026-04-11 17:30:20');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Portugal',
  `role` enum('customer','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'Admin','jpopereira9@gmail.com','$2y$10$TrW2HQTlI2NdU90Se1dkFO047sEf.tnBOwG55DE7BPuCxsey2n7Ae',NULL,'+351964895643','','','','Portugal','admin','active','2026-04-01 23:09:16','2026-04-01 23:15:15'),(3,'joao Pereira','jpopereira10@gmail.com','$2y$10$GNx82LCiorVkUl4XPeIizOmtKCTE3VQsi7mO6Dr9SkFzuyzXEWIJi',NULL,NULL,NULL,NULL,NULL,'Portugal','customer','active','2026-04-11 16:43:05','2026-04-11 16:43:05'),(4,'Admin','admin@vincit.pt','$2y$10$mzE1n9V6EPwF.1m4h8eveeoU.ndXjKy8Vh2FMpEJ4dUEbKF9F62ku',NULL,NULL,NULL,NULL,NULL,'Portugal','admin','active','2026-08-10 09:19:22','2026-08-10 09:19:22');
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

-- Dump completed on 2026-08-27  0:58:31
