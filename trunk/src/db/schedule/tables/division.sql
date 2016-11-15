create table division (
    id                    bigint auto_increment,
    seasonId              bigint not NULL,
    name                  varchar(60) not NULL,
    PRIMARY KEY (id),
    unique index ux_leagueName(seasonId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
