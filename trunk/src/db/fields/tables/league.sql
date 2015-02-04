create table league (
    id          bigint auto_increment,
    name        varchar(60),
    PRIMARY KEY (id),
    unique index ux_name(name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
