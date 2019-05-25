create table if not exists team (
    id              bigint auto_increment,
    divisionId      bigint not NULL,
    poolId          bigint default NULL,
    name            varchar(60) not NULL,
    nameId          varchar(10) not NULL,
    color           varchar(24) not NULL default '',
    region          varchar(60) not NULL default '',
    city            varchar(60) not NULL default '',
    volunteerPoints int         not NULL default 0,
    seed            int         not NULL default 0,
    PRIMARY KEY (id),
    unique key ux_divisionNameIdName(divisionId, nameId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
