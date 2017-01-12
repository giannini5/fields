create table schedule (
    id                    bigint auto_increment,
    divisionId            bigint not NULL,
    name                  varchar(60) not NULL,
    gamesPerTeam          int not NULL,
    startDate             date NOT NULL,
    endDate               date NOT NULL,
    daysOfWeek            char(8) DEFAULT '0000011',
    published             tinyint NOT NULL default 0,

    PRIMARY KEY (id),
    unique index ux_divisionName(divisionId, name),
    index ix_divisionPublished(divisionId, published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
