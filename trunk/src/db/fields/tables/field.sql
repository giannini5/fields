create table field (
    id           bigint auto_increment,
    facilityId   bigint,
    name         varchar(60),
    startDate    date,
    endDate      date,
    startTime    time,
    endTime      time,
    enabled      tinyint,
    PRIMARY KEY (id),
    unique index ux_facilityName(facilityId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
