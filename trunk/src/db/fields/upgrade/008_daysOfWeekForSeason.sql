drop procedure if exists 008_daysOfWeekForSeason;

delimiter $$
create procedure 008_daysOfWeekForSeason()
begin
    if not exists(
        select
            *
        from
            information_schema.COLUMNS
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'season'
            and COLUMN_NAME = 'daysOfWeek')
    then
        alter table season add column daysOfWeek char(8) default '1111100' after endTime;
    end if;
end $$
delimiter ;

call 008_daysOfWeekForSeason();
drop procedure if exists 008_daysOfWeekForSeason;