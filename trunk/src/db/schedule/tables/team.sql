create table team (
    id           bigint auto_increment,
    divisionId   bigint not NULL,
    poolId       bigint default NULL,
    name         varchar(60) not NULL,
    PRIMARY KEY (id),
    unique key ux_divisionName(divisionId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
