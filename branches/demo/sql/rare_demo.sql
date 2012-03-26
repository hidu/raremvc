-- phpMyAdmin SQL Dump
-- version 3.4.3.2
-- http://www.phpmyadmin.net
--
-- 主机: 127.0.0.1
-- 生成日期: 2012 年 03 月 26 日 23:38
-- 服务器版本: 5.5.21
-- PHP 版本: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `rare_demo`
--

-- --------------------------------------------------------

--
-- 表的结构 `article`
--
-- 创建时间: 2012 年 03 月 26 日 15:37
-- 最后更新: 2012 年 03 月 26 日 15:37
--

DROP TABLE IF EXISTS `article`;
CREATE TABLE IF NOT EXISTS `article` (
  `articleid` int(11) NOT NULL AUTO_INCREMENT,
  `cateid` int(10) unsigned NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `pinyin` varchar(255) CHARACTER SET latin1 NOT NULL,
  `ctime` int(10) unsigned NOT NULL DEFAULT '0',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0',
  `stateid` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `body` longtext NOT NULL COMMENT '内容',
  PRIMARY KEY (`articleid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `category`
--
-- 创建时间: 2012 年 03 月 26 日 15:23
-- 最后更新: 2012 年 03 月 26 日 15:26
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `cateid` int(11) NOT NULL AUTO_INCREMENT,
  `catename` varchar(255) NOT NULL,
  `pinyin` varchar(255) NOT NULL,
  PRIMARY KEY (`cateid`),
  UNIQUE KEY `title` (`catename`),
  UNIQUE KEY `pinyin` (`pinyin`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
