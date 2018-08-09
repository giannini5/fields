create table player (
    id              bigint auto_increment,
    teamId          bigint not NULL,
    familyId        bigint default NULL,
    name            varchar(60) not NULL,
    email           varchar(128) not NULL,
    phone           varchar(128) not NULL,
    number          int default NULL,
    goals           int default 0,
    quartersSub     int default 0,
    quartersKeep    int default 0,
    quartersInjured int default 0,
    quartersAbsent  int default 0,
    yellowCards     int default 0,
    redCards        int default 0,

    PRIMARY KEY (id),
    unique index ux_teamNameEmail(teamId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;