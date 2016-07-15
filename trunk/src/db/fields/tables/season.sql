create table season (
    id                    bigint auto_increment,
    leagueId              bigint not NULL,
    name                  varchar(60) not NULL,
    beginReservationsDate dateTime not NULL,
    startDate             date not NULL,
    endDate               date not NULL,
    startTime             time not NULL,
    endTime               time not NULL,
    daysOfWeek            char(8) default '1111100',
    loginAllowed          tinyint default 1,
    createAllowed         tinyint default 1,
    enabled               tinyint default 1,

    PRIMARY KEY (id),
    unique index ux_leagueName(leagueId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
