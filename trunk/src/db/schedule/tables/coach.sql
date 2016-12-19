create table coach (
    id           bigint auto_increment,
    teamId       bigint not NULL,
    familyId     bigint default NULL,
    name         varchar(60) not NULL,
    email        varchar(128) not NULL,
    phone1       varchar(128) not NULL,
    phone2       varchar(128) not NULL,
    PRIMARY KEY (id),
    unique index ux_teamName(teamId),
    index ix_familyId(familyId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
