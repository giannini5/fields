create table coach (
    id           bigint auto_increment,
    seasonId     bigint not NULL,
    divisionId   bigint not NULL,
    email        varchar(128) not NULL,
    name         varchar(60) not NULL,
    phone        varchar(128) not NULL,
    password     varchar(10) default '',
    PRIMARY KEY (id),
    unique index ux_seasonDivisionEmail(seasonId, divisionId, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
