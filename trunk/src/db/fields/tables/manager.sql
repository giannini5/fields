create table manager (
    id           bigint auto_increment,
    teamId       bigint,
    email        varchar(128),
    name         varchar(60),
    phone        varchar(128),
    password     varchar(10),
    PRIMARY KEY (id),
    unique index ux_teamEmail(teamId, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
