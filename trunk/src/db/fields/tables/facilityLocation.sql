create table facilityLocation (
    id           bigint auto_increment,
    facilityId   bigint not null,
    locationId   bigint not null,
    PRIMARY KEY (id),
    unique index ux_facilityLocation(facilityId, locationId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
