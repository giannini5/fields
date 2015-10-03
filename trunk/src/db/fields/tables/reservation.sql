create table reservation (
    id           bigint auto_increment,
    seasonId     bigint not NULL,
    fieldId      bigint not NULL,
    teamId       bigint not NULL,
    startTime    time not NULL,
    endTime      time not NULL,
    daysOfWeek   char(8),
    PRIMARY KEY (id),
    index ix_fieldId(seasonId, fieldId),
    index ix_teamId(seasonId, teamId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
