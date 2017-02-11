create table game (
    id              bigint auto_increment,
    flightId        bigint not NULL,
    poolId          bigint default NULL,
    gameTimeId      bigint not NULL,
    homeTeamId      bigint default NULL,
    visitingTeamId  bigint default NULL,
    title           varchar(60) not NULL default '';

    PRIMARY KEY (id),
    unique key ux_gameTimeTeamFlight(flightId, gameTimeId, homeTeamId, visitingTeamId),
    key ix_gameTimeTeamPool(poolId, gameTimeId, homeTeamId, visitingTeamId),
    key ix_gameTime(gameTimeId),
    key ix_homeTeam(homeTeamId),
    key ix_visitingTeam(visitingTeamId),
    key ix_title(title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;