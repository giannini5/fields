drop procedure if exists 006_fieldAvailabilityDaysOfWeek;

delimiter $$
create procedure 006_fieldAvailabilityDaysOfWeek()
begin
    if not exists(
        select
            *
        from
            information_schema.COLUMNS
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'fieldAvailability'
            and COLUMN_NAME = 'daysOfWeek')
    then
        alter table fieldAvailability add column daysOfWeek char(8) not NULL default '0111110' after endTime;
    end if;
end $$
delimiter ;

call 006_fieldAvailabilityDaysOfWeek();
drop procedure if exists 006_fieldAvailabilityDaysOfWeek;