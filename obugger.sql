--
-- Table structure for table `bugs`
--

CREATE TABLE IF NOT EXISTS `bugs` (
  `bugID` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `state` varchar(16) NOT NULL DEFAULT 'open',
  `priority` varchar(8) NOT NULL DEFAULT 'None',
  `assignedTo` bigint(20) NOT NULL,
  `fileDate` int(10) NOT NULL,
  `lastUpdated` int(10) NOT NULL,
  PRIMARY KEY (`bugID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=56 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `accountID` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `privileges` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`accountID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

