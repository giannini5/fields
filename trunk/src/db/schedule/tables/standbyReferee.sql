create table if not exists standbyReferee (
  id            bigint      auto_increment,
  facilityId    bigint      not NULL,
  gameDateId    bigint      not NULL,
  divisionName  varchar(60) not NULL,
  startTime     time        not NULL,
  refereeId     bigint      not NULL,
  role          char        not NULL,
  refereeCrewId bigint      default null,

  PRIMARY KEY (id),
  unique index ux_standbyReferee(facilityId, gameDateId, divisionName, startTime, refereeId),
  index ix_standbyRefereeGameDate(facilityId, gameDateId, refereeId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;