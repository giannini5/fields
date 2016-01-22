create table facility (
    id           bigint auto_increment,
    leagueId     bigint not NULL,
    name         varchar(60) not NULL,
    address1     varchar(128) default '',
    address2     varchar(128) default '',
    city         varchar(128) default '',
    state        varchar(128) default '',
    postalCode   varchar(20) default '',
    country      varchar(128) default '',
    contactName  varchar(128) default '',
    contactEmail varchar(128) default '',
    contactPhone varchar(128) default '',
    image        varchar(128) default '',
    preApproved  tinyint default 1,
    enabled      tinyint default 1,
    PRIMARY KEY (id),
    unique index ux_leagueName(leagueId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
