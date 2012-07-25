-- MySQL dump 10.13  Distrib 5.5.24, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: goldsense
-- ------------------------------------------------------
-- Server version	5.5.24-4-log

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
-- Table structure for table `Categories`
--

DROP TABLE IF EXISTS `Categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Categories` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '[ml]',
  `name_tw` varchar(255) NOT NULL,
  `name_cn` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `iden` varchar(255) NOT NULL,
  `description` varchar(1000) DEFAULT NULL COMMENT '[ml]',
  `description_tw` varchar(1000) DEFAULT NULL,
  `description_cn` varchar(1000) DEFAULT NULL,
  `description_en` varchar(1000) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0',
  `imageId` mediumint(8) unsigned DEFAULT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iden` (`iden`),
  KEY `imageId` (`imageId`),
  CONSTRAINT `Categories_ibfk_1` FOREIGN KEY (`imageId`) REFERENCES `Multimedia` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Categories`
--

LOCK TABLES `Categories` WRITE;
/*!40000 ALTER TABLE `Categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `Categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ExternalProducts`
--

DROP TABLE IF EXISTS `ExternalProducts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ExternalProducts` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '[ml]',
  `description` mediumtext COMMENT '[ml][html]',
  `website` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0',
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ExternalProducts`
--

LOCK TABLES `ExternalProducts` WRITE;
/*!40000 ALTER TABLE `ExternalProducts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ExternalProducts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Multimedia`
--

DROP TABLE IF EXISTS `Multimedia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Multimedia` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '[ml]',
  `name_tw` varchar(255) NOT NULL,
  `name_cn` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `iden` varchar(255) NOT NULL,
  `description` mediumtext COMMENT '[ml][html]',
  `description_tw` mediumtext COMMENT '[html]',
  `description_cn` mediumtext COMMENT '[html]',
  `description_en` mediumtext COMMENT '[html]',
  `fileFileSize` int(11) unsigned DEFAULT NULL COMMENT '[FSO]',
  `fileMd5Sum` varchar(80) DEFAULT NULL,
  `fileMimeType` varchar(80) DEFAULT NULL,
  `fileBaseName` varchar(255) DEFAULT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iden` (`iden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Multimedia`
--

LOCK TABLES `Multimedia` WRITE;
/*!40000 ALTER TABLE `Multimedia` DISABLE KEYS */;
/*!40000 ALTER TABLE `Multimedia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Places`
--

DROP TABLE IF EXISTS `Places`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Places` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '[ml]',
  `iden` varchar(255) NOT NULL,
  `shortDescription` mediumtext COMMENT '[ml][html]',
  `longDescription` mediumtext COMMENT '[ml][html]',
  `province` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iden` (`iden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Places`
--

LOCK TABLES `Places` WRITE;
/*!40000 ALTER TABLE `Places` DISABLE KEYS */;
/*!40000 ALTER TABLE `Places` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ProducerAddresses`
--

DROP TABLE IF EXISTS `ProducerAddresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ProducerAddresses` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '[ml]',
  `iden` varchar(255) NOT NULL,
  `street` varchar(1000) NOT NULL,
  `city` varchar(255) NOT NULL,
  `zipCode` varchar(100) NOT NULL,
  `province` varchar(255) DEFAULT NULL,
  `state` varchar(255) NOT NULL,
  `producerId` mediumint(8) unsigned NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iden` (`iden`),
  KEY `producerId` (`producerId`),
  CONSTRAINT `ProducerAddresses_ibfk_1` FOREIGN KEY (`producerId`) REFERENCES `Producers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ProducerAddresses`
--

LOCK TABLES `ProducerAddresses` WRITE;
/*!40000 ALTER TABLE `ProducerAddresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `ProducerAddresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Producers`
--

DROP TABLE IF EXISTS `Producers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Producers` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `iden` varchar(255) NOT NULL,
  `description` mediumtext COMMENT '[ml][html]',
  `website` varchar(255) DEFAULT NULL,
  `defaultImageId` mediumint(8) unsigned DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0',
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iden` (`iden`),
  KEY `defaultImageId` (`defaultImageId`),
  CONSTRAINT `Producers_ibfk_1` FOREIGN KEY (`defaultImageId`) REFERENCES `Multimedia` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Producers`
--

LOCK TABLES `Producers` WRITE;
/*!40000 ALTER TABLE `Producers` DISABLE KEYS */;
/*!40000 ALTER TABLE `Producers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Products`
--

DROP TABLE IF EXISTS `Products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Products` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '[ml]',
  `description` mediumtext COMMENT '[ml][html]',
  `producerId` mediumint(8) unsigned DEFAULT NULL,
  `price` mediumint(8) unsigned NOT NULL,
  `productType` enum('product','experience','extra') DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0',
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `producerId` (`producerId`),
  CONSTRAINT `Products_ibfk_1` FOREIGN KEY (`producerId`) REFERENCES `Producers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Products`
--

LOCK TABLES `Products` WRITE;
/*!40000 ALTER TABLE `Products` DISABLE KEYS */;
/*!40000 ALTER TABLE `Products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Recipes`
--

DROP TABLE IF EXISTS `Recipes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Recipes` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '[ml]',
  `iden` varchar(255) NOT NULL,
  `shortDescription` mediumtext COMMENT '[ml][html]',
  `longDescription` mediumtext COMMENT '[ml][html]',
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iden` (`iden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Recipes`
--

LOCK TABLES `Recipes` WRITE;
/*!40000 ALTER TABLE `Recipes` DISABLE KEYS */;
/*!40000 ALTER TABLE `Recipes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RelExperienceProducts`
--

DROP TABLE IF EXISTS `RelExperienceProducts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RelExperienceProducts` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `experienceId` mediumint(8) unsigned NOT NULL,
  `productId` mediumint(8) unsigned NOT NULL,
  `quantity` int(11) DEFAULT '1',
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `productId` (`productId`),
  KEY `experienceId` (`experienceId`),
  CONSTRAINT `RelExperienceProducts_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `Products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RelExperienceProducts_ibfk_2` FOREIGN KEY (`experienceId`) REFERENCES `Products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RelExperienceProducts`
--

LOCK TABLES `RelExperienceProducts` WRITE;
/*!40000 ALTER TABLE `RelExperienceProducts` DISABLE KEYS */;
/*!40000 ALTER TABLE `RelExperienceProducts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RelExternalProductsCategories`
--

DROP TABLE IF EXISTS `RelExternalProductsCategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RelExternalProductsCategories` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `categoryId` mediumint(8) unsigned NOT NULL,
  `externalProductId` mediumint(8) unsigned NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `categoryId` (`categoryId`),
  KEY `externalProductId` (`externalProductId`),
  CONSTRAINT `RelExternalProductsCategories_ibfk_1` FOREIGN KEY (`categoryId`) REFERENCES `Categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RelExternalProductsCategories_ibfk_2` FOREIGN KEY (`externalProductId`) REFERENCES `ExternalProducts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RelExternalProductsCategories`
--

LOCK TABLES `RelExternalProductsCategories` WRITE;
/*!40000 ALTER TABLE `RelExternalProductsCategories` DISABLE KEYS */;
/*!40000 ALTER TABLE `RelExternalProductsCategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RelExternalProductsMultimedia`
--

DROP TABLE IF EXISTS `RelExternalProductsMultimedia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RelExternalProductsMultimedia` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `externalProductId` mediumint(8) unsigned NOT NULL,
  `multimediaId` mediumint(8) unsigned NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `externalProductId` (`externalProductId`),
  KEY `multimediaId` (`multimediaId`),
  CONSTRAINT `RelExternalProductsMultimedia_ibfk_1` FOREIGN KEY (`externalProductId`) REFERENCES `ExternalProducts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RelExternalProductsMultimedia_ibfk_2` FOREIGN KEY (`multimediaId`) REFERENCES `Multimedia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RelExternalProductsMultimedia`
--

LOCK TABLES `RelExternalProductsMultimedia` WRITE;
/*!40000 ALTER TABLE `RelExternalProductsMultimedia` DISABLE KEYS */;
/*!40000 ALTER TABLE `RelExternalProductsMultimedia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RelMultimediaCategories`
--

DROP TABLE IF EXISTS `RelMultimediaCategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RelMultimediaCategories` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `multimediaId` mediumint(8) unsigned NOT NULL,
  `categoryId` mediumint(8) unsigned NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `multimediaId` (`multimediaId`),
  KEY `categoryId` (`categoryId`),
  CONSTRAINT `RelMultimediaCategories_ibfk_1` FOREIGN KEY (`multimediaId`) REFERENCES `Multimedia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RelMultimediaCategories_ibfk_2` FOREIGN KEY (`categoryId`) REFERENCES `Categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RelMultimediaCategories`
--

LOCK TABLES `RelMultimediaCategories` WRITE;
/*!40000 ALTER TABLE `RelMultimediaCategories` DISABLE KEYS */;
/*!40000 ALTER TABLE `RelMultimediaCategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RelPlacesMultimedia`
--

DROP TABLE IF EXISTS `RelPlacesMultimedia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RelPlacesMultimedia` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `placeId` mediumint(8) unsigned NOT NULL,
  `multimediaId` mediumint(8) unsigned NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `placeId` (`placeId`),
  KEY `multimediaId` (`multimediaId`),
  CONSTRAINT `RelPlacesMultimedia_ibfk_2` FOREIGN KEY (`multimediaId`) REFERENCES `Multimedia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RelPlacesMultimedia_ibfk_1` FOREIGN KEY (`placeId`) REFERENCES `Places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RelPlacesMultimedia`
--

LOCK TABLES `RelPlacesMultimedia` WRITE;
/*!40000 ALTER TABLE `RelPlacesMultimedia` DISABLE KEYS */;
/*!40000 ALTER TABLE `RelPlacesMultimedia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RelProducersMultimedia`
--

DROP TABLE IF EXISTS `RelProducersMultimedia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RelProducersMultimedia` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `producerId` mediumint(8) unsigned NOT NULL,
  `multimediaId` mediumint(8) unsigned NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `producerId` (`producerId`),
  KEY `multimediaId` (`multimediaId`),
  CONSTRAINT `RelProducersMultimedia_ibfk_2` FOREIGN KEY (`multimediaId`) REFERENCES `Multimedia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RelProducersMultimedia_ibfk_1` FOREIGN KEY (`producerId`) REFERENCES `Producers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RelProducersMultimedia`
--

LOCK TABLES `RelProducersMultimedia` WRITE;
/*!40000 ALTER TABLE `RelProducersMultimedia` DISABLE KEYS */;
/*!40000 ALTER TABLE `RelProducersMultimedia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RelProductsCategories`
--

DROP TABLE IF EXISTS `RelProductsCategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RelProductsCategories` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `productId` mediumint(8) unsigned NOT NULL,
  `categoryId` mediumint(8) unsigned NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `productId` (`productId`),
  KEY `categoryId` (`categoryId`),
  CONSTRAINT `RelProductsCategories_ibfk_2` FOREIGN KEY (`categoryId`) REFERENCES `Categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RelProductsCategories_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `Products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RelProductsCategories`
--

LOCK TABLES `RelProductsCategories` WRITE;
/*!40000 ALTER TABLE `RelProductsCategories` DISABLE KEYS */;
/*!40000 ALTER TABLE `RelProductsCategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RelProductsExternalProducts`
--

DROP TABLE IF EXISTS `RelProductsExternalProducts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RelProductsExternalProducts` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `productId` mediumint(8) unsigned NOT NULL,
  `externalProductId` mediumint(8) unsigned NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `productId` (`productId`),
  KEY `externalProductId` (`externalProductId`),
  CONSTRAINT `RelProductsExternalProducts_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `Products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RelProductsExternalProducts_ibfk_2` FOREIGN KEY (`externalProductId`) REFERENCES `ExternalProducts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RelProductsExternalProducts`
--

LOCK TABLES `RelProductsExternalProducts` WRITE;
/*!40000 ALTER TABLE `RelProductsExternalProducts` DISABLE KEYS */;
/*!40000 ALTER TABLE `RelProductsExternalProducts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RelProductsMultimedia`
--

DROP TABLE IF EXISTS `RelProductsMultimedia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RelProductsMultimedia` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `productId` mediumint(8) unsigned NOT NULL,
  `multimediaId` mediumint(8) unsigned NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `productId` (`productId`),
  KEY `multimediaId` (`multimediaId`),
  CONSTRAINT `RelProductsMultimedia_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `Products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RelProductsMultimedia_ibfk_2` FOREIGN KEY (`multimediaId`) REFERENCES `Multimedia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RelProductsMultimedia`
--

LOCK TABLES `RelProductsMultimedia` WRITE;
/*!40000 ALTER TABLE `RelProductsMultimedia` DISABLE KEYS */;
/*!40000 ALTER TABLE `RelProductsMultimedia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RelProductsProducts`
--

DROP TABLE IF EXISTS `RelProductsProducts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RelProductsProducts` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `productId` mediumint(8) unsigned NOT NULL,
  `otherProductId` mediumint(8) unsigned NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `productId` (`productId`),
  KEY `otherProductId` (`otherProductId`),
  CONSTRAINT `RelProductsProducts_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `Products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RelProductsProducts_ibfk_2` FOREIGN KEY (`otherProductId`) REFERENCES `Products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RelProductsProducts`
--

LOCK TABLES `RelProductsProducts` WRITE;
/*!40000 ALTER TABLE `RelProductsProducts` DISABLE KEYS */;
/*!40000 ALTER TABLE `RelProductsProducts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RelRecipesCategories`
--

DROP TABLE IF EXISTS `RelRecipesCategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RelRecipesCategories` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `recipeId` mediumint(8) unsigned NOT NULL,
  `categoryId` mediumint(8) unsigned NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `recipeId` (`recipeId`),
  KEY `categoryId` (`categoryId`),
  CONSTRAINT `RelRecipesCategories_ibfk_1` FOREIGN KEY (`recipeId`) REFERENCES `Recipes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RelRecipesCategories_ibfk_2` FOREIGN KEY (`categoryId`) REFERENCES `Categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RelRecipesCategories`
--

LOCK TABLES `RelRecipesCategories` WRITE;
/*!40000 ALTER TABLE `RelRecipesCategories` DISABLE KEYS */;
/*!40000 ALTER TABLE `RelRecipesCategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RelRecipesMultimedia`
--

DROP TABLE IF EXISTS `RelRecipesMultimedia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RelRecipesMultimedia` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `recipeId` mediumint(8) unsigned NOT NULL,
  `multimediaId` mediumint(8) unsigned NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `recipeId` (`recipeId`),
  KEY `multimediaId` (`multimediaId`),
  CONSTRAINT `RelRecipesMultimedia_ibfk_1` FOREIGN KEY (`recipeId`) REFERENCES `Recipes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RelRecipesMultimedia_ibfk_2` FOREIGN KEY (`multimediaId`) REFERENCES `Multimedia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RelRecipesMultimedia`
--

LOCK TABLES `RelRecipesMultimedia` WRITE;
/*!40000 ALTER TABLE `RelRecipesMultimedia` DISABLE KEYS */;
/*!40000 ALTER TABLE `RelRecipesMultimedia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RelShoppingCartProducts`
--

DROP TABLE IF EXISTS `RelShoppingCartProducts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RelShoppingCartProducts` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `shoppingCartId` mediumint(8) unsigned NOT NULL,
  `productId` mediumint(8) unsigned NOT NULL,
  `quantity` mediumint(9) NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `productId` (`productId`),
  KEY `shoppingCartId` (`shoppingCartId`),
  CONSTRAINT `RelShoppingCartProducts_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `Products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RelShoppingCartProducts_ibfk_2` FOREIGN KEY (`shoppingCartId`) REFERENCES `ShoppingCart` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RelShoppingCartProducts`
--

LOCK TABLES `RelShoppingCartProducts` WRITE;
/*!40000 ALTER TABLE `RelShoppingCartProducts` DISABLE KEYS */;
/*!40000 ALTER TABLE `RelShoppingCartProducts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Roles`
--

DROP TABLE IF EXISTS `Roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Roles` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '[ml]',
  `iden` varchar(255) NOT NULL,
  `description` varchar(1000) DEFAULT NULL COMMENT '[ml]',
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iden` (`iden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Roles`
--

LOCK TABLES `Roles` WRITE;
/*!40000 ALTER TABLE `Roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `Roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Sections`
--

DROP TABLE IF EXISTS `Sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Sections` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '[ml]',
  `name_tw` varchar(255) NOT NULL,
  `name_cn` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `iden` varchar(255) NOT NULL,
  `content` mediumtext COMMENT '[ml][html]',
  `content_tw` mediumtext COMMENT '[html]',
  `content_cn` mediumtext COMMENT '[html]',
  `content_en` mediumtext COMMENT '[html]',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iden` (`iden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Sections`
--

LOCK TABLES `Sections` WRITE;
/*!40000 ALTER TABLE `Sections` DISABLE KEYS */;
/*!40000 ALTER TABLE `Sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ShoppingCart`
--

DROP TABLE IF EXISTS `ShoppingCart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ShoppingCart` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `userId` mediumint(8) unsigned NOT NULL,
  `billingAddressId` mediumint(8) unsigned DEFAULT NULL,
  `shippingAddressId` mediumint(8) unsigned DEFAULT NULL,
  `status` enum('shopping','confirmed') DEFAULT NULL,
  `paid` tinyint(1) DEFAULT '0',
  `sent` tinyint(1) DEFAULT '0',
  `hash` varchar(255) DEFAULT NULL,
  `userComments` mediumtext,
  `adminComments` mediumtext,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `billingAddressId` (`billingAddressId`),
  KEY `shippingAddressId` (`shippingAddressId`),
  CONSTRAINT `ShoppingCart_ibfk_3` FOREIGN KEY (`shippingAddressId`) REFERENCES `UserAddresses` (`id`),
  CONSTRAINT `ShoppingCart_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `Users` (`id`),
  CONSTRAINT `ShoppingCart_ibfk_2` FOREIGN KEY (`billingAddressId`) REFERENCES `UserAddresses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ShoppingCart`
--

LOCK TABLES `ShoppingCart` WRITE;
/*!40000 ALTER TABLE `ShoppingCart` DISABLE KEYS */;
/*!40000 ALTER TABLE `ShoppingCart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Stock`
--

DROP TABLE IF EXISTS `Stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Stock` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `productId` mediumint(8) unsigned NOT NULL,
  `quantity` mediumint(9) NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `productId` (`productId`),
  CONSTRAINT `Stock_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `Products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Stock`
--

LOCK TABLES `Stock` WRITE;
/*!40000 ALTER TABLE `Stock` DISABLE KEYS */;
/*!40000 ALTER TABLE `Stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `UserAddresses`
--

DROP TABLE IF EXISTS `UserAddresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UserAddresses` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `iden` varchar(255) NOT NULL,
  `street` varchar(1000) NOT NULL,
  `city` varchar(255) NOT NULL,
  `zipCode` varchar(100) NOT NULL,
  `province` varchar(255) DEFAULT NULL,
  `state` varchar(255) NOT NULL,
  `type` enum('billing','shipping','both') DEFAULT NULL,
  `userId` mediumint(8) unsigned NOT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iden` (`iden`),
  KEY `userId` (`userId`),
  CONSTRAINT `UserAddresses_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `Users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `UserAddresses`
--

LOCK TABLES `UserAddresses` WRITE;
/*!40000 ALTER TABLE `UserAddresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `UserAddresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(70) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `emailAddress` varchar(255) NOT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `birthDate` date NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `roleId` mediumint(8) unsigned DEFAULT NULL,
  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `roleId` (`roleId`),
  CONSTRAINT `Users_ibfk_1` FOREIGN KEY (`roleId`) REFERENCES `Roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Users`
--

LOCK TABLES `Users` WRITE;
/*!40000 ALTER TABLE `Users` DISABLE KEYS */;
/*!40000 ALTER TABLE `Users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-07-09 14:50:18
