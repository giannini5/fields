drop procedure if exists 028_addGameStatsToGame;

delimiter $$
create procedure 028_addGameStatsToGame()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'game'
          and COLUMN_NAME = 'homeTeamScore')
    then
      alter table game add COLUMN homeTeamScore int default NULL after visitingTeamId;
      alter table game add COLUMN visitingTeamScore int default NULL after homeTeamScore;
      alter table game add COLUMN homeTeamYellowCards int default 0 after visitingTeamScore;
      alter table game add COLUMN visitingTeamYellowCards int default 0 after homeTeamYellowCards;
      alter table game add COLUMN homeTeamRedCards int default 0 after visitingTeamYellowCards;
      alter table game add COLUMN visitingTeamRedCards int default 0 after homeTeamRedCards;
      alter table game add COLUMN notes varchar(1024) default '' after visitingTeamRedCards;
    end if;
  end $$
delimiter ;

call 028_addGameStatsToGame();
drop procedure if exists 028_addGameStatsToGame;
