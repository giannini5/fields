create table reservation (
    id           bigint auto_increment,
    fieldId      bigint,
    teamId       bigint,
    startTime    time,
    endTime      time,
    PRIMARY KEY (id),
    index ix_fieldId(fieldId),
    index ix_teamId(teamId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
