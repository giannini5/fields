create table team (
    id           bigint auto_increment,
    divisionId   bigint,
    teamNumber   int,
    name         varchar(60),
    PRIMARY KEY (id),
    unique index ux_divisionTeam(divisionId, teamNumber)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
