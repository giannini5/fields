create table pool (
    id                    bigint auto_increment,
    flightId              bigint not NULL,
    scheduleId            bigint not NULL,
    name                  varchar(60) not NULL,
    gamesAgainstPoolId    bigint default NULL,

    PRIMARY KEY (id),
    unique index ux_flightName(flightId, name),
    unique index ux_scheduleFlightName(scheduleId, flightId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
