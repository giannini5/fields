drop procedure if exists 050_addScheduleIdToGame;

delimiter $$
create procedure 050_addScheduleIdToGame()
  begin

    if not exists(
        select
          *
        from
          information_schema.statistics
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'game'
          and index_name = 'ix_schedule')
    then
      -- Add scheduleId after id
      alter table game add COLUMN scheduleId bigint not NULL default 0 after id;

      -- Populate scheduleId from associated flight
      update game, flight
          set
              game.scheduleId = flight.scheduleId
          where
              game.flightId = flight.id;

      -- Create index
      create index ix_schedule on game(scheduleId);
    end if;
  end $$
delimiter ;

call 050_addScheduleIdToGame();
drop procedure if exists 050_addScheduleIdToGame;