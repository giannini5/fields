create table location (
    id           bigint auto_increment,
    leagueId     bigint not NULL,
    name         varchar(60) not NULL,
    PRIMARY KEY (id),
    unique index ux_leagueLocation(leagueId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
