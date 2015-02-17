create table practiceFieldCoordinator (
    id          bigint auto_increment,
    leagueId    bigint,
    email       varchar(128),
    name        varchar(60),
    password    varchar(10),
    PRIMARY KEY (id),
    unique index ux_leagueEmail(leagueId, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
