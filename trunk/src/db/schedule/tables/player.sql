create table player (
    id           bigint auto_increment,
    teamId       bigint not NULL,
    familyId     bigint default NULL,
    name         varchar(60) not NULL,
    email        varchar(128) not NULL,
    phone        varchar(128) not NULL,
    PRIMARY KEY (id),
    unique index ux_teamNameEmail(teamId, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;