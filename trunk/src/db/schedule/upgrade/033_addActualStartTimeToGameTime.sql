drop procedure if exists 033_addActualStartTimeToGameTime;

delimiter $$
create procedure 033_addActualStartTimeToGameTime()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'gameTime'
          and COLUMN_NAME = 'actualStartTime')
    then
      alter table gameTime add COLUMN actualStartTime time default null after startTime;
    end if;
  end $$
delimiter ;

call 033_addActualStartTimeToGameTime();
drop procedure if exists 033_addActualStartTimeToGameTime;
