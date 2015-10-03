create table session (
    id           bigint auto_increment,
    creationDate datetime default now(),
    userId       bigint not NULL,
    userType     tinyInt,
    teamId       bigint not NULL,
    PRIMARY KEY (id),
    unique key ux_user (userId, userType, teamId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
