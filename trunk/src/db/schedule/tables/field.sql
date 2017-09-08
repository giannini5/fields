create table field (
    id                  bigint auto_increment,
    facilityId          bigint not NULL,
    name                varchar(60) not NULL,
    enabled             tinyint default 1,
    thirdPartyFieldId   int default 0,
    PRIMARY KEY (id),
    unique index ux_facilityName(facilityId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;