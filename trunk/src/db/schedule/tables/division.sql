create table if not exists division (
    id                      bigint auto_increment,
    seasonId                bigint not NULL,
    name                    varchar(60) not NULL,
    gender                  varchar(20) not NULL,
    maxPlayersPerTeam       int default 22,
    gameDurationMinutes     int not NULL,
    minutesBetweenGames     int not NULL default 180,
    scoringTracked          int not NULL default 1,
    displayOrder            int not NULL default 0,
    combineLeagueSchedules  tinyint not NULL default 0,
    PRIMARY KEY (id),
    unique index ux_leagueNameGender(seasonId, name, gender)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
