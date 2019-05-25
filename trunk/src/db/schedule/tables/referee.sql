create table if not exists referee (
  id                  bigint auto_increment,
  seasonId            bigint not NULL,
  familyId            bigint default NULL,
  name                varchar(60) not NULL,
  email               varchar(128) not NULL,
  phone               varchar(128) not NULL,
  badgeId             char not NULL,
  maxGamesPerDay      int not NULL,
  specialInstructions varchar(1028),

  PRIMARY KEY (id),
  unique index ux_leagueEmail(seasonId, email, name),
  index ix_familyId(familyId),
  index ix_name(name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
