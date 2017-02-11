create table if not exists flight (
    id                    bigint auto_increment,
    scheduleId            bigint not NULL,
    name                  varchar(60) not NULL,

    PRIMARY KEY (id),
    unique index ux_scheduleName(scheduleId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
