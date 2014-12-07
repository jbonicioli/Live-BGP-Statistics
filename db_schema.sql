SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DROP TABLE IF EXISTS `cclass`;
CREATE TABLE IF NOT EXISTS `cclass` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `CClass` varchar(19) NOT NULL,
  `Node_id` int(10) NOT NULL,
  `state` enum('up','down') NOT NULL,
  `date` int(10) NOT NULL,
  `Seenby` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Node_id` (`Node_id`),
  KEY `state` (`state`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

DROP TABLE IF EXISTS `cclass_temp`;
CREATE TABLE IF NOT EXISTS `cclass_temp` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `Node_id` int(5) NOT NULL,
  `CClass` varchar(19) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `node1` (`Node_id`,`CClass`),
  KEY `node1_2` (`Node_id`),
  KEY `node2` (`CClass`)
) ENGINE=MEMORY  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

DROP TABLE IF EXISTS `links`;
CREATE TABLE IF NOT EXISTS `links` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `node1` int(10) NOT NULL DEFAULT '0',
  `node2` int(10) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  `state` enum('up','down') NOT NULL DEFAULT 'up',
  `active` enum('0','1') NOT NULL DEFAULT '0',
  `byrouter` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `node1` (`node1`,`node2`),
  KEY `node1_2` (`node1`),
  KEY `node2` (`node2`),
  KEY `state` (`state`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

DROP TABLE IF EXISTS `links_temp`;
CREATE TABLE IF NOT EXISTS `links_temp` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `node1` int(10) NOT NULL,
  `node2` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_nodes` (`node1`,`node2`),
  KEY `node1` (`node1`),
  KEY `node2` (`node2`)
) ENGINE=MEMORY  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

DROP TABLE IF EXISTS `nodes`;
CREATE TABLE IF NOT EXISTS `nodes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `Node_id` int(10) NOT NULL DEFAULT '0',
  `Node_name` varchar(255) NOT NULL DEFAULT '',
  `Node_area` varchar(255) NOT NULL DEFAULT '',
  `lat` varchar(255) NOT NULL DEFAULT '',
  `lon` varchar(255) NOT NULL DEFAULT '',
  `C-Class` varchar(255) NOT NULL,
  `Owner` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Node_id` (`Node_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

DROP TABLE IF EXISTS `prepends`;
CREATE TABLE IF NOT EXISTS `prepends` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nodeid` int(10) NOT NULL DEFAULT '0',
  `parent_nodeid` varchar(255) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL,
  `state` enum('up','down') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nodeid` (`nodeid`,`parent_nodeid`),
  KEY `date` (`date`,`state`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

DROP TABLE IF EXISTS `prepends_temp`;
CREATE TABLE IF NOT EXISTS `prepends_temp` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `nodeid` int(10) NOT NULL,
  `parent_nodeid` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `node1` (`nodeid`,`parent_nodeid`),
  KEY `node1_2` (`nodeid`),
  KEY `node2` (`parent_nodeid`)
) ENGINE=MEMORY  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
