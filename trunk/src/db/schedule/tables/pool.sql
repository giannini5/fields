create table pool (
    id                    bigint auto_increment,
    divisionId            bigint not NULL,
    name                  varchar(60) not NULL,
    PRIMARY KEY (id),
    unique index ux_divisionName(divisionId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;