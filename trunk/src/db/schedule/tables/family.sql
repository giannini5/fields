create table family (
    id           bigint auto_increment,
    seasonId     bigint not NULL,
    phone        varchar(128) not NULL,
    PRIMARY KEY (id),
    unique index ux_seasonPhone (seasonId, phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;