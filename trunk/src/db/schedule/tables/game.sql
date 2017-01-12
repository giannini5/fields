create table game (
    id              bigint auto_increment,
    poolId          bigint not NULL,
    gameTimeId      bigint not NULL,
    homeTeamId      bigint not NULL,
    visitingTeamId  bigint not NULL,

    PRIMARY KEY (id),
    unique key ux_gameTimeTeamPool(poolId, gameTimeId, homeTeamId, visitingTeamId),
    key ix_gameTime(gameTimeId),
    key ix_homeTeam(homeTeamId),
    key ix_visitingTeam(visitingTeamId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;