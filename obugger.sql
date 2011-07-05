-- phpMyAdmin SQL Dump
-- version 3.1.5
-- http://www.phpmyadmin.net
--
-- Host: mysql1080.servage.net
-- Generation Time: Jul 05, 2011 at 04:02 PM
-- Server version: 5.0.85
-- PHP Version: 5.2.42-servage15

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `obugger`
--

-- --------------------------------------------------------

--
-- Table structure for table `bugs`
--

CREATE TABLE IF NOT EXISTS `bugs` (
  `bugID` bigint(20) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `state` varchar(16) NOT NULL default 'open',
  `priority` varchar(8) NOT NULL default 'None',
  `fileDate` int(10) NOT NULL,
  PRIMARY KEY  (`bugID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `accountID` bigint(20) NOT NULL auto_increment,
  `username` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `privileges` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`accountID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

