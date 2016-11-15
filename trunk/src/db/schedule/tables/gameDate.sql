create table gameDate (
    id           bigint auto_increment,
    seasonId     bigint not NULL,
    day          date not NULL,
    PRIMARY KEY (id),
    index ix_seasonDay(seasonId, day)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
