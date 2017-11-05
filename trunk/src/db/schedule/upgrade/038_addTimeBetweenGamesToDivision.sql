drop procedure if exists 038_addMinutesBetweenGamesToDivision;

delimiter $$
create procedure 038_addMinutesBetweenGamesToDivision()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'division'
          and COLUMN_NAME = 'minutesBetweenGames')
    then
      alter table division add COLUMN minutesBetweenGames int not NULL default 180 after gameDurationMinutes;
    end if;
  end $$
delimiter ;

call 038_addMinutesBetweenGamesToDivision();
drop procedure if exists 038_addMinutesBetweenGamesToDivision;
