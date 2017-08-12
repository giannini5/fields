drop procedure if exists 031_addScheduleGamesToFlight;

delimiter $$
create procedure 031_addScheduleGamesToFlight()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'flight'
          and COLUMN_NAME = 'scheduleGames')
    then
      alter table flight add COLUMN scheduleGames tinyint not NULL default 1 after includeChampionshipGame;
    end if;
  end $$
delimiter ;

call 031_addScheduleGamesToFlight();
drop procedure if exists 031_addScheduleGamesToFlight;
