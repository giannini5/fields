create table divisionField (
    id          bigint auto_increment,
    divisionId  bigint not null,
    facilityId  bigint not null,
    fieldId     bigint not null,
    PRIMARY KEY (id),
    unique index ux_divisionField(divisionId, facilityId, fieldId),
    index ix_facilityField(facilityId, fieldId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;