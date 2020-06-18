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
-- Table structure for table `authors`
--

DROP TABLE IF EXISTS `authors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authors` (
  `author` varchar(70) NOT NULL,
  `authorId` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`authorId`),
  UNIQUE KEY `UNIQUE` (`author`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bookmarks`
--

DROP TABLE IF EXISTS `bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookmarks` (
  `dirId` int(10) NOT NULL,
  `bookmark` varchar(50) NOT NULL,
  PRIMARY KEY (`dirId`),
  KEY `dirId` (`dirId`),
  CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`dirId`) REFERENCES `directories` (`dirId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dir_author_link`
--

DROP TABLE IF EXISTS `dir_author_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dir_author_link` (
  `dirId` int(10) NOT NULL,
  `authorId` int(10) NOT NULL,
  KEY `dirId` (`dirId`),
  KEY `authorId` (`authorId`),
  CONSTRAINT `dir_author_link_ibfk_1` FOREIGN KEY (`dirId`) REFERENCES `directories` (`dirId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dir_author_link_ibfk_2` FOREIGN KEY (`authorId`) REFERENCES `authors` (`authorId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dir_tag_link`
--

DROP TABLE IF EXISTS `dir_tag_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dir_tag_link` (
  `dirId` int(10) NOT NULL,
  `tagId` int(10) NOT NULL,
  KEY `dirId` (`dirId`),
  KEY `tagId` (`tagId`),
  CONSTRAINT `dir_tag_link_ibfk_1` FOREIGN KEY (`dirId`) REFERENCES `directories` (`dirId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dir_tag_link_ibfk_2` FOREIGN KEY (`tagId`) REFERENCES `tags` (`tagId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `directories`
--

DROP TABLE IF EXISTS `directories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `directories` (
  `dirName` varchar(300) NOT NULL,
  `dirCover` varchar(300) NOT NULL,
  `dirId` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`dirId`),
  UNIQUE KEY `UNIQUE` (`dirName`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `favourites`
--

DROP TABLE IF EXISTS `favourites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `favourites` (
  `dirId` int(10) NOT NULL,
  PRIMARY KEY (`dirId`),
  CONSTRAINT `favourites_ibfk_1` FOREIGN KEY (`dirId`) REFERENCES `directories` (`dirId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `tag` varchar(50) NOT NULL,
  `tagId` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`tagId`),
  UNIQUE KEY `UNIQUE` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
