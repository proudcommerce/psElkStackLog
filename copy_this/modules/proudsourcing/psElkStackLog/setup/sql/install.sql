-- psElkStackLog
CREATE TABLE `pselkstacklog_queue` (
  `OXID` char(32) COLLATE latin1_general_ci NOT NULL,
  `OXSHOPID` varchar(32) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `OXTYPE` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `OXSTATUS` tinyint(1) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
  `OXDATA` text COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `OXTIMESTAMP` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`oxid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
