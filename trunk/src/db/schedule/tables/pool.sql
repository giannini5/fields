create table pool (
    id                    bigint auto_increment,
    scheduleId            bigint not NULL,
    name                  varchar(60) not NULL,
    gamesAgainstPoolId    bigint default NULL,

    PRIMARY KEY (id),
    unique index ux_scheduleName(scheduleId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
