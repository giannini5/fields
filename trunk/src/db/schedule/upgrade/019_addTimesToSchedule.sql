drop procedure if exists 019_addTimesToSchedule;

delimiter $$
create procedure 019_addTimesToSchedule()
begin

    if not exists(
        select
            *
        from
            information_schema.columns
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'schedule'
            and COLUMN_NAME = 'startTime')
    then
        alter table schedule add COLUMN startTime time not null after endDate;
        alter table schedule add COLUMN endTime time not null after startTime;
    end if;
end $$
delimiter ;

call 019_addTimesToSchedule();
drop procedure if exists 019_addTimesToSchedule;
