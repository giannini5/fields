create table coach (
    id           bigint auto_increment,
    teamId       bigint not NULL,
    email        varchar(128) not NULL,
    name         varchar(60) not NULL,
    phone        varchar(128) not NULL,
    password     varchar(10) default '',
    PRIMARY KEY (id),
    unique index ux_teamEmail(teamId, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
