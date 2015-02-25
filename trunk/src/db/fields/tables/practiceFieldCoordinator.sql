create table practiceFieldCoordinator (
    id          bigint auto_increment,
    leagueId    bigint not NULL,
    email       varchar(128) not NULL,
    name        varchar(60) not NULL,
    password    varchar(10) default '',
    PRIMARY KEY (id),
    unique index ux_leagueEmail(leagueId, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
