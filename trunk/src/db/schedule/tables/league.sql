create table if not exists league (
    id          bigint auto_increment,
    name        varchar(60) not NULL,
    PRIMARY KEY (id),
    unique index ux_name(name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
