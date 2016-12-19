create table if not exists divisionField (
    id           bigint auto_increment,
    divisionId   bigint not NULL,
    fieldId      bigint not NULL,
    PRIMARY KEY (id),
    unique index ux_divisionField(divisionId, fieldId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;