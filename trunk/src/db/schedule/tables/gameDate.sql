create table if not exists gameDate (
    id           bigint auto_increment,
    seasonId     bigint not NULL,
    day          date not NULL,
    PRIMARY KEY (id),
    unique key ux_seasonDay(seasonId, day)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
