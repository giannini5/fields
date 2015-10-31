drop procedure if exists 002_seasoneDateTime;

delimiter $$
create procedure 002_seasoneDateTime()
begin
    if not exists(
        select
            *
        from
            information_schema.COLUMNS
        where
            TABLE_SCHEMA = DATABASE()
            and TABLE_NAME = 'season'
            and COLUMN_NAME = 'startDate')
    then
        alter table season add column startDate date after name;
        alter table season add column endDate date after startDate;
        alter table season add column startTime time after endDate;
        alter table season add column endTime time after startTime;
    end if;
end $$
delimiter ;

call 002_seasoneDateTime();
drop procedure if exists 002_seasoneDateTime;
