create table division (
    id                    bigint auto_increment,
    seasonId              bigint not NULL,
    name                  varchar(60) not NULL,
    gender                varchar(20) not NULL,
    gameDurationMinutes   int not NULL,
    displayOrder          int not NULL default 0,
    PRIMARY KEY (id),
    unique index ux_leagueNameGender(seasonId, name, gender)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
