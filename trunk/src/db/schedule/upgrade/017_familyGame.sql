create table familyGame (
    id        bigint auto_increment,
    familyId  bigint not NULL,
    gameId    bigint not NULL,
    PRIMARY KEY (id),
    index ux_familyGame(familyId, gameId),
    index ux_game(gameId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;