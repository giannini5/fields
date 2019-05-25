create table if not exists teamReferee (
  id        bigint auto_increment,
  teamId    bigint not NULL,
  refereeId bigint not NULL,

  PRIMARY KEY (id),
  unique index ux_teamReferee(teamId, refereeId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;