create table if not exists refereeCrew (
  id                  bigint auto_increment,
  centerRefereeId     bigint not NULL,
  assistantReferee1Id bigint not NULL,
  assistantReferee2Id bigint not NULL,
  divisionId          bigint not NULL,
  teamId              bigint not NULL default 0,

  PRIMARY KEY (id),
  unique index ux_teamReferee(divisionId, centerRefereeId, assistantReferee1Id, assistantReferee2Id, teamId),
  index ix_team(teamId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;