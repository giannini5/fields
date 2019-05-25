create table if not exists family (
    id           bigint auto_increment,
    seasonId     bigint not NULL,
    phone1       varchar(128) not NULL,
    phone2       varchar(128) not NULL default '',
    PRIMARY KEY (id, seasonId, phone1, phone2),
    UNIQUE KEY ux_seasonPhone (seasonId, phone1, phone2)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;