create table if not exists divisionReferee (
  id          bigint auto_increment,
  divisionId  bigint not NULL,
  refereeId   bigint not NULL,
  isCenter    tinyint not NULL,
  isAssistant tinyint not NULL,
  isMentor    tinyint not NULL,

  PRIMARY KEY (id),
  unique index ux_divisionReferee(divisionId, refereeId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;