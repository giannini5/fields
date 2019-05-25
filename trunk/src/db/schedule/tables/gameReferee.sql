create table if not exists gameReferee (
  id        bigint      auto_increment,
  gameId    bigint      not NULL,
  refereeId bigint      not NULL,
  role      varchar(3)  not NULL,

  PRIMARY KEY (id),
  unique index ux_gameReferee(gameId, refereeId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;