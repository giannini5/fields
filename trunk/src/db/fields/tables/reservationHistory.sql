create table reservationHistory (
    id           bigint auto_increment,
    seasonId     bigint not NULL,
    fieldId      bigint not NULL,
    teamId       bigint not NULL,
    coachId      bigint not NULL,
    startTime    time not NULL,
    endTime      time not NULL,
    daysOfWeek   char(8),
    creationDate datetime not NULL default CURRENT_TIMESTAMP,
    type         char(1) not NULL,
    PRIMARY KEY (id),
    index ix_coachId(seasonId, coachId),
    index ix_fieldId(seasonId, fieldId),
    index ix_teamId(seasonId, teamId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
