create table playerGameStats (
  gameId                  bigint not NULL,
  teamId                  bigint not NULL,
  playerId                bigint default NULL,
  goals                   int default 0,
  substitutionQuarter1    int default 0,
  substitutionQuarter2    int default 0,
  substitutionQuarter3    int default 0,
  substitutionQuarter4    int default 0,
  keeperQuarter1          int default 0,
  keeperQuarter2          int default 0,
  keeperQuarter3          int default 0,
  keeperQuarter4          int default 0,
  yellowCards             int default 0,
  redCard                 int default 0,

  PRIMARY KEY (gameId, teamId, playerId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;