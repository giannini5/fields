CREATE TABLE `season` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `leagueId` bigint(20) NOT NULL,
  `name` varchar(60) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `daysOfWeek` char(8) DEFAULT '0000011',
  `enabled` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_leagueName` (`leagueId`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;