create table game (
    id              bigint auto_increment,
    scheduleId      bigint not NULL,
    gameTimeId      bigint not NULL,
    homeTeamId      bigint not NULL,
    visitingTeamId  bigint not NULL,
    PRIMARY KEY (id),
    unique key ux_gameTimeTeamSchedule(scheduleId, gameTimeId, homeTeamId, visitingTeamId),
    unique key ux_oneGame(gameTimeId),
    key ix_homeTeam(homeTeamId),
    key ix_visitingTeam(visitingTeamId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;