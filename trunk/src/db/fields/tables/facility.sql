create table facility (
    id           bigint auto_increment,
    leagueId     bigint,
    name         varchar(60),
    address1     varchar(128),
    address2     varchar(128),
    city         varchar(128),
    state        varchar(128),
    postalCode   varchar(20),
    country      varchar(128),
    contactName  varchar(128),
    contactEmail varchar(128),
    contactPhone varchar(128),
    enabled      tinyint,
    PRIMARY KEY (id),
    unique index ux_leagueName(leagueId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
