create table season (
    id           bigint auto_increment,
    leagueId     bigint not NULL,
    name         varchar(60) not NULL,
    enabled      tinyint default 1,
    PRIMARY KEY (id),
    unique index ux_leagueName(leagueId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
