create table fieldAvailability (
    id           bigint auto_increment,
    fieldId      bigint not NULL,
    startDate    date not NULL,
    endDate      date not NULL,
    startTime    time not NULL,
    endTime      time not NULL,
    daysOfWeek   char(8),
    PRIMARY KEY (id),
    unique index ux_fieldId(fieldId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
