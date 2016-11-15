create table gameTime (
    id           bigint auto_increment,
    gameDateId   bigint not NULL,
    divisionId   bigint not NULL,
    fieldId      bigint not NULL,
    startTime    time not NULL,
    PRIMARY KEY (id),
    unique key ux_gameDateFieldTime(gameDateId, divisionId, fieldId, startTime),
    unique key ux_gameOneGame(gameDateId, fieldId, startTime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;