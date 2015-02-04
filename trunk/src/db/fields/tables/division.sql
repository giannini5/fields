create table division (
    id           bigint auto_increment,
    leagueId     bigint,
    name         varchar(60),
    PRIMARY KEY (id),
    unique index ux_leagueName(leagueId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
