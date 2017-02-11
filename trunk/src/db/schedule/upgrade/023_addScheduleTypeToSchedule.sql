drop procedure if exists 023_addScheduleTypeToSchedule;

delimiter $$
create procedure 023_addScheduleTypeToSchedule()
begin

    if not exists(
        select
            *
        from
            information_schema.columns
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'schedule'
            and COLUMN_NAME = 'scheduleType')
    then
        alter table schedule add COLUMN scheduleType char(1) NOT NULL after name;
    end if;
end $$
delimiter ;

call 023_addScheduleTypeToSchedule();
drop procedure if exists 023_addScheduleTypeToSchedule;
