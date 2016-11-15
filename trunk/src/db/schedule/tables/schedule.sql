create table schedule (
    id                    bigint auto_increment,
    poolId                bigint not NULL,
    name                  varchar(60) not NULL,
    PRIMARY KEY (id),
    unique index ux_poolName(poolId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
