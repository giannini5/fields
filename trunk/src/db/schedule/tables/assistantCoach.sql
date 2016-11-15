CREATE TABLE `assistantCoach` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `teamId` bigint(20) NOT NULL,
  `familyId` bigint(20) DEFAULT NULL,
  `name` varchar(60) NOT NULL,
  `email` varchar(128) NOT NULL,
  `phone1` varchar(128) NOT NULL,
  `phone2` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_teamNameEmail` (`teamId`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
