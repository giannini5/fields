drop procedure if exists 046_addDisplayNotesToSchedule.sql;

delimiter $$
create procedure 046_addDisplayNotesToSchedule()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'schedule'
          and COLUMN_NAME = 'displayNotes')
    then
      alter table schedule add COLUMN displayNotes VARCHAR(4096) after daysOfWeek;
    end if;
  end $$
delimiter ;

call 046_addDisplayNotesToSchedule();
drop procedure if exists 046_addDisplayNotesToSchedule;