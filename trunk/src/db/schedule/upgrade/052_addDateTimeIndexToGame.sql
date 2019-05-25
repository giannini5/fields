drop procedure if exists 052_addDateTeamIndexToGame;

delimiter $$
create procedure 052_addDateTeamIndexToGame()
  begin

    if not exists(
        select
          *
        from
          information_schema.statistics
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'game'
          and index_name = 'ix_dateHomeTeamId')
    then
      -- Create index
      create index ix_dateHomeTeamId on game(gameDateId, homeTeamId);
      create index ix_dateVisitingTeamId on game(gameDateId, visitingTeamId);
    end if;
  end $$
delimiter ;

call 052_addDateTeamIndexToGame();
drop procedure if exists 052_addDateTeamIndexToGame;