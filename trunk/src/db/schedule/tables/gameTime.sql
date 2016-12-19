create table gameTime (
    id                bigint auto_increment,
    gameDateId        bigint not NULL,
    fieldId           bigint not NULL,
    startTime         time not NULL,
    genderPreference  varchar(10) not NULL,
    gameId            bigint default NULL,
    PRIMARY KEY (id),
    unique key ux_gameOneGame(gameDateId, fieldId, startTime),
    unique key ux_gameId(gameId),
    key ix_fieldIdStartTime(fieldId, startTime),
    key ix_fieldIdGender(fieldId, genderPreference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;