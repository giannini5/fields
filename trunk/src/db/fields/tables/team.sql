create table team (
    id           bigint auto_increment,
    divisionId   bigint not NULL,
    teamNumber   int not NULL,
    name         varchar(60) not NULL,
    PRIMARY KEY (id),
    unique index ux_divisionTeam(divisionId, teamNumber)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
