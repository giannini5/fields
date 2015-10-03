create table team (
    id           bigint auto_increment,
    divisionId   bigint not NULL,
    coachId      bigint not NULL,
    gender       char not NULL,
    name         varchar(60) not NULL,
    PRIMARY KEY (id),
    unique key ux_divisionCoach(divisionId, coachId, gender)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
