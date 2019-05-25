create table if not exists gameDateReferee (
  id          bigint auto_increment,
  gameDateId  bigint not NULL,
  refereeId   bigint not NULL,

  PRIMARY KEY (id),
  unique index ux_gameDateReferee(gameDateId, refereeId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;