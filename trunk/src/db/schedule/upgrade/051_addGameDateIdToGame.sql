drop procedure if exists 051_addGameDateIdToGame;

delimiter $$
create procedure 051_addGameDateIdToGame()
  begin

    if not exists(
        select
          *
        from
          information_schema.statistics
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'game'
          and index_name = 'ix_scheduleDate')
    then
      -- Add scheduleId after id
      alter table game add COLUMN gameDateId bigint not NULL default 0 after poolId;

      -- Populate scheduleId from associated flight
      update game, gameTime
      set
        game.gameDateId = gameTime.gameDateId
      where
        game.gameTimeId = gameTime.id;

      -- Create index
      create index ix_scheduleDate on game(scheduleId, gameDateId);
    end if;
  end $$
delimiter ;

call 051_addGameDateIdToGame();
drop procedure if exists 051_addGameDateIdToGame;