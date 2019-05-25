create table if not exists flight (
    id                        bigint auto_increment,
    scheduleId                bigint not NULL,
    name                      varchar(60) not NULL,
    include5th6thGame         tinyint not NULL default 0,
    include3rd4thGame         tinyint not NULL default 0,
    includeSemiFinalGames     tinyint not NULL default 0,
    includeChampionshipGame   tinyint not NULL default 0,
    scheudleGames             tinyint not NULL default 1,

    PRIMARY KEY (id),
    unique index ux_scheduleName(scheduleId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
